import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, SelectControl, Spinner } from '@wordpress/components';
import { useState, useEffect } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import './editor.scss';

export default function Edit({ attributes, setAttributes }) {
    const [areas, setAreas] = useState([]);
    const [pages, setPages] = useState([]);
    const [loadingAreas, setLoadingAreas] = useState(true);
    const [loadingPages, setLoadingPages] = useState(true);

    useEffect(() => {
        // Fetch areas of study
        apiFetch({ path: '/wp/v2/area?per_page=100' })
            .then((data) => {
                const options = data.map((term) => ({
                    label: term.name,
                    value: term.id.toString(),
                }));
                setAreas(options);
                setLoadingAreas(false);
            })
            .catch(() => setLoadingAreas(false));

        // Fetch site pages
        apiFetch({ path: '/wp/v2/pages?per_page=100' })
            .then((data) => {
                const pageOptions = data.map((page) => ({
                    label: page.title.rendered,
                    value: page.id.toString(),
                }));
                setPages(pageOptions);
                setLoadingPages(false);
            })
            .catch(() => setLoadingPages(false));
    }, []);

    return (
        <>
            <InspectorControls>
                <PanelBody title={__('Block Settings', 'degree-search-widget')} initialOpen={true}>
                    {loadingAreas ? (
                        <Spinner />
                    ) : (
                        <SelectControl
                            label={__('Select an Area of Study', 'degree-search-widget')}
                            value={attributes.areaOfStudy}
                            options={[{ label: __('All Areas of Study', 'degree-search-widget'), value: '' }, ...areas]}
                            onChange={(newValue) => setAttributes({ areaOfStudy: newValue })}
                        />
                    )}

                    {loadingPages ? (
                        <Spinner />
                    ) : (
                        <SelectControl
                            label={__('Degree Search Page', 'degree-search-widget')}
                            value={attributes.degreeSearchPage}
                            options={[{ label: __('Select a Page', 'degree-search-widget'), value: '' }, ...pages]}
                            onChange={(newPage) => setAttributes({ degreeSearchPage: newPage })}
                        />
                    )}
                </PanelBody>
            </InspectorControls>

            <section {...useBlockProps()} className="areasContainer alignfull" id="search-widget">
				<div>
					<section className="searchContainer">
						<form className="">
							<div className="mb-3 input-group">
								<div className="form-floating">
									<input type="text" id="floatingInput" className="form-control" value="" />
									<label for="floatingInput">Find your program</label>
								</div>
								<button type="submit" id="button-addon2" className="btn btn-outline-secondary">Search</button>
							</div>
						</form>
					</section>
					<div>
						<ul className="linkList">
							<li><a href="http://plugins.local/degree-search/?degree_type=Undergraduate">Undergraduate</a></li>
							<li><a href="http://plugins.local/degree-search/?degree_type=Graduate">Graduate</a></li>
							<li><a href="http://plugins.local/degree-search/?online=true">Online</a></li>
							<li><a href="http://plugins.local/degree-search/?degree_type=certificate">Certificate</a></li>
							<li><a href="http://plugins.local/degree-search/">All programs</a></li>
						</ul>
					</div>
				</div>
				<div style={{display:"flex", justifyContent: "space-evenly", fontSize: "medium", paddingTop: "20px", color: "#ff8200"}}>
					<p>{attributes.areaOfStudy ? `Selected Area of Study: ${areas.find(a => a.value === attributes.areaOfStudy)?.label}` : __('Searching all AOS', 'degree-search-widget')}</p>
					{attributes.degreeSearchPage && (
						<p>Search Page: {pages.find((p) => p.value === attributes.degreeSearchPage)?.label}</p>
					)}
				</div>
            </section>
			
        </>
    );
}