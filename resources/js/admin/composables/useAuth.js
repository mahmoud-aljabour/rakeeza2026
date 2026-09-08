import { reactive } from 'vue';
import axios from 'axios';

const state = reactive({
    user: null,
    loaded: false,
});

export function useAuth() {
    async function fetchUser() {
        const { data } = await axios.get('/api/user');
        state.user = data;
        state.loaded = true;
        return data;
    }

    async function login(email, password) {
        await axios.get('/sanctum/csrf-cookie');
        await axios.post('/login', { email, password });

        try {
            return await fetchUser();
        } catch {
            state.user = null;
            state.loaded = true;
            throw new Error('تعذر حفظ جلسة الدخول. افتح اللوحة من نفس رابط الموقع ثم أعد تسجيل الدخول.');
        }
    }

    async function logout() {
        await axios.post('/logout');
        state.user = null;
    }

    return { state, fetchUser, login, logout };
}
