<template>
    <header>
        <router-link to="/" class="logo">
            <img src="../assets/icons/logo.svg" alt="logo">
            <span>サイトーズ</span>
        </router-link>
        <nav>
            <router-link to="/catalog"><span>カタログ</span></router-link>
            <router-link to="/delivery"><span>配送について</span></router-link>
            <router-link to="/contact"><span>連絡</span></router-link>
            <button v-if="!isUserLoggedIn"><span>ログイン</span></button>
            <div v-else class="account">
                <img src="../assets/icons/user.svg" alt="logo">
                <span class="name">{{ user['name'] }}</span>
                <button @click="logout" class="danger"><span>ログアウト</span></button>
            </div>
            <div class="cart">
                <img src="../assets/icons/cart.svg" alt="logo">
                <span v-if="cart_count != 0" class="count">{{ cart_count }}</span>
            </div>
        </nav>
    </header>
</template>
<!-- eslint-disable -->
<script>

import { defineComponent, inject } from 'vue';

export default defineComponent({
    name: 'MainHeader',
    setup() {

        const { cart_count, updateCount } = inject('cart_count')

        const { user, isUserLoggedIn, logout } = inject('auth')


        return {
            cart_count,
            updateCount,
            user,
            isUserLoggedIn,
            logout
        };
    }
});
</script>

<style lang="scss" scoped>
header {
    border-bottom: 1px solid #d9d9d9;
    padding: 32px;
    background: #fff;
    display: flex;
    justify-content: space-between;

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
        font-weight: 400;
        font-size: 16px;
        line-height: 100%;
        color: #f5f5f5;
        border: 1px solid #2c2c2c;
        border-radius: 8px;
        padding: 8px 8px 8px 8px;
        width: 100px;
        height: 32px;
        background: #2c2c2c;

        cursor: pointer;
        transition: background 0.3s ease;

        &:hover {
            background: #1e1e1e;
        }


        &.danger {
            border: 1px solid #c00f0c;
            background: #ec221f;

            &:hover {
                border: 1px solid #900b09;
                background: #c00f0c;
            }
        }
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
        }
    }

    .account {
        align-items: center;
        display: flex;
        gap: 10px;

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
</style>
