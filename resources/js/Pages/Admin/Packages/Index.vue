<script setup>
import AdminLayout from '../../AdminLayout.vue'
import { router, useForm } from '@inertiajs/vue3'
defineOptions({ layout: AdminLayout })
defineProps({ packages: Array })
const fresh = useForm({ name: '', price: 0, duration_days: 30, term_months: 1, grace_days: 5, grace_window_days: 7 })
function add() { fresh.post('/admin/packages', { onSuccess: () => fresh.reset('name', 'price') }) }
function save(pkg) {
  router.put(`/admin/packages/${pkg.id}`, { name: pkg.name, price: pkg.price, duration_days: pkg.duration_days, term_months: pkg.term_months, grace_days: pkg.grace_days, grace_window_days: pkg.grace_window_days, active: pkg.active })
}
</script>

<template>
  <div>
    <p class="text-body-tertiary">Package charges are monthly. The term sets the minimum commitment. Running subscriptions keep their existing terms.</p>
    <div class="card mb-4 border-primary"><div class="card-body"><h5>Add new package</h5><form @submit.prevent="add"><div class="row g-3"><div v-for="[key,label,type] in [['name','Name','text'],['price','Charge per month (tk)','number'],['duration_days','Days in a month cycle','number'],['term_months','Term (months)','number'],['grace_days','Grace (days)','number'],['grace_window_days','Grace option opens (days left)','number']]" :key="key" class="col-lg-2"><label class="form-label">{{ label }}</label><input v-model="fresh[key]" class="form-control" :type="type" :step="key==='price'?'0.01':undefined" required></div><div class="col-lg-2 d-flex align-items-end"><button class="btn btn-primary w-100" :disabled="fresh.processing">Add</button></div></div></form></div></div>
    <div class="row g-3"><div v-for="pkg in packages" :key="pkg.id" class="col-lg-6"><div class="card"><div class="card-body"><div class="d-flex justify-content-between mb-3"><h5>{{ pkg.name }}</h5><span class="badge badge-phoenix-info">{{ pkg.is_trial?'one-time trial':`${pkg.term_months} month term` }}</span></div><form @submit.prevent="save(pkg)"><div class="row g-3"><div class="col-12"><label class="form-label">Name</label><input v-model="pkg.name" class="form-control" name="name" required></div><div class="col-md-4"><label class="form-label">Charge / month</label><input v-model="pkg.price" class="form-control" type="number" step="0.01" min="0" required></div><div class="col-md-4"><label class="form-label">Days / month</label><input v-model="pkg.duration_days" class="form-control" type="number" min="1" required></div><div v-if="!pkg.is_trial" class="col-md-4"><label class="form-label">Term months</label><input v-model="pkg.term_months" class="form-control" type="number" min="1" required></div><div class="col-md-4"><label class="form-label">Grace days</label><input v-model="pkg.grace_days" class="form-control" type="number" min="0" required></div><div class="col-md-8"><label class="form-label">Grace option opens (days left)</label><input v-model="pkg.grace_window_days" class="form-control" type="number" min="1" max="365" required></div><div class="col-12"><label class="form-check"><input v-model="pkg.active" class="form-check-input" type="checkbox"><span class="form-check-label">Active (available to assign)</span></label></div></div><button class="btn btn-primary mt-3">Save</button></form></div></div></div></div>
  </div>
</template>
