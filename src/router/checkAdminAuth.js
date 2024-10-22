import axios from 'axios';

// Функция для проверки, авторизован ли пользователь как администратор
async function checkAdminAuth() {
    try {
        const response = await axios.get(process.env.VUE_APP_BACKEND_URL + '/backend/admin/auth.php?action=get_user', {
            withCredentials: true
        });

        if (response.data.status === 'success') {
            return true; // Администратор авторизован
        } else {
            return false; // Не авторизован
        }
    } catch (error) {
        return false; // Ошибка, не авторизован
    }
}

export default checkAdminAuth;
