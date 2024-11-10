import {
  createRouter,
  createWebHistory
} from 'vue-router'
import HomeView from '../views/HomeView.vue'
import CatalogView from '../views/admin/CatalogView.vue'
import ClientsView from '../views/admin/ClientsView.vue'
import OrdersView from '../views/admin/OrdersView.vue'
import LoginView from '../views/admin/LoginView.vue'
import NotFound from '../views/NotFound.vue'
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
    path: '/product/:uid',
    name: 'product',
    component: () => import('../views/ProductView.vue')
  },
  {
    path: '/cart',
    name: 'cart',
    component: () => import('../views/CartView.vue')
  },
  {
    path: '/admin/login',
    name: 'LoginView',
    component: LoginView
  },
  {
    path: '/admin/catalog',
    redirect: '/admin/catalog/products',
    meta: { requiresAdmin: true }  // Защищённый маршрут
  },
  {
    path: '/admin/catalog/products',
    component: CatalogView,
    props: { defaultTab: 'products' },
    meta: { requiresAdmin: true }  // Защищённый маршрут
  },
  {
    path: '/admin/catalog/options',
    component: CatalogView,
    props: { defaultTab: 'options' },
    meta: { requiresAdmin: true }  // Защищённый маршрут
  },
  {
    path: '/admin/clients',
    name: 'ClientsView',
    component: ClientsView,
    meta: { requiresAdmin: true }  // Защищённый маршрут
  },
  {
    path: '/admin/orders',
    name: 'OrdersView',
    component: OrdersView,
    meta: { requiresAdmin: true }  // Защищённый маршрут
  },
  {
    path: '/:catchAll(.*)', // Маршрут для любых неизвестных путей
    name: 'NotFound',
    component: NotFound
  }
]

// Создание роутера
const router = createRouter({
  history: createWebHistory(process.env.BASE_URL),
  routes,
  scrollBehavior() {
    return { top: 0 };
  },
})

// Перехватчик маршрутов
router.beforeEach(async (to, from, next) => {
  // Проверяем, требует ли маршрут авторизации
  if (to.matched.some(record => record.meta.requiresAdmin)) {
    const isAdminAuth = await checkAdminAuth();
    if (isAdminAuth) {
      if (to.path === '/admin') {
        next({ path: '/admin/catalog' }); // Перенаправляем на /admin/catalog, если уже авторизован
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
      next({ path: '/admin/catalog' }); // Если администратор авторизован, перенаправляем на catalog
    } else {
      next({ path: '/admin/login' }); // Если не авторизован, перенаправляем на логин
    }
  } else {
    next(); // Пропускаем маршрут, если не требуется авторизация
  }
})

export default router
