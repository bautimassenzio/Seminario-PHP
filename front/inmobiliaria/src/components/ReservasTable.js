import React, { useEffect, useState } from 'react';
import '../assets/styles/ItemTable.css';
import Button from './Button';
import Message from './MessageComponent';
import { Link } from 'react-router-dom';

const ReservasTable = ({ fetchItems, deleteItem }) => {
    const [items, setItems] = useState([]);
    const [message, setMessage] = useState('');

    useEffect(() => {
        const fetchData = async () => {
            try {
                const data = await fetchItems();
                console.log('Data fetched from API:', data);
                setItems(data);
            } catch (error) {
                console.error('Error: ', error);
                setItems([]);
            }
        };

        fetchData();
    }, [fetchItems]);

    const handleDelete = async (id) => {
        const confirmDelete = window.confirm("¿Estás seguro que quieres eliminar?");
        if (!confirmDelete) {
            return;
        }
        try {
            const data = await deleteItem(id);
            if (data.status === 'success') {
                const updatedItems = items.filter(item => item.id !== id);
                setItems(updatedItems);
            }
            setMessage(`${data.mensaje}`);
        } catch (error) {
            console.error('Error al borrar item: ', error);
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
            <h2>Lista de Reservas</h2>
            <table className="items-table">
                <thead>
                    <tr>
                        <th>Propiedad </th>
                        <th>Nombre Inquilino</th>
                        <th>Apellido Inquilino</th>
                        <th>Fecha Desde</th>
                        <th>Cantidad de Noches</th>
                        <th>Valor Total</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    {items.length > 0 ? (
                        items.map((item, index) => (
                            <tr key={index}>
                                <td>{item.domicilio}</td>
                                <td>{item.nombre_inquilino }</td>
                                <td>{item.apellido_inquilino }</td>
                                <td>{item.fecha_desde}</td>
                                <td>{item.cantidad_noches}</td>
                                <td>{item.valor_total}</td>
                                <td>
                                    <Link to={`editar/${item.id}`}>
                                        <Button label="Editar" />
                                    </Link>
                                    <Button label="Eliminar" onClick={() => handleDelete(item.id)} />
                                </td>
                            </tr>
                        ))
                    ) : (
                        <tr>
                            <td colSpan="6">No hay datos disponibles</td>
                        </tr>
                    )}
                </tbody>
            </table>
        </div>
    );
};

export default ReservasTable;
