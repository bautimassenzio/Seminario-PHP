const API_URL = 'http://localhost'; 

export const getItemsTipoPropiedad = async () =>{
    try{
        const response = await fetch(`${API_URL}/Proyecto/public/tipos_propiedad`, {
            credentials: 'include', 
          });
        if (!response.ok){
            throw new Error ('La respuesta no fue correcta');
        }
        const { data } = await response.json(); 
        console.log('Data fetched in api.js:', data); 
        return data;
    }catch (error){
        console.error('Error: ', error);
        throw error;
    }
};

export const deleteItem = async (id) =>{
    try{
        const response = await fetch(`${API_URL}/Proyecto/public/tipos_propiedad/${id}`, {
            method: 'DELETE',
            credentials: 'include', 
          });
        const data=await response.json();
        if (!response.ok){
            throw new Error(data.mensaje || 'La respuesta no fue correcta');
        }
        console.log('Item borrado correctamente');
        return data;
    }catch (error){
        console.error('Error: ', error);
        throw error;
    }
};

export const updateItem = async (id, item) => {
    try {
        const response = await fetch(`${API_URL}/Proyecto/public/tipos_propiedad/${id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
            },
            credentials: 'include',
            body: JSON.stringify(item),
        });
        if (!response.ok) {
            throw new Error('La respuesta no fue correcta');
        }
        const data = await response.json();
        console.log('que retorna: ',data);
        return data;
    } catch (error) {
        console.error('Error: ', error);
        throw error;
    }
};
export const addItem = async (item) => {
    try {
        const response = await fetch(`${API_URL}/Proyecto/public/tipos_propiedad`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            credentials: 'include',
            body: JSON.stringify(item),
        });
        if (!response.ok) {
            throw new Error('La respuesta no fue correcta');
        }
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Error: ', error);
        throw error;
    }
};
