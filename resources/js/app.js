require('./bootstrap');

import Vue from 'vue'
import AuthModal  from '@/components/AuthModal'
import store from '@/store'
import router from '@/router'
import { mapActions, mapGetters } from 'vuex'
import io from 'socket.io-client'

const socket = io(':' + process.env.MIX_LARAVEL_WEBSOCKETS_PORT, {
  path: '/app/chat',
  query: {
    appKey: process.env.MIX_PUSHER_APP_KEY
  },
  transports: ['websocket']
}).on('message', (data) => {
  console.log(data)
})

// for fun
window.socket = socket

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
  data () {
    return {
      i: 0
    }
  },
  mounted () {
    // anytime state is changed, update
    socket.on('Chat.state', (i) => this.i = i)
    // load the current state
    socket.emit('Chat.getState', (i) => this.i = i)
  },
  methods: {
    ...mapActions('auth', [
      'showAuthModal',
      'startSession',
      'logout'
    ]),
    increment () {
      socket.emit('Chat.increment')
    }
  }
})