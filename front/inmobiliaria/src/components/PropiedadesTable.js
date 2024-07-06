import React, { useState } from 'react';
import '../assets/styles/ItemTable.css'; 
import Button from './Button';
import Message from './MessageComponent';
import { Link } from 'react-router-dom';

const PropiedadesTable = ({ propiedades, deleteItem }) => {
    const [message, setMessage] = useState('');

    const handleDelete = async (id) => {
        const confirmDelete = window.confirm("¿Estás seguro que quieres eliminar?");
        if (!confirmDelete) {
            return;
        }
        try {
            const data = await deleteItem(id);
            setMessage(`${data.mensaje}`);
        } catch (error) {
            console.error('Error al borrar propiedad: ', error);
            if (error && error.error) {
                setMessage(`Error al eliminar el elemento: ${error.error}`);
            } else {
                setMessage(`Error al eliminar el elemento`);
            }
        }
    };

    const closeMessage = () => {
        setMessage('');
    };

    return (
        <div className="items-table-container">
            {message && <Message text={message} onClose={closeMessage} />}
            <h2>Listado de Propiedades</h2>
            <table className="items-table">
                <thead>
                    <tr>
                        <th>Domicilio</th>
                        <th>Localidad</th>
                        <th>Tipo de Propiedad</th>
                        <th>Fecha de Inicio</th>
                        <th>Cantidad de Huéspedes</th>
                        <th>Valor Noche</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    {propiedades.length > 0 ? (
                        propiedades.map(propiedad => (
                            <tr key={propiedad.id}>
                                <td>{propiedad.domicilio}</td>
                                <td>{propiedad.localidad}</td>
                                <td>{propiedad.tipo_de_propiedad}</td>
                                <td>{propiedad.fecha_inicio_disponibilidad}</td>
                                <td>{propiedad.cantidad_huespedes}</td>
                                <td>{propiedad.valor_noche}</td>
                                <td>
                                    <Link to={`editar/${propiedad.id}`}>
                                        <Button label="Editar" />
                                    </Link>
                                    <Link to={`detail/${propiedad.id}`}>
                                        <Button label="Detalle" />
                                    </Link>
                                    <Button label="Eliminar" onClick={() => handleDelete(propiedad.id)} />
                                </td>
                            </tr>
                        ))
                    ) : (
                        <tr>
                            <td colSpan="7">No hay propiedades disponibles</td>
                        </tr>
                    )}
                </tbody>
            </table>
        </div>
    );
};

export default PropiedadesTable;
