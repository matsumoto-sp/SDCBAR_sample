const anonymous = 'anonymous'
const member = 'member'
const admin = 'admin'
const pagePermission = {
    '/':                [admin, anonymous, member],
    '/about':           [admin, anonymous, member],
    '/signup':          [anonymous],
    '/login':           [anonymous],
    '/logout':          [admin, member],
    '/users':           [admin, anonymous, member],
    '/my_profile':      [admin, member],
    '/my_profile/edit': [admin, member],
    '/admin':           [admin],
    '/sql':             [admin, anonymous, member],
}

const pagePermissionRegex = [
  {re: new RegExp(/^\/users\/[0-9]+$/), allow: [admin, anonymous, member]}
]

const PermissionManager = {
    store: null,
    install: function (Vue, store) {
        Vue.prototype.$permissionManager = this
        this.store = store
    },
    useful: function (path) {
        if (path === undefined) {
          return false;
        }
        let role = this.store.getters.role
        if (pagePermission[path] && pagePermission[path].indexOf(role) != -1) {
          return true;
        }
        for (let i = 0, c = pagePermissionRegex.length; i < c; i++) {
          if (path.match(pagePermissionRegex[i].re) && pagePermissionRegex[i].allow.indexOf(role) != -1) {
            return true;
          }
        }
        return false;
    }
}

export default PermissionManager
