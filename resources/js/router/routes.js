import Home from '@/views/Home'

import Button from '@/views/examples/Button'

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
  }
]

