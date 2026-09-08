import { reactive } from 'vue';

const state = reactive({
    items: [],
});

let nextId = 1;

export function useToast() {
    function dismiss(id) {
        const index = state.items.findIndex((item) => item.id === id);

        if (index !== -1) {
            state.items.splice(index, 1);
        }
    }

    function push(type, text, timeout = 4200) {
        const id = nextId++;
        state.items.push({ id, type, text });

        window.setTimeout(() => dismiss(id), timeout);

        return id;
    }

    function success(text) {
        return push('success', text);
    }

    function error(text) {
        return push('error', text);
    }

    function fromResponse(response, fallback) {
        return success(response?.data?.message || response?.message || fallback);
    }

    return {
        toasts: state.items,
        success,
        error,
        fromResponse,
        dismiss,
    };
}
