import { createApp, h } from 'vue';
import { createInertiaApp, usePage } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import { localizedRoute } from './plugins/localizedRoute';
import 'lazysizes';
import 'lazysizes/plugins/attrchange/ls.attrchange';

createInertiaApp({
    title: (title) => title ? `${title} – Inserate Plattform` : 'Inserate Plattform',
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue);

        // Keep window locale in sync so localizedRoute() can read it
        app.mixin({
            mounted()  { window.__inertia_locale__ = usePage().props.locale ?? 'de'; },
            updated()  { window.__inertia_locale__ = usePage().props.locale ?? 'de'; },
        });

        // Override global route() to auto-inject {locale} where required
        app.config.globalProperties.route = localizedRoute;

        app.mount(el);
    },
    progress: { color: '#e35d8f' },
});
