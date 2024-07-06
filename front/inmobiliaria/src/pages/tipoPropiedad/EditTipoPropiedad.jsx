import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import Header from '../../components/HeaderComponent';
import Footer from '../../components/FooterComponent';
import { updateItem, getItemsTipoPropiedad } from '../../services/tipoPropiedadApi';
import Message from '../../components/MessageComponent';
import Button from '../../components/Button'; 
import '../../assets/styles/EditTipoProp.css';

const EditItem = () => {
    const { id } = useParams();
    const navigate = useNavigate();
    const [item, setItem] = useState([]);
    const [message, setMessage] = useState(null);

    useEffect(() => {
        const fetchItem = async () => {
            try {
                const data = await getItemsTipoPropiedad();
                const currentItem = data.find(item => item.id === id);
                setItem(currentItem);
            } catch (error) {
                console.error('Error al obtener el item');
                setMessage({ text: 'Error al obtener el item', type: 'error' });
            }
        };

        fetchItem();
    }, [id]);

    const handleChange = (e) => {
        setItem({ ...item, [e.target.name]: e.target.value });
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        if (!item.nombre.trim()){
          setMessage({text:'El campo nombre no puede ser vacio'});
        }else{
          try {
            const data = await updateItem(id, item);
            setMessage({text:`${data.mensaje}`});
            
          } catch (error) {
            console.error('Error al actualizar el item', error);
            setMessage({ text: 'Error al actualizar el item', type: 'error' });
          }
        }
        setTimeout(() => navigate('/tipopropiedad'), 2000);
    };

    return (
      <div>
        <Header />
        <div className="container">
        <div className="form-container">         
          <h2>Editar tipo de propiedad</h2>
          {message && <Message text={message.text} />}
          <form onSubmit={handleSubmit}>
            <label>
              Nombre:
              <input
                type="text"
                name="nombre"
                value={item.nombre}
                onChange={handleChange}
                required
              />
            </label>
            <Button label="Actualizar" />
          </form>
        </div>
      </div> 
      <Footer />
      </div>

    );
};

export default EditItem;