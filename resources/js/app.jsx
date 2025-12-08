import React from 'react'
import ReactDOM from "react-dom/client";
import './bootstrap';
const App = () => {
  return (
    <div>app</div>
  )
}

ReactDOM.createRoot(document.getElementById('app')).render(
  <React.StrictMode>
    <App />
  </React.StrictMode>,
);


