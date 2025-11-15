import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
    "./Modules/**/*.blade.php",
    "./Modules/**/*.js",
    "./Modules/**/*.vue",
  ],
  darkMode: 'class',
  theme: {
    extend: {
      fontFamily: {
        'inter': ['Inter', 'sans-serif'],
      },
      colors: {
        "primary": "#1f4b8e",
        "primary-dark": "#102a52",
        "secondary": "#182430",
        "secondary-dark": "#060C11",
      }
    },
  },
  plugins: [
    forms,
    function({ addUtilities, theme }) {
      const newUtilities = {
        '.custom-scrollbar': {
          '&::-webkit-scrollbar': { width: '6px' },
          '&::-webkit-scrollbar-track': { background: theme('colors.secondary')},
          '&::-webkit-scrollbar-thumb': { background: '#888' },
          '&::-webkit-scrollbar-thumb:hover': {background: '#555'},
        }
      }
      addUtilities(newUtilities, ['responsive', 'hover'])
    }
  ],
}
