import { createApp, h } from 'vue';
import { createInertiaApp, router } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import { localizedRoute } from './plugins/localizedRoute';
import 'lazysizes';
import 'lazysizes/plugins/attrchange/ls.attrchange';

// Keep window locale in sync across Inertia navigations
router.on('navigate', (event) => {
    window.__inertia_locale__ = event.detail.page?.props?.locale ?? 'de';
});

createInertiaApp({
    title: (title) => title ? `${title} – Inserate Plattform` : 'Inserate Plattform',
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        // Bootstrap locale from initial page data
        window.__inertia_locale__ = props.initialPage?.props?.locale ?? 'de';

        const app = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue);

        // Override route() globally: in templates via globalProperties,
        // in script-setup via window.route (set by ZiggyVue/@routes)
        app.config.globalProperties.route = localizedRoute;
        window.route = localizedRoute;

        app.mount(el);
    },
    progress: { color: '#e35d8f' },
});
