// Settings shared by the store and admin stylesheets.
// Theme colours come from Admin → Settings at run time, so they are CSS variables ("R G B") that the
// layouts print in :root; <alpha-value> keeps opacity classes such as bg-brand-600/90 working.
const v = (name) => `rgb(var(--vt-${name}) / <alpha-value>)`;

module.exports = {
  v,
  content: [
    './resources/views/**/*.blade.php',
    './resources/js/**/*.js',
    './public/theme/js/**/*.js',
    './app/**/*.php',
  ],
  // Built from a number in a template, e.g. lg:grid-cols-{{ min(4, $count) }}
  safelist: [{ pattern: /^grid-cols-[1-4]$/, variants: ['lg'] }],
  fonts: {
    sans: ['Plus Jakarta Sans', 'Hind Siliguri', 'ui-sans-serif', 'system-ui'],
    display: ['Plus Jakarta Sans', 'Hind Siliguri', 'sans-serif'],
  },
};
