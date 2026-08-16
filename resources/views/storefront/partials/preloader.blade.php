@if(request()->routeIs('home') || request()->is('/'))
<div id="sitePreloader" class="fixed inset-0 z-[99999] bg-white flex flex-col items-center justify-center transition-all duration-500 ease-out select-none">
  {{-- Center Brand Pulse --}}
  <div class="flex flex-col items-center justify-center gap-4 text-center px-4 animate-pulse">
    @include('partials.brand', ['light' => false, 'size' => 'lg'])
  </div>

  {{-- Animated Loading Progress Bar --}}
  <div class="mt-6 flex flex-col items-center gap-2.5">
    <div class="relative w-48 h-1.5 bg-stone-100 rounded-full overflow-hidden shadow-inner border border-stone-200/60">
      <div class="absolute inset-y-0 left-0 bg-brand-600 rounded-full w-full animate-preloader-progress"></div>
    </div>
    <span class="text-[10px] font-extrabold uppercase tracking-[0.2em] text-stone-400">Loading...</span>
  </div>
</div>

<style>
  @keyframes preloaderProgress {
    0% { transform: translateX(-100%); }
    60% { transform: translateX(-20%); }
    100% { transform: translateX(0%); }
  }
  .animate-preloader-progress {
    animation: preloaderProgress 0.75s cubic-bezier(0.4, 0, 0.2, 1) forwards;
  }
</style>

<script>
  (function() {
    const preloader = document.getElementById('sitePreloader');
    if (!preloader) return;

    // Only show on first homepage load per browser session
    try {
      if (sessionStorage.getItem('home_loader_shown')) {
        preloader.remove();
        return;
      }
      sessionStorage.setItem('home_loader_shown', 'true');
    } catch(e) {}

    function hidePreloader() {
      if (preloader.classList.contains('opacity-0')) return;
      preloader.classList.add('opacity-0', 'pointer-events-none', 'scale-105');
      setTimeout(function() {
        if (preloader && preloader.parentNode) {
          preloader.parentNode.removeChild(preloader);
        }
      }, 500);
    }

    if (document.readyState === 'complete') {
      setTimeout(hidePreloader, 150);
    } else {
      window.addEventListener('load', function() {
        setTimeout(hidePreloader, 150);
      });
      // Safety fallback cap at max 800ms
      setTimeout(hidePreloader, 800);
    }
  })();
</script>
@endif
