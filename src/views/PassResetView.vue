<template>
    <div>
        <div class="reset-pass">
            <h1 v-if="isInvalidLink">エラー</h1>
            <template v-else>
                <h1>パスワードリセット</h1>
                <template v-if="successMessage == ''">
                    <CustomInput v-model="pass.password" labelText="新しいパスワード" type="password" />
                    <CustomInput v-model="pass.password_confirmation" labelText="パスワード確認" type="password" />
                    <button class="button" @click="sendNewPassword" :disabled="isSubmitting">
                        送信
                    </button>
                </template>

                <div v-if="successMessage" class="alert-filed success">
                    <p>{{ successMessage }}</p>
                </div>
                <router-link v-if="successMessage" to="/">
                    <button class="button">ホームページへ戻る</button>
                </router-link>
            </template>

            <div v-if="errorMessage" class="alert-filed danger">
                <img src="../assets/icons/info-danger.svg" alt="info">
                <p class="error">{{ errorMessage }}</p>
            </div>

        </div>
    </div>
</template>


<script>
import { defineComponent, ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios'; // Убедитесь, что Axios подключен

export default defineComponent({
    name: 'PassResetView',
    setup() {
        const route = useRoute();
        const pass_reset_url = route.params.uid;

        const pass = ref({
            password: '',
            password_confirmation: '',
        });

        const isSubmitting = ref(false);
        const errorMessage = ref('');
        const successMessage = ref('');
        const isInvalidLink = ref(false);

        const checkPassResetUrl = async () => {
            try {
                const response = await axios.post(
                    `${process.env.VUE_APP_BACKEND_URL}/backend/auth.php?action=check_pass_reset`,
                    { pass_reset_url },
                    {
                        withCredentials: true,
                    }
                );

                if (response.data.status !== 'success') {
                    isInvalidLink.value = true;
                    errorMessage.value = '無効なパスワードリセットリンクです';
                }
            } catch (error) {
                errorMessage.value = 'リンク確認中にエラーが発生しました';
                console.error('Ошибка при проверке ссылки сброса пароля:', error);
            }
        };

        const sendNewPassword = async () => {
            if (pass.value.password !== pass.value.password_confirmation) {
                errorMessage.value = 'パスワードが一致しません!';
                return;
            }

            if (pass.value.password == '' || pass.value.password_confirmation == '') {
                errorMessage.value = 'パスワードを入力してください!';
                return;
            }

            isSubmitting.value = true;
            errorMessage.value = '';
            successMessage.value = '';

            const formData = {
                password: pass.value.password,
                pass_reset_url: pass_reset_url,
            };

            try {
                const response = await axios.post(
                    `${process.env.VUE_APP_BACKEND_URL}/backend/auth.php?action=approve_pass_reset`,
                    formData,
                    {
                        withCredentials: true,
                    }
                );

                if (response.status === 200) {
                    successMessage.value = 'パスワードがリセットされました';
                    pass.value = { password: '', password_confirmation: '' };
                } else {
                    errorMessage.value = 'パスワードのリセット中にエラーが発生しました';
                }
            } catch (error) {
                errorMessage.value = 'リクエスト中にエラーが発生しました';
                console.error('Ошибка при сбросе пароля:', error);
            } finally {
                isSubmitting.value = false;
            }
        };

        onMounted(() => {
            checkPassResetUrl();
        });

        return {
            pass_reset_url,
            pass,
            isSubmitting,
            errorMessage,
            successMessage,
            isInvalidLink,
            sendNewPassword,
        };
    },
});
</script>


<style lang="scss" scoped>
.reset-pass {
    display: flex;
    flex-direction: column;
    gap: 12px;
    width: 100%;
    max-width: 500px;
    margin-top: 20px;
    margin-left: auto;
    margin-right: auto;
    padding: 16px;
    border: 1px solid #d9d9d9;
    border-radius: 8px;
    height: min-content;

    h1 {
        text-align: center;
    }

    a {
        margin-left: auto;
        margin-right: auto;
    }

}
</style>
