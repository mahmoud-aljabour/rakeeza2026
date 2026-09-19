import { useLocale } from './useLocale';

export function useApiError() {
    const { t } = useLocale();

    function message(error, fallback) {
        if (error instanceof Error && !error.response) {
            return error.message || fallback || t('errors.generic');
        }

        const status = error?.response?.status;
        const raw = error?.response?.data?.message
            || Object.values(error?.response?.data?.errors || {})[0]?.[0];

        if (status === 401 || raw === 'Unauthenticated.') {
            return t('errors.unauthenticated');
        }

        if (status === 419) {
            return t('errors.expired');
        }

        return raw || fallback || t('errors.generic');
    }

    return { message };
}
