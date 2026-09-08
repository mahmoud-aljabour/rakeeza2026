import { ref } from 'vue';

export function itemsFrom(payload) {
    if (Array.isArray(payload?.data)) {
        return payload.data;
    }

    if (Array.isArray(payload)) {
        return payload;
    }

    return [];
}

export function metaFrom(payload, fallbackPage = 1) {
    return {
        current_page: Number(payload?.current_page) || fallbackPage,
        last_page: Number(payload?.last_page) || 1,
        total: Number(payload?.total) || 0,
        from: Number(payload?.from) || 0,
        to: Number(payload?.to) || 0,
    };
}

export function usePageState(initial = 1) {
    return ref(Math.max(1, Number(initial) || 1));
}
