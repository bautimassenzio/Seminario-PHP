import React, { useState, useEffect } from 'react';
import { useParams } from 'react-router-dom'; // Importa useParams para acceder a los parámetros de la ruta
import Header from '../../components/HeaderComponent';
import Footer from '../../components/FooterComponent';
import { getItem } from '../../services/propiedadesApi';
import Message from '../../components/MessageComponent';

const DetailPropiedadPage = () => {
    const { id } = useParams(); // Obtiene el ID de la propiedad de la URL
    const [propiedad, setPropiedad] = useState({});
    const [message, setMessage] = useState(null);

    useEffect(() => {
        const fetchProperty = async () => {
            try {
                const data = await getItem(id);
                const currentItem = data.data.find(item => item.id === id);
                setPropiedad(currentItem);
            } catch (error) {
                console.error('Error al obtener la propiedad', error);
                setMessage({ text: 'Error al obtener la propiedad', type: 'error' });
            }
        };
        fetchProperty();
    }, [id]); 

    return (
        <div>
            <Header />
            <div className="container">
                <div className="detail-container">         
                    <h2>Detalles de la Propiedad</h2>
                    {message && <Message text={message.text} />}
                    {propiedad && (
                        <div>
                            <p><strong>Domicilio:</strong> {propiedad.domicilio}</p>
                            <p><strong>Localidad ID:</strong> {propiedad.localidad_id}</p>
                            <p><strong>Cantidad de Habitaciones:</strong> {propiedad.cantidad_habitaciones || 'N/A'}</p>
                            <p><strong>Cantidad de Baños:</strong> {propiedad.cantidad_banios || 'N/A'}</p>
                            <p><strong>Cochera:</strong> {propiedad.cochera ? 'Sí' : 'No' || 'N/A'}</p>
                            <p><strong>Cantidad de Huéspedes:</strong> {propiedad.cantidad_huespedes}</p>
                            <p><strong>Fecha de Inicio Disponibilidad:</strong> {propiedad.fecha_inicio_disponibilidad}</p>
                            <p><strong>Cantidad de Días:</strong> {propiedad.cantidad_dias}</p>
                            <p><strong>Valor por Noche:</strong> {propiedad.valor_noche}</p>
                            <p><strong>Tipo de Propiedad:</strong> {propiedad.tipo_propiedad_id}</p>
                            <p><strong>Disponible:</strong> {propiedad.disponible ? 'Sí' : 'No'}</p>
                            {propiedad.imagen && <img src={propiedad.imagen} alt="Imagen de la propiedad" />}
                        </div>
                    )}
                </div>
            </div> 
            <Footer />
        </div>
    );
};

export default DetailPropiedadPage;