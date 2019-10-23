<template>
  <section class="contents">
    <h1>Login</h1>
    <form>
      <div class="form-group">
        <label for="ui-id">Login ID</label>
        <b-input id="ui-id" type="text" v-model="loginName" placeholder="Login ID" />
      </div>
      <div class="form-group">
        <label for="ui-pw">Password</label>
        <b-input type="password" id="ui-pw" v-model="password" placeholder="Password" />
      </div>
      <b-button v-on:click="login" variant="primary" block>Submit</b-button>
    </form>
    <b-modal id="modal-login-failed" :title="$appName" ok-only>
      <p>Incorrect Login ID or password.</p>
    </b-modal>
    <b-modal id="modal-login-vaidation-check-failed" :title="$appName" ok-only>
      <p>Enter Login ID or password.</p>
    </b-modal>
  </section>
</template>
<script>
export default {
  name: 'login',
  data: () => {
    return {
      loginName: '',
      password: '',
      
    };
  },
  methods: {
    login: function () {
      if (this.loginName === '' || this.password === '') {
        this.$bvModal.show('modal-login-vaidation-check-failed')
      } else {
        let params = new URLSearchParams()
        params.append('loginName', this.loginName)
        params.append('password', this.password)
        this.$http.post('/api/login.php', params)
          .then((res) => {
            this.$store.commit('setRole', res.data.role)
            this.$store.commit('setLoginName', res.data.loginName)
            this.$routingSyncAlert.push('Hello ' + this.loginName)
            this.$router.push('/')
          })
          .catch(() => {
            this.$bvModal.show('modal-login-failed')
          })
      }
    }
  },
  computed: {
  },
  components: {
  }
}
</script>

