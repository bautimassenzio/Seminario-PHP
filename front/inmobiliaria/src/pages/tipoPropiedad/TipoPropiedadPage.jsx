import Header from '../../components/HeaderComponent';
import Footer from '../../components/FooterComponent';
import ItemsTable from '../../components/ItemsTableComponent';
import {getItemsTipoPropiedad, deleteItem} from '../../services/tipoPropiedadApi';
import Button from '../../components/Button';
import { Link } from 'react-router-dom';
import '../../assets/styles/CentrarBotonPage.css';
function TipoPropiedadPage() {
  
    return (
      <div className="App">
        <Header />
        <div className='centrar-boton-page'>
          <Link to={`crear`}>
            <Button label="Crear Tipo de Propiedad" />
          </Link>  
        </div>     
          <main>
          <ItemsTable fetchItems={getItemsTipoPropiedad} deleteItem={deleteItem}/>
        </main>
        <Footer />
      </div>
    );
  }
  
  export default TipoPropiedadPage;