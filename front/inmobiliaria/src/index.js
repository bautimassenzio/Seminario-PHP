import React from 'react';
import ReactDOM from 'react-dom/client';
import { BrowserRouter, Routes, Route } from 'react-router-dom';
import './index.css';
import reportWebVitals from './reportWebVitals';
import TipoPropiedadPage from './pages/tipoPropiedad/TipoPropiedadPage.jsx';
import EditarTipoPropiedadPage from './pages/tipoPropiedad/EditTipoPropiedad.jsx';
import CrearTipoPropiedadPage from './pages/tipoPropiedad/NewTipoPropiedad.jsx';
import PropiedadesPage from './pages/propiedades/PropiedadesPage.jsx';
import NewPropiedad from './pages/propiedades/NewPropiedad.jsx';
import EditarPropiedadPage from './pages/propiedades/EditPropiedad.jsx';
import ReservaPage from './pages/reserva/ReservaPage.jsx';
import CrearReservaPage from './pages/reserva/NewReserva.jsx';
import EditarReservaPage from './pages/reserva/EditReserva.jsx';
import DetailPropiedadPage from './pages/propiedades/DetailPropiedad.jsx';


const root = ReactDOM.createRoot(document.getElementById('root'));
root.render(
  <React.StrictMode>
    <BrowserRouter>
      <Routes>
        <Route path="/tipopropiedad" element={<TipoPropiedadPage />} />
        <Route path="/" element={<PropiedadesPage />} />
        <Route path="/tipopropiedad/editar/:id" element={<EditarTipoPropiedadPage />} />
        <Route path="/editar/:id" element={<EditarPropiedadPage />} />
        <Route path="/tipopropiedad/crear" element={<CrearTipoPropiedadPage />} />
        <Route path="/crear" element={<NewPropiedad />} />
        <Route path="/reserva" element={<ReservaPage />} />
        <Route path="/reserva/crear" element={<CrearReservaPage />} />
        <Route path="/reserva/editar/:id" element={<EditarReservaPage />} />
        <Route path="/detail/:id" element={<DetailPropiedadPage />} />


      </Routes>
    </BrowserRouter>
  </React.StrictMode>
);
reportWebVitals();
