<template>
    <div v-if="closeFlag" class="modal">
        <div class="form">
            <img class="close" src="../assets/icons/close.svg" alt="close" @click="closeModal">
            <!-- Форма входа -->
            <form v-if="type === 'login'" @submit.prevent="login">
                <h3>ログインフォーム</h3>
                <CustomInput :required="true" :type="'email'" v-model="email" :labelText="'メールアドレス'" placeholderText="メールアドレス" />
                <CustomInput :required="true" :type="'password'" v-model="password" :labelText="'パスワード'" placeholderText="パスワード" />

                <!-- Вывод ошибки логина -->
                <div v-if="errorMessage" class="alert-filed danger">
                    <img src="../assets/icons/info-danger.svg" alt="info">
                    <p>{{ errorMessage }}</p>
                </div>

                <button class="button" type="submit">ログイン</button>
                <button class="button" @click="type = 'register'; clear()">登録</button>
            </form>

            <!-- Форма регистрации -->
            <form v-if="type === 'register'" @submit.prevent="register">
                <h3>登録フォーム</h3>

                <CustomInput :required="true" v-model="name" :labelText="'名前'" placeholderText="名前" />
                <CustomInput :required="true" :type="'email'" v-model="email" :labelText="'メールアドレス'" placeholderText="メールアドレス" />
                <CustomInput :required="true" :type="'password'" v-model="password" :labelText="'パスワード'" placeholderText="パスワード" />
                <CustomInput :required="true" :type="'password'" v-model="confirmPassword" :labelText="'パスワード確認'" placeholderText="パスワード確認" />

                <!-- Вывод ошибки регистрации -->
                <div v-if="errorMessage" class="alert-filed danger">
                    <img src="../assets/icons/info-danger.svg" alt="info">
                    <p>{{ errorMessage }}</p>
                </div>

                <button class="button" type="submit">登録</button>
                <button class="button" @click="type = 'login'; clear()">戻る</button>
            </form>
        </div>
    </div>
</template>

<script>
import { ref, defineComponent, inject, watch } from 'vue';
import axios from 'axios';

export default defineComponent({
    name: "LoginForm",
    props: {
        closeFlag: {
            type: Boolean,
            required: true
        }
    },
    emits: ['update:closeFlag'],
    setup(props, { emit }) {
        const email = ref('');
        const password = ref('');
        const name = ref('');
        const confirmPassword = ref('');
        const errorMessage = ref(''); // Переменная для хранения сообщений об ошибках

        const type = ref('login'); // Переключение между логином и регистрацией
        const { reloadUserData } = inject('auth');

        // Функция для логина
        const login = async () => {
            const url = process.env.VUE_APP_BACKEND_URL + '/backend/auth.php?action=login';
            errorMessage.value = ''; // Сбрасываем ошибки перед каждой попыткой
            try {
                const response = await axios.post(url, { email: email.value, password: password.value }, { withCredentials: true });
                if (response.data.status === 'success') {
                    localStorage.setItem('auth_token', response.data.auth_token);
                    reloadUserData();
                    closeModal();
                    clear();
                } else {
                    errorMessage.value = response.data.message; // Устанавливаем сообщение об ошибке
                }
            } catch (error) {
                errorMessage.value = 'An error occurred during login.';
                console.error('Error:', error);
            }
        };

        // Функция для регистрации
        const register = async () => {
            errorMessage.value = ''; // Сбрасываем ошибки перед каждой попыткой
            if (password.value !== confirmPassword.value) {
                errorMessage.value = "Passwords do not match!";
                return;
            }
            const url = process.env.VUE_APP_BACKEND_URL + '/backend/auth.php?action=register';
            try {
                const response = await axios.post(url, { name: name.value, email: email.value, password: password.value }, { withCredentials: true });
                if (response.data.status === 'success') {
                    closeModal();
                    clear();
                } else {
                    errorMessage.value = response.data.message; // Устанавливаем сообщение об ошибке
                }
            } catch (error) {
                errorMessage.value = 'An error occurred during registration.';
                console.error('Error:', error);
            }
        };

        const closeModal = () => {
            emit('update:closeFlag', false);
        };

        watch(() => props.closeFlag, (newValue) => {
            if (newValue) {
                document.body.classList.add('no-scroll');
            } else {
                document.body.classList.remove('no-scroll');
            }
        });

        function clear() {
            email.value = '';
            password.value = '';
            name.value = '';
            confirmPassword.value = '';
            errorMessage.value = ''; // Очищаем сообщение об ошибке
        }

        return {
            email,
            password,
            name,
            confirmPassword,
            login,
            register,
            closeModal,
            type,
            clear,
            errorMessage // Возвращаем переменную с сообщениями об ошибках
        };
    }
});
</script>

<style lang="scss" scoped>
.form {
    border: 1px solid #d9d9d9;
    border-radius: 8px;
    padding: 32px;
    max-width: 600px;
    width: 400px;
    box-shadow: 0 4px 4px -4px rgba(12, 12, 13, 0.05), 0 16px 32px -4px rgba(12, 12, 13, 0.1);
    background: #fff;
    margin-top: 140px;
    margin-left: auto;
    margin-right: auto;
    position: relative;

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

    .close {
        position: absolute;
        right: 20px;
        top: 20px;
        cursor: pointer;
    }
}
</style>
