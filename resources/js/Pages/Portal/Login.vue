<script setup>
import { useForm, Link } from '@inertiajs/vue3'
import AuthLayout from '../AuthLayout.vue'

const form = useForm({ editor_mobile: '', password: '' })
function submit() { form.post('/login') }
</script>

<template>
  <AuthLayout title="Portal Login" subtitle="Enter your mobile number and password to continue">
    <form @submit.prevent="submit" novalidate>
      <div class="mb-3">
        <label class="form-label" for="editor_mobile">Mobile number</label>
        <input id="editor_mobile" v-model="form.editor_mobile" class="form-control" :class="{ 'is-invalid': form.errors.editor_mobile }" type="tel" placeholder="01XXXXXXXXX" autocomplete="tel" required autofocus>
        <div v-if="form.errors.editor_mobile" class="invalid-feedback">{{ form.errors.editor_mobile }}</div>
      </div>
      <div class="mb-4">
        <label class="form-label" for="password">Password</label>
        <input id="password" v-model="form.password" class="form-control" :class="{ 'is-invalid': form.errors.password }" type="password" autocomplete="current-password" required>
        <div v-if="form.errors.password" class="invalid-feedback">{{ form.errors.password }}</div>
      </div>
      <button class="btn btn-primary w-100 mb-3" type="submit" :disabled="form.processing">{{ form.processing ? 'Signing in…' : 'Login' }}</button>
      <p class="text-center text-body-tertiary">New here? <Link href="/signup" class="fw-semibold">Create an account</Link></p>
    </form>
  </AuthLayout>
</template>
