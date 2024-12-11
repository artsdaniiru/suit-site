<template>
    <form @submit.prevent="submitContactForm">

        <template v-if="!mail_send">

            <CustomInput v-model="contactForm.name" labelText="名前" placeholderText="名前を入力してください" :required="true" />
            <CustomInput v-model="contactForm.email" labelText="メール" placeholderText="メールアドレスを入力してください" type="email" :required="true" />
            <CustomInput v-model="contactForm.phone" labelText="電話番号" placeholderText="電話番号を入力してください" type="phone" />

            <div class="form-elem">
                <label for="message">メッセージ</label>
                <textarea id="message" v-model="contactForm.message" placeholder="メッセージを入力してください"></textarea>
            </div>
            <button :class="['button', { loading: lock_send }]" type="submit">送信</button>
        </template>
        <template v-else>
            <h2>メッセージは送信されました</h2>
        </template>

    </form>
</template>

<script>
import { defineComponent, onMounted, ref, inject } from "vue";
import axios from "axios";

export default defineComponent({
    name: "ContactForm",
    setup() {
        const mail_send = ref(false);
        const lock_send = ref(false);

        const { user, isUserLoggedIn } = inject("auth");

        const contactForm = ref({
            name: '',
            email: '',
            phone: '',
            message: '',
        });

        onMounted(() => {
            if (isUserLoggedIn.value) {
                contactForm.value.name = user.value.name;
                contactForm.value.email = user.value.email;
            }
        });

        const submitContactForm = async () => {
            lock_send.value = true;
            const formData = {
                to: contactForm.value.email,
                subject: contactForm.value.name,
                name: contactForm.value.name,
                phone: contactForm.value.phone,
                message: contactForm.value.message,
            };

            try {
                const response = await axios.post(
                    process.env.VUE_APP_BACKEND_URL + '/backend/sendmail.php?action=contact',
                    formData,
                    {
                        withCredentials: true
                    }
                );
                if (response.status === 200) {
                    console.log('Сообщение успешно отправлено!');
                    contactForm.value = { name: '', email: '', phone: '', message: '' }; // Сброс формы
                } else {
                    console.log('Ошибка при отправке сообщения.');
                }
            } catch (error) {
                console.error('Ошибка при отправке формы:', error);
                console.log('Не удалось отправить сообщение.');
            } finally {
                lock_send.value = false;
                mail_send.value = true;
                setTimeout(() => {
                    mail_send.value = false;
                }, 3000);
            }
        };

        return {
            contactForm,
            submitContactForm,
            mail_send,
            lock_send
        };
    }
});
</script>

<style lang="scss" scoped>
form {
    display: flex;
    flex-direction: column;
    gap: 24px;
    border: 1px solid #d9d9d9;
    border-radius: 8px;
    padding: 24px;

    .form-elem {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
}

.button {


    &.loading {
        cursor: not-allowed;
        background-color: #ccc;

        &:after {
            content: '';
            position: absolute;
            border: 3px solid transparent;
            border-top: 3px solid white;
            border-radius: 50%;
            width: 16px;
            height: 16px;
            animation: spin 1s linear infinite;
        }
    }
}

@keyframes spin {
    0% {
        transform: rotate(0deg);
    }

    100% {
        transform: rotate(360deg);
    }
}
</style>
