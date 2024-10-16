<template>
    <footer>
        <div class="content">
            <router-link to="/" class="logo">
                <img src="../assets/icons/logo.svg" alt="logo">
                <span>サイトーズ</span>
            </router-link>
            <div class="social-links">
                <a href="#" aria-label="X"><img src="../assets/icons/x.svg" alt="x"></a>
                <a href="#" aria-label="Instagram"><img src="../assets/icons/instagram.svg" alt="Instagram"></a>
                <a href="#" aria-label="YouTube"><img src="../assets/icons/youtube.svg" alt="YouTube"></a>
            </div>
        </div>

    </footer>
</template>

<script>
import { defineComponent } from 'vue';

export default defineComponent({
    name: 'MainFooter',
    setup() {

        return {
        };
    }
});
</script>

<style lang="scss" scoped>
footer {
    border-top: 1px solid #d9d9d9;
    padding: 0px 32px 0px 32px;
    height: 110px;
    margin-top: auto;
    margin-bottom: 0px;
    display: flex;
    align-items: center;


    .content {
        display: flex;
        flex-direction: column;
        gap: 20px;

        .logo {
            display: flex;
            gap: 24px;

            font-weight: 400;
            font-size: 16px;
            color: #000;
            align-items: center;
            text-decoration: unset;

            img {
                width: 34px;
            }
        }


        .social-links {
            display: flex;
            gap: 16px;

            a {
                text-decoration: unset;

                img {
                    width: 24px;
                }
            }
        }
    }




}
</style>
