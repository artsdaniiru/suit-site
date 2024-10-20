<template>
    <div>
        <h1>Admin Login</h1>
        <form @submit.prevent="loginAdmin">
            <input v-model="email" type="email" placeholder="Email" required />
            <input v-model="password" type="password" placeholder="Password" required />
            <button type="submit">Login</button>
        </form>
    </div>
</template>

<script>
import { defineComponent, ref, inject } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios';

export default defineComponent({
    name: 'LoginView',
    setup() {
        const router = useRouter();
        const email = ref('');
        const password = ref('');

        const { isUserLoggedIn, logout } = inject('auth')
        const { reloadAdminUserData } = inject('admin_auth')

        const loginAdmin = async () => {

            if (isUserLoggedIn.value) {
                logout();
            }

            const url = process.env.VUE_APP_BACKEND_URL + '/backend/admin/auth.php?action=login';
            try {
                const response = await axios.post(url,
                    { email: email.value, password: password.value },
                    { withCredentials: true }
                );

                console.log(response);


                if (response.data.status === 'success') {
                    localStorage.setItem('admin_auth_token', response.data.auth_token);
                    reloadAdminUserData();
                    // Успешная авторизация, перенаправляем на панель администратора
                    router.push('/admin/dashboard');
                } else {
                    alert(response.message);
                }
            } catch (error) {
                console.error('Error during login:', error);
            }
        };

        return {
            email,
            password,
            loginAdmin
        };
    }
});
</script>
