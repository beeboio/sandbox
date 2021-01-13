import { me, logout } from '@/api'

const defaultUser = {
  id: null,
  email: null,
  name: null
}

const state = {
  action: null,
  user: defaultUser
}

const getters = {
  action: (state) => state.action,
  user: (state) => state.user
}

const mutations = {
  action: (state, action) => state.action = action,
  user: (state, user) => state.user = user
}

const actions = {
  showAuthModal: ({commit}, action) => commit('action', (action === undefined) ? 'login' : action),
  hideAuthModal: ({commit}) => commit('action', null),
  startSession: ({commit}, auth) => me().then(response => commit('user', response.data)),
  logout: ({commit}) => logout().then(response => {
    commit('user', defaultUser)
    document.location.replace('/')
  })
}

export default {
  namespaced: true,
  state,
  getters,
  mutations,
  actions
}