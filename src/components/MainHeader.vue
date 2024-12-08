<template>
    <header>
        <div class="content">
            <router-link to="/" class="logo">
                <img src="../assets/icons/logo.svg" alt="logo">
                <span>サイトーズ</span>
            </router-link>
            <nav>
                <router-link to="/catalog"><span>カタログ</span></router-link>
                <router-link to="/guide"><span>ご利用ガイド</span></router-link>
                <router-link to="/delivery"><span>配送について</span></router-link>
                <router-link to="/contact"><span>連絡</span></router-link>
                <router-link v-if="isUserLoggedIn" to="/account"><span>マイページ</span></router-link>
                <button class="button" v-if="!isUserLoggedIn" @click="closeLogin = true"><span>ログイン</span></button>
                <div v-else class="account">
                    <img src="../assets/icons/user.svg" alt="logo" @click="goToAccount">
                    <span class="name" @click="goToAccount">{{ user['name'] }}</span>
                    <button @click="logout" class="button danger"><span>ログアウト</span></button>
                </div>

                <div class="cart" @click="goToCart">
                    <img src="../assets/icons/cart.svg" alt="logo">
                    <span v-if="cart.length != 0" class="count">{{ cart.length }}</span>
                </div>


            </nav>
        </div>
    </header>
    <LoginForm :closeFlag="closeLogin" @update:closeFlag="closeLogin = false"></LoginForm>
</template>
<script>

import { defineComponent, inject } from 'vue';
import { useRouter } from 'vue-router'

import LoginForm from './LoginForm.vue';

export default defineComponent({
    name: 'MainHeader',
    components: {
        LoginForm
    },
    setup() {
        const router = useRouter();

        const { cart } = inject('cart')

        const { user, isUserLoggedIn, logout } = inject('auth')


        const { closeLogin } = inject('login')


        function goToCart() {
            router.push('/cart');
        }
        function goToAccount() {
            router.push('/account');
        }

        return {
            cart,
            user,
            isUserLoggedIn,
            logout,
            closeLogin,
            goToCart,
            goToAccount
        };
    }
});
</script>

<style lang="scss" scoped>
header {
    border-bottom: 1px solid #d9d9d9;
    padding: 0px 32px 0px 32px;
    height: 90px;
    background: #fff;

    display: flex;
    align-items: center;

    .content {
        display: flex;
        justify-content: space-between;
        width: -webkit-fill-available;

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


        nav {
            display: flex;
            justify-content: flex-end;
            gap: 8px;
            align-items: center;

            a {
                text-decoration: none;
                font-weight: 400;
                font-size: 16px;
                line-height: 100%;
                color: #1e1e1e;
                padding: 0px 8px 0px 8px;
                align-items: center;
                display: flex;
                height: 32px;

                &:hover,
                &.router-link-exact-active {
                    background: #f5f5f5;
                    border-radius: 8px;
                }


            }

            button {
                padding: 8px;
                height: 32px;
            }

            .cart {
                position: relative;
                display: flex;
                align-items: center;
                cursor: pointer;

                img {
                    width: 26px;
                }

                .count {
                    position: absolute;
                    right: -12px;
                    top: -5px;
                    border-radius: 25px;
                    width: 20px;
                    height: 20px;
                    background: #ff7a00;
                    font-size: 12px;
                    text-align: center;
                    color: #fff;
                    align-content: center;
                }
            }

            .account {
                align-items: center;
                display: flex;
                gap: 10px;
                cursor: pointer;

                .name {
                    font-weight: 400;
                    font-size: 16px;
                    line-height: 100%;
                    color: #000;
                }

                img {
                    width: 24px;
                }
            }
        }
    }
}
</style>
