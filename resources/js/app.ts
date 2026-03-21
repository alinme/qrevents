import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { createApp, Fragment, h } from 'vue';
import 'vue-sonner/style.css';
import '../css/app.css';
import AppFlashToasts from '@/components/AppFlashToasts.vue';
import { Toaster } from '@/components/ui/sonner';
import { initializeTheme } from '@/composables/useAppearance';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) =>
        resolvePageComponent(
            `./pages/${name}.vue`,
            import.meta.glob<DefineComponent>('./pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        createApp({
            render: () =>
                h(Fragment, [
                    h(App, props),
                    h(Toaster, {
                        position: 'top-right',
                        richColors: true,
                    }),
                    h(AppFlashToasts),
                ]),
        })
            .use(plugin)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});

// Force the app into the single supported light theme on page load.
initializeTheme();
