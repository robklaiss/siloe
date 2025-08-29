/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./app/views/**/*.php",
    "./public/**/*.js"
  ],
  important: true,
  corePlugins: {
    appearance: true,
    verticalAlign: true,
  },
  theme: {
    extend: {},
  },
  plugins: [
    require('@tailwindcss/forms')({
      strategy: 'class',
    }),
  ],
}
