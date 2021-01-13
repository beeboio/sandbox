import axios from 'axios'
import store from '@/store'

axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'

const token = document.head.querySelector('meta[name="csrf-token"]')
if (token) {
  axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content
} else {
  console.error(
    'CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token'
  )
}

/*
axios.interceptors.response.use(
  response => response,
  error => {
    // authentication errors should display auth modal
    if (
      error.response && (
        error.response.status === 429 ||
        error.response.status === 419 ||
        error.response.status === 401
      )
    ) {
      store.dispatch('auth/showAuthModal')
    }

    if (error.response && error.response.status === 500) {
      window.console && console.error(error)
      // window.location.replace('/#/pages/500')
    }

    return Promise.reject(error)
  }
)
*/

window.axios = axios

const api = (path, version) => `/api/v${version || 1}/${path}`

export const me = () =>
  axios.get(api('me'))

export const login = (credentials) =>
  axios.post('/login', credentials)

export const logout = () =>
  axios.post('/logout')
