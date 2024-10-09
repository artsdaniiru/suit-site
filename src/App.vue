<template>
  <main>
    <MainHeader />
    <RouterView />
    <MainFooter />
  </main>
</template>

<!-- eslint-disable -->
<script>
import { defineComponent, provide, ref } from 'vue';

import MainHeader from './components/MainHeader.vue';
import MainFooter from './components/MainFooter.vue';



export default defineComponent({
  name: 'App',
  components: {
    MainHeader,
    MainFooter,
  },
  setup() {

    const cart_count = ref(0)

    function updateCount() {
      cart_count.value++;
    }

    provide('cart_count', {
      cart_count,
      updateCount
    })


    return {
    };
  }
});
</script>
