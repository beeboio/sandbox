require('./bootstrap');

import Vue from 'vue'
import AuthModal  from '@/components/AuthModal'
import store from '@/store'
import router from '@/router'
import { mapActions, mapGetters } from 'vuex'
import socket from '@/io'

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
  mounted () {
    //
  },
  methods: {
    ...mapActions('auth', [
      'showAuthModal',
      'startSession',
      'logout'
    ])
  }
})