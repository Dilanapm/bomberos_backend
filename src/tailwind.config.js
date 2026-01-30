import defaultTheme from 'tailwindcss/defaultTheme';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
            },
            fontSize: {
                'xs': '0.75rem',      // 12px
                'sm': '0.875rem',     // 14px
                'base': '1rem',       // 16px (default)
                'lg': '1.125rem',     // 18px
                'xl': '1.25rem',      // 20px
                '2xl': '1.5rem',      // 24px
                '3xl': '1.875rem',    // 30px
                '4xl': '2.25rem',     // 36px
                '5xl': '3rem',        // 48px
            },
            fontWeight: {
                light: '300',
                normal: '400',
                medium: '500',
                semibold: '600',
                bold: '700',
                extrabold: '800',
                black: '900',
            },
        },
        colors: {
            // Rojo institucional (paleta de ColorBox)
            primary: {
                0: "#FFFFFF", // Blanco puro → texto oscuro encima (NO usar texto blanco)

                1: "#EEC1BF", // Fondo muy claro → texto negro / secondary-900
                2: "#DD8B86", // Fondo claro → texto negro / secondary-900

                3: "#CC5E59", // Fondo medio-claro → texto negro (blanco NO cumple WCAG)

                4: "#BB3D36", // Fondo medio → texto blanco SOLO para títulos grandes
                5: "#AA241D", // COLOR PRINCIPAL
                // Texto blanco OK (botón principal)
                // Fondo de acción crítica

                6: "#92150F", // Fondo fuerte → texto blanco SIEMPRE
                // Ideal hover del primary-5

                7: "#7A0C06", // Fondo muy oscuro → texto blanco
                // Acciones destructivas / danger

                8: "#630702", // Fondo muy oscuro → texto blanco
                // Headers críticos / alerts severos

                9: "#4B0400", // Fondo extremo → texto blanco
                // Muy poco uso, solo énfasis

                10: "#330300", // Fondo casi negro → texto blanco
                // Dark mode, fondos especiales
            },


            // Secondary gris-azulado institucional
            secondary: {
                50: "#F1F5F9", // Fondo general claro (pantallas, layouts)
                100: "#E2E8F0", // Fondo de cards
                200: "#CBD5E1", // Bordes suaves / divisores

                300: "#94A3B8", // Texto secundario (labels)
                400: "#64748B", // Texto normal alternativo
                500: "#475569", // Texto principal en dark-bg claro

                600: "#334155", // Botón secundario → texto blanco
                700: "#1E293B", // Header / navbar → texto blanco
                800: "#0F172A", // Fondo dark mode → texto claro
                900: "#020617", // Fondo dark absoluto → texto claro
            },


            // Accent (warning / atención no crítica)
            accent: {
                400: "#F59E0B",
                500: "#D97706",
            },

            // Success (verde para acciones positivas - login, guardar, etc)
            success: {
                500: "#10B981", // Verde medio → texto blanco
                600: "#059669", // Verde oscuro → texto blanco (hover)
            },

            // Asegurar que white está disponible
            white: "#FFFFFF",
        },
    },
    plugins: [],
};
