import React, {useEffect} from 'react';
import '../assets/styles/Message.css';

const Message = ({text, onClose}) => {
    useEffect(() =>{
        const timer = setTimeout(() =>{
            onClose();
        }, 3000); 

        return () =>{
            clearTimeout(timer);
        };
    },[onClose]);
    
    return <div className='message'>{text}</div>;
};

export default Message;
