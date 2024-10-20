<template>
  <main>
    <MainHeader v-if="!isAdminPage" />
    <AdminMainHeader v-else />
    <RouterView class="router-view" />
    <MainFooter />
  </main>
</template>

<!-- eslint-disable -->
<script>
import { defineComponent, provide, ref, onBeforeMount, computed } from 'vue';
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

    const cart_count = ref(0);


    // Функция для обновления количества в корзине
    function updateCount() {
      cart_count.value++;
    }

    // Предоставляем контекст для других компонентов
    provide('cart_count', {
      cart_count,
      updateCount,
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
      cart_count,
      user,
      isUserLoggedIn, // Возвращаем флаг авторизации
      isAdminPage,
    };
  },
});
</script>

<style lang="scss"></style>
