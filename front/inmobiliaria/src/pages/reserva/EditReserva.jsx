import React, { useEffect, useState } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import Header from '../../components/HeaderComponent';
import Footer from '../../components/FooterComponent';
import { getItem , updateItem } from '../../services/reservasApi'; 
import { getItems } from '../../services/propiedadesApi';
import { getItemsInquilino } from '../../services/inquilinosApi';
import Message from '../../components/MessageComponent';
import Button from '../../components/Button';

const EditarReservaPage = () => {
    const { id } = useParams();
    const navigate = useNavigate();
    const [propiedadId, setPropiedadId] = useState('');
    const [inquilinoId, setInquilinoId] = useState('');
    const [fechaDesde, setFechaDesde] = useState('');
    const [cantidadNoches, setCantidadNoches] = useState('');
    const [valorTotal, setValorTotal] = useState('');
    const [message, setMessage] = useState('');
    const [propiedades, setPropiedades] = useState([]);
    const [inquilinos, setInquilinos] = useState([]);

    useEffect(() =>{
        const fetchReservas = async () =>{
            try{
                const data = await getItem();
                const dataID= data.find(item => item.id === id);
                setPropiedadId(dataID.propiedad_id);
                setInquilinoId(dataID.inquilino_id);
                setFechaDesde(dataID.fecha_desde);
                setCantidadNoches(dataID.cantidad_noches);
                setValorTotal(dataID.valor_total);

            }catch(error){
                console.error('Error ',error);
            }
        };
        fetchReservas();
    }, [id]);

    useEffect(() => {
        const fetchPropiedades = async () => {
            try{
                const initialFilters = {
                    disponible: '',
                    localidad_id: '',
                    fecha_inicio_disponibilidad: '',
                    cantidad_huespedes: '',
                  };
                const data = await getItems(initialFilters);
                const propiedadesID = data.data.map(propiedad => propiedad.id);
                setPropiedades(propiedadesID);
            } catch (error){
                console.error('Error ', error);
            }
        };
        fetchPropiedades();
    }, []);

    useEffect(() => {
        const fetchInquilinos = async () =>{
            try{
                const data = await getItemsInquilino();
                const inquilinosID = data.map(inquilino => inquilino.id);
                setInquilinos(inquilinosID);
            }catch (error){
                console.error('Error ',error);
            }
        };
        fetchInquilinos();
    }, [id]);

    const handleChange = (e) => {
        const { name, value } = e.target;
        switch (name) {
            case 'propiedadId':
                // Verifica que el valor sea un número antes de actualizar el estado
                if (!isNaN(value)) {
                    setPropiedadId(value);
                }
                break;
            case 'inquilinoId':
                // Verifica que el valor sea un número antes de actualizar el estado
                if (!isNaN(value)) {
                    setInquilinoId(value);
                }
                break;
            case 'fechaDesde':
                setFechaDesde(value);
                break;
            case 'cantidadNoches':
                setCantidadNoches(value);
                break;
            case 'valorTotal':
                setValorTotal(value);
                break;
            default:
                break;
        }
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        if (!fechaDesde.trim() || !cantidadNoches.trim() || !valorTotal.trim()){
            setMessage({text:'No puede contener campos vacios'});
        }else{
            try {
                const newReserva = {
                    propiedad_id: propiedadId,
                    inquilino_id: inquilinoId,
                    fecha_desde: fechaDesde,
                    cantidad_noches: cantidadNoches,
                    valor_total: valorTotal,
                };
    
                const data = await updateItem(id,newReserva);
                console.log('que trae',data);
                setMessage(`${data.mensaje}`);
            } catch (error) {
                console.error('Error al crear la reserva', error);
                setMessage({ text: 'Error al crear la reserva', type: 'error' });
            }
        }
        setTimeout(() => navigate('/reserva'), 2000);
    };

    return (
        <div>
            <Header />
            <div className="container">
                <div className="form-container">
                    <h2>Editar Reserva</h2>
                    {message && <Message text={message} />}
                    <form onSubmit={handleSubmit}>
                        <label>
                            Propiedad:
                            <select name="propiedadId" value={propiedadId} onChange={handleChange} required>
                                <option value="">Seleccione una propiedad</option>
                                {propiedades.map((propiedad) => (
                                    <option key={propiedad} value={propiedad}>
                                        {propiedad}
                                    </option>
                                ))}
                            </select>
                        </label>
                        <label>
                            Inquilino:
                            <select name="inquilinoId" value={inquilinoId} onChange={handleChange} required>
                                <option value="">Seleccione un inquilino</option>
                                {inquilinos.map((inquilino) => (
                                    <option key={inquilino} value={inquilino}>
                                        {inquilino}
                                    </option>
                                ))}
                            </select>
                        </label>
                        <label>
                            Fecha Desde:
                            <input type="date" name="fechaDesde" value={fechaDesde} onChange={handleChange} required />
                        </label>
                        <label>
                            Cantidad Noches:
                            <input type="number" name="cantidadNoches" value={cantidadNoches} onChange={handleChange} required />
                        </label>
                        <label>
                            Valor Total:
                            <input type="number" name="valorTotal" value={valorTotal} onChange={handleChange} required />
                        </label>
                        <div className="button-container">
                            <Button label="Editar" />
                        </div>
                    </form>
                </div>
            </div>
            <Footer />
        </div>
    );
};

export default EditarReservaPage;