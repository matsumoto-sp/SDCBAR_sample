<template>
  <section class="contents">
    <h1>My Profile<b-button to="/my_profile/edit" variant="outline-primary" class="float-right">Edit</b-button></h1>
    <table class="table table-bordered table-hover">
      <tbody>
        <tr>
          <th>UserID</th>
          <td>{{user.user_id}}</td>
        </tr>
        <tr>
          <th>Login Name</th>
          <td>{{user.login_name}}</td>
        </tr>
        <tr>
          <th>E-Mail</th>
          <td><a :href="'mailto:' + user.email">{{user.email}}</a></td>
        </tr>
        <tr>
          <th>Profile</th>
          <td>{{user.profile}}</td>
        </tr>
        <tr>
          <th>Profile disclosure</th>
          <td>{{user.profile_open}}</td>
        </tr>
        <tr v-if="$store.state.role != 'anonymous'">
          <th>My friend list</th>
          <td>
            <span v-for="f in friends" :key="f.userId">
              <permit-link :msg="f.loginName" :to="'/users/' + f.userId" />&nbsp;
            </span>
          </td>
        </tr>
        <tr v-if="$store.state.role != 'anonymous'">
          <th>People who has me on the friend list</th>
          <td>
            <span v-for="f in reverseFriends" :key="f.userId">
              <permit-link :msg="f.loginName" :to="'/users/' + f.userId" />&nbsp;
            </span>
          </td>
        </tr>
      </tbody>
    </table>
  </section>
</template>
<script>
function onload() {
    this.$nextTick(function () {
      let params1 = new URLSearchParams()
      params1.append('sql', 'CALL my_info()');
      this.$http.post('/api/query.php', params1)
        .then((res) => {
          this.user = res.data[0]
        })
      let params2 = new URLSearchParams()
      params2.append('sql',this.sql`
        SELECT
          u.user_id AS userId, u.login_name AS loginName
        FROM
          users_friend_users f
        INNER JOIN
          users_view u ON u.user_id = f.friend_user_id
        INNER JOIN 
          users_view m ON m.user_id = f.user_id
        WHERE
          m.login_name = ${this.$store.state.loginName}
        ORDER BY
          f.friend_user_id
      `)
      this.$http.post('/api/query.php', params2)
        .then((res) => {
          this.friends = res.data
        })
      let params3 = new URLSearchParams()
      params3.append('sql',this.sql`
        SELECT
          u.user_id AS userId, u.login_name AS loginName
        FROM
          users_friend_users f
        INNER JOIN
          users_view u ON u.user_id = f.friend_user_id
        INNER JOIN 
          users_view m ON m.user_id = f.user_id
        WHERE
          u.login_name = ${this.$store.state.loginName}
        ORDER BY
          f.friend_user_id
      `)
      this.$http.post('/api/query.php', params3)
        .then((res) => {
          this.reverseFriends = res.data
        })
    })
}


export default {
  data: () => {
    return {
      user: {},
      friends: [],
      reverseFriends: [],
    }
  },
  name: 'user',
  mounted: onload,
}

</script>

