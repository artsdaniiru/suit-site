import {
  createRouter,
  createWebHistory
} from 'vue-router'
import HomeView from '../views/HomeView.vue'
import DashboardView from '../views/admin/DashboardView.vue'
import LoginView from '../views/admin/LoginView.vue'
import axios from 'axios';

// Маршруты
const routes = [
  {
    path: '/',
    name: 'home',
    component: HomeView
  },
  {
    path: '/about',
    name: 'about',
    component: () => import( /* webpackChunkName: "about" */ '../views/AboutView.vue')
  },
  {
    path: '/users',
    name: 'users',
    component: () => import( /* webpackChunkName: "about" */ '../views/UsersView.vue')
  },
  {
    path: '/catalog',
    name: 'catalog',
    component: () => import('../views/CatalogView.vue')
  },
  {
    path: '/test-api',
    name: 'Test API',
    component: () => import('../views/TestApiView.vue')
  },
  {
    path: '/product',
    name: 'product',
    component: () => import('../views/ProductView.vue')
  },
  {
    path: '/admin/login',
    name: 'LoginView',
    component: LoginView
  },
  {
    path: '/admin/dashboard',
    name: 'DashboardView',
    component: DashboardView,
    meta: { requiresAdmin: true }  // Защищённый маршрут
  }
]

// Создание роутера
const router = createRouter({
  history: createWebHistory(process.env.BASE_URL),
  routes
})

// Перехватчик маршрутов
router.beforeEach(async (to, from, next) => {
  // Проверяем, требует ли маршрут авторизации
  if (to.matched.some(record => record.meta.requiresAdmin)) {
    try {
      // Проверяем авторизацию администратора
      const response = await axios.get(process.env.VUE_APP_BACKEND_URL + '/backend/admin/auth.php?action=get_user', {
        withCredentials: true
      });
      if (response.data.status === 'success') {
        next(); // Администратор авторизован
      } else {
        next({ name: 'LoginView' }); // Не авторизован, перенаправляем на страницу логина
      }
    } catch (error) {
      next({ name: 'LoginView' });
    }
  } else {
    next();
  }
})

export default router
