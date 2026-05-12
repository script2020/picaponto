const defaultTheme = require('tailwindcss/defaultTheme');

/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    safelist: [
        // prioridade
        'bg-red-100', 'text-red-700',
        'bg-yellow-100', 'text-yellow-700',
        'bg-green-100', 'text-green-700',
        // estado
        'bg-gray-100', 'text-gray-600',
        'bg-orange-100', 'text-orange-700',
        'bg-blue-100', 'text-blue-700',
        // progresso
        'bg-indigo-600', 'text-white',
        'bg-gray-200', 'text-gray-500',
        'text-indigo-600', 'text-gray-400',
        'font-medium',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [require('@tailwindcss/forms')],
};