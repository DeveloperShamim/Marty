// Admin (and admin login) Tailwind build → public/theme/css/tw-admin.css (npm run build:css).
// Brand colours come from Store Settings at runtime: layouts/admin.blade.php sets the --tw-* variables
// as "R G B" channels, so opacity modifiers like ring-brand-600/20 still work.
import colors from 'tailwindcss/colors';

const v = (name) => `rgb(var(--tw-${name}) / <alpha-value>)`;
const brand = Object.fromEntries([50, 100, 200, 300, 400, 500, 600, 700, 800, 900].map((k) => [k, v(`brand-${k}`)]));

export default {
    content: [
        './resources/views/admin/**/*.blade.php',
        './resources/views/layouts/admin.blade.php',
        './resources/views/partials/{brand,google-button}.blade.php',
        './resources/views/components/**/*.blade.php',
        './public/theme/js/{admin,admin-shell,order-alerts,camera-scanner}.js',
        './app/**/*.php',
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
    ],
    theme: {
        extend: {
            colors: {
                primary: { DEFAULT: v('primary') },
                brand,
                // teal was the old admin accent; it follows the store brand now
                teal: brand,
                // gray uses the warm stone palette; slate panels (POS, order page) too, darkest shades in the store's dark tone
                gray: colors.stone,
                slate: { ...colors.stone, 800: v('slate-800'), 900: v('slate-900'), 950: v('slate-950') },
                accent: { 500: v('primary') },
                ink: v('ink'),
            },
            fontFamily: { sans: ['Plus Jakarta Sans', 'Hind Siliguri', 'ui-sans-serif', 'system-ui'], display: ['Plus Jakarta Sans', 'Hind Siliguri', 'sans-serif'] },
            boxShadow: { card: '0 1px 2px rgba(41,37,36,.04), 0 4px 16px -8px rgba(41,37,36,.08)', panel: '0 1px 2px rgba(28,25,23,.03), 0 12px 32px -20px rgba(28,25,23,.18)' },
            borderRadius: { panel: '24px' },
        },
    },
};
