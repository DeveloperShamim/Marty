// Storefront stylesheet (layouts/storefront.blade.php). Build: npm run build:css
const { v, content, safelist } = require('./shared.cjs');

module.exports = {
  content,
  safelist,
  theme: {
    extend: {
      // One corner scale for the whole shop: 6 tags · 10 buttons & inputs · 16 cards · 24 large panels · pill.
      borderRadius: { sm: '6px', DEFAULT: '6px', md: '10px', lg: '10px', xl: '10px', '2xl': '16px', '3xl': '24px' },
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
      fontFamily: { sans: ['Plus Jakarta Sans', 'Hind Siliguri', 'sans-serif'], display: ['Plus Jakarta Sans', 'Hind Siliguri', 'sans-serif'] },
    },
  },
};
