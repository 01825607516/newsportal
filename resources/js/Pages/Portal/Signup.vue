<script setup>
import { useForm, Link } from '@inertiajs/vue3'
import AuthLayout from '../AuthLayout.vue'

const form = useForm({ portal_name: '', portal_reg_no: '', editor_name: '', editor_email: '', editor_mobile: '', password: '', password_confirmation: '' })
function submit() { form.post('/signup') }
</script>

<template>
  <AuthLayout title="Portal Sign Up" subtitle="Create your portal account. You will log in with your mobile number and password.">
    <form @submit.prevent="submit" novalidate>
      <div v-for="field in [
        { key: 'portal_name', label: 'Portal name', type: 'text', required: true },
        { key: 'portal_reg_no', label: 'Portal reg. no (optional)', type: 'text' },
        { key: 'editor_name', label: 'Editor name (optional)', type: 'text' },
        { key: 'editor_email', label: 'Editor email (optional)', type: 'email' },
        { key: 'editor_mobile', label: 'Editor mobile', type: 'tel', required: true, placeholder: '01XXXXXXXXX' },
        { key: 'password', label: 'Password', type: 'password', required: true, hint: 'At least 6 characters.' },
        { key: 'password_confirmation', label: 'Confirm password', type: 'password', required: true },
      ]" :key="field.key" class="mb-3">
        <label class="form-label" :for="field.key">{{ field.label }} <span v-if="field.required" class="text-danger">*</span></label>
        <input :id="field.key" v-model="form[field.key]" class="form-control" :class="{ 'is-invalid': form.errors[field.key] }" :type="field.type" :placeholder="field.placeholder" :required="field.required" :autocomplete="field.key.includes('password') ? 'new-password' : undefined">
        <div v-if="form.errors[field.key]" class="invalid-feedback">{{ form.errors[field.key] }}</div>
        <div v-if="field.hint" class="form-text">{{ field.hint }}</div>
      </div>
      <button class="btn btn-primary w-100 mb-3" type="submit" :disabled="form.processing">{{ form.processing ? 'Creating account…' : 'Sign up' }}</button>
      <div class="text-center"><span class="text-body-tertiary">Already have an account? </span><Link href="/login" class="fw-semibold">Login</Link></div>
    </form>
  </AuthLayout>
</template>
