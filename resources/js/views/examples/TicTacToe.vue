<template>

</template>

<script>
import socket from '@/io'

export default {
  mounted () {
    socket.on('state', (state) => console.log(state))
  }
}
</script>