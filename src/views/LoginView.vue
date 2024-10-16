<template>
    <div>
        <h2>Login</h2>
        <form @submit.prevent="login">
            <input type="email" v-model="email" placeholder="Email" required />
            <input type="password" v-model="password" placeholder="Password" required />
            <button type="submit">Login</button>
        </form>
    </div>
</template>

<script>
import { ref, defineComponent, inject } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';

export default defineComponent({
    name: "LoginView",
    setup() {
        const email = ref('');
        const password = ref('');

        const { reloadUserData } = inject('auth')
        const router = useRouter();
        const login = async () => {
            const url = process.env.VUE_APP_BACKEND_URL + '/backend/auth.php?action=login';
            try {

                const response = await axios.post(url,
                    {
                        email: email.value,
                        password: password.value,
                    },
                    {
                        withCredentials: true // Разрешаем отправку куки сессии
                    }
                );
                console.log(response.data);
                if (response.data.status === 'success') {
                    localStorage.setItem('auth_token', response.data.auth_token); // Сохранение токена

                    reloadUserData();
                    router.push('/')
                } else {
                    alert(response.data.message);
                }
            } catch (error) {
                console.error('Error:', error);
            }
        };

        return {
            email,
            password,
            login
        };
    }
});
</script>

<style scoped>
/* Стили для формы */
</style>
