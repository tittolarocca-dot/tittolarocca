import { route as ziggyRoute } from '../../../vendor/tightenco/ziggy';

/**
 * Wraps Ziggy's route() to auto-inject the current locale for routes
 * that define a {locale} parameter (i.e. the SEO-prefixed public routes).
 */
export function localizedRoute(name, params = {}, absolute = false) {
    try {
        // Check if the named route requires {locale} by attempting a test resolve
        const config = window.Ziggy ?? {};
        const definition = config?.routes?.[name];
        const needsLocale = definition?.uri?.startsWith('{locale}');

        if (needsLocale) {
            const locale = window.__inertia_locale__ ?? 'de';
            params = typeof params === 'object' && !Array.isArray(params)
                ? { locale, ...params }
                : params;
        }

        return ziggyRoute(name, params, absolute);
    } catch {
        return ziggyRoute(name, params, absolute);
    }
}
