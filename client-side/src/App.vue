<template>
  <div id="app">
    <b-navbar toggleable="md" type="dark" variant="info">
      <b-navbar-toggle target="collapse-menu"></b-navbar-toggle>
      <permit-menu class="navbar-brand" to="/" :msg="$appName" />
      <b-collapse is-nav id="collapse-menu">
        <b-navbar-nav>
          <permit-menu to="/about" msg="About" />
          <permit-menu to="/users" msg="Users" />
          <permit-menu to="/my_profile" msg="My Profile" />
          <permit-menu to="/admin" msg="Admin" />
          <permit-menu to="/sql" msg="SQL" />
        </b-navbar-nav>
        <b-navbar-nav class="ml-auto">
          <permit-menu to="/signup" msg="Signup" />
          <permit-menu to="/login" msg="Login" />
          <permit-menu modal="/logout" msg="Logout" />
        </b-navbar-nav>
      </b-collapse>
    </b-navbar>
    <routing-sync-alert />
    <router-view class="m-md-3"/>
    <b-modal id="modal-logout" @ok="doLogout" :title="$appName">
      <p>Do you want to log out?</p>
    </b-modal>
  </div>
</template>

<style>
#app {
  font-family: 'Avenir', Helvetica, Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  color: #2c3e50;
}
.label {
  font-weight:bold;
}
label.password2 {
  text-align: right;
}

.nav-link {
  color: white;
}

h1 .float-right {
  margin-left: 12px;
}

</style>
<script>
export default {
  name: 'app',
  methods: {
    doLogout: function() {
      this.$http.post('/api/logout.php', {})
        .then(() => {
          this.$store.commit('setRole', 'anonymous')
          this.$store.commit('setLoginName', 'anonymous')
          if (this.$router.currentRoute.path == '/') {
            this.$routingSyncAlert.replace('Logged out')
          } else {
            this.$routingSyncAlert.push('Logged out')
            this.$router.push('/')
          }
        })
    }
  },
  computed: {
  },
}
</script>
