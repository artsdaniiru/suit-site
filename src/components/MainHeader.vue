<template>
    <header>
        <div class="content">
            <router-link to="/" class="logo">
                <img src="../assets/icons/logo.svg" alt="logo">
                <span>サイトーズ</span>
            </router-link>
            <nav class="desktop">
                <router-link to="/catalog"><span>カタログ</span></router-link>
                <router-link to="/guide"><span>ご利用ガイド</span></router-link>
                <router-link to="/contact"><span>連絡</span></router-link>
                <router-link v-if="isUserLoggedIn" to="/account"><span>マイページ</span></router-link>
                <button class="button" v-if="!isUserLoggedIn" @click="closeLogin = true"><span>ログイン</span></button>
                <div v-else class="account" @click="goToAccount">
                    <img src="../assets/icons/user.svg" alt="logo">
                    <span class="name">{{ user['name'] }}</span>
                    <button @click.stop="logout" class="button danger"><span>ログアウト</span></button>
                </div>

                <div class="cart" @click="goToCart">
                    <img src="../assets/icons/cart.svg" alt="logo">
                    <span v-if="cart.length != 0" class="count">{{ cart.length }}</span>
                </div>
            </nav>
            <div class="mob-menu" @click="toggleMobileMenu">
                <img src="../assets/icons/menu.svg" alt="logo">
            </div>
            <transition name="menu-slide">
                <div v-if="isMobileMenuOpen" class="mobile-menu" v-click-out-side="() => toggleMobileMenu()">
                    <nav>


                        <router-link to="/catalog" @click="closeMobileMenu"><span>カタログ</span></router-link>
                        <router-link to="/guide" @click="closeMobileMenu"><span>ご利用ガイド</span></router-link>
                        <router-link to="/contact" @click="closeMobileMenu"><span>連絡</span></router-link>
                        <router-link v-if="isUserLoggedIn" to="/account" @click="closeMobileMenu"><span>マイページ</span></router-link>
                        <div v-if="isUserLoggedIn" class="account">
                            <img src="../assets/icons/user.svg" alt="logo">
                            <span class="name">{{ user['name'] }}</span>
                        </div>
                        <button class="button" v-if="!isUserLoggedIn" @click="closeLogin = true; closeMobileMenu"><span>ログイン</span></button>
                        <button class="button danger" v-if="isUserLoggedIn" @click="logout; closeMobileMenu"><span>ログアウト</span></button>
                        <div class="cart" @click="goToCart">
                            <img src="../assets/icons/cart.svg" alt="logo">
                            <span v-if="cart.length != 0" class="count">{{ cart.length }}</span>
                        </div>
                    </nav>
                </div>
            </transition>
        </div>

    </header>
    <LoginForm :closeFlag="closeLogin" @update:closeFlag="closeLogin = false"></LoginForm>
</template>
<script>

import { defineComponent, inject, ref } from 'vue';
import { useRouter } from 'vue-router'

import LoginForm from './LoginForm.vue';

export default defineComponent({
    name: 'MainHeader',
    components: {
        LoginForm
    },
    setup() {
        const router = useRouter();

        const { cart } = inject('cart');
        const { user, isUserLoggedIn, logout } = inject('auth');
        const { closeLogin } = inject('login');

        const isMobileMenuOpen = ref(false);

        function toggleMobileMenu() {
            isMobileMenuOpen.value = !isMobileMenuOpen.value;
            if (isMobileMenuOpen.value) {
                document.body.classList.add('no-scroll');
            } else {
                document.body.classList.remove('no-scroll');
            }
        }

        function closeMobileMenu() {
            isMobileMenuOpen.value = false;
        }

        function goToCart() {
            router.push('/cart');
            closeMobileMenu();
        }

        function goToAccount() {
            router.push('/account');
            closeMobileMenu();
        }

        return {
            cart,
            user,
            isUserLoggedIn,
            logout,
            closeLogin,
            isMobileMenuOpen,
            toggleMobileMenu,
            closeMobileMenu,
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

    @include respond-to('md') {
        padding: 0px 16px 0px 16px;
    }

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

        .mob-menu {
            display: none;

            @include respond-to('md') {
                display: block;
            }

            img {
                cursor: pointer;
                width: 24px;
            }
        }

        nav {
            display: flex;
            justify-content: flex-end;
            gap: 8px;
            align-items: center;

            &.desktop {
                @include respond-to('md') {
                    display: none;
                }
            }

            @include respond-to('md') {
                flex-direction: column;
                justify-content: unset;
            }


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

    .mobile-menu {
        position: fixed;
        top: 0;
        right: 0;
        height: 100vh;
        width: 70%;
        background: #fff;
        box-shadow: -2px 0 5px rgba(0, 0, 0, 0.1);
        display: flex;
        flex-direction: column;
        padding: 16px;
        z-index: 1000;
    }
}

.menu-slide-enter-active,
.menu-slide-leave-active {
    transition: transform 0.3s ease-in-out, opacity 0.3s ease-in-out;
}

.menu-slide-enter-from {
    transform: translateX(100%);
    opacity: 0;
}

.menu-slide-leave-to {
    transform: translateX(100%);
    opacity: 0;
}

.menu-slide-enter-to {
    transform: translateX(0);
    opacity: 1;
}

.menu-slide-leave-from {
    transform: translateX(0);
    opacity: 1;
}
</style>
