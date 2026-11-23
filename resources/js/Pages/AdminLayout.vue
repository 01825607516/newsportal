<script setup>
import { Link, useForm } from '@inertiajs/vue3'
const logout = useForm({})
function toggleTheme() {
  const root = document.documentElement
  const theme = root.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark'
  root.setAttribute('data-bs-theme', theme)
  try { localStorage.setItem('phoenixTheme', theme) } catch {}
}
const nav = [
  { label: 'Dashboard', href: '/admin', icon: '⌂' },
  { label: 'Portals', href: '/admin/portals', icon: '◎' },
  { label: 'Requests', href: '/admin/requests', icon: '▤', count: true },
  { label: 'Activity monitor', href: '/admin/activity', icon: '◷' },
  { label: 'Packages', href: '/admin/packages', icon: '◇' },
]
</script>

<template>
  <main class="main">
    <nav class="navbar navbar-vertical navbar-expand-lg">
      <div class="collapse navbar-collapse show" id="adminNav"><div class="navbar-vertical-content"><ul class="navbar-nav flex-column">
        <li class="nav-item"><p class="navbar-vertical-label">Admin</p><hr class="navbar-vertical-line">
          <div v-for="item in nav" :key="item.href" class="nav-item-wrapper">
            <Link :href="item.href" class="nav-link label-1" :class="{ active: $page.url.split('?')[0] === item.href || (item.href !== '/admin' && $page.url.startsWith(item.href)) }">
              <span class="nav-link-icon">{{ item.icon }}</span><span class="nav-link-text">{{ item.label }}</span><span v-if="item.count && $page.props.pendingRequestCount" class="badge ms-auto badge-phoenix badge-phoenix-warning">{{ $page.props.pendingRequestCount }}</span>
            </Link>
          </div>
        </li>
      </ul></div></div>
    </nav>
    <nav class="navbar navbar-top fixed-top navbar-expand" id="navbarDefault"><div class="container-fluid justify-content-between">
      <Link href="/admin" class="navbar-brand d-flex align-items-center"><img :src="'/media/assets/img/icons/logo.png?size=128'" alt="" width="27"><span class="ms-2 fw-bold">phoenix</span></Link>
      <div class="d-flex align-items-center gap-3"><button class="btn btn-sm btn-phoenix-secondary" aria-label="Switch theme" @click="toggleTheme">◐</button><span class="text-body-secondary">{{ $page.props.currentAdmin?.name }}</span><button class="btn btn-sm btn-phoenix-secondary" :disabled="logout.processing" @click="logout.post('/admin/logout')">Sign out</button></div>
    </div></nav>
    <div class="content">
      <header class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4"><div><h2 class="mb-1 lh-sm">{{ pageTitle }}</h2></div><slot name="actions" /></header>
      <div v-if="$page.props.flash?.success" class="alert alert-subtle-success" role="status">{{ $page.props.flash.success }}</div>
      <div v-if="$page.props.flash?.warning" class="alert alert-subtle-warning" role="status">{{ $page.props.flash.warning }}</div>
      <div v-if="Object.keys($page.props.errors || {}).length" class="alert alert-subtle-danger" role="alert"><div v-for="(error, key) in $page.props.errors" :key="key">{{ error }}</div></div>
      <slot />
      <footer class="footer"><div class="row"><div class="col"><p class="mb-0">News Portal Admin · {{ new Date().getFullYear() }} ©</p></div><div class="col-auto"><p class="mb-0 text-body-tertiary">Inertia</p></div></div></footer>
    </div>
  </main>
</template>

<script>
export default {
  computed: {
    pageTitle() {
      const path = this.$page.url.split('?')[0]
      return ({ '/admin': 'Admin Dashboard', '/admin/portals': 'Portals', '/admin/activity': 'Activity monitor', '/admin/requests': 'Portal requests', '/admin/packages': 'Packages' })[path] || (path.startsWith('/admin/portals/') ? 'Manage portal' : 'Admin')
    },
  },
}
</script>
