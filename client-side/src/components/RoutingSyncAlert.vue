<template>
  <div>
    <b-alert v-for="(a, i) in currentAlerts" :key="i" show :variant="a.variant">{{a.message}}</b-alert>
  </div>
</template>
<script>
export default {
  data: () => {
    return {
      currentAlerts: [],
      nextAlerts: [],
    }
  },
  me: null,
  install: function (Vue) {
      Vue.prototype.$routingSyncAlert = this
  },
  push: function (message, variant = 'success') {
    this.me.push(message, variant)
  },
  replace: function (message, variant = 'success') {
    this.me.replace(message, variant)
  },
  name: 'RoutingSyncAlert',
  methods: {
    push (message, variant = 'success') {
      this.nextAlerts.push({
        message: message,
        variant: variant
      })
    },
    replace (message, variant = 'success') {
      this.currentAlerts = [{
        message: message,
        variant: variant
      }]
    }
  },
  watch: {
    '$route': function () {
      this.currentAlerts = this.nextAlerts
      this.nextAlerts = []
    }
  },
  mounted: function() {
    this.$routingSyncAlert.me = this;
  }
}
</script>

