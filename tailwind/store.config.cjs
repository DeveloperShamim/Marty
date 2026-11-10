// Storefront stylesheet (layouts/storefront.blade.php). Build: npm run build:css
const { v, content, safelist } = require('./shared.cjs');

module.exports = {
  content,
  safelist,
  theme: {
    extend: {
      // One corner scale for the whole shop: tags · buttons & inputs · cards · large panels · pill.
      // The sizes are CSS variables set by the store layout from Settings → Brand & theme → Corners
      // (App\Support\StoreStyle); the fallbacks are the "Rounded" style: 6 · 10 · 16 · 24.
      borderRadius: {
        sm: 'var(--r-sm, 6px)', DEFAULT: 'var(--r-sm, 6px)', md: 'var(--r-md, 10px)', lg: 'var(--r-md, 10px)',
        xl: 'var(--r-md, 10px)', '2xl': 'var(--r-card, 16px)', '3xl': 'var(--r-panel, 24px)',
      },
      colors: {
        brand: {
          50: v('soft'), 100: v('border'), 200: v('border'),
          300: v('primary'), 400: v('primary'), 500: v('primary'), 600: v('primary'),
          700: v('hover'), 800: v('dark'), 900: v('dark'),
        },
        accent: { 400: v('border'), 500: v('primary'), 600: v('primary'), 700: v('dark') },
        ink: v('dark'),
        surface: { light: '#FFFFFF', soft: v('surface') },
      },
      // Font pair from Settings → Brand & theme → Font (CSS variables set by the store layout)
      fontFamily: {
        sans: ['var(--font-body, "Plus Jakarta Sans")', 'Hind Siliguri', 'sans-serif'],
        display: ['var(--font-display, "Plus Jakarta Sans")', 'Hind Siliguri', 'sans-serif'],
      },
    },
  },
};
