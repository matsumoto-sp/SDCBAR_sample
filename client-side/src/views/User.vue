<template>
  <section class="contents">
    <h1>
      User: {{user.loginName}}
      <b-button
        :disabled="$store.state.role == 'anonymous' || !isMyFriend || isMe"
        v-b-modal="'modal-user-remove-friends'"
        variant="outline-danger"
        class="float-right">Remove from my friends</b-button>
      <b-button 
        :disabled="$store.state.role == 'anonymous' || isMyFriend || isMe"
        v-b-modal="'modal-user-add-friends'"
        variant="outline-primary" 
        class="float-right">Add to my friends</b-button>
    </h1>
    <table class="table table-bordered table-hover">
      <tbody>
        <tr>
          <th>UserID</th>
          <td>{{user.userId}}</td>
        </tr>
        <tr>
          <th>profile</th>
          <td>{{user.profile}}</td>
        </tr>
        <tr v-if="$store.state.role != 'anonymous'">
          <th>His/Her friend list</th>
          <td>
            <span v-for="f in friends" :key="f.userId">
              <permit-link :msg="f.loginName" :to="'/users/' + f.userId" />&nbsp;
            </span>
          </td>
        </tr>
        <tr v-if="$store.state.role != 'anonymous'">
          <th>People who has him/her on the friend list</th>
          <td>
            <span v-for="f in reverseFriends" :key="f.userId">
              <permit-link :msg="f.loginName" :to="'/users/' + f.userId" />&nbsp;
            </span>
          </td>
        </tr>
      </tbody>
    </table>
    <b-modal id="modal-user-add-friends" :title="$appName" @ok="addFriend">
      Want to add this user to your friends?
    </b-modal>
    <b-modal id="modal-user-remove-friends" :title="$appName" ok-variant="danger" @ok="removeFriend">
      Want to remove this user from your friends?
    </b-modal>
  </section>
</template>
<script>
function onload() {
    this.$nextTick(function () {
      let params1 = new URLSearchParams()
      params1.append('sql',this.sql`
        SELECT
          u.user_id AS userId, u.login_name AS loginName, u.profile
        FROM
          users_view u
        WHERE
          u.user_id = ${this.$route.params.id}
      `)
      this.$http.post('/api/query.php', params1)
        .then((res) => {
          this.user = res.data[0]
        })
      if (this.$store.state.role != 'anonymous') {
        let params2 = new URLSearchParams()
        params2.append('sql',this.sql`
          SELECT
            u.user_id AS userId, u.login_name AS loginName
          FROM
            users_friend_users f
          INNER JOIN
            users_view u ON f.friend_user_id = u.user_id
          WHERE
            f.user_id = ${this.$route.params.id}
          ORDER BY
            f.user_id
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
            users_view u ON f.user_id = u.user_id
          WHERE
            f.friend_user_id = ${this.$route.params.id}
          ORDER BY
            f.user_id
        `)
        this.$http.post('/api/query.php', params3)
          .then((res) => {
            this.reverseFriends = res.data
          })
      }
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
  computed: {
    isMe() {
      return this.$store.state.loginName == this.user.loginName
    },
    isMyFriend() {
      return this.reverseFriends.some(f => f.loginName == this.$store.state.loginName)
    }
  },
  methods: {
    addFriend() {
      let params = new URLSearchParams()
      params.append('sql',this.sql`CALL add_friend(${this.user.loginName})`)
      this.$http.post('/api/query.php', params)
        .then(() => {
          this.$routingSyncAlert.replace('Friend added.')
          onload.call(this)
        })
    },
    removeFriend() {
      let params = new URLSearchParams()
      params.append('sql',this.sql`CALL remove_friend(${this.user.loginName})`)
      this.$http.post('/api/query.php', params)
        .then(() => {
          this.$routingSyncAlert.replace('Friend removed.')
          onload.call(this)
        })
    }
  },
  name: 'user',
  mounted: onload,
  watch: {
    '$route': onload
  },
}

</script>

