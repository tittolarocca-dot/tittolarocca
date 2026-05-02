import { route as ziggyRoute } from '../../../vendor/tightenco/ziggy';

/**
 * Locale-aware wrapper around Ziggy's route().
 * Strategy: try calling the route as-is; if Ziggy complains about a missing
 * parameter (which happens when the route has a {locale} prefix), inject the
 * current locale and retry. This works for all param types without needing
 * access to the Ziggy config object at module load time.
 */
export function localizedRoute(name, params = {}, absolute = false) {
    try {
        return ziggyRoute(name, params, absolute);
    } catch (e) {
        if (typeof e?.message === 'string' && e.message.includes('parameter is required')) {
            const locale = window.__inertia_locale__ ?? 'de';

            let localeParams;
            if (typeof params === 'string' || typeof params === 'number') {
                // Positional string → locale first, value second
                localeParams = [locale, params];
            } else if (Array.isArray(params)) {
                localeParams = [locale, ...params];
            } else {
                localeParams = { locale, ...params };
            }

            try {
                return ziggyRoute(name, localeParams, absolute);
            } catch {
                throw e; // re-throw original error if retry also fails
            }
        }
        throw e;
    }
}
