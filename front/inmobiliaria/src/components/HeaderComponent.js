import React from 'react';
import '../assets/styles/Header.css';
import logo from '../assets/images/logo-header.png'
import NavBar from './NavBarComponent';

const Header = () =>{
    return (
        <header className='header'>
            <div className='header-logo'>
                <img src={logo} alt="logo de la aplicacion"/> 
            </div>
            <h1 className='header-title'>Inmobiliaria</h1>
            <NavBar className='navbar'/> 
        </header>
    )
}

export default Header;