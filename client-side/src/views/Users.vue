<template>
  <section class="contents">
    <h1>Users</h1>
    <table class="table table-striped table-bordered table-hover">
      <thead>
        <tr>
          <th>UserID</th>
          <th>LoginName</th>
          <th>Profile</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="user in users" :key="user.userId">
          <td>{{user.userId}}</td>
          <td><permit-link :msg="user.loginName" :to="'/users/' + user.userId" /></td>
          <td>{{user.profile}}</td>
        </tr>
      </tbody>
    </table>
  </section>
</template>
<script>
export default {
  data: () => {
    return {
      users: []
    }
  },
  name: 'users',
  mounted : function () {
    this.$nextTick(function () {
      let params = new URLSearchParams()
      params.append('sql',
        'SELECT user_id AS userId, login_name AS loginName, profile FROM users_view ORDER BY user_id')
      this.$http.post('/api/query.php', params)
        .then((res) => {
          this.users = res.data
        })
    })
  },
}
</script>
