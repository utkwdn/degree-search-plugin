import apiFetch from '@wordpress/api-fetch';
import React, { useState, useEffect, useRef } from 'react';
import { createRoot } from 'react-dom/client';
import Placeholder from 'react-bootstrap/Placeholder';
import Form from 'react-bootstrap/Form';

export default function View() {
    const [programs, setPrograms] = useState([]);
    const [resultsPerPage] = useState(20);
    const [pagesLoaded, setPagesLoaded] = useState(1);
    const [totalPages, setTotalPages] = useState(1);
    const [isLoading, setIsLoading] = useState(true);
    const [loadingMore, setLoadingMore] = useState(false);
    const observer = useRef(null);
    const fetchController = useRef(null);
    const searchTimeout = useRef(null);

    const [searchTerm, setSearchTerm] = useState(new URLSearchParams(window.location.search).get('search') || '');
    const [degreeTypeFilter, setDegreeTypeFilter] = useState(new URLSearchParams(window.location.search).get('degree_type') || '');
    const [areaFilter, setAreaFilter] = useState(new URLSearchParams(window.location.search).get('area') || '');
    const [areaFilterName, setAreaFilterName] = useState('');
    const [collegeFilter, setCollegeFilter] = useState(new URLSearchParams(window.location.search).get('college') || '');
    const [collegeFilterName, setCollegeFilterName] = useState('');
    const [onlineFilter, setOnlineFilter] = useState(new URLSearchParams(window.location.search).get('online') || '');
    const [areaMap, setAreaMap] = useState([]);
    const [collegeMap, setCollegeMap] = useState([]);

    const fetchPrograms = (page = 1, append = false, signal) => {
        setIsLoading(page === 1);
        setLoadingMore(page > 1);

        const baseURL = `/wp/v2/program?_embed&orderby=title&order=asc&per_page=${resultsPerPage}&page=${page}`;
        const urlParams = new URLSearchParams(window.location.search);

        // Remove final 's' from search term ("Communications" will also match "Communication")
        const searchTerm = urlParams.get('search');
        if (searchTerm && searchTerm.length > 1 && searchTerm.endsWith('s')) {
            urlParams.set('search', searchTerm.slice(0, -1)); // Remove final 's'
        }

        const params = urlParams.toString();

        apiFetch({ path: `${baseURL}&${params}`, signal, parse: false }) // Use parse: false to access headers
            .then((response) => {
                const totalPages = Number(response.headers.get('X-WP-TotalPages')) || 1;

                return response.json().then((data) => ({
                    data,
                    totalPages,
                }));
            })
            .then(({ data, totalPages }) => {
                setTotalPages(totalPages); // Save the total pages in state

                const formattedPrograms = data.map((program) => {
                    // Extract Major Name
                    const major = program.title.rendered;
    
                    // Extract Degrees
                    const degrees = program._embedded?.["wp:term"]
                        ?.flat()
                        .filter(term => term.taxonomy === "degree")
                        .map(degree => ({
                            id: degree.id,
                            name: degree.name,
                            type: degree.acf?.["degree_type"] || "",
                            url: program.acf?.["program-url"] || ""
                        })) || [];
    
                    // Extract Concentrations
                    const concentrations = program._embedded?.["wp:term"]
                        ?.flat()
                        .filter(term => term.taxonomy === "concentration")
                        .map(concentration => ({
                            id: concentration.id,
                            name: concentration.name.replace(' (Online)', ''),
                            online: concentration.acf?.online ?? false
                        })) || [];
    
                    return {
                        id: program.id,
                        major,
                        degrees,
                        concentrations,
                    };
                    
                });
                setPrograms(prev => append ? [...prev, ...formattedPrograms] : formattedPrograms);
            })
            .catch((error) => {
                if (error.name !== 'AbortError') {
                    console.error('Error fetching programs:', error);
                }
            })
            .finally(() => {
                setIsLoading(false);
                setLoadingMore(false);
            });
    };

    // Fetch data on initial page load or filter/search changes
    useEffect(() => {
        if (searchTimeout.current) {
            clearTimeout(searchTimeout.current);
        }

        // Allow 500ms for user to finish typing before performing search
        searchTimeout.current = setTimeout(() => {
            // Cancel any incomplete fetch requests when new filter/search is initiated
            if (fetchController.current) {
                fetchController.current.abort(); // Cancel previous fetch
            }
            fetchController.current = new AbortController(); // Create new controller
            const { signal } = fetchController.current; // Get the signal for the fetch request

            setPagesLoaded(1);
            fetchPrograms(1, false, signal);
            
        }, 500);

        return () => {
            // Cleanup searchTimeouot
            if (searchTimeout.current) {
                clearTimeout(searchTimeout.current);
            }

            // Abort fetch on dependency change
            fetchController.current?.abort(); 
        };
    }, [searchTerm, degreeTypeFilter, areaFilter, collegeFilter, onlineFilter]);

    // Trigger fetch to load additional data when user reaches end of the list
    useEffect(() => {
        observer.current = new IntersectionObserver(entries => {
            if (entries[0].isIntersecting && !isLoading && !loadingMore) {
                setPagesLoaded(prev => {
                    const nextPage = prev + 1;

                    if (nextPage > totalPages) {
                        return prev; // Stop fetching if we've reached the last page
                    }

                    fetchPrograms(nextPage, true, fetchController.current?.signal);
                    return nextPage;
                });
            }
        });

        const observerTarget = document.getElementById('load-more-trigger');
        if (observerTarget) observer.current.observe(observerTarget);

        return () => observer.current.disconnect();
    }, [isLoading, loadingMore, totalPages]);

    // Fetch area and college data to populate select boxes
    useEffect(() => {
        // Fetch areas of study via API and save to state
        apiFetch({ path: '/wp/v2/area?per_page=50' })
            .then((data) => {
                if (Array.isArray(data)) {
                    const formattedAreas = data.map(area => ({
                        name: area.name, 
                        id: area.id, 
                    }));
                    setAreaMap(formattedAreas);
                } else {
                    console.error('Unexpected data format:', data);
                }
            })
            .catch((error) => console.error('Error fetching areas:', error));

        // Fetch colleges via API and save to state
        apiFetch({ path: '/wp/v2/college?per_page=50' })
            .then((data) => {
                if (Array.isArray(data)) {
                    const formattedColleges = data.map(college => ({
                        name: college.name, 
                        id: college.id, 
                    }));
                    setCollegeMap(formattedColleges);
                } else {
                    console.error('Unexpected data format:', data);
                }
            })
            .catch((error) => console.error('Error fetching colleges:', error));

    }, []);

    // Match area ID with name for filter chips
    useEffect(() => {
        const match = areaMap.find(obj => obj.id === parseInt(areaFilter));
        if(match && match.name) {
            setAreaFilterName(match.name);
        }

    }, [areaFilter, areaMap]);

    // Match college ID with name for filter chips
    useEffect(() => {
        const match = collegeMap.find(obj => obj.id === parseInt(collegeFilter));
        if(match && match.name) {
            setCollegeFilterName(match.name);
        }

    }, [collegeFilter, collegeMap]);

    const handleFilterChange = (key, value, setter) => {
        // Update filters for fetch and display
        setter(value);

        updateURLParams(key, value);
    };

    const updateURLParams = (key, value) => {
        const params = new URLSearchParams(window.location.search);
        if (value) {
            params.set(key, value);
        } else {
            params.delete(key);
        }
        const seperator = params.size > 0 ? '?' : '';
        window.history.replaceState({}, '', `${window.location.pathname}${seperator}${params.toString()}`);
    };

    const displayPlaceholders = (numItems = 3) => {
        return (
            <>
                {Array.from({ length: numItems }).map((_, index) => (
                    <li key={index} className="programEntry">
                        {Array.from({ length: 3 }).map((_, subIndex) => (
                            <ol key={subIndex} className="concentrationList"><Placeholder as="li" animation="glow"><Placeholder xs={12} size="lg" /></Placeholder></ol>
                        ))}
                    </li>
                ))}
            </>
        );
    };

    return (
        <section className="areasContainer alignfull" id="filters">
            <div id="filters-form">
                <section className="searchNavContainer">
                    <div className="filters">
                        <div className="filter">
                            <div class="form-floating">
                                <input className='form-control' aria-label="Program Search" id="program-search" name="search" type="search" value={searchTerm} onChange={(e) => handleFilterChange('search', e.target.value, setSearchTerm)} placeholder="Find a program" />
                                <label for="program-search">Find a Program</label>
                            </div>
                        </div>
                        <div className="filter">
                            <div class="form-floating">
                                <select name="degree-type" className="form-select" id="degree-type" aria-label="Degree Type" onChange={(e) => handleFilterChange('degree_type', e.target.value, setDegreeTypeFilter)}>
                                    <option value="">Degree type</option>
                                    <option aria-label="option" value="Undergraduate" selected={degreeTypeFilter === 'Undergraduate' ? true : false}>Undergraduate</option>
                                    <option aria-label="option" value="Graduate" selected={degreeTypeFilter === 'Graduate' ? true : false}>Graduate</option>
                                    <option aria-label="option" value="Undergraduate Certificate" selected={degreeTypeFilter === 'Undergraduate Certificate' ? true : false}>Undergraduate Certificate</option>
                                    <option aria-label="option" value="Graduate Certificate" selected={degreeTypeFilter === 'Graduate Certificate' ? true : false}>Graduate Certificate</option>
                                </select>
                                <label for="degree-type">Degree Type</label>
                            </div>
                        </div>
                        <div className="filter">
                            <div class="form-floating">
                                <select name="area" className="form-select" id="area-of-study" aria-label="Area of Study" onChange={(e) => handleFilterChange('area', e.target.value, setAreaFilter)}>
                                    <option value="">Area of study</option>
                                    {areaMap.map((area) => (
                                        <option key={area.id} aria-label="option" value={area.id} selected={areaFilter == area.id ? true : false}>{area.name}</option>
                                    ))}
                                </select>
                                <label for="area-of-study">Area of Study</label>
                            </div>
                        </div>
                        <div className="filter">
                            <div class="form-floating">
                                <select name="college" className="form-select" id="college" aria-label="College" onChange={(e) => handleFilterChange('college', e.target.value, setCollegeFilter)}>
                                    <option value="">College</option>
                                    {collegeMap.map((college) => (
                                        <option key={college.id} aria-label="option" value={college.id} selected={collegeFilter == college.id ? true : false}>{college.name}</option>
                                    ))}
                                </select>
                                <label for="college">College</label>
                            </div>
                        </div>
                        <div className="filter">
                            <Form.Check
                                type="switch"
                                id="custom-switch"
                                label="Online"
                                checked={onlineFilter === 'true'} // Ensure it reflects the current state
                                onChange={() => handleFilterChange('online', onlineFilter ? '' : 'true', setOnlineFilter)} 
                            />
                        </div>
                    </div>
                    <section className="filtersSection"></section>
                </section>
            </div>
            <div id="filterChips">
                {searchTerm.length > 0 ? <div className='filterChip' onClick={() => handleFilterChange('search', '', setSearchTerm)}>{searchTerm} <span>x</span></div> : ''}
                {degreeTypeFilter.length > 0 ? <div className='filterChip' onClick={() => handleFilterChange('degree_type', '', setDegreeTypeFilter)}>{degreeTypeFilter} <span>x</span></div> : ''}
                {areaFilter.length > 0 ? <div className='filterChip' onClick={() => handleFilterChange('area', '', setAreaFilter)}>{areaFilterName} <span>x</span></div> : ''}
                {collegeFilter.length > 0 ? <div className='filterChip' onClick={() => handleFilterChange('college', '', setCollegeFilter)}>{collegeFilterName} <span>x</span></div> : ''}
                {onlineFilter.length > 0 ? <div className='filterChip' onClick={() => handleFilterChange('online', '', setOnlineFilter)}>Online<span>x</span></div> : ''}
            </div>
            <section className="resultsSection">
                
                    <ol className="programGrid" id="program-results">
                        <li className="labelContainer">
                            <h2 className="programLabel">Program</h2>
                            <h2 className="programLabel">Degree / Certificate</h2>
                            <h2 className="programLabel">
                                Concentration
                                <span className="toolTip" tabIndex="0">
                                    <span className="messageConcentratation">
                                        <p>Some programs may not offer concentrations while others may require them.</p>
                                    </span>
                                </span>
                            </h2>
                        </li>
                        {isLoading ? (
                            displayPlaceholders(7)
                        ) : programs.length === 0 && !isLoading ? (
                            <div id="no-results">
                                <h2>There are no matches for your search.</h2>
                                <p>Try searching again with different terms.</p>
                            </div>
                        ) : (
                            <>
                            {programs.map((program) => (
                                <li key={program.id} className="programEntry">
                                    <div className="programName">
                                        <p>{program.major}</p>
                                    </div>
                                    <ol className="degreeList">
                                        {program.degrees.map((degree, index) => (
                                            <li key={index}>
                                                {degree.name}
                                                {/* <a href={degree.url} target="_blank" rel="noopener noreferrer">
                                                    {degree.name}
                                                </a> */}
                                            </li>
                                        ))}
                                    </ol>
                                    <ol className="concentrationList">
                                        {program.concentrations.map((concentration, index) => (
                                            <li key={index}>
                                                {concentration.name}{' '}
                                                {concentration.online && (
                                                    <span className="onlineTag">Online</span>
                                                )}
                                            </li>
                                        ))}
                                    </ol>
                                </li>
                            ))}
                            </>
                        )}
                        <div id="load-more-trigger" style={{ gridColumn: '1 / 4' }}></div>
                        <div style={{ display: "none" }}></div>
                        {loadingMore && displayPlaceholders(5) }
                    </ol>
            </section>
        </section>
    );
}

const root = createRoot(document.getElementById('filters'));
root.render(<View />);
