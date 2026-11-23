<script setup>
import AdminLayout from '../AdminLayout.vue'
import { Link } from '@inertiajs/vue3'
defineOptions({ layout: AdminLayout })
defineProps({ stats: Object, flagged: Array, recentPortals: Array, recent: Array })
</script>

<template>
  <div>
    <div v-if="$page.props.pendingRequestCount" class="alert alert-subtle-info d-flex justify-content-between align-items-center">{{ $page.props.pendingRequestCount }} request(s) are waiting for approval. <Link href="/admin/requests" class="btn btn-sm btn-phoenix-primary">Review requests</Link></div>
    <div class="row g-3 mb-4"><div v-for="[label, value] in [['Total portals', stats.total], ['Active', stats.active], ['Suspended', stats.suspended], ['Requests (24h)', stats.requests24]]" :key="label" class="col-6 col-xl-3"><div class="card"><div class="card-body"><div class="stat-label">{{ label }}</div><div class="stat-value">{{ Number(value).toLocaleString() }}</div></div></div></div></div>
    <div class="card mb-4"><div class="card-header"><h5 class="mb-0">Needs attention</h5></div><div class="card-body"><div v-if="!flagged.length" class="text-body-tertiary">No portals need attention.</div><div v-for="item in flagged" :key="item.portal.id" class="d-flex justify-content-between gap-3 py-3 border-top"><div><Link :href="`/admin/portals/${item.portal.id}?tab=activity`" class="fw-bold">{{ item.portal.portal_name }}</Link><div v-for="alert in item.alerts" :key="alert.message" class="text-warning">{{ alert.message }}</div></div><Link :href="`/admin/portals/${item.portal.id}?tab=activity`" class="btn btn-sm btn-phoenix-secondary">Review</Link></div></div></div>
    <div class="card"><div class="card-header"><h5 class="mb-0">Recent portals</h5></div><div class="table-responsive"><table class="table mb-0"><thead><tr><th>Portal</th><th>Editor</th><th>Status</th><th>Created</th><th></th></tr></thead><tbody><tr v-for="portal in recentPortals" :key="portal.id"><td>{{ portal.portal_name }}</td><td>{{ portal.editor_name || portal.editor_mobile }}</td><td>{{ portal.status }}</td><td>{{ portal.created_at }}</td><td><Link :href="`/admin/portals/${portal.id}`">Manage</Link></td></tr></tbody></table></div></div>
  </div>
</template>
