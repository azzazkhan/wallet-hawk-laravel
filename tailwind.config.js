/** @type {import('tailwindcss').Config} */
module.exports = {
    darkMode: 'class',
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.ts',
        './resources/**/*.tsx',
        './resources/**/*.vue',
        './node_modules/flowbite/**/*.js'
    ],
    theme: {
        extend: {
            fontSize: {
                xxxs: '.5rem',
                xxs: '.625rem'
            }
        }
    },
    plugins: [require('flowbite/plugin'), require('@tailwindcss/line-clamp')]
};
