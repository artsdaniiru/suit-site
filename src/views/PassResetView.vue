<template>
    <div class="reset-pass">
        <CustomInput v-model="pass.password" labelText="パスワード" type="password" />
        <CustomInput v-model="pass.password_confirmation" labelText="パスワード確認" type="password" />
        <button class="button" @click="sendNewPassword" :disabled="isSubmitting">
            送信
        </button>
        <div v-if="errorMessage" class="alert-filed danger">
            <p class="error">{{ errorMessage }}</p>
        </div>
        <div v-if="successMessage" class="alert-filed success">
            <p v-if="successMessage">{{ successMessage }}</p>
        </div>


    </div>
</template>


<script>
import { defineComponent, ref } from 'vue';
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

        const sendNewPassword = async () => {
            if (pass.value.password !== pass.value.password_confirmation) {
                errorMessage.value = 'Пароли не совпадают!';
                return;
            }

            isSubmitting.value = true;
            errorMessage.value = '';
            successMessage.value = '';

            const formData = {
                password: pass.value.password,
                pass_reset_url: pass_reset_url, // Если необходимо передать ID заказа
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

        return {
            pass_reset_url,
            pass,
            isSubmitting,
            errorMessage,
            successMessage,
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
    margin: 20px auto;
    padding: 16px;
    border: 1px solid #ddd;
    border-radius: 8px;
    height: min-content;



    .error {
        color: red;
        font-size: 14px;
    }

    .success {
        color: green;
        font-size: 14px;
    }
}
</style>
