import React, { useState } from 'react';
import { createRoot } from 'react-dom/client';

import FloatingLabel from 'react-bootstrap/FloatingLabel';
import Form from 'react-bootstrap/Form';
import Button from 'react-bootstrap/Button';

export default function View({ areaOfStudy, degreeSearchUrl }) {
    const [searchValue, setSearchValue] = useState('');

    // Generate links with query parameters
    const generateLink = (base, params) => {
        const url = new URL(base, window.location.origin);
        Object.entries(params).forEach(([key, value]) => {
            if (value) url.searchParams.set(key, value);
        });
        return url.toString();
    };

    const handleSearch = (event) => {
        event.preventDefault();

        if (degreeSearchUrl) {
            window.location.href = generateLink(degreeSearchUrl, {
                area: areaOfStudy,
                search: searchValue
            });
        }
    };

    // Define link items to avoid repetition
    const linkItems = [
        { label: 'Undergraduate', params: { degree_type: 'Undergraduate' } },
        { label: 'Graduate', params: { degree_type: 'Graduate' } },
        { label: 'Online', params: { online: 'true' } },
        { label: 'Certificate', params: { degree_type: 'certificate' } },
        { label: 'All programs', params: {} }
    ];

    return (
        <div className="programs-search-container">
            <Form onSubmit={handleSearch} className="wp-block-search__button-outside wp-block-search__text-button wp-block-search">
                <div className="wp-block-search__inside-wrapper">
                    <FloatingLabel controlId="floatingInput" label="Find your program">
                        <Form.Control 
                            type="search" 
                            onChange={(e) => setSearchValue(e.target.value)} 
                            value={searchValue} 
                            placeholder='...'
                        />
                    </FloatingLabel>
                    <Button className="button-submit wp-block-search__button wp-element-button" type="submit">
                        Search
                    </Button>
                </div>
            </Form>
            <div className="programs-search-quick-links">
                <ul className="programs-search-quick-links-list">
                    {linkItems.map(({ label, params }) => (
                        <li key={label}>
                            <a href={generateLink(degreeSearchUrl, { ...params, area: areaOfStudy, search: searchValue })}>
                                {label}
                            </a>
                        </li>
                    ))}
                </ul>
            </div>
        </div>
    );
}

// Get the container and read the `data-area` and `data-url` attributes
const container = document.getElementById('degree-search-widget');
const areaOfStudy = container.getAttribute('data-area') || '';
const degreeSearchUrl = container.getAttribute('data-url') || '';

const root = createRoot(container);
root.render(<View areaOfStudy={areaOfStudy} degreeSearchUrl={degreeSearchUrl} />);