<template>
  <b-container>
    <b-row>
      <b-col>
        <b-card>
          <p>
            This button has been pressed {{ i }} times since the server started.
          </p>
          <b-button
            variant="primary"
            @click.prevent="increment()"
          >
            Press Me
          </b-button>
        </b-card>
      </b-col>
    </b-row>
  </b-container>
</template>

<script>
export default {
  data () {
    return {
      i: 0
    }
  },
  mounted () {
    // anytime state is changed, update
    socket.on('Button.state', (i) => this.i = i)
    // load the current state
    socket.emit('Button.getState', (i) => this.i = i)
  },
  methods: {
    increment() {
      socket.emit('Button.increment')
    }
  }
}
</script>