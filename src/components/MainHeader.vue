<template>
    <header>
        <div class="logo">
            <img src="../assets/icons/logo.svg" alt="logo">
            <span>サイトーズ</span>
        </div>
        <nav>
            <router-link to="/"><span>カタログ</span></router-link>
            <router-link to="/delivery"><span>配送について</span></router-link>
            <router-link to="/contact"><span>連絡</span></router-link>
            <router-link to="/login"><span>ログイン</span></router-link>
            <button><span>ログイン</span></button>
            <div class="cart">
                <img src="../assets/icons/cart.svg" alt="logo">
                <span class="count">10</span>
            </div>
        </nav>
    </header>
</template>

<script setup>
import { defineComponent } from 'vue';

defineComponent({});
</script>

<style lang="scss" scoped>
header {
    padding: 32px;
    box-shadow: 0 4px 4px 0 rgba(0, 0, 0, 0.25);
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
    }

    .cart {
        position: relative;
        display: flex;
        align-items: center;

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
}
</style>
