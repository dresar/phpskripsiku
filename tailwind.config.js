/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ["./public/**/*.php", "./public/**/*.html"],
  darkMode: 'class',
  theme: {
    extend: {
      colors: {
        panel: '#1e293b',
        card: '#334155',
        accent: '#0ea5e9',
        'mutu-prima': '#22c55e',
        'mutu-rendah': '#ef4444',
        terawetkan: '#eab308',
        oplos: '#3b82f6',
        kontaminasi: '#a855f7',
      },
    },
  },
  plugins: [],
};
