<template>
    <div v-if="closeFlag" class="modal">
        <div class="form">
            <img class="close" src="../assets/icons/close.svg" alt="close" @click="closeModal">
            <!-- Форма входа -->
            <form v-if="type === 'login'" @submit.prevent="login">
                <h3>ログインフォーム</h3>
                <div class="form-elem">
                    <label for="email">メールアドレス</label>
                    <input type="email" v-model="email" placeholder="メールアドレス" required />
                </div>
                <div class="form-elem">
                    <label for="password">パスワード</label>
                    <input type="password" v-model="password" placeholder="パスワード" required />
                </div>
                <button class="button" type="submit">ログイン</button>
                <button class="button" @click="type = 'register'">登録</button>
            </form>

            <!-- Форма регистрации -->
            <form v-if="type === 'register'" @submit.prevent="register">
                <h3>登録フォーム</h3>
                <div class="form-elem">
                    <label for="name">名前</label>
                    <input type="text" v-model="name" placeholder="名前" required />
                </div>
                <div class="form-elem">
                    <label for="email">メールアドレス</label>
                    <input type="email" v-model="email" placeholder="メールアドレス" required />
                </div>
                <div class="form-elem">
                    <label for="password">パスワード</label>
                    <input type="password" v-model="password" placeholder="パスワード" required />
                </div>
                <div class="form-elem">
                    <label for="confirmPassword">パスワード確認</label>
                    <input type="password" v-model="confirmPassword" placeholder="パスワード確認" required />
                </div>
                <button class="button" type="submit">登録</button>
                <button class="button" @click="type = 'login'">戻る</button>
            </form>
        </div>
    </div>
</template>

<script>
import { ref, defineComponent, inject } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';

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

        const type = ref('login'); // Переключение между логином и регистрацией
        const { reloadUserData } = inject('auth');
        const router = useRouter();

        // Функция для логина
        const login = async () => {
            const url = process.env.VUE_APP_BACKEND_URL + '/backend/auth.php?action=login';
            try {
                const response = await axios.post(url, { email: email.value, password: password.value }, { withCredentials: true });
                if (response.data.status === 'success') {
                    localStorage.setItem('auth_token', response.data.auth_token);
                    reloadUserData();
                    closeModal();
                } else {
                    alert(response.data.message);
                }
            } catch (error) {
                console.error('Error:', error);
            }
        };

        // Функция для регистрации
        const register = async () => {
            if (password.value !== confirmPassword.value) {
                alert("Passwords do not match!");
                return;
            }
            const url = process.env.VUE_APP_BACKEND_URL + '/backend/auth.php?action=register';
            try {
                const response = await axios.post(url, { name: name.value, email: email.value, password: password.value }, { withCredentials: true });
                if (response.data.status === 'success') {
                    router.push('/login');
                    closeModal();
                } else {
                    alert(response.data.message);
                }
            } catch (error) {
                console.error('Error:', error);
            }
        };

        const closeModal = () => {
            emit('update:closeFlag', false);
        };

        return {
            email,
            password,
            name,
            confirmPassword,
            login,
            register,
            closeModal,
            type
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
