<template>
    <div class="form">
        <!-- Форма логина -->
        <form v-if="type === 'login'" @submit.prevent="login">
            <h3>ログインフォーム</h3>
            <CustomInput :required="true" :type="'email'" v-model="email" :labelText="'メールアドレス'" placeholderText="メールアドレス" />
            <CustomInput :required="true" :type="'password'" v-model="password" :labelText="'パスワード'" placeholderText="パスワード" />
            <div v-if="errorMessage" class="alert-filed danger">
                <img src="../assets/icons/info-danger.svg" alt="info">
                <p>{{ errorMessage }}</p>
            </div>
            <button class="button" type="submit">ログイン</button>
            <button class="button" @click="type = 'register'; clear()">登録</button>
            <a @click.prevent="type = 'passwordReset'; clear()">パスワードをお忘れですか？</a>
        </form>

        <!-- Форма регистрации -->
        <form v-if="type === 'register'" @submit.prevent="register">
            <h3>登録フォーム</h3>
            <template v-if="!registerSuccess">
                <CustomInput :required="true" v-model="name" :labelText="'名前'" placeholderText="名前" />
                <CustomInput :required="true" :type="'email'" v-model="email" :labelText="'メールアドレス'" placeholderText="メールアドレス" />
                <CustomInput :required="true" :type="'password'" v-model="password" :labelText="'パスワード'" placeholderText="パスワード" />
                <CustomInput :required="true" :type="'password'" v-model="confirmPassword" :labelText="'パスワード確認'" placeholderText="パスワード確認" />
                <div v-if="errorMessage" class="alert-filed danger">
                    <img src="../assets/icons/info-danger.svg" alt="info">
                    <p>{{ errorMessage }}</p>
                </div>
                <button class="button" type="submit">登録</button>
            </template>
            <div v-if="registerSuccess" class="alert-filed success">
                <img src="../assets/icons/info.svg" alt="info">
                <p>ユーザーが正常に登録されました。</p>
            </div>
            <button class="button" @click="type = 'login'; clear()">戻る</button>


        </form>

        <!-- Форма сброса пароля -->
        <form v-if="type === 'passwordReset'" @submit.prevent="resetPassword">
            <h3>パスワードリセットフォーム</h3>
            <CustomInput :required="true" :type="'email'" v-model="email" :labelText="'メールアドレス'" placeholderText="メールアドレス" />
            <div v-if="errorMessage" class="alert-filed danger">
                <img src="../assets/icons/info-danger.svg" alt="info">
                <p>{{ errorMessage }}</p>
            </div>
            <div v-if="successMessage" class="alert-filed success">
                <p>{{ successMessage }}</p>
            </div>
            <button class="button" type="submit">リセット</button>
            <div v-if="emailSent" class="alert-filed success">
                <p>パスワードリセットメールが送信されました。</p>
            </div>
            <button class="button" @click="type = 'login'; clear()">戻る</button>
        </form>
    </div>
</template>
<script>
import { ref, defineComponent, inject } from 'vue';
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
        const errorMessage = ref('');
        const emailSent = ref(false);

        const type = ref('login');
        const { reloadUserData } = inject('auth');
        const { isAdminLoggedIn, admin_logout } = inject('admin_auth')

        const login = async () => {

            if (isAdminLoggedIn.value) {
                admin_logout(true);
            }

            const url = process.env.VUE_APP_BACKEND_URL + '/backend/auth.php?action=login';
            errorMessage.value = '';
            try {
                const response = await axios.post(url, { email: email.value, password: password.value }, { withCredentials: true });
                if (response.data.status === 'success') {
                    localStorage.setItem('auth_token', response.data.auth_token);
                    reloadUserData();
                    closeModal();
                    clear();
                } else {
                    errorMessage.value = response.data.message;
                }
            } catch (error) {
                errorMessage.value = 'ログイン中にエラーが発生しました。';
                console.error('Error:', error);
            }
        };

        const registerSuccess = ref(false);

        const register = async () => {
            errorMessage.value = '';
            if (password.value !== confirmPassword.value) {
                errorMessage.value = "Passwords do not match!";
                return;
            }
            const url = process.env.VUE_APP_BACKEND_URL + '/backend/auth.php?action=register';
            try {
                const response = await axios.post(url, { name: name.value, email: email.value, password: password.value }, { withCredentials: true });
                if (response.data.status === 'success') {

                    clear();
                    registerSuccess.value = true;
                    setTimeout(() => {
                        registerSuccess.value = false;
                        type.value = 'login';
                    }, 3000);
                } else {
                    errorMessage.value = response.data.message;
                }
            } catch (error) {
                errorMessage.value = '登録中にエラーが発生しました。';
                console.error('Error:', error);
            }
        };

        const closeModal = () => {
            emit('update:closeFlag', false);
        };


        function clear() {
            email.value = '';
            password.value = '';
            name.value = '';
            confirmPassword.value = '';
            errorMessage.value = '';
            emailSent.value = false;
        }

        const resetPassword = async () => {
            errorMessage.value = '';
            successMessage.value = '';

            const url = `${process.env.VUE_APP_BACKEND_URL}/backend/auth.php?action=pass_reset`;

            try {
                const response = await axios.post(url, { email: email.value }, { withCredentials: true });
                console.log(response.data);
                if (response.data.status === 'success') {
                    successMessage.value = 'リセットリンクが送信されました。メールを確認してください。';
                    emailSent.value = true;
                    clear();
                } else {
                    errorMessage.value = response.data.message;
                }
            } catch (error) {
                errorMessage.value = 'パスワードのリセット中にエラーが発生しました。';
                console.error('Error:', error);
            }
        };

        const successMessage = ref('');

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
            errorMessage,
            resetPassword,
            successMessage,
            emailSent,
            registerSuccess
        };
    }
});
</script>

<style lang="scss" scoped>
.form {
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

            @include respond-to('md') {
                min-width: unset;
            }
        }
    }

    .close {
        position: absolute;
        right: 20px;
        top: 20px;
        cursor: pointer;
    }
}

a {
    text-decoration: underline;
    cursor: pointer;
}
</style>
