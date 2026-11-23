<script setup>
import AdminLayout from '../../AdminLayout.vue'
import { Link } from '@inertiajs/vue3'
defineOptions({ layout: AdminLayout })
defineProps({ logs: Object, ranges: Object, portals: Array, flagged: Array })
</script>

<template>
  <div>
    <p class="text-body-tertiary">Portal requests, response status, IP and database query counts. Request bodies and passwords are never stored.</p>
    <div v-for="item in flagged" :key="item.portal.id" class="alert alert-subtle-warning d-flex justify-content-between align-items-center"><span><b>{{ item.portal.portal_name }}</b> — {{ item.alerts.map(a => a.message).join(' ') }}</span><Link :href="`/admin/portals/${item.portal.id}?tab=activity`" class="btn btn-sm btn-phoenix-warning">Review</Link></div>
    <form class="card mb-3" method="get"><div class="card-body row g-3 align-items-end"><div class="col-md-4"><label class="form-label" for="portal">Portal</label><select id="portal" name="portal_id" class="form-select"><option value="">All portals</option><option v-for="p in portals" :key="p.id" :value="p.id" :selected="String(p.id) === new URLSearchParams($page.url.split('?')[1] || '').get('portal_id')">{{ p.portal_name }}</option></select></div><div class="col-md-3"><label class="form-label" for="range">Date range</label><select id="range" name="range" class="form-select"><option v-for="(label,key) in ranges" :key="key" :value="key" :selected="new URLSearchParams($page.url.split('?')[1] || '').get('range') === key">{{ label }}</option></select></div><div class="col-auto"><button class="btn btn-primary">Filter</button></div><div class="col-auto"><Link href="/admin/activity" class="btn btn-phoenix-secondary">Clear</Link></div></div></form>
    <div class="card"><div class="table-responsive"><table class="table table-hover mb-0"><thead><tr><th>Time</th><th>Portal</th><th>Event</th><th>Request</th><th>Status</th><th>IP</th><th>Queries</th><th>Writes</th></tr></thead><tbody><tr v-for="log in logs.data" :key="log.id"><td>{{ log.created_at }}</td><td>{{ log.portal?.portal_name || '—' }}</td><td>{{ log.event }}</td><td><code>{{ log.method }} {{ log.path }}</code></td><td><span class="badge" :class="log.status >= 400 ? 'badge-phoenix-danger' : 'badge-phoenix-success'">{{ log.status }}</span></td><td>{{ log.ip }}</td><td>{{ log.db_queries }}</td><td>{{ log.db_writes }}</td></tr><tr v-if="!logs.data.length"><td colspan="8" class="text-center py-5 text-body-tertiary">No activity found.</td></tr></tbody></table></div><div class="card-footer d-flex justify-content-between"><span>Page {{ logs.current_page }} of {{ logs.last_page }}</span><div class="d-flex gap-2"><Link v-if="logs.prev_page_url" :href="logs.prev_page_url" class="btn btn-sm btn-phoenix-secondary">Previous</Link><Link v-if="logs.next_page_url" :href="logs.next_page_url" class="btn btn-sm btn-phoenix-secondary">Next</Link></div></div></div>
  </div>
</template>
