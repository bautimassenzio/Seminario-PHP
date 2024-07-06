const API_URL = 'http://localhost'; 

export const getItem = async () => {
  try {
    const response = await fetch(`${API_URL}/Proyecto/public/reservas`, {
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

export const deleteItem = async (id) => {
  try {
    const response = await fetch(`${API_URL}/Proyecto/public/reservas/${id}`, {
      method: 'DELETE',
      credentials: 'include', 
    });
    const data = await response.json();
    if (!response.ok) {
      throw new Error(data.mensaje || 'La respuesta no fue correcta');
    }
    console.log('Reserva borrada correctamente');
    return data;
  } catch (error) {
    console.error('Error: ', error);
    throw error;
  }
};

export const updateItem = async (id, reserva) => {
  try {
    const response = await fetch(`${API_URL}/Proyecto/public/reservas/${id}`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
      },
      credentials: 'include',
      body: JSON.stringify(reserva),
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

export const addItem = async (reserva) => {
  try {
    const response = await fetch(`${API_URL}/Proyecto/public/reservas`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      credentials: 'include',
      body: JSON.stringify(reserva),
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
