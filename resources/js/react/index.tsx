import React, { useState, ChangeEvent } from "react";
import ReactDOM from 'react-dom/client';
import ChunkUploader from "./components/ChunkUploader";

ReactDOM.createRoot(document.getElementById('react-root')).render(
    <React.StrictMode>
        <ChunkUploader />
    </React.StrictMode>
);
