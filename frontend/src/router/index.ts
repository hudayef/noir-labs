import { createRouter, createWebHistory } from 'vue-router'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: '/\,
      name: 'home',
      component: () => import('../pages/Home/HomeView.vue')
    },
    {
      path: '/auth',
      component: () => import('../layouts/AuthLayout.vue'),
      children: [
        {
          path: 'login',
          name: 'login',
          component: () => import('../pages/Auth/LoginView.vue')
        }
      ]
    }
  ]
})

export default router
