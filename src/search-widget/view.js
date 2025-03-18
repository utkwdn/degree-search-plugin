import React, { useState, useEffect, useRef } from 'react';
import { createRoot } from 'react-dom/client';


export default function View() {

    return (
        <p>Your widget here.</p>
    );
}

const root = createRoot(document.getElementById('filters'));
root.render(<View />);
