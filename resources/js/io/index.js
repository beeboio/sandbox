import client from 'socket.io-client'

const socket = client((process.env.MIX_LARAVEL_WEBSOCKETS_HOST || '') + ':' + process.env.MIX_LARAVEL_WEBSOCKETS_PORT, {
  path: '/app/examples',
  query: {
    appKey: process.env.MIX_PUSHER_APP_KEY
  },
  transports: ['websocket']
})

export const io = client

export default socket
