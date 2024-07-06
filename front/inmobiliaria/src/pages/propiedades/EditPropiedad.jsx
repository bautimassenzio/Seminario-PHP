import React, { useEffect, useState } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import Header from '../../components/HeaderComponent';
import Footer from '../../components/FooterComponent';
import {getItems, updateItem } from '../../services/propiedadesApi';
import Message from '../../components/MessageComponent';
import Button from '../../components/Button';
import '../../assets/styles/CentrarForms.css';
import { getItemsLocalidades } from '../../services/localidadesApi';
import { getItemsTipoPropiedad } from '../../services/tipoPropiedadApi';

const EditarPropiedadPage = () => {
    const { id } = useParams();
    const navigate = useNavigate();
    const [domicilio, setDomicilio] = useState('');
    const [localidadId, setLocalidadId] = useState('');
    const [habitaciones, setHabitaciones] = useState('');
    const [banios, setBanios] = useState('');
    const [cochera, setCochera] = useState();
    const [huespedes, setHuespedes] = useState('');
    const [fechaInicio, setFechaInicio] = useState('');
    const [dias, setDias] = useState('');
    const [valorNoche, setValorNoche] = useState('');
    const [tipoPropiedad, setTipoPropiedad] = useState('');
    const [disponible, setDisponible] = useState();
    const [imagen, setImagen] = useState('');
    const [tipoImagen, setTipoImagen] = useState('');
    const [message, setMessage] = useState(null);
    const [localidadesId, setLocalidades] = useState([]);
    const [tipoPropiedadesId, setTipoPropiedades] = useState([]);

    useEffect(() => {
        const fetchLocalidades = async () =>{
            try{
                const data = await getItemsLocalidades();
                const localidadesID = data.map(localidad => localidad.id);
                setLocalidades(localidadesID);
            }catch (error){
                console.error('Error ',error);
            }
        };
        fetchLocalidades();
    }, []);

    useEffect(() => {
        const fetchTipoPropiedad = async () =>{
            try{
                const data = await getItemsTipoPropiedad();
                const tipoPropiedadesID = data.map(propiedad => propiedad.id);
                setTipoPropiedades(tipoPropiedadesID);
            }catch (error){
                console.error('Error ',error);
            }
        };
        fetchTipoPropiedad();
    }, []);


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
                const propiedadID = data.data.find(propiedad => propiedad.id === id);
                setDomicilio(propiedadID.domicilio);
                setLocalidadId(propiedadID.localidad_id);
                setHabitaciones(propiedadID.cantidad_habitaciones);
                setBanios(propiedadID.cantidad_banios);
                setCochera(propiedadID.cochera);
                setHuespedes(propiedadID.cantidad_huespedes);
                setFechaInicio(propiedadID.fecha_inicio_disponibilidad);
                setDias(propiedadID.cantidad_dias);
                setDisponible(propiedadID.disponible);
                setValorNoche(propiedadID.valor_noche);
                setTipoPropiedad(propiedadID.tipo_propiedad_id);
                setImagen(propiedadID.imagen);
            } catch (error){
                console.error('Error ', error);
            }
        };
        fetchPropiedades();
    }, [id]);


    const handleChange = (e) => {
        const { name, value, checked, type } = e.target;
        const inputValue = type === 'checkbox' ? checked : value;
        switch (name) {
            case 'domicilio':
                setDomicilio(value);
                break;
            case 'localidadId':
                setLocalidadId(value);
                break;
            case 'habitaciones':
                setHabitaciones(value);
                break;
            case 'banios':
                setBanios(value);
                break;
            case 'cochera':
                setCochera(inputValue);
                break;
            case 'huespedes':
                setHuespedes(value);
                break;
            case 'fechaInicio':
                setFechaInicio(value);
                break;
            case 'dias':
                setDias(value);
                break;
            case 'valorNoche':
                setValorNoche(value);
                break;
            case 'tipoPropiedad':
                setTipoPropiedad(value);
                break;
            case 'disponible':
                if (inputValue===true){
                    setDisponible(1);
                }else{
                    setDisponible(0);
                }         
                break;
            case 'imagen':
                handleImageChange(e);
                break;
            default:
                break;
        }
    };

    const handleImageChange = (e) => {
        const file = e.target.files[0];
        const reader = new FileReader();
        reader.onloadend = () => {
            setImagen(reader.result);
            setTipoImagen(file.type);
        };
        if (file) {
            reader.readAsDataURL(file);
        }
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        if (!domicilio.trim() || !fechaInicio.trim() || !dias.trim() || !valorNoche.trim()){
            setMessage({text:'No puede contener campos vacios'});
        }else{
            try {
                const newProperty = { 
                    domicilio,
                    localidad_id: localidadId,
                    cantidad_habitaciones: habitaciones,
                    cantidad_banios: banios,
                    cochera:cochera,
                    cantidad_huespedes: huespedes,
                    fecha_inicio_disponibilidad: fechaInicio,
                    cantidad_dias: dias,
                    valor_noche: valorNoche,
                    tipo_propiedad_id: tipoPropiedad,
                    disponible,
                    imagen,
                    tipo_imagen: tipoImagen
                };
    
                const data = await updateItem(id,newProperty);
                setMessage({ text: `${data.mensaje}` });
            } catch (error) {
                console.error('Error al crear la propiedad', error);
                setMessage({ text: 'Error al crear la propiedad', type: 'error' });
            }
        }
        setTimeout(() => navigate('/'), 2000);
    };

    return (
        <div>
            <Header />
            <div className="header-placeholder"></div>
            <div className="container">
                <div className="form-container">         
                    <h2>Editar Propiedad</h2>
                    {message && <Message text={message.text} />}
                    <form onSubmit={handleSubmit}>
                    <div className="form-columns">
                            <div className="column">
                                <label>
                                    Domicilio:
                                    <input
                                        type="text"
                                        name="domicilio"
                                        value={domicilio}  
                                        onChange={handleChange}
                                        required
                                    />
                                </label>
                                <label>
                                    Localidad ID:
                                    <select
                                        name="localidadId"
                                        value={localidadId}  
                                        onChange={handleChange}
                                        required>
                                        <option value="" >Seleccione una localidad</option>
                                        {localidadesId.map((localidad) => (
                                        <option key={localidad} value={localidad}>
                                            {localidad}
                                        </option>
                                ))}
                                    </select>
                                </label>
                                <label>
                                    Cantidad de Habitaciones:
                                    <input
                                        type="number"
                                        name="habitaciones"
                                        value={habitaciones}  
                                        onChange={handleChange}
                                    />
                                </label>
                                <label>
                                    Cantidad de Baños:
                                    <input
                                        type="number"
                                        name="banios"
                                        value={banios}  
                                        onChange={handleChange}
                                    />
                                </label>
                            </div>
                            <div className="column">
                                <label>
                                    Cochera:
                                    <input
                                        type="checkbox"
                                        name="cochera"
                                        checked={cochera}  
                                        onChange={handleChange}
                                    />
                                </label>
                                <label>
                                    Cantidad de Huéspedes:
                                    <input
                                        type="number"
                                        name="huespedes"
                                        value={huespedes}  
                                        onChange={handleChange}
                                        required
                                    />
                                </label>
                                <label>
                                    Fecha de Inicio Disponibilidad:
                                    <input
                                        type="date"
                                        name="fechaInicio"
                                        value={fechaInicio}  
                                        onChange={handleChange}
                                        required
                                    />
                                </label>
                                <label>
                                    Cantidad de Días:
                                    <input
                                        type="number"
                                        name="dias"
                                        value={dias}  
                                        onChange={handleChange}
                                        required
                                    />
                                </label>
                                <label>
                                    Valor por Noche:
                                    <input
                                        type="number"
                                        name="valorNoche"
                                        value={valorNoche}  
                                        onChange={handleChange}
                                        required
                                    />
                                </label>
                                <label>
                                    Tipo de Propiedad:
                                    <select
                                        name="tipoPropiedad"
                                        value={tipoPropiedad}  
                                        onChange={handleChange}
                                        required>
                                        <option value="" >Seleccione un tipo de propiedad</option>
                                        {tipoPropiedadesId.map((tipoPropiedad) => (
                                        <option key={tipoPropiedad} value={tipoPropiedad}>
                                            {tipoPropiedad}
                                        </option>
                                ))}
                                    </select>
                                </label>
                                <label>
                                    Disponible:
                                    <input
                                        type="checkbox"
                                        name="disponible"
                                        checked={disponible}
                                        onChange={handleChange}
                                    />
                                </label>
                                <label>
                                    Imagen:
                                    <input
                                        type="file"
                                        name="imagen"
                                        accept="image/*"
                                        onChange={handleChange}
                                    />
                                </label>
                            </div>
                        </div>
                        <div className="button-container">
                            <Button label="Editar" />
                        </div>
                        {imagen && <img src={imagen} alt="Imagen de la propiedad" />}
                    </form>
                </div>
            </div> 
            <div className="footer-placeholder"></div>
            <Footer />
        </div>
    );
};

export default EditarPropiedadPage;
