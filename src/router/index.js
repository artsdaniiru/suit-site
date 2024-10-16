import {
  createRouter,
  createWebHistory
} from 'vue-router'
import HomeView from '../views/HomeView.vue'

const routes = [{
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
  // route level code-splitting
  // this generates a separate chunk (about.[hash].js) for this route
  // which is lazy-loaded when the route is visited.
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
}
]

const router = createRouter({
  history: createWebHistory(process.env.BASE_URL),
  routes
})

export default router