import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import Header from '../../components/HeaderComponent';
import Footer from '../../components/FooterComponent';
import { getItems , deleteItem } from '../../services/propiedadesApi';
import Button from '../../components/Button';
import PropiedadesTable from '../../components/PropiedadesTable';

function PropiedadesPage() {
  const initialFilters = {
    disponible: '',
    localidad_id: '',
    fecha_inicio_disponibilidad: '',
    cantidad_huespedes: '',
  };

  const [filtro, setFiltro] = useState(initialFilters);
  const [propiedades, setPropiedades] = useState([]);
  const [localidades, setLocalidades] = useState([]);
  
  useEffect(() => {
    fetchLocalidades();
  }, []);

  const fetchLocalidades = async () => {
    try {
      const data = await getItems({});
      // Extraer localidades únicas de las propiedades
      const localidades = [...new Set(data.data.map(prop => ({
        id: prop.localidad_id,
        nombre: prop.localidad,
      })))];
      const uniqueLocalidades = localidades.filter((localidad, index, self) =>
        index === self.findIndex(l => l.id === localidad.id)
      );
      setLocalidades(uniqueLocalidades);
    } catch (error) {
      console.error('Error al obtener localidades: ', error);
    }
  };

  const fetchPropiedades = async () => {
    try {
      const data = await getItems(filtro);
      setPropiedades(data.data);
    } catch (error) {
      console.error('Error al obtener propiedades: ', error);
      setPropiedades([]);
    }
  };

  const handleInputChange = (e) => {
    const { name, value, type, checked } = e.target;
    const newValue = type === 'checkbox' ? checked : value;
    setFiltro({
      ...filtro,
      [name]: newValue,
    });
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    fetchPropiedades();
  };


  const handleReset = () => {
    setFiltro(initialFilters);
    setPropiedades([]);
  };

  return (
    <div className="App">
      <Header />
      <div className='centrar-boton-page'>
        <Link to={`crear`}>
          <Button label="Crear Propiedad" />
        </Link>
      </div>
      <main>
        <form onSubmit={handleSubmit}>
          <label>
            Disponible:
            <input
              type="checkbox"
              name="disponible"
              checked={filtro.disponible}
              onChange={handleInputChange}
            />
          </label>
          <label>
            Localidad:
            <select name="localidad_id" value={filtro.localidad_id} onChange={handleInputChange}>
              <option value="">Seleccione una localidad</option>
              {localidades.map((localidad) => (
                <option key={localidad.id} value={localidad.id}>
                  {localidad.nombre}
                </option>
              ))}
            </select>
          </label>
          <label>
            Fecha de inicio:
            <input
              type="date"
              name="fecha_inicio_disponibilidad"
              value={filtro.fecha_inicio_disponibilidad}
              onChange={handleInputChange}
            />
          </label>
          <label>
            Cantidad de huéspedes:
            <input
              type="number"
              name="cantidad_huespedes"
              value={filtro.cantidad_huespedes}
              onChange={handleInputChange}
            />
          </label>
          <button type="submit">Filtrar</button>
          <button type="button" onClick={handleReset}>Limpiar Filtros</button>
        </form>
        <PropiedadesTable propiedades={propiedades} deleteItem={deleteItem} />
      </main>  
      <Footer />
    </div>
  );
}

export default PropiedadesPage;

