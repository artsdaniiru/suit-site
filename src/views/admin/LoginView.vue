<template>
    <div>
        <div class="form">
            <form @submit.prevent="loginAdmin">
                <h3>ログイン</h3>
                <CustomInput :required="true" :type="'email'" v-model="email" :labelText="'メールアドレス'" placeholderText="メールアドレス" />
                <CustomInput :required="true" :type="'password'" v-model="password" :labelText="'パスワード'" placeholderText="パスワード" />
                <!-- Вывод ошибки логина -->
                <div v-if="errorMessage" class="alert-filed danger">
                    <img src="../../assets/icons/info-danger.svg" alt="info">
                    <p>{{ errorMessage }}</p>
                </div>

                <button class="button" type="submit">ログイン</button>
            </form>
        </div>
    </div>

</template>

<script>
import { defineComponent, ref, inject, onBeforeMount } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios';

import { useToast } from "vue-toast-notification";
const toast = useToast();


export default defineComponent({
    name: 'LoginView',
    setup() {
        const router = useRouter();
        const email = ref('');
        const password = ref('');

        const errorMessage = ref(''); // Переменная для хранения сообщений об ошибках

        const { isUserLoggedIn, logout } = inject('auth')
        const { isAdminLoggedIn, reloadAdminUserData, admin_logout } = inject('admin_auth')


        onBeforeMount(() => {
            if (isAdminLoggedIn) {
                router.push('/admin/catalog');
            }
        })

        const loginAdmin = async () => {

            await admin_logout();
            errorMessage.value = '';
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
                    router.push('/admin/catalog');
                } else {
                    errorMessage.value = response.data.message;
                }
            } catch (error) {
                console.error('Error during login:', error);
                toast.error("Error during login:" + error);
            }
        };

        return {
            email,
            password,
            loginAdmin,
            errorMessage
        };
    }
});
</script>
<style lang="scss" scoped>
.form {
    border: 1px solid #d9d9d9;
    border-radius: 8px;
    padding: 32px;
    background: #fff;
    margin-top: 20px;
    margin-left: auto;
    margin-right: auto;
    position: relative;

    height: fit-content;
    width: 400px;

    form {
        display: flex;
        flex-direction: column;
        gap: 12px;

        h3 {
            margin: 0;
        }

        .form-elem {
            display: flex;
            flex-direction: column;
            gap: 8px;
            min-width: 400px;
        }
    }
}
</style>
