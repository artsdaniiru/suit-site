<template>
    <header>
        <nav>
            <router-link to="/">商品</router-link> |
            <router-link to="/delivery">配送について</router-link> |
            <router-link to="/contact">連絡</router-link> |
            <router-link to="/login">ログイン</router-link>
        </nav>
    </header>
</template>

<script setup>
import { defineComponent } from 'vue';

defineComponent({});
</script>

<style scoped>
header {
    background-color: #fff;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

nav {
    display: flex;
    justify-content: flex-end;
    gap: 20px;
}

nav a {
    font-weight: bold;
    color: #2c3e50;
    text-decoration: none;
}

nav a.router-link-exact-active {
    color: #42b983;
}
</style>
