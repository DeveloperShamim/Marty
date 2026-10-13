// Storefront Tailwind build → public/theme/css/tw-store.css (npm run build:css).
// Theme colours come from Store Settings at runtime: layouts/storefront.blade.php sets the --tw-* variables
// as "R G B" channels, so opacity modifiers like bg-brand-50/70 still work.
const v = (name) => `rgb(var(--tw-${name}) / <alpha-value>)`;

export default {
    content: [
        './resources/views/**/*.blade.php',
        '!./resources/views/admin/**',
        '!./resources/views/layouts/admin.blade.php',
        './public/theme/js/{storefront,auto-row,main}.js',
        './app/**/*.php',
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
    ],
    theme: {
        extend: {
            // One corner scale for the whole shop: 6 tags · 10 buttons & inputs · 16 cards · 24 large panels · pill.
            borderRadius: { sm: '6px', DEFAULT: '6px', md: '10px', lg: '10px', xl: '10px', '2xl': '16px', '3xl': '24px' },
            colors: {
                brand: {
                    50: v('brand-50'), 100: v('brand-100'), 200: v('brand-200'), 300: v('brand-300'), 400: v('brand-400'),
                    500: v('brand-500'), 600: v('brand-600'), 700: v('brand-700'), 800: v('brand-800'), 900: v('brand-900'),
                },
                accent: { 400: v('accent-400'), 500: v('accent-500'), 600: v('accent-600'), 700: v('accent-700') },
                ink: v('ink'),
                surface: { light: '#FFFFFF', soft: v('surface-soft') },
            },
            fontFamily: { sans: ['Plus Jakarta Sans', 'Hind Siliguri', 'sans-serif'], display: ['Plus Jakarta Sans', 'Hind Siliguri', 'sans-serif'] },
        },
    },
};
