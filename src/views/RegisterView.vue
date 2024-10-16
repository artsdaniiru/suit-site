<template>
    <div>
        <h2>Register</h2>
        <form @submit.prevent="register">
            <input type="text" v-model="name" placeholder="Name" required />
            <input type="email" v-model="email" placeholder="Email" required />
            <input type="password" v-model="password" placeholder="Password" required />
            <button type="submit">Register</button>
        </form>
    </div>
</template>

<script>
import { ref, defineComponent } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';

export default defineComponent({
    name: "RegisterView",
    setup() {
        const name = ref('');
        const email = ref('');
        const password = ref('');

        const router = useRouter();

        const register = async () => {
            const url = process.env.VUE_APP_BACKEND_URL + '/backend/auth.php?action=register';

            try {
                const response = await axios.post(url,
                    {
                        name: name.value,
                        email: email.value,
                        password: password.value,
                    },
                    {
                        withCredentials: true // Разрешаем отправку куки сессии
                    });

                if (response.data.status === 'success') {
                    console.log('Registration successful!');
                    router.push('/login')
                } else {
                    console.log('Error: ' + response.data.message);
                }
            } catch (error) {
                console.error('Error:', error);
            }
        };

        return {
            name,
            email,
            password,
            register
        };
    }
});
</script>

<style scoped>
/* Стили для формы */
</style>
