import { route as ziggyRoute } from '../../../vendor/tightenco/ziggy';

/**
 * Locale-aware wrapper around Ziggy's route().
 * Auto-injects the current locale for any route whose URI starts with {locale}.
 * Handles string, number, array, and object params correctly.
 */
export function localizedRoute(name, params = {}, absolute = false) {
    const config     = window.Ziggy ?? {};
    const definition = config?.routes?.[name];
    const needsLocale = definition?.uri?.startsWith('{locale}');

    if (needsLocale) {
        const locale = window.__inertia_locale__ ?? 'de';

        if (typeof params === 'string' || typeof params === 'number') {
            // Positional string/number: map to the first non-locale parameter
            const routeParams = definition.parameters ?? [];
            const firstNonLocale = routeParams.find(p => p !== 'locale');
            params = firstNonLocale
                ? { locale, [firstNonLocale]: params }
                : { locale };
        } else if (Array.isArray(params)) {
            // Array: prepend the locale value
            params = [locale, ...params];
        } else {
            // Object: merge locale in (existing locale key wins)
            params = { locale, ...params };
        }
    }

    return ziggyRoute(name, params, absolute);
}
