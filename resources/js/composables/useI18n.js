import { usePage } from '@inertiajs/vue3';

/**
 * Returns a $t() translation function scoped to the current locale.
 * Usage: const { t } = useI18n()
 *        t('nav.login')  → looks up translations.nav.login
 */
export function useI18n() {
    const page = usePage();

    function t(key, replacements = {}) {
        const [file, ...rest] = key.split('.');
        const subKey = rest.join('.');

        const translations = page.props.translations ?? {};
        const group        = translations[file] ?? {};
        let   value        = subKey ? (group[subKey] ?? key) : (group ?? key);

        if (typeof value !== 'string') return key;

        Object.entries(replacements).forEach(([k, v]) => {
            value = value.replace(`:${k}`, v);
        });

        return value;
    }

    return { t, locale: page.props.locale };
}
