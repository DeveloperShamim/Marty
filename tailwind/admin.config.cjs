// Admin stylesheet (layouts/admin.blade.php and the admin login). Build: npm run build:css
const { v, content, safelist, fonts } = require('./shared.cjs');

// gray (and slate) use the warm stone palette and teal (the old admin accent) follows the store brand.
const stone = { 50: '#fafaf9', 100: '#f5f5f4', 200: '#e7e5e4', 300: '#d6d3d1', 400: '#a8a29e', 500: '#78716c', 600: '#57534e', 700: '#44403c', 800: '#292524', 900: '#1c1917', 950: '#0c0a09' };
const brand = Object.fromEntries([50, 100, 200, 300, 400, 500, 600, 700, 800, 900].map((k) => [k, v('b' + k)]));

module.exports = {
  content,
  safelist,
  theme: {
    extend: {
      colors: {
        primary: { DEFAULT: v('primary') },
        brand,
        teal: brand,
        gray: stone,
        // slate panels (POS, order page) turn warm, with the darkest shades in the store's dark leather tone
        slate: { ...stone, 800: v('s800'), 900: v('dark'), 950: v('s950') },
        accent: { 500: v('primary') },
        ink: v('dark'),
      },
      fontFamily: fonts,
      boxShadow: { card: '0 1px 2px rgba(41,37,36,.04), 0 4px 16px -8px rgba(41,37,36,.08)', panel: '0 1px 2px rgba(28,25,23,.03), 0 12px 32px -20px rgba(28,25,23,.18)' },
      borderRadius: { panel: '24px' },
    },
  },
};
