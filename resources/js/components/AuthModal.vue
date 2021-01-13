<template>
  <b-modal
    :visible="action !== null"
    :hide-header="true"
    :hide-footer="true"
    no-close-on-backdrop
    no-close-on-esc
  >
    <b-form
      v-if="'login' === action"
      class="p-4"
      @submit.prevent="handleLogin()"
    >
      <h1 class="h4 mb-3">
        Log In
      </h1>
      <b-form-group
        label="Email"
        label-for="login_email"
        :invalid-feedback="invalidEmailFeedback"
        :state="stateEmail"
      >
        <b-input
          id="login_email"
          name="login[email]"
          type="email"
          :state="stateEmail"
          v-model="credentials.email"
        ></b-input>
      </b-form-group>
      <b-form-group
        label="Password"
        label-for="login_password"
        class="mb-0"
      >
        <div
          class="position-relative"
        >
          <b-input
            v-if="hidePassword"
            id="login_password"
            name="login[password]"
            type="password"
            v-model="credentials.password"
          ></b-input>
          <b-input
            v-if="!hidePassword"
            id="login_password"
            name="login[password]"
            type="text"
            v-model="credentials.password"
          ></b-input>
          <b-button
            class="toggle-password text-muted"
            @click.prevent="hidePassword = !hidePassword"
            variant="link"
          >
            <i
              v-if="hidePassword"
              class="fa fa-fw fa-eye"
            ></i>
            <i
              v-if="!hidePassword"
              class="fa fa-fw fa-eye-slash"
            ></i>
            <span class="sr-only">
              Show Password
            </span>
          </b-button>
        </div>
        <div class="text-right">
          <a
            href="#"
            @click.prevent="showAuthModal('forgot')"
            class="small"
          >
            Forgot Password?
          </a>
        </div>
      </b-form-group>
      <b-form-group
        class="mb-4"
      >
        <b-checkbox
          id="login_remember"
          name="login[remember]"
          v-model="credentials.remember"
        >
          Remember me
        </b-checkbox>
      </b-form-group>
      <div class="d-flex align-items-center">
        <b-button
          variant="primary"
          type="submit"
          class="px-4"
          @click.prevent="handleLogin"
        >
          Login
        </b-button>
        <span
          class="ml-3 small"
        >
          New user?
          <a
            href="#"
            @click.prevent="showAuthModal('register')"
          >
            Sign up
          </a>
        </span>
      </div>
    </b-form>
    <b-alert
      :show="error.code === 419"
      class="mb-0"
      variant="warning"
    >
      Oops! You need to <a href="#" @click="reload()">refresh</a> and then try again.
    </b-alert>
  </b-modal>
</template>

<style scoped>
.toggle-password {
  position: absolute;
  top: 0;
  right: 0;
}
</style>

<script>
import { mapGetters, mapActions } from 'vuex'
import { login } from '@/api'

export default {
  data () {
    return {
      credentials: {
        email: null,
        password: null,
        remember: null
      },
      error: {
        code: null
      },
      errors: {},
      hidePassword: true
    }
  },
  watch: {
    errors: {
      handler () {
        //
      },
      deep: true
    }
  },
  computed: {
    ...mapGetters('auth', [
      'action'
    ]),
    invalidEmailFeedback () {
      return this.errors.email ? this.errors.email[0] : null
    },
    stateEmail () {
      if (this.invalidEmailFeedback) {
        return false
      } else {
        return null
      }
    }
  },
  methods: {
    ...mapActions('auth', [
      'showAuthModal',
      'hideAuthModal'
    ]),
    async handleLogin () {
      try {
        this.errors = {}
        this.error.code = null
        await login(this.credentials)
        this.reload()
      } catch (e) {
        if (e.response) {
          this.error.code = e.response.status
          this.errors = e.response.data.errors
        }
      }
    },
    reload () {
      document.location.reload()
    }
  }
}
</script>