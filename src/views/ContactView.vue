<template>
    <div>
        <div class="contact">
            <h1>連絡</h1>
            <ContactForm />
        </div>
    </div>

</template>

<script>
import { defineComponent } from 'vue';
import ContactForm from '../components/ContactForm.vue';

export default defineComponent({
    name: 'ContactView',
    components: {
        ContactForm,
    },
    setup() {

    },
});
</script>

<style lang="scss" scoped>
.contact {
    h1 {
        text-align: center;
    }

    margin-top: 20px;
    margin-left: auto;
    margin-right: auto;

    width: 80%;

    @include is-mobile() {
        margin-left: unset;
        margin-right: unset;
        padding-left: 24px;
        padding-right: 24px;
        width: -webkit-fill-available;
    }

}
</style>
