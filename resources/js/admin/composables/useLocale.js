import { computed, reactive } from 'vue';
import axios from 'axios';
import { messages } from '../i18n/messages';

const STORAGE_KEY = 'rakeeza.admin.locale';

const state = reactive({
    locale: detectLocale(),
});

function detectLocale() {
    try {
        const stored = window.localStorage.getItem(STORAGE_KEY);
        if (stored === 'ar' || stored === 'en') {
            return stored;
        }
    } catch {
        // Ignore blocked storage.
    }

    const server = window.RakeezaAdmin?.locale;

    return server === 'en' ? 'en' : 'ar';
}

function lookup(table, key) {
    return key.split('.').reduce((value, part) => value?.[part], table);
}

function applyDocument(locale) {
    const dir = locale === 'en' ? 'ltr' : 'rtl';
    document.documentElement.lang = locale;
    document.documentElement.dir = dir;
    document.title = lookup(messages[locale], 'meta.title') || document.title;

    if (window.RakeezaAdmin) {
        window.RakeezaAdmin.locale = locale;
        window.RakeezaAdmin.dir = dir;
    }
}

applyDocument(state.locale);

export function useLocale() {
    const locale = computed(() => state.locale);
    const isRtl = computed(() => state.locale !== 'en');
    const dir = computed(() => (isRtl.value ? 'rtl' : 'ltr'));

    function t(key, params = {}) {
        const table = messages[state.locale] || messages.ar;
        let text = lookup(table, key);

        if (typeof text !== 'string') {
            text = lookup(messages.ar, key);
        }

        if (typeof text !== 'string') {
            return key;
        }

        Object.entries(params)
            .sort(([left], [right]) => right.length - left.length)
            .forEach(([name, value]) => {
                text = text.replaceAll(`:${name}`, String(value));
            });

        return text;
    }

    async function setLocale(next) {
        if (next !== 'ar' && next !== 'en') {
            return;
        }

        if (state.locale === next) {
            return;
        }

        state.locale = next;
        applyDocument(next);

        try {
            window.localStorage.setItem(STORAGE_KEY, next);
        } catch {
            // Ignore blocked storage.
        }

        try {
            await axios.get(`/locale/${next}`, {
                validateStatus: (status) => status >= 200 && status < 400,
            });
        } catch {
            // Keep the UI locale even if the session sync fails.
        }
    }

    return {
        locale,
        isRtl,
        dir,
        t,
        setLocale,
    };
}
