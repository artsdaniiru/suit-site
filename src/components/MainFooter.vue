<template>
    <footer>
        <div class="social-links">
            <a href="#" aria-label="X">X</a>
            <a href="#" aria-label="Instagram">Instagram</a>
            <a href="#" aria-label="YouTube">YouTube</a>
            <a href="#" aria-label="LinkedIn">LinkedIn</a>
        </div>
    </footer>
</template>

<script setup>
import { defineComponent } from 'vue';

defineComponent({});
</script>

<style scoped>
footer {
    background-color: #f8f8f8;
    padding: 20px;
    text-align: center;
    border-top: 1px solid #eaeaea;
}

.social-links a {
    margin: 0 10px;
    text-decoration: none;
    font-size: 24px;
    color: #2c3e50;
}

.social-links a:hover {
    color: #42b983;
}
</style>
