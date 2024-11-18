<template>
  <main>
    <MainHeader v-if="!isAdminPage" />
    <AdminMainHeader v-else />
    <RouterView class="router-view" />
    <MainFooter />
  </main>
</template>

<script>
import { defineComponent, provide, ref, onBeforeMount, computed, watch } from 'vue';
import axios from 'axios';

import MainHeader from './components/MainHeader.vue';
import AdminMainHeader from './components/AdminMainHeader.vue';
import MainFooter from './components/MainFooter.vue';

import { useRouter, useRoute } from 'vue-router'

export default defineComponent({
  name: 'App',
  components: {
    MainHeader,
    MainFooter,
    AdminMainHeader
  },
  setup() {

    const router = useRouter();
    const route = useRoute();

    const cart = ref(localStorage.getItem('cart') === null ? [] : JSON.parse(localStorage.getItem('cart')));

    watch(cart, (newValue) => {
      localStorage.setItem('cart', JSON.stringify(newValue));
      if (isUserLoggedIn.value) {
        updateCartInBackend();
      }
    }, { deep: true });

    async function updateCartInBackend() {
      const auth_token = localStorage.getItem('auth_token');
      if (!auth_token) {
        console.log('No auth token found, site is available for guests');
        return;
      }

      try {
        const response = await axios.post(process.env.VUE_APP_BACKEND_URL + '/backend/auth.php?action=update_cart', {
          cart: cart.value
        }, {
          withCredentials: true
        });

        console.log(response);


        if (response.data.status === 'success') {
          console.log('Cart updated successfully');
        } else {
          console.error('Error:', response.data.message);
        }
      } catch (error) {
        console.error('An error occurred while updating cart:', error);
      }
    }



    function addToCart(item) {
      cart.value.push(item);
    }

    function deleteFromCart(id) {
      cart.value = cart.value.filter(item => item.id !== id);
    }

    function updateCartItem(id, updatedItem) {
      cart.value = cart.value.map(item => {
        if (item.id === id) {
          return { ...item, ...updatedItem }; // Обновляем только нужные поля
        }
        return item; // Возвращаем неизмененный элемент
      });
    }
    function updateCart(cart) {
      cart.value = cart;
    }

    // Предоставляем контекст для других компонентов
    provide('cart', {
      cart,
      addToCart,
      deleteFromCart,
      updateCartItem,
      updateCart
    });



    //АВТОРИЗАЦИЯ
    const user = ref({});
    const isUserLoggedIn = ref(false); // Флаг для проверки авторизации




    // Функция для получения данных пользователя только если есть токен
    const fetchUserData = async () => {
      const auth_token = localStorage.getItem('auth_token');

      if (!auth_token) {
        console.log('No auth token found, site is available for guests');
        return; // Не загружаем данные пользователя, просто продолжаем работу
      }

      try {
        const response = await axios.get(process.env.VUE_APP_BACKEND_URL + '/backend/auth.php?action=get_user', {
          withCredentials: true
        });

        if (response.data.status === 'success') {
          user.value = response.data.user; // Сохраняем данные пользователя
          isUserLoggedIn.value = true; // Устанавливаем флаг авторизации
          console.log('User data:', user.value); // Выводим в консоль
          if (response.data.user.cart != null) {
            cart.value = JSON.parse(response.data.user.cart);
            localStorage.setItem('cart', JSON.stringify(cart.value));
          }

        } else {
          console.error('Error:', response.data.message);
          // localStorage.removeItem('auth_token'); // Удаляем токен, если он недействителен
        }
      } catch (error) {
        console.error('An error occurred while fetching user data:', error);
        // localStorage.removeItem('auth_token'); // Удаляем токен при ошибке
      }
    };


    const logout = async () => {
      try {
        const response = await axios.post(process.env.VUE_APP_BACKEND_URL + '/backend/auth.php?action=logout', {}, {
          withCredentials: true // Передача куки сессии
        });

        if (response.data.status === 'success') {
          // Логаут успешен
          console.log(response.data.message);

          // Очищаем локальные данные (например, токен или пользовательские данные)
          localStorage.removeItem('auth_token');

          isUserLoggedIn.value = false;
          user.value = {};
        } else {
          console.error('Logout failed:', response.data.message);
        }
      } catch (error) {
        console.error('Error during logout:', error);
      }
    };

    function reloadUserData() {
      fetchUserData();
    }

    provide('auth', {
      user,
      isUserLoggedIn,
      logout,
      reloadUserData
    });



    //АВТОРИЗАЦИЯ
    const admin_user = ref({});
    const isAdminLoggedIn = ref(false); // Флаг для проверки авторизации




    // Функция для получения данных пользователя только если есть токен
    const fetchAdminUserData = async () => {

      try {
        const response = await axios.get(process.env.VUE_APP_BACKEND_URL + '/backend/admin/auth.php?action=get_user', {
          withCredentials: true
        });

        if (response.data.status === 'success') {
          admin_user.value = response.data.user; // Сохраняем данные пользователя
          console.log(response.data.user);

          isAdminLoggedIn.value = true; // Устанавливаем флаг авторизации
          console.log('User data:', user.value); // Выводим в консоль
        } else {
          console.error('Error:', response.data.message);
          // localStorage.removeItem('auth_token'); // Удаляем токен, если он недействителен
        }
      } catch (error) {
        console.error('An error occurred while fetching user data:', error);
        // localStorage.removeItem('auth_token'); // Удаляем токен при ошибке
      }
    };


    const admin_logout = async () => {
      try {
        const response = await axios.post(process.env.VUE_APP_BACKEND_URL + '/backend/admin/auth.php?action=logout', {}, {
          withCredentials: true // Передача куки сессии
        });

        if (response.data.status === 'success') {
          // Логаут успешен
          console.log(response.data.message);

          // Очищаем локальные данные (например, токен или пользовательские данные)
          localStorage.removeItem('admin_auth_token');

          isAdminLoggedIn.value = false;
          admin_user.value = {};
          router.push('/admin/login');
        } else {
          console.error('Logout failed:', response.data.message);
        }
      } catch (error) {
        console.error('Error during logout:', error);
      }
    };

    function reloadAdminUserData() {
      fetchAdminUserData();
    }

    provide('admin_auth', {
      admin_user,
      isAdminLoggedIn,
      admin_logout,
      reloadAdminUserData
    });


    onBeforeMount(() => {
      const admin_auth_token = localStorage.getItem('admin_auth_token');

      if (!admin_auth_token) {
        console.log('check client');
        fetchUserData();
      } else {
        isAdminLoggedIn.value = true;
        fetchAdminUserData();// Загружаем данные пользователя, только если есть токен
      }
    })


    // Функция для проверки, является ли текущий маршрут админским
    const isAdminPage = computed(() => {
      return route.path.startsWith('/admin');
    });



    return {
      user,
      isUserLoggedIn, // Возвращаем флаг авторизации
      isAdminPage,
    };
  },
});
</script>

<style lang="scss"></style>
