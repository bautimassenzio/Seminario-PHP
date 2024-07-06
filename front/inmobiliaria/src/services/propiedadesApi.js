

const API_URL = 'http://localhost'; 

export const getItem = async (id) => {
    try {
        const response = await fetch(`${API_URL}/Proyecto/public/propiedades/${id}`, {
            credentials: 'include', 
        });
        if (!response.ok) {
            throw new Error('La respuesta no fue correcta');
        }
        const result = await response.json(); 
        const data = result;
        console.log('Data fetched in api.js:', data); 
        return data;
    } catch (error) {
        console.error('Error: ', error);
        throw error;
    }
};

export const getItems = async (filters) => {
    try {
        let endpoint = `${API_URL}/Proyecto/public/propiedades`;

        
        const cleanedFilters = Object.fromEntries(
            Object.entries(filters).filter(([key, value]) => value !== '')
        );

        
        if (Object.keys(cleanedFilters).length > 0) {
            const query = new URLSearchParams(cleanedFilters).toString();
            endpoint += `?${query}`;
        }

        const response = await fetch(endpoint, {
            method: 'GET',
            credentials: 'include',
        });

        const data = await response.json();
        if (!response.ok) {
            throw new Error(data.message || 'La respuesta no fue correcta');
        }
        return data;
    } catch (error) {
        console.error('Error: ', error);
        throw error;
    }
};



export const deleteItem  = async (id) => {
    try {
        const response = await fetch(`${API_URL}/Proyecto/public/propiedades/${id}`, {
            method: 'DELETE',
            credentials: 'include', 
        });
        const data = await response.json();
        if (!response.ok) {
            throw new Error(data.mensaje || 'La respuesta no fue correcta');
        }
        console.log('Propiedad borrada correctamente');
        return data;
    } catch (error) {
        console.error('Error: ', error);
        throw error;
    }
};

export const updateItem  = async (id, propiedad) => {
    try {
        const response = await fetch(`${API_URL}/Proyecto/public/propiedades/${id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
            },
            credentials: 'include',
            body: JSON.stringify(propiedad),
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

export const addItem  = async (propiedad) => {
    try {
        const response = await fetch(`${API_URL}/Proyecto/public/propiedades`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            credentials: 'include',
            body: JSON.stringify(propiedad),
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
