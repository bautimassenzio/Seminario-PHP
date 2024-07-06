import React, { useEffect, useState } from 'react';
import '../assets/styles/ItemTable.css'; 
import Button from './Button';
import Message from './MessageComponent';
import { Link } from 'react-router-dom';

const ItemsTable = ({ fetchItems, deleteItem }) => {
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

const handleDelete = async (id) =>{
    const confirmDelete = window.confirm("Estas seguro que quieres eliminar?");
    if (!confirmDelete){
        return;
    }
    try{
    const data = await deleteItem(id);
    if (data.status==='success'){
        const updateItems =items.filter(item=> item.id !== id);
        setItems(updateItems);
    }
    setMessage(`${data.mensaje}`);
  } catch (error){
      console.error('Error al borrar item: ', error);
      if (error && error.error) {
        setMessage(`Error al eliminar el elemento: ${error.error}`);
      } else {
        setMessage(`Error al eliminar el elemento`);
      }
  }
};

const closeMessage = () =>{
  setMessage('');
};

    return (
        <div className="items-table-container">
            {message && <Message text={message} onClose={closeMessage} />}
            <h2>Lista de Tipo de Propiedades</h2>
            <table className="items-table">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    {items.length > 0 ? (
                        items.map((item, index) => (
                            <tr key={index}>
                                <td>{item.nombre}</td>
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
                            <td colSpan="2">No hay datos disponibles</td>
                        </tr>
                    )}
                </tbody>
            </table>
        </div>
    );
};

export default ItemsTable;
