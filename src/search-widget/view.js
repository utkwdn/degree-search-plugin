import React, { useState, useEffect, useRef } from 'react';
import { createRoot } from 'react-dom/client';


export default function View({ areaOfStudy }) {    
    return (
        <p>Selected Area: {areaOfStudy ? areaOfStudy : "None selected."}</p>
    );
}

// Get the container and read the `data-area` attribute
const container = document.getElementById('search-widget');
const areaOfStudy = container.getAttribute('data-area') || '';

const root = createRoot(container);
root.render(<View areaOfStudy={areaOfStudy} />);
