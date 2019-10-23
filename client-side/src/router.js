import Vue from 'vue'
import Router from 'vue-router'
import PermissionManager from './permissionManager'
import store from './store'
import axios from 'axios'

Vue.use(Router)

const router = new Router({
  mode: 'history',
  base: process.env.BASE_URL,
  routes: [
    {
      path: '/',
      name: 'top',
      component: () => import('./views/Top.vue')
    },
    {
      path: '/about',
      name: 'about',
      component: () => import('./views/About.vue')
    },
    {
      path: '/signup',
      name: 'signup',
      component: () => import('./views/Signup.vue')
    },
    {
      path: '/login',
      name: 'login',
      component: () => import('./views/Login.vue')
    },
    {
      path: '/users',
      name: 'users',
      component: () => import('./views/Users.vue')
    },
    {
      path: '/users/:id',
      name: 'user',
      component: () => import('./views/User.vue')
    },
    {
      path: '/my_profile',
      name: 'my_profile',
      component: () => import('./views/MyProfile.vue')
    },
    {
      path: '/my_profile/edit',
      name: 'my_profile_edit',
      component: () => import('./views/MyProfileEdit.vue')
    },
    {
      path: '/admin',
      name: 'admin',
      component: () => import('./views/Admin.vue')
    },
    {
      path: '/sql',
      name: 'sql',
      component: () => import('./views/Sql.vue')
    },
  ],
})


router.beforeEach((to, from, next) => {
  function nextPage() {
    if (PermissionManager.useful(to.path)) {
      next()
    }
  }
  if (store.state.role === null) {
    let params = new URLSearchParams()
    params.append('sql', 'SELECT my_role() AS role, my_login_name() AS loginName')
    axios.post('/api/query.php', params)
      .then((res) => {
        store.commit('setRole', res.data[0].role)
        store.commit('setLoginName', res.data[0].loginName)
        nextPage()
      })
  } else {
    nextPage()
  }
})

export default router
