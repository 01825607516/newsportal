<script setup>
defineProps({ title: String, subtitle: String })
function toggleTheme() {
  const root = document.documentElement
  const theme = root.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark'
  root.setAttribute('data-bs-theme', theme)
  try { localStorage.setItem('phoenixTheme', theme) } catch {}
}
</script>

<template>
  <button type="button" class="btn btn-phoenix-secondary position-fixed top-0 end-0 m-3" aria-label="Switch theme" @click="toggleTheme">◐</button>
  <main class="main">
    <div class="container">
      <div class="row flex-center min-vh-100 py-5">
        <div class="col-sm-10 col-md-8 col-lg-5 col-xl-5">
          <div class="text-center mb-7">
            <img :src="'/media/assets/img/icons/logo.png?size=128'" alt="" width="48" @error="$event.target.style.display = 'none'">
            <h3 class="text-body-highlight mt-3">{{ title }}</h3>
            <p class="text-body-tertiary">{{ subtitle }}</p>
          </div>
          <div v-if="$page.props.flash?.status" class="alert alert-subtle-success" role="status">{{ $page.props.flash.status }}</div>
          <div v-if="$page.props.flash?.warning" class="alert alert-subtle-warning" role="status">{{ $page.props.flash.warning }}</div>
          <slot />
        </div>
      </div>
    </div>
  </main>
</template>
