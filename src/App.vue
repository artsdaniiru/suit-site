<template>
  <main>
    <MainHeader />
    <RouterView />
    <MainFooter />
  </main>
</template>

<!-- eslint-disable -->
<script>
import { defineComponent } from 'vue';

import MainHeader from './components/MainHeader.vue';
import MainFooter from './components/MainFooter.vue';


export default defineComponent({
  name: 'App',
  components: {
    MainHeader,
    MainFooter,
  },
  setup() {

    return {
    };
  }
});
</script>
