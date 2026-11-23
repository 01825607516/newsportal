<script setup>
import { useForm } from '@inertiajs/vue3'
import AuthLayout from '../AuthLayout.vue'

const form = useForm({ email: '', password: '' })
function submit() { form.post('/admin/login') }
</script>

<template>
  <AuthLayout title="Admin Login" subtitle="Sign in to manage portals">
    <form @submit.prevent="submit" novalidate>
      <div class="mb-3">
        <label class="form-label" for="email">Email <span class="text-danger">*</span></label>
        <input id="email" v-model="form.email" class="form-control" :class="{ 'is-invalid': form.errors.email }" type="email" required autofocus>
        <div v-if="form.errors.email" class="invalid-feedback">{{ form.errors.email }}</div>
      </div>
      <div class="mb-4">
        <label class="form-label" for="password">Password <span class="text-danger">*</span></label>
        <input id="password" v-model="form.password" class="form-control" :class="{ 'is-invalid': form.errors.password }" type="password" autocomplete="current-password" required>
        <div v-if="form.errors.password" class="invalid-feedback">{{ form.errors.password }}</div>
      </div>
      <button class="btn btn-primary w-100" type="submit" :disabled="form.processing">{{ form.processing ? 'Signing in…' : 'Login' }}</button>
    </form>
  </AuthLayout>
</template>
