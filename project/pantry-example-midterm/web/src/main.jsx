// Puts the app on the page (as in HW4 and HW5).
import { StrictMode } from 'react';
import { createRoot } from 'react-dom/client';
import App from './App.jsx';
import './index.css';

createRoot(document.getElementById('root')).render(
  <StrictMode><App /></StrictMode>,
);
