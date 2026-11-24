<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import { router } from '@inertiajs/vue3'
import { Head } from '@inertiajs/vue3'

const props = defineProps({ html: { type: String, required: true } })
const frame = ref(null)

const bridgeScript = `<script>
(function () {
  function send(payload) { window.parent.postMessage(payload, '*'); }

  var nativeSubmit = HTMLFormElement.prototype.submit;
  HTMLFormElement.prototype.submit = function () {
    var event = new Event('submit', { bubbles: true, cancelable: true });
    if (this.dispatchEvent(event)) nativeSubmit.call(this);
  };

  document.addEventListener('click', function (event) {
    if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
    var link = event.target.closest && event.target.closest('a[href]');
    if (!link || link.target || link.hasAttribute('download')) return;
    var url;
    try { url = new URL(link.href, window.location.href); } catch (error) { return; }
    if (url.origin !== window.location.origin || url.hash && url.pathname === window.location.pathname && url.search === window.location.search) return;
    if (link.getAttribute('href').charAt(0) === '#') return;
    event.preventDefault();
    send({ type: 'inertia-link', url: url.href });
  });

  document.addEventListener('submit', function (event) {
    if (event.defaultPrevented) return;
    var form = event.target;
    if (!(form instanceof HTMLFormElement)) return;
    var submitter = event.submitter;
    var url;
    try { url = new URL(submitter && submitter.formAction || form.action || window.location.href, window.location.href); } catch (error) { return; }
    if (url.origin !== window.location.origin) return;
    event.preventDefault();
    var formData = new FormData(form);
    if (submitter && submitter.name) formData.append(submitter.name, submitter.value);
    send({
      type: 'inertia-form',
      url: url.href,
      method: (submitter && submitter.formMethod || form.method || 'get').toLowerCase(),
      entries: Array.from(formData.entries()),
    });
  });
})();
<\/script>`

const srcdoc = computed(() => {
  const html = props.html || '<!doctype html><html><body></body></html>'
  return /<\/body\s*>/i.test(html)
    ? html.replace(/<\/body\s*>/i, `${bridgeScript}</body>`)
    : `${html}${bridgeScript}`
})
const pageTitle = computed(() => new DOMParser().parseFromString(props.html, 'text/html').title || 'News Portal')

function onMessage(event) {
  if (event.source !== frame.value?.contentWindow || !event.data || typeof event.data !== 'object') return

  if (event.data.type === 'inertia-link' && typeof event.data.url === 'string') {
    router.visit(event.data.url)
    return
  }

  if (event.data.type !== 'inertia-form' || typeof event.data.url !== 'string' || !Array.isArray(event.data.entries)) return

  const method = String(event.data.method || 'get').toLowerCase()
  const data = new FormData()
  event.data.entries.forEach(([key, value]) => data.append(key, value))

  if (method === 'get') {
    const url = new URL(event.data.url, window.location.href)
    for (const [key, value] of data.entries()) url.searchParams.append(key, value)
    router.visit(url.href)
    return
  }

  router.visit(event.data.url, {
    method,
    data,
    forceFormData: true,
  })
}

onMounted(() => window.addEventListener('message', onMessage))
onBeforeUnmount(() => window.removeEventListener('message', onMessage))
</script>

<template>
  <Head :title="pageTitle" />
  <iframe
    ref="frame"
    class="legacy-page-frame"
    :srcdoc="srcdoc"
    title="News portal"
  />
</template>

<style>
html,
body,
#app {
  width: 100%;
  min-width: 0;
  min-height: 100%;
  margin: 0;
}

.legacy-page-frame {
  display: block;
  width: 100%;
  height: 100vh;
  min-height: 100vh;
  border: 0;
}
</style>
