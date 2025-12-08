import axios from 'axios';

export const deleteRecord = ({ 
    url, 
    id, 
    confirmMessage = 'Are you sure you want to delete this record?',
    onSuccess,
    onError = (error) => {
        console.error('Error deleting record:', error);
        alert('An error occurred while deleting the record');
    }
}) => {
    if (confirm(confirmMessage)) {
        axios.delete(`${url}/${id}`)
            .then(() => {
                if (onSuccess) onSuccess();
            })
            .catch(error => {
                if (onError) onError(error);
            });
    }
};