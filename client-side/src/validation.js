import Vue from 'vue'
import { ValidationProvider, extend } from 'vee-validate';
import { required , email, confirmed, max } from 'vee-validate/dist/rules';

extend('required', { ...required,
  message: 'The {_field_} field is required.' });
extend('email', email)
extend('confirmed', { ...confirmed,
  message: 'Match the confirmation field.' });
extend('max', max)

Vue.component('validation-provider', ValidationProvider)
