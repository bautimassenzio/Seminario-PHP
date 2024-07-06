const API_URL = 'http://localhost'; 

export const getItemsInquilino = async () => {
    try {
        const response = await fetch(`${API_URL}/Proyecto/public/inquilinos`, {
            credentials: 'include', 
        });
        if (!response.ok) {
            throw new Error('La respuesta no fue correcta');
        }
        const { data } = await response.json(); 
        console.log('Data fetched in api.js:', data); 
        return data;
    } catch (error) {
        console.error('Error: ', error);
        throw error;
    }
};