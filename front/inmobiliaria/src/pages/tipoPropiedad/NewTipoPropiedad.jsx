import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import Header from '../../components/HeaderComponent';
import Footer from '../../components/FooterComponent';
import { addItem } from '../../services/tipoPropiedadApi';
import Message from '../../components/MessageComponent';
import Button from '../../components/Button';


const CrearTipoPropiedadPage = () => {
    const navigate = useNavigate();
    const [nombre, setNombre] = useState('');
    const [message, setMessage] = useState(null);

    const handleChange = (e) => {
        setNombre(e.target.value);
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        if (!nombre.trim()){
            setMessage({text:'El campo nombre no puede ser vacio'});
        }else{
            try {
                const data = await addItem({ nombre });
                setMessage({text:`${data.mensaje}`});
            } catch (error) {
                console.error('Error al crear el tipo de propiedad', error);
                setMessage({ text: 'Error al crear el tipo de propiedad', type: 'error' });
            }
        }
        setTimeout(() => navigate('/tipopropiedad'), 2000);

    };

    return (
        <div>
            <Header />
            <div className="container">
                <div className="form-container">         
                    <h2>Crear tipo de propiedad</h2>
                    {message && <Message text={message.text} type={message.type} />}
                    <form onSubmit={handleSubmit}>
                        <label>
                            Nombre:
                            <input
                                type="text"
                                name="nombre"
                                value={nombre}  
                                onChange={handleChange}
                                required
                            />
                        </label>
                        <Button label="Crear" />
                    </form>
                </div>
            </div> 
            <Footer />
        </div>
    );
};

export default CrearTipoPropiedadPage;
