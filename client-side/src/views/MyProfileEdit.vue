<template>
  <section class="contents">
    <h1>Edit My Profile</h1>
    <form>
      <b-container fluid>
        <b-row class="my-2">
          <b-col sm="3" class="label">UserID</b-col>
          <b-col sm="9">{{user.user_id}}</b-col>
        </b-row>
        <b-row class="my-2">
          <b-col sm="3" class="label">Login Name</b-col>
          <b-col sm="9">{{user.login_name}}</b-col>
        </b-row>
        <b-row class="my-2">
          <b-col sm="3" class="label"><label for ="myprofile-email">E-Mail</label></b-col>
          <b-col sm="9">
            <validation-provider name="email" rules="required|email" ref="email" v-slot="{ errors, valid }">
              <b-input 
                type="email"
                :state="valid"
                id="myprofile-email"
                v-model="user.email" trim />
              <b-form-invalid-feedback>{{errors[0]}}</b-form-invalid-feedback>
            </validation-provider>
          </b-col>
        </b-row>
        <b-row class="my-2">
          <b-col sm="3" class="label"><label for ="myprofile-password">Password</label></b-col>
          <b-col sm="3">
            <validation-provider name="password" rules="confirmed:confirm" ref="password" v-slot="{ errors, valid }">
              <b-input
                type="password"
                :state="valid && !user.password.length ? null : valid"
                id="myprofile-password"
                v-model="user.password" />
              <b-form-invalid-feedback>{{errors[0]}}</b-form-invalid-feedback>
            </validation-provider>
          </b-col>
          <b-col sm="3" class="label">
            <label class="password2 d-none d-sm-block" for ="myprofile-password-2">Confirm</label>
            <label class="d-sm-none d-block" for ="myprofile-password-2">Password Confirm</label>
          </b-col>
          <b-col sm="3">
            <validation-provider name="confirm" rules="confirmed:password" ref="password2" v-slot="{ errors, valid }">
              <b-input
                type="password"
                :state="valid && !user.password_2.length ? null : valid"
                id="myprofile-password-2"
                v-model="user.password_2" />
              <b-form-invalid-feedback>{{errors[0]}}</b-form-invalid-feedback>
            </validation-provider>
          </b-col>
        </b-row>
        <b-row class="my-2">
          <b-col sm="3" class="label"><label for ="myprofile-profile">Profile</label></b-col>
          <b-col sm="9">
            <validation-provider name="profile" rules="max:200" ref="profile" v-slot="{ errors, valid }">
              <b-textarea
                id="myprofile-profile"
                :state="valid"
                v-model="user.profile" />
              <b-form-invalid-feedback>{{errors[0]}}</b-form-invalid-feedback>
            </validation-provider>
          </b-col>
        </b-row>
        <b-row class="my-2">
          <b-col sm="3" class="label"><label for ="myprofile-profile_open">Profile</label></b-col>
          <b-col sm="9">
            <b-form-select 
              id="myprofile-profile_open"
              :state="true"
              v-model="user.profile_open"
              :options="profile_open_options"/>
          </b-col>
        </b-row>
        <b-row class="my-4">
          <b-col sm="12">
            <b-button v-on:click="submit" variant="primary" block>Submit</b-button>
          </b-col>
        </b-row>
      </b-container>
    </form>
    <b-modal id="modal-profile-edit-submit-profile" @ok="okSubmit" :title="$appName">
      <p>Do you want to update your profile?</p>
    </b-modal>
    <b-modal id="modal-profile-edit-vaidation-check-failed" :title="$appName" ok-only>
      <p>Please execute submit after solving all errors.</p>
    </b-modal>
  </section>
</template>
<script>
export default {
  data: () => {
    return {
      user: {
        password: '',
        password_2: ''
      },
      friends: [],
      profile_open_options: [
        {value: 'none', text: 'none'},
        {value: 'friends', text: 'friends'},
        {value: 'members', text: 'members'},
        {value: 'open', text: 'open'},
      ]
    }
  },
  methods: {
    submit: function () {
      for (let r in this.$refs) {
        if (this.$refs[r].messages.length) {
          this.$bvModal.show('modal-profile-edit-vaidation-check-failed')
          return
        }
      }
      this.$bvModal.show('modal-profile-edit-submit-profile')
    },
    okSubmit: function () {
      let params = new URLSearchParams()
      params.append('sql', this.sql`
        CALL update_me(
          ${this.user.password == '' ? null : this.user.password},
          ${this.user.profile},
          ${this.user.email},
          ${this.user.profile_open})
      `)
      this.$http.post('/api/query.php', params)
      .then(() => {
        this.$routingSyncAlert.push('Profile updated.')
        this.$router.push('/my_profile')
      })
    }
  },
  name: 'my_profile_edit',
  mounted: function() {
    this.$nextTick(function () {
      let params1 = new URLSearchParams()
      params1.append('sql', 'CALL my_info()');
      this.$http.post('/api/query.php', params1)
        .then((res) => {
          this.user = res.data[0]
          this.user.password = ''
          this.user.password_2 = ''
        })
    })
  },
}

</script>

