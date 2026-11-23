<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue'

const props = defineProps({ module: String, html: String })
const content = ref(null)
const title = props.module === 'categories' ? 'Categories' : 'News entry'
let addedStyles = []
let disposed = false

function loadScript(src) {
  return new Promise((resolve, reject) => {
    const script = document.createElement('script')
    script.src = src
    script.async = false
    script.onload = resolve
    script.onerror = () => reject(new Error(`Could not load ${src}`))
    document.body.appendChild(script)
  })
}

async function mountLegacyModule() {
  const template = document.createElement('template')
  template.innerHTML = props.html

  const scripts = [...template.content.querySelectorAll('script')]
  template.content.querySelectorAll('script').forEach((script) => script.remove())
  template.content.querySelectorAll('link[rel="stylesheet"]').forEach((link) => {
    const stylesheet = document.createElement('link')
    stylesheet.rel = 'stylesheet'
    stylesheet.href = link.href
    document.head.appendChild(stylesheet)
    addedStyles.push(stylesheet)
    link.remove()
  })

  content.value.replaceChildren(template.content.cloneNode(true))

  for (const original of scripts) {
    if (disposed) return
    if (original.src) {
      try {
        await loadScript(original.src)
      } catch (error) {
        console.error(error)
      }
      continue
    }

    const script = document.createElement('script')
    let source = original.textContent
    if (props.module === 'news-entry') {
      source = source.replace(
        "const cfg=document.getElementById('news-config'), root=document.getElementById('news-module-template').content.firstElementChild;",
        "const cfg=document.getElementById('news-config'), root=document.getElementById('news-module-template').content.firstElementChild, sectionTemplate=document.getElementById('news-section-template');",
      ).replace(
        "document.getElementById('news-section-template').content",
        'sectionTemplate.content',
      )
    }
    script.textContent = source
    document.body.appendChild(script)
    script.remove()
  }
}

onMounted(mountLegacyModule)
onBeforeUnmount(() => {
  disposed = true
  addedStyles.forEach((style) => style.remove())
})
</script>

<template>
  <main class="main">
    <nav class="navbar navbar-top fixed-top navbar-expand" id="navbarDefault"><div class="container-fluid justify-content-between">
      <a href="/" class="navbar-brand d-flex align-items-center"><img :src="'/media/assets/img/icons/logo.png?size=128'" alt="" width="28"><span class="ms-2 fw-bold">phoenix</span></a>
      <div class="d-flex align-items-center gap-3"><span class="text-body-secondary">{{ $page.props.currentPortal?.portal_name }}</span><form method="post" action="/logout"><input type="hidden" name="_token" :value="$page.props.csrfToken"><button class="btn btn-sm btn-phoenix-secondary">Sign out</button></form></div>
    </div></nav>
    <nav class="navbar navbar-vertical navbar-expand-lg"><div class="navbar-vertical-content"><ul class="navbar-nav flex-column"><li class="nav-item"><p class="navbar-vertical-label">Portal</p><hr class="navbar-vertical-line"><div class="nav-item-wrapper"><a href="/" class="nav-link label-1"><span class="nav-link-icon">⌂</span><span class="nav-link-text">Dashboard</span></a></div><div class="nav-item-wrapper"><a href="/categories" class="nav-link label-1" :class="{active:module==='categories'}"><span class="nav-link-icon">▦</span><span class="nav-link-text">Categories</span></a></div><div class="nav-item-wrapper"><a href="/news/create" class="nav-link label-1" :class="{active:module==='news-entry'}"><span class="nav-link-icon">✎</span><span class="nav-link-text">News entry</span></a></div></li></ul></div></nav>
    <div class="content"><header class="d-flex justify-content-between align-items-center mb-3"><h2 class="mb-0">{{ title }}</h2><a v-if="module==='categories'" class="btn btn-outline-primary" href="/news/create">Create news</a><a v-else class="btn btn-outline-primary" href="/categories">Manage categories</a></header><div ref="content"></div></div>
  </main>
</template>
