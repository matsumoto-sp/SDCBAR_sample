import Vue from 'vue'
import Vuex from 'vuex'

Vue.use(Vuex)

const store = new Vuex.Store({

  state: {
    role: null,
    loginName: null
  },

  getters:{
    role(state) {
      return state.role
    },
    loginName(state) {
      return state.loginName
    }
  },

  mutations: {
    setRole(state, role){
      state.role = role
    },
    setLoginName(state, loginName){
      state.loginName = loginName
    }
  },
})
export default store
