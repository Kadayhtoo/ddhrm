import { defineStore } from 'pinia';
import axios from 'axios';
import { getStoredToken, setStoredToken } from '../bootstrap';

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: null,
        token: getStoredToken(),
    }),
    getters: {
        isAuthenticated: (state) => Boolean(state.token),
        permissionSlugs: (state) => state.user?.permissions ?? [],
        roleList: (state) => state.user?.roles ?? [],
    },
    actions: {
        can(permission) {
            return (this.user?.permissions ?? []).includes(permission);
        },
        canAny(permissions) {
            return permissions.some((p) => this.can(p));
        },
        hasRoleSlug(slug) {
            return (this.user?.roles ?? []).some((r) => r.slug === slug);
        },
        setSession({ token, user }) {
            this.token = token;
            this.user = user;
            setStoredToken(token);
        },
        clearSession() {
            this.token = null;
            this.user = null;
            setStoredToken(null);
        },
        async login(payload) {
            const { data } = await axios.post('/api/auth/login', payload);
            this.setSession({ token: data.token, user: data.user });
        },
        async logout() {
            try {
                await axios.post('/api/auth/logout');
            } finally {
                this.clearSession();
            }
        },
        async fetchMe() {
            const { data } = await axios.get('/api/auth/me');
            this.user = data.user;
        },
    },
});
