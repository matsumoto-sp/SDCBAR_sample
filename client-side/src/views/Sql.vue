<template>
  <section class="contents">
    <h1>SQL</h1>
    <p>SQL can be issued freely according to the current account privileges.</p>
    <form>
      <b-container fluid>
        <b-row class="my-2">
          <b-col sm="12">
            <validation-provider name="sql" rules="max:4000" ref="sqlstr" v-slot="{ errors, valid }">
              <b-textarea
                rows="5"
                id="sql-sqlstr"
                :state="valid"
                v-model="sqlstr" />
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
    <h2 v-if="resultStatus !== null">Status: {{resultStatus}}</h2>
    <table v-if="resultStatus == 'ok' && keys != null" class="table table-striped table-bordered table-hover">
      <thead>
        <tr>
          <th v-for="(k, i) in keys" :key="i">{{k}}</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(r, i) in result" :key="i">
          <td v-for="(k, j) in keys" :key="j">{{r[k]}}</td>
          
        </tr>
      </tbody>
    </table>
    <table v-if="resultStatus == 'error'" class="table table-striped table-bordered table-hover">
      <tbody>
        <tr>
          <th>File</th>
          <td>{{result.file}}</td>
        </tr>
        <tr>
          <th>Line</th>
          <td>{{result.line}}</td>
        </tr>
        <tr>
          <th>Message</th>
          <td>{{result.message}}</td>
        </tr>
      </tbody>
    </table>
    <b-modal id="modal-profile-edit-submit-profile" @ok="okSubmit" :title="$appName">
      <p>Do you want to run SQL?</p>
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
      sqlstr: '',
      resultStatus: null,
      result: null,
      keys: null,
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
      params.append('sql', this.sqlstr)
      this.$http.post('/api/query.php', params)
      .then((res) => {
        this.resultStatus = 'ok'
        if (res.data.length) {
          this.keys = Object.keys(res.data[0])
          this.result = res.data
        } else {
          this.keys = null
          this.result = []
        }
      })
      .catch((res) => {
        this.resultStatus = 'error'
        this.result = res.response.data.error
      })
    }
  },
  name: 'sql',
}

</script>

