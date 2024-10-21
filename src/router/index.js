import {
  createRouter,
  createWebHistory
} from 'vue-router'
import HomeView from '../views/HomeView.vue'
import DashboardView from '../views/admin/DashboardView.vue'
import LoginView from '../views/admin/LoginView.vue'
import checkAdminAuth from './checkAdminAuth';

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
    const isAdminAuth = await checkAdminAuth();
    if (isAdminAuth) {
      if (to.path === '/admin') {
        next({ path: '/admin/dashboard' }); // Перенаправляем на /admin/dashboard, если уже авторизован
      } else {
        next(); // Администратор авторизован, продолжаем
      }
    } else {
      next({ path: '/admin/login' }); // Не авторизован, перенаправляем на страницу логина
    }
  } else if (to.path === '/admin') {
    // Проверка для маршрута /admin
    const isAdminAuth = await checkAdminAuth();
    if (isAdminAuth) {
      next({ path: '/admin/dashboard' }); // Если администратор авторизован, перенаправляем на dashboard
    } else {
      next({ path: '/admin/login' }); // Если не авторизован, перенаправляем на логин
    }
  } else {
    next(); // Пропускаем маршрут, если не требуется авторизация
  }
})

export default router
