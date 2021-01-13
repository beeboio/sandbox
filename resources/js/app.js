require('./bootstrap');

import Vue from 'vue'
import AuthModal  from '@/components/AuthModal'
import store from '@/store'
import router from '@/router'
import { mapActions, mapGetters } from 'vuex'
import io from 'socket.io-client'

window.socket = io(':' + process.env.MIX_LARAVEL_WEBSOCKETS_PORT, {
  path: '/chat',
  query: {
    appKey: process.env.MIX_PUSHER_APP_KEY
  },
  transports: ['websocket']
}).on('message', (data) => {
  console.log(data)
})

new Vue({
  components: {
    AuthModal
  },
  store,
  router,
  el: '#app',
  computed: {
    ...mapGetters('auth', [
      'user'
    ])
  },
  mounted () {
    // this.startSession()
  },
  methods: {
    ...mapActions('auth', [
      'showAuthModal',
      'startSession',
      'logout'
    ])
  }
})