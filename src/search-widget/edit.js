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
        <div {...useBlockProps()}>
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

            <div id="degree-search-widget" className="programs-search-container">
                <div className="programs-search-form">
                    <div className="programs-search-wrap">
                        <div className="form-floating">
                            <input placeholder="..." type="search" id="floatingInput" className="form-control" value="" />
                            <label for="floatingInput">Find your program</label>
                        </div>
                        <button type="button" className="wp-element-button button-submit">Search</button>
                    </div>
                </div>
                <div className="programs-search-quick-links">
                    <ul className="programs-search-quick-links-list">
                        <li><a href="#">Undergraduate</a></li>
                        <li><a href="#">Graduate</a></li>
                        <li><a href="#">Online</a></li>
                        <li><a href="#">Certificate</a></li>
                        <li><a href="#">All programs</a></li>
                    </ul>
                </div>
                <div className='search-widget-info'>
					<div className={`notice ${attributes.areaOfStudy ? 'notice-success' : 'notice-warning'}`}>
						<p><strong>
                        {attributes.areaOfStudy
                            ? `Selected Area of Study: ${areas.find(a => a.value === attributes.areaOfStudy)?.label}`
                            : __('Searching all Areas of Study', 'degree-search-widget')}
                    	</strong></p>
					</div>
					<div className={`notice ${attributes.degreeSearchPage ? 'notice-success' : 'notice-error'}`}>
						<p><strong>
							{attributes.degreeSearchPage
								? `Search Page: ${pages.find((p) => p.value === attributes.degreeSearchPage)?.label}`
								: __('Please choose a Degree Search Page', 'degree-search-widget')}
						</strong></p>
					</div>
                </div>
            </div>
        </div>
    );
}