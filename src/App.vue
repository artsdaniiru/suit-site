<template>
  <main>
    <MainHeader />
    <router-view />
    <MainFooter />
  </main>
</template>

<script setup>
import { defineComponent } from 'vue';
import MainHeader from './components/MainHeader.vue';
import MainFooter from './components/MainFooter.vue';

defineComponent({
  components: {
    MainHeader,
    MainFooter,
  },
});
</script>

<style>
body {
  margin: 0;
}

#app {
  font-family: 'Noto Sans JP', 'Noto Sans';
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  color: #2c3e50;
}

main {
  max-width: 1200px;
  margin-left: auto;
  margin-right: auto;
}
</style>


<!-- <template>
  <nav>
    <router-link to="/">Home</router-link> |
    <router-link to="/about">About</router-link> |
    <router-link to="/users">Users</router-link>
  </nav>
  <router-view />
</template>

<style>
#app {
  font-family: Avenir, Helvetica, Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  text-align: center;
  color: #2c3e50;
}

nav {
  padding: 30px;
}

nav a {
  font-weight: bold;
  color: #2c3e50;
}

nav a.router-link-exact-active {
  color: #42b983;
}
</style> -->
