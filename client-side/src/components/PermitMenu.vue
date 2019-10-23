<template>
<b-nav-item
  v-if="to !== null && $permissionManager.useful(to)"
  :to="to"
>{{msg}}</b-nav-item>
<b-nav-item
  v-else-if="modal !== null && $permissionManager.useful(modal)"
  v-b-modal="'modal-' + modal.substr(1)"
>{{msg}}</b-nav-item>
</template>

<script>
export default {
  name: 'PermitMenu',
  props: {
    msg: String,
    to: String,
    modal: String
  },
}
</script>
