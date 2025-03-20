import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, SelectControl, Spinner } from '@wordpress/components';
import { useState, useEffect } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import './editor.scss';

export default function Edit({ attributes, setAttributes }) {
    const [areas, setAreas] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        apiFetch({ path: '/wp/v2/area?per_page=100' }) // Fetch taxonomy terms
            .then((data) => {
                const options = data.map((term) => ({
                    label: term.name,
                    value: term.id.toString(),
                }));
                setAreas(options);
                setLoading(false);
            })
            .catch(() => setLoading(false));
    }, []);

    return (
        <>
            <InspectorControls>
                <PanelBody title={__('Block Settings', 'degree-search-widget')} initialOpen={true}>
                    {loading ? (
                        <Spinner />
                    ) : (
                        <SelectControl
                            label={__('Select an Area of Study', 'degree-search-widget')}
                            value={attributes.areaOfStudy}
                            options={[{ label: __('Select an Area', 'degree-search-widget'), value: '' }, ...areas]}
                            onChange={(newValue) => setAttributes({ areaOfStudy: newValue })}
                        />
                    )}
                </PanelBody>
            </InspectorControls>

            <section {...useBlockProps()} className="areasContainer alignfull" id="filters">
                <p>{attributes.areaOfStudy ? `Selected Area: ${areas.find(a => a.value === attributes.areaOfStudy)?.label}` : __('Your widget here.', 'degree-search-widget')}</p>
            </section>
        </>
    );
}
