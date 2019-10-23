<template>
  <section class="contents">
    <h1>Sign up</h1>
    <form>
      <b-container fluid>
        <b-row class="my-2">
          <b-col sm="3" class="label">Login Name</b-col>
          <b-col sm="9">
            <validation-provider
              name="loginName"
              rules="required|max:20"
              ref="loginName"
              v-slot="{ errors, valid, pristine}">
              <b-input 
                type="text"
                :state="(pristine && !firstSubmit) ? null : valid"
                id="signup-login-name"
                v-model="user.loginName" trim />
              <b-form-invalid-feedback>{{errors[0]}}</b-form-invalid-feedback>
            </validation-provider>
          </b-col>
        </b-row>
        <b-row class="my-2">
          <b-col sm="3" class="label"><label for ="signup-email">E-Mail</label></b-col>
          <b-col sm="9">
            <validation-provider
              name="email"
              rules="required|email"
              ref="email"
              v-slot="{ errors, valid, pristine }">
              <b-input 
                type="email"
                :state="(pristine && !firstSubmit) ? null : valid"
                id="signup-email"
                v-model="user.email" trim />
              <b-form-invalid-feedback>{{errors[0]}}</b-form-invalid-feedback>
            </validation-provider>
          </b-col>
        </b-row>
        <b-row class="my-2">
          <b-col sm="3" class="label"><label for ="signup-password">Password</label></b-col>
          <b-col sm="3">
            <validation-provider
              name="password"
              rules="required|confirmed:confirm"
              ref="password"
              v-slot="{ errors, valid, pristine }">
              <b-input
                type="password"
                :state="(pristine && !firstSubmit) ? null : valid && !user.password.length ? null : valid"
                id="signup-password"
                v-model="user.password" />
              <b-form-invalid-feedback>{{errors[0]}}</b-form-invalid-feedback>
            </validation-provider>
          </b-col>
          <b-col sm="3" class="label">
            <label class="password2 d-none d-sm-block" for ="signup-password-2">Confirm</label>
            <label class="d-sm-none d-block" for ="signup-password-2">Password Confirm</label>
          </b-col>
          <b-col sm="3">
            <validation-provider
              name="confirm"
              rules="required|confirmed:password"
              ref="password2"
              v-slot="{ errors, valid, pristine }">
              <b-input
                type="password"
                :state="(pristine && !firstSubmit) ? null : valid && !user.password_2.length ? null : valid"
                id="signup-password-2"
                v-model="user.password_2" />
              <b-form-invalid-feedback>{{errors[0]}}</b-form-invalid-feedback>
            </validation-provider>
          </b-col>
        </b-row>
        <b-row class="my-4">
          <b-col sm="12">
            <b-button v-on:click="submit" variant="primary" block>Submit</b-button>
          </b-col>
        </b-row>
      </b-container>
    </form>
    <b-modal id="modal-signup-submit" @ok="okSubmit" :title="$appName">
      <p>Do you want to sign up?</p>
    </b-modal>
    <b-modal id="modal-signup-vaidation-check-failed" :title="$appName" ok-only>
      <p>Please execute submit after solving all errors.</p>
    </b-modal>
    <b-modal id="modal-signup-signup-failed" :title="$appName" ok-only>
      <p>Signup failed. Please change your login name.</p>
    </b-modal>
  </section>
</template>
<script>
export default {
  data: () => {
    return {
      user: {
        loginName: '',
        email: '',
        password: '',
        password_2: ''
      },
      firstSubmit: false
    }
  },
  methods: {
    submit: function () {
      this.firstSubmit = true;
      let promises = [];
      for (let r in this.$refs) {
        promises.push(this.$refs[r].validate());
      }
      Promise.all(promises).then(() => {
        for (let r in this.$refs) {
          if (this.$refs[r].messages.length) {
            this.$bvModal.show('modal-signup-vaidation-check-failed')
            return
          }
        }
        this.$bvModal.show('modal-signup-submit')
      })
    },
    okSubmit: function () {
      let params = new URLSearchParams()
      params.append('sql', this.sql`
        CALL add_user(
          ${this.user.loginName},
          ${this.user.password},
          '',
          ${this.user.email})
      `)
      this.$http.post('/api/query.php', params)
      .then(() => {
        this.$routingSyncAlert.push('Signed up successfully. Please login with the information you just registered.')
        this.$router.push('/login')
      }).catch(() => {
        this.$bvModal.show('modal-signup-signup-failed')
      })
    }
  },
  name: 'signup',
}

</script>
