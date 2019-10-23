import Vue from 'vue'
import Vuex from 'vuex'
import 'bootstrap/dist/css/bootstrap.css'
import 'bootstrap-vue/dist/bootstrap-vue.css'
import BootstrapVue from 'bootstrap-vue'
import axios from 'axios'

import App from './App'
import router from './router'
import store from './store'
import './validation'
import PermissionManager from './permissionManager'
import PermitMenu from '@/components/PermitMenu'
import PermitLink from '@/components/PermitLink'
import RoutingSyuncAlert from '@/components/RoutingSyncAlert'

Vue.use(BootstrapVue)
Vue.config.productionTip = false
Vue.use(Vuex)
Vue.use(PermissionManager, store)
Vue.use(RoutingSyuncAlert)
Vue.component('permit-menu', PermitMenu)
Vue.component('permit-link', PermitLink)
Vue.component('routing-sync-alert', RoutingSyuncAlert)
Vue.prototype.$http = axios
Vue.prototype.$appName = 'SDCBAR Sample'

Vue.prototype.sql = function() {
  function escape(s) {
    let ctrlRegStr = '[\\x00-\\x1b]';
    let ctrlReg = new RegExp(ctrlRegStr, 'gu')
    return s
      .replace('/\\/ug', '\\\\')
      .replace(/'/ug, "''")
      .replace(/\n/ug, '\\n')
      .replace(/\r/ug, '\\r')
      .replace(/\t/ug, '\\t')
      .replace(ctrlReg, '')
  }
  let strs = arguments[0]
  let out = ''
  for (let i = 1, c = arguments.length; i <= c; i++) {
    out += strs[i - 1]
    if (i != c) out += arguments[i] == null ? 'NULL' : "'" + escape(arguments[i]) + "'"
  }
  return out;
}

new Vue({
  store,
  router,
  render: h => h(App),
}).$mount('#app')
