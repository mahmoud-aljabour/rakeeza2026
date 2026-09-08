export function useApiError() {
    function message(error, fallback = 'تعذر تنفيذ العملية.') {
        if (error instanceof Error && !error.response) {
            return error.message || fallback;
        }

        const status = error?.response?.status;
        const raw = error?.response?.data?.message
            || Object.values(error?.response?.data?.errors || {})[0]?.[0];

        if (status === 401 || raw === 'Unauthenticated.') {
            return 'انتهت الجلسة أو لم تُحفظ. سجّل الدخول مرة أخرى من نفس رابط الموقع.';
        }

        if (status === 419) {
            return 'انتهت صلاحية النموذج. حدّث الصفحة ثم أعد المحاولة.';
        }

        return raw || fallback;
    }

    return { message };
}
