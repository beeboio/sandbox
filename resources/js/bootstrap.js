window.Vue = require('vue')

import { BootstrapVue } from 'bootstrap-vue'
import Echo from 'laravel-echo';

window._ = require('lodash');

Vue.use(BootstrapVue)

window.Pusher = require('pusher-js');

window.Echo = new Echo({
  broadcaster: 'pusher',
  key: process.env.MIX_PUSHER_APP_KEY,
  cluster: process.env.MIX_PUSHER_APP_CLUSTER,
  wsHost: window.location.hostname,
  wsPort: process.env.MIX_LARAVEL_WEBSOCKETS_PORT,
  wssPort: process.env.MIX_LARAVEL_WEBSOCKETS_PORT,
  forceTLS: false,
  encrypted: true,
  disableStats: true,
  enabledTransports: ['ws', 'wss']
});
