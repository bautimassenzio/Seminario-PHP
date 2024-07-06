import React from 'react';
import Header from '../../components/HeaderComponent';
import Footer from '../../components/FooterComponent';
import { getItem, deleteItem } from '../../services/reservasApi';
import Button from '../../components/Button';
import { Link } from 'react-router-dom';
import ReservasTable from '../../components/ReservasTable';
import '../../assets/styles/CentrarBotonPage.css';

function PropiedadesPage() {
  return (
    <div className="App">
      <Header />
      <div className='centrar-boton-page'>
        <Link to={`crear`}>
          <Button label="Crear Reserva"/>
        </Link>
      </div>
      <main>
        <ReservasTable fetchItems={getItem} deleteItem={deleteItem} />
      </main>
      <Footer />
    </div>
  );
}

export default PropiedadesPage;
