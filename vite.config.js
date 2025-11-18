import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import { resolve } from 'path';

export default defineConfig({
    define: {
        // Ensure global is available for UMD modules
        global: 'globalThis',
        // Alpine.js is loaded globally, define it for Vite parsing
        'window.Alpine': 'window.Alpine',
    },
    resolve: {
        alias: {
            '@': resolve(__dirname, 'resources'),
        },
    },
    optimizeDeps: {
        include: ['jquery', 'trumbowyg', 'trix'],
        esbuildOptions: {
            // Ensure UMD modules are treated correctly
            define: {
                global: 'globalThis',
            },
        },
        // Trumbowyg'in jQuery'ye bağımlılığını belirt
        // Bu, Trumbowyg'in jQuery yüklendikten sonra yüklenmesini garanti eder
        force: false,
    },
    // Ensure UMD modules work correctly
    ssr: {
        noExternal: ['jquery', 'trumbowyg', 'trix'],
    },
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                // Posts Module
                'Modules/Posts/resources/assets/sass/app.scss',
                'Modules/Posts/resources/assets/js/app.js',
                'resources/js/image-preview-renderer/index.js',
                // Articles Module
                'Modules/Articles/resources/assets/sass/app.scss',
                'Modules/Articles/resources/assets/js/app.js',
                // Authors Module
                'Modules/Authors/resources/assets/sass/app.scss',
                'Modules/Authors/resources/assets/js/app.js',
                // Categories Module
                'Modules/Categories/resources/assets/sass/app.scss',
                'Modules/Categories/resources/assets/js/app.js',
                // Files Module
                'Modules/Files/resources/assets/sass/app.scss',
                'Modules/Files/resources/assets/js/app.js',
                // Roles Module
                'Modules/Roles/resources/assets/sass/app.scss',
                'Modules/Roles/resources/assets/js/app.js',
                // User Module
                'Modules/User/resources/assets/sass/app.scss',
                'Modules/User/resources/assets/js/app.js',
                // Comments Module
                'Modules/Comments/resources/assets/sass/app.scss',
                'Modules/Comments/resources/assets/js/app.js',
                // Logs Module
                'Modules/Logs/resources/assets/sass/app.scss',
                'Modules/Logs/resources/assets/js/app.js',
                // Headline Module
                'Modules/Headline/resources/assets/sass/app.scss',
                'Modules/Headline/resources/assets/js/app.js',
                // Settings Module
                'Modules/Settings/resources/assets/sass/app.scss',
                'Modules/Settings/resources/assets/js/app.js',
                // AgencyNews Module
                'Modules/AgencyNews/resources/assets/sass/app.scss',
                'Modules/AgencyNews/resources/assets/js/app.js',
                // Banks Module
                'Modules/Banks/resources/assets/sass/app.scss',
                'Modules/Banks/resources/assets/js/app.js',
                // Newsletters Module
                'Modules/Newsletters/resources/assets/sass/app.scss',
                'Modules/Newsletters/resources/assets/js/app.js',
                // Lastminutes Module
                'Modules/Lastminutes/resources/assets/js/app.js',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        host: '127.0.0.1',
        port: 5173,
        strictPort: true,
    },
    build: {
        rollupOptions: {
            output: {
                manualChunks: (id) => {
                    // jQuery ve Trumbowyg'i ana bundle'da tut
                    // Bu, jQuery'nin global olarak expose edilmesini ve Trumbowyg'in
                    // jQuery'ye erişebilmesini garanti eder
                    if (id.includes('node_modules/jquery') || id.includes('node_modules/trumbowyg')) {
                        return undefined; // Ana bundle'a dahil et
                    }

                    // Vendor chunk for common libraries
                    if (id.includes('node_modules/alpinejs') || id.includes('node_modules/sortablejs')) {
                        return 'vendor';
                    }

                    // Module chunks for better caching
                    if (id.includes('Modules/Posts')) return 'posts';
                    if (id.includes('Modules/Articles')) return 'articles';
                    if (id.includes('Modules/Authors')) return 'authors';
                    if (id.includes('Modules/Categories')) return 'categories';
                    if (id.includes('Modules/Files')) return 'files';
                    if (id.includes('Modules/Roles')) return 'roles';
                    if (id.includes('Modules/User')) return 'users';
                    if (id.includes('Modules/Comments')) return 'comments';
                    if (id.includes('Modules/Logs')) return 'logs';
                    if (id.includes('Modules/Headline')) return 'headlines';
                    if (id.includes('Modules/Settings')) return 'settings';
                    if (id.includes('Modules/AgencyNews')) return 'agencynews';
                    if (id.includes('Modules/Banks')) return 'banks';
                    if (id.includes('Modules/Newsletters')) return 'newsletters';
                    if (id.includes('Modules/Lastminutes')) return 'lastminutes';
                },
                // Ensure jQuery is available globally in the bundle
                globals: {
                    'jquery': 'jQuery',
                }
            }
        }
    }
});
