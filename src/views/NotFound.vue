<template>
    <div>
        <h1>404 - Page Not Found</h1>
        <p>The page you are looking for doesn't exist.</p>
        <router-link to="/">Go back to Home</router-link>
    </div>
</template>

<script>
import { defineComponent } from 'vue';

export default defineComponent({
    name: 'NotFound'
});
</script>
