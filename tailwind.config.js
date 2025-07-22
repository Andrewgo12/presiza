import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                primary: {
                    50: '#eef2ff',
                    100: '#e0e7ff',
                    200: '#c7d2fe',
                    300: '#a5b4fc',
                    400: '#818cf8',
                    500: '#6366f1',
                    600: '#4f46e5',
                    700: '#4338ca',
                    800: '#3730a3',
                    900: '#312e81',
                    950: '#1e1b4b',
                },
                gray: {
                    50: '#f9fafb',
                    100: '#f3f4f6',
                    200: '#e5e7eb',
                    300: '#d1d5db',
                    400: '#9ca3af',
                    500: '#6b7280',
                    600: '#4b5563',
                    700: '#374151',
                    800: '#1f2937',
                    900: '#111827',
                    950: '#030712',
                },
            },
            animation: {
                'fade-in': 'fadeIn 0.5s ease-in-out',
                'slide-in': 'slideIn 0.3s ease-out',
                'bounce-in': 'bounceIn 0.6s ease-out',
                'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
            },
            keyframes: {
                fadeIn: {
                    '0%': { opacity: '0' },
                    '100%': { opacity: '1' },
                },
                slideIn: {
                    '0%': { transform: 'translateX(-100%)' },
                    '100%': { transform: 'translateX(0)' },
                },
                bounceIn: {
                    '0%': { 
                        transform: 'scale(0.3)',
                        opacity: '0'
                    },
                    '50%': { 
                        transform: 'scale(1.05)',
                        opacity: '0.8'
                    },
                    '70%': { 
                        transform: 'scale(0.9)',
                        opacity: '0.9'
                    },
                    '100%': { 
                        transform: 'scale(1)',
                        opacity: '1'
                    },
                },
            },
            spacing: {
                '18': '4.5rem',
                '88': '22rem',
                '128': '32rem',
            },
            maxWidth: {
                '8xl': '88rem',
                '9xl': '96rem',
            },
            zIndex: {
                '60': '60',
                '70': '70',
                '80': '80',
                '90': '90',
                '100': '100',
            },
            backdropBlur: {
                xs: '2px',
            },
            boxShadow: {
                'inner-lg': 'inset 0 2px 4px 0 rgba(0, 0, 0, 0.1)',
                'glow': '0 0 20px rgba(99, 102, 241, 0.3)',
                'glow-lg': '0 0 40px rgba(99, 102, 241, 0.4)',
            },
            borderRadius: {
                '4xl': '2rem',
            },
            typography: {
                DEFAULT: {
                    css: {
                        maxWidth: 'none',
                        color: '#374151',
                        '[class~="lead"]': {
                            color: '#4b5563',
                        },
                        a: {
                            color: '#4f46e5',
                            textDecoration: 'none',
                            fontWeight: '500',
                            '&:hover': {
                                color: '#4338ca',
                                textDecoration: 'underline',
                            },
                        },
                        strong: {
                            color: '#111827',
                            fontWeight: '600',
                        },
                        'ol[type="A"]': {
                            '--list-counter-style': 'upper-alpha',
                        },
                        'ol[type="a"]': {
                            '--list-counter-style': 'lower-alpha',
                        },
                        'ol[type="A" s]': {
                            '--list-counter-style': 'upper-alpha',
                        },
                        'ol[type="a" s]': {
                            '--list-counter-style': 'lower-alpha',
                        },
                        'ol[type="I"]': {
                            '--list-counter-style': 'upper-roman',
                        },
                        'ol[type="i"]': {
                            '--list-counter-style': 'lower-roman',
                        },
                        'ol[type="I" s]': {
                            '--list-counter-style': 'upper-roman',
                        },
                        'ol[type="i" s]': {
                            '--list-counter-style': 'lower-roman',
                        },
                        'ol[type="1"]': {
                            '--list-counter-style': 'decimal',
                        },
                    },
                },
            },
        },
    },

    plugins: [
        forms,
        require('@tailwindcss/typography'),
        require('@tailwindcss/aspect-ratio'),
        
        // Plugin personalizado para utilidades adicionales
        function({ addUtilities, addComponents, theme }) {
            // Utilidades personalizadas
            addUtilities({
                '.text-shadow': {
                    textShadow: '0 2px 4px rgba(0,0,0,0.10)',
                },
                '.text-shadow-md': {
                    textShadow: '0 4px 8px rgba(0,0,0,0.12), 0 2px 4px rgba(0,0,0,0.08)',
                },
                '.text-shadow-lg': {
                    textShadow: '0 15px 35px rgba(0,0,0,0.1), 0 5px 15px rgba(0,0,0,0.07)',
                },
                '.text-shadow-none': {
                    textShadow: 'none',
                },
                '.scrollbar-hide': {
                    '-ms-overflow-style': 'none',
                    'scrollbar-width': 'none',
                    '&::-webkit-scrollbar': {
                        display: 'none',
                    },
                },
                '.scrollbar-thin': {
                    'scrollbar-width': 'thin',
                    '&::-webkit-scrollbar': {
                        width: '6px',
                        height: '6px',
                    },
                    '&::-webkit-scrollbar-track': {
                        backgroundColor: theme('colors.gray.100'),
                        borderRadius: theme('borderRadius.full'),
                    },
                    '&::-webkit-scrollbar-thumb': {
                        backgroundColor: theme('colors.gray.300'),
                        borderRadius: theme('borderRadius.full'),
                        '&:hover': {
                            backgroundColor: theme('colors.gray.400'),
                        },
                    },
                },
            });

            // Componentes personalizados
            addComponents({
                '.btn': {
                    padding: `${theme('spacing.2')} ${theme('spacing.4')}`,
                    borderRadius: theme('borderRadius.md'),
                    fontWeight: theme('fontWeight.medium'),
                    fontSize: theme('fontSize.sm'),
                    lineHeight: theme('lineHeight.5'),
                    display: 'inline-flex',
                    alignItems: 'center',
                    justifyContent: 'center',
                    transition: 'all 0.2s ease-in-out',
                    cursor: 'pointer',
                    '&:disabled': {
                        opacity: '0.5',
                        cursor: 'not-allowed',
                    },
                },
                '.btn-primary': {
                    backgroundColor: theme('colors.primary.600'),
                    color: theme('colors.white'),
                    boxShadow: theme('boxShadow.sm'),
                    '&:hover:not(:disabled)': {
                        backgroundColor: theme('colors.primary.700'),
                        boxShadow: theme('boxShadow.md'),
                    },
                    '&:focus': {
                        outline: 'none',
                        boxShadow: `0 0 0 3px ${theme('colors.primary.200')}`,
                    },
                },
                '.btn-secondary': {
                    backgroundColor: theme('colors.white'),
                    color: theme('colors.gray.700'),
                    boxShadow: theme('boxShadow.sm'),
                    border: `1px solid ${theme('colors.gray.300')}`,
                    '&:hover:not(:disabled)': {
                        backgroundColor: theme('colors.gray.50'),
                        borderColor: theme('colors.gray.400'),
                    },
                    '&:focus': {
                        outline: 'none',
                        boxShadow: `0 0 0 3px ${theme('colors.gray.200')}`,
                    },
                },
                '.card': {
                    backgroundColor: theme('colors.white'),
                    borderRadius: theme('borderRadius.lg'),
                    boxShadow: theme('boxShadow.sm'),
                    overflow: 'hidden',
                },
                '.card-header': {
                    padding: `${theme('spacing.4')} ${theme('spacing.6')}`,
                    borderBottom: `1px solid ${theme('colors.gray.200')}`,
                    backgroundColor: theme('colors.gray.50'),
                },
                '.card-body': {
                    padding: theme('spacing.6'),
                },
                '.card-footer': {
                    padding: `${theme('spacing.4')} ${theme('spacing.6')}`,
                    borderTop: `1px solid ${theme('colors.gray.200')}`,
                    backgroundColor: theme('colors.gray.50'),
                },
            });
        },
    ],
};
