import apiFetch from '@wordpress/api-fetch';
import React, { useState, useEffect, useRef } from 'react';
import { createRoot } from 'react-dom/client';
import Placeholder from 'react-bootstrap/Placeholder';
import Form from 'react-bootstrap/Form';
import OverlayTrigger from 'react-bootstrap/OverlayTrigger';
import Tooltip from 'react-bootstrap/Tooltip';

export default function View() {
    const [programs, setPrograms] = useState([]);
    const [resultsPerPage] = useState(20);
    const [pagesLoaded, setPagesLoaded] = useState(1);
    const [totalPages, setTotalPages] = useState(1);
    const [isLoading, setIsLoading] = useState(true);
    const [isBackToVisible, setIsBackToVisible] = useState(false);
    const [isSticky, setIsSticky] = useState(false);
    const [loadingMore, setLoadingMore] = useState(false);
    const observer = useRef(null);
    const fetchController = useRef(null);
    const searchTimeout = useRef(null);
    const stickyEl = useRef(null);

    const [searchTerm, setSearchTerm] = useState(new URLSearchParams(window.location.search).get('search') || '');
    const [degreeTypeFilter, setDegreeTypeFilter] = useState(new URLSearchParams(window.location.search).get('degree_type') || '');
    const [areaFilter, setAreaFilter] = useState(new URLSearchParams(window.location.search).get('area') || '');
    const [areaFilterName, setAreaFilterName] = useState('');
    const [collegeFilter, setCollegeFilter] = useState(new URLSearchParams(window.location.search).get('college') || '');
    const [collegeFilterName, setCollegeFilterName] = useState('');
    const [onlineFilter, setOnlineFilter] = useState(new URLSearchParams(window.location.search).get('online') || '');
    const [areaMap, setAreaMap] = useState([]);
    const [collegeMap, setCollegeMap] = useState([]);

    // Possibly replace with default Bootstrap from UTKWDS
    const TooltipEl = ({ id, children, title }) => (
        <OverlayTrigger overlay={<Tooltip id={id}>{title}</Tooltip>}>
            <span>{children}</span>
        </OverlayTrigger>
    );

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
        if (match && match.name) {
            setAreaFilterName(match.name);
        }

    }, [areaFilter, areaMap]);

    // Match college ID with name for filter chips
    useEffect(() => {
        const match = collegeMap.find(obj => obj.id === parseInt(collegeFilter));
        if (match && match.name) {
            setCollegeFilterName(match.name);
        }

    }, [collegeFilter, collegeMap]);

    // Show back to top element
    useEffect(() => {
        const toggleVisibility = () => {
          setIsBackToVisible(window.scrollY > 1200);
        };
        
        window.addEventListener("scroll", toggleVisibility);
        return () => window.removeEventListener("scroll", toggleVisibility);
      }, []);
    
      const scrollToElement = () => {
        const element = document.getElementById("filters");
        if (element) {
            element.scrollIntoView({ behavior: "smooth" });
        }
    };

    // set class on sticky filters
    useEffect(() => {
        const handleScroll = () => {
            if (stickyEl.current) {
                const rect = stickyEl.current.getBoundingClientRect();
                let offset = 0;
                
                if (document.body.classList.contains("admin-bar")) {
                    offset = 32;
                }
                
                if (rect.top <= offset) {
                    setIsSticky(true);
                } else {
                    setIsSticky(false);
                }
            }
        };

        window.addEventListener("scroll", handleScroll);
        return () => window.removeEventListener("scroll", handleScroll);
    }, []);

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
                    <div key={index} className="program-entry">
                        {Array.from({ length: 3 }).map((_, subIndex) => (
                            <ul key={subIndex} className="placeholder-list"><Placeholder as="li" animation="glow"><Placeholder xs={12} size="lg" /></Placeholder></ul>
                        ))}
                    </div>
                ))}
            </>
        );
    };

    const CloseIcon = () => {
        return (
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M5.354 4.646a.5.5 0 1 0-.708.708L7.293 8l-2.647 2.646a.5.5 0 0 0 .708.708L8 8.707l2.646 2.647a.5.5 0 0 0 .708-.708L8.707 8l2.647-2.646a.5.5 0 0 0-.708-.708L8 7.293z" />
            </svg>
        );
    };

    const InfoIcon = () => {
        return (
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16m.93-9.412-1 4.705c-.07.34.029.533.304.533.194 0 .487-.07.686-.246l-.088.416c-.287.346-.92.598-1.465.598-.703 0-1.002-.422-.808-1.319l.738-3.468c.064-.293.006-.399-.287-.47l-.451-.081.082-.381 2.29-.287zM8 5.5a1 1 0 1 1 0-2 1 1 0 0 1 0 2"/>
            </svg>
        );
    };

    const ChevronUpIcon = () => {
        return (
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                <path fill-rule="evenodd" d="M7.646 4.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1-.708.708L8 5.707l-5.646 5.647a.5.5 0 0 1-.708-.708z"/>
            </svg>
        );
    };

    return (
        <>
            <div className="wp-block-block alignfull utkwds-orange-bar-texture has-orange-background-color has-background" />
            <section className="programs-container wp-block-group alignfull has-global-padding is-layout-constrained wp-block-group-is-layout-constrained">
                <div className="programs-filters alignwide">
                    <div className="programs-filters-fields">
                        <div className="programs-filters-field">
                            <div class="form-floating">
                                <input className='form-control' aria-label="Program Search" id="program-search" name="search" type="search" value={searchTerm} onChange={(e) => handleFilterChange('search', e.target.value, setSearchTerm)} placeholder="Find a program" />
                                <label for="program-search">Find a Program</label>
                            </div>
                        </div>
                        <div className="programs-filters-field">
                            <div class="form-floating">
                                <select name="degree-type" className="form-select" id="degree-type" aria-label="Degree Type" onChange={(e) => handleFilterChange('degree_type', e.target.value, setDegreeTypeFilter)}>
                                    <option value="">Select a Degree</option>
                                    <option aria-label="option" value="Undergraduate" selected={degreeTypeFilter === 'Undergraduate' ? true : false}>Undergraduate</option>
                                    <option aria-label="option" value="Graduate" selected={degreeTypeFilter === 'Graduate' ? true : false}>Graduate</option>
                                    <option aria-label="option" value="Undergraduate Certificate" selected={degreeTypeFilter === 'Undergraduate Certificate' ? true : false}>Undergraduate Certificate</option>
                                    <option aria-label="option" value="Graduate Certificate" selected={degreeTypeFilter === 'Graduate Certificate' ? true : false}>Graduate Certificate</option>
                                </select>
                                <label for="degree-type">Degree Type</label>
                            </div>
                        </div>
                        <div className="programs-filters-field">
                            <div class="form-floating">
                                <select name="area" className="form-select" id="area-of-study" aria-label="Area of Study" onChange={(e) => handleFilterChange('area', e.target.value, setAreaFilter)}>
                                    <option value="">Select an Area of Study</option>
                                    {areaMap.map((area) => (
                                        <option key={area.id} aria-label="option" value={area.id} selected={areaFilter == area.id ? true : false}>{area.name}</option>
                                    ))}
                                </select>
                                <label for="area-of-study">Area of Study</label>
                            </div>
                        </div>
                        <div className="programs-filters-field">
                            <div class="form-floating">
                                <select name="college" className="form-select" id="college" aria-label="College" onChange={(e) => handleFilterChange('college', e.target.value, setCollegeFilter)}>
                                    <option value="">Select a College</option>
                                    {collegeMap.map((college) => (
                                        <option key={college.id} aria-label="option" value={college.id} selected={collegeFilter == college.id ? true : false}>{college.name}</option>
                                    ))}
                                </select>
                                <label for="college">College</label>
                            </div>
                        </div>
                        <div className="programs-filters-field">
                            <Form.Check
                                type="switch"
                                id="custom-switch"
                                label="Online"
                                checked={onlineFilter === 'true'} // Ensure it reflects the current state
                                onChange={() => handleFilterChange('online', onlineFilter ? '' : 'true', setOnlineFilter)}
                            />
                        </div>
                    </div>
                    <div
                        ref={stickyEl}
                        className={`programs-filters-sticky${isSticky ? " programs-filters-sticky--stuck" : ""}`}
                    >
                        {(searchTerm.length > 0 || degreeTypeFilter.length > 0 || areaFilter.length > 0 || collegeFilter.length > 0 || onlineFilter.length > 0) && (
                            <div className="programs-filters-chips">
                                {searchTerm.length > 0 && (
                                    <div className="programs-filters-chip" onClick={() => handleFilterChange('search', '', setSearchTerm)}>
                                        {searchTerm} <CloseIcon />
                                    </div>
                                )}
                                {degreeTypeFilter.length > 0 && (
                                    <div className="programs-filters-chip" onClick={() => handleFilterChange('degree_type', '', setDegreeTypeFilter)}>
                                        {degreeTypeFilter} <CloseIcon />
                                    </div>
                                )}
                                {areaFilter.length > 0 && (
                                    <div className="programs-filters-chip" onClick={() => handleFilterChange('area', '', setAreaFilter)}>
                                        {areaFilterName} <CloseIcon />
                                    </div>
                                )}
                                {collegeFilter.length > 0 && (
                                    <div className="programs-filters-chip" onClick={() => handleFilterChange('college', '', setCollegeFilter)}>
                                        {collegeFilterName} <CloseIcon />
                                    </div>
                                )}
                                {onlineFilter.length > 0 && (
                                    <div className="programs-filters-chip" onClick={() => handleFilterChange('online', '', setOnlineFilter)}>
                                        Online <CloseIcon />
                                    </div>
                                )}
                            </div>
                        )}
                        <div className="programs-filters-headings">
                            <h2 className="programs-filters-heading">Program</h2>
                            <h2 className="programs-filters-heading">Degree / Certificate</h2>
                            <h2 className="programs-filters-heading">
                                Concentration
                                <TooltipEl title="Tooltip text goes here. It can be multiple lines long if it has to be." id="t-1">
                                    <InfoIcon />
                                </TooltipEl>
                            </h2>
                        </div>
                    </div>
                    <div className="programs-filters-results" id="program-results">
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
                                    <div key={program.id} className="program-entry">
                                        <div className="program-entry-block">
                                            <p className="program-entry-label">Program</p>
                                            <p className="program-entry-text program-entry-text--bold">{program.major}</p>
                                        </div>
                                        <div className="program-entry-block">
                                            <p className="program-entry-label">Degree / Certificate</p>
                                            <ul className="degree-list">
                                                {program.degrees.map((degree, index) => (
                                                    <li key={index} className="program-entry-text program-entry-text--bold">
                                                        {degree.name}
                                                        {/* <a href={degree.url} target="_blank" rel="noopener noreferrer">
                                                            {degree.name}
                                                        </a> */}
                                                    </li>
                                                ))}
                                            </ul>
                                        </div>
                                        {program.concentrations.length > 0 && (
                                            <div className="program-entry-block">
                                                <p className="program-entry-label">Concentrations</p>
                                                <ul className="concentration-list">
                                                    {program.concentrations.map((concentration, index) => (
                                                        <li key={index} className="program-entry-text">
                                                            {concentration.name}{' '}
                                                            {concentration.online && (
                                                                <span className="online-tag">Online</span>
                                                            )}
                                                        </li>
                                                    ))}
                                                </ul>
                                            </div>
                                        )}
                                    </div>
                                ))}
                            </>
                        )}
                        <div id="load-more-trigger"></div>
                        <div style={{ display: "none" }}></div>
                        {loadingMore && displayPlaceholders(5)}
                        {isBackToVisible && (
                            <button className="programs-back-to-element" onClick={scrollToElement}>
                                <ChevronUpIcon />
                            </button>
                        )}
                    </div>
                </div>
            </section>
        </>
    );
}

const root = createRoot(document.getElementById('filters'));
root.render(<View />);
