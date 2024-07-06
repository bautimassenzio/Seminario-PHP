import React from 'react';
import '../assets/styles/NavBar.css'
const NavBar = () =>{
    return (
        <nav className='navbar'>
        <ul className='navbar-links'>
          <li><a href="/tipopropiedad">Tipo Propiedad</a></li>
          <li><a href="/">Propiedades</a></li>
          <li><a href="/reserva">Reservas</a></li>
        </ul>
      </nav>
      );
};
export default NavBar;