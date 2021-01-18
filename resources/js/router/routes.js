import Home from '@/views/Home'

import Button from '@/views/examples/Button'
import TicTacToe from '@/views/examples/TicTacToe'

export default [
  {
    path: '/',
    name: 'home',
    redirect: '/examples/button'
  },
  {
    path: '/examples/button',
    name: 'examples.button',
    component: Button
  },
  {
    path: '/examples/tictactoe',
    name: 'examples.tictactoe',
    componet: TicTacToe
  }
]

