<template>
<router-link :to="to" :class="{disabled: !useful(to)}">{{msg}}</router-link>
</template>

<script>
export default {
  name: 'PermitLink',
  props: {
    msg: String,
    to: String
  },
  methods: {
    useful: function(url) {
      return this.$permissionManager.useful(url)
    }
  }
}
</script>
