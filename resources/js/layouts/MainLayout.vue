<template>
    <div>
        <v-navigation-drawer
            v-model="drawer"
            :rail="rail && mdAndUp"
            :permanent="mdAndUp"
            :temporary="!mdAndUp"
            color="surface"
            border="end"
            app
        >
            <div class="pa-4 d-flex align-center">
                <v-avatar color="primary" size="36" rounded="lg" class=" font-weight-bold">
                    D
                </v-avatar>
                <div v-if="!rail || !mdAndUp" class="ms-3">
                    <div class="text-subtitle-1 font-weight-bold ">DDHRM</div>
                    <div class=" text-medium-emphasis te">HR + Payroll + Invoices</div>
                </div>
            </div>

            <v-list density="comfortable" nav class="px-2 ">
                <v-list-item 
                    v-if="auth.can('dashboard.view')"
                    exact
                    prepend-icon="mdi-view-dashboard-outline"
                    title="Dashboard"
                    :to="{ name: 'dashboard' }"
                    rounded="lg"
                />
                <v-list-item
                    v-if="auth.can('admin.access')"
                    prepend-icon="mdi-shield-crown-outline"
                    title="Admin panel"
                    :to="{ name: 'admin' }"
                    rounded="lg"
                />
                <v-list-item
                    v-if="auth.can('departments.view')"
                    prepend-icon="mdi-office-building-outline"
                    title="Department"
                    :to="{ name: 'department' }"
                    rounded="lg"
                />
                <v-list-item
                    v-if="auth.can('positions.view')"
                    prepend-icon="mdi-sitemap-outline"
                    title="Position"
                    :to="{ name: 'position' }"
                    rounded="lg"
                />
                <v-list-item
                    v-if="auth.can('staff.view')"
                    prepend-icon="mdi-account-group-outline"
                    title="Staff"
                    :to="{ name: 'staff' }"
                    rounded="lg"
                />
                <v-list-group value="attendance" v-if="auth.can('attendance.view')">
                    <template #activator="{ props }">
                        <v-list-item v-bind="props" prepend-icon="mdi-clock-outline" title="Attendance Management"></v-list-item>
                    </template>
                <v-list-item
                    v-if="auth.can('attendance.view')"
                    prepend-icon="mdi-clock-outline"
                    title="Attendance"
                    :to="{ name: 'attendance' }"
                    rounded="lg"
                />
                <v-list-item
                    v-if="auth.can('attendance.manage')"
                    prepend-icon="mdi-clock-settings"
                    title="Attendance Settings"
                    :to="{ name: 'attendance.settings' }"
                    rounded="lg"
                />
                </v-list-group>
                <v-list-group value="leave_management">
                    <template #activator="{ props }">
                        <v-list-item v-bind="props" prepend-icon="mdi-palm-tree" title="Leave Manage"></v-list-item>
                    </template>

                    <v-list-item 
                        prepend-icon="mdi-cog-outline" 
                        title="Leave Rules" 
                        :to="{ name: 'leave-rules' }"
                    ></v-list-item>
                    
                    <v-list-item 
                        prepend-icon="mdi-file-document-edit-outline" 
                        title="Leave Requests" 
                        :to="{ name: 'leave-requests' }"
                    ></v-list-item>                 
                </v-list-group>

                <v-list-item
                    v-if="auth.can('payroll.view')"
                    prepend-icon="mdi-currency-usd"
                    title="Payroll"
                    :to="{ name: 'payroll' }"
                    rounded="lg"
                />
                <v-list-item
                    prepend-icon="mdi-file-document-outline"
                    title="Invoices"
                    :to="{ name: 'invoices' }"
                    rounded="lg"
                />
                <v-list-group value="system-management" v-if="showSystemManageMenu">
                    <template #activator="{ props }">
                        <v-list-item
                            v-bind="props"
                            prepend-icon="mdi-cog-outline"
                            title="System Manage"
                            rounded="lg"
                        />
                    </template>
                    <v-list-item
                        v-if="showRolesMenu"
                        prepend-icon="mdi-shield-account-outline"
                        title="Roles"
                        :to="{ name: 'roles' }"
                        rounded="lg"
                    />
                </v-list-group>
            </v-list>
            <template #append>
                <div class="pa-2">
                    <v-btn
                        v-if="mdAndUp"
                        block
                        variant="text"
                        size="small"
                        @click="rail = !rail"
                    >
                        {{ rail ? 'Expand' : 'Collapse' }}
                    </v-btn>
                </div>
            </template>
        </v-navigation-drawer>

        <v-app-bar flat color="white" border="b" elevation="0" height="72" app>
            <v-app-bar-nav-icon v-if="!mdAndUp" class="ms-1" @click="drawer = !drawer" />

            <v-toolbar-title class="text-h6 font-weight-semibold">
                {{ pageTitle }}
            </v-toolbar-title>
            <v-spacer />
            <v-menu>
                <template #activator="{ props }">
                    <v-btn v-bind="props" variant="text" class="me-2">
                        <v-avatar color="primary" size="32" class=" me-2">
                            {{ initials }}
                        </v-avatar>
                        <span class="d-none d-sm-inline text-body-2">{{ auth.user?.name }}</span>
                        <v-icon end>mdi-chevron-down</v-icon>
                    </v-btn>
                </template>

                <v-list density="compact" min-width="200">
                    <v-list-item 
                        title="Profile" 
                        prepend-icon="mdi-account-outline" 
                        to="/profile" 
                    />
                    
                    <v-list-item 
                        title="Logout" 
                        prepend-icon="mdi-logout" 
                        @click="onLogout" 
                    />
                </v-list>
            </v-menu>
        </v-app-bar>

        <v-main class="bg-background">
            <v-container fluid class="py-6">
                <router-view />
            </v-container>
        </v-main>

        <v-footer app border class=" text-medium-emphasis justify-center">
            Copyright @{{ new Date().getFullYear() }} — All rights reserved.
        </v-footer>
    </div>
</template>

<script setup>
    import { computed, ref, onMounted } from 'vue';
    import { useRoute, useRouter } from 'vue-router';
    import axios from 'axios';
    import { useDisplay } from 'vuetify';
    import { useAuthStore } from '@/stores/auth';

    const { mdAndUp } = useDisplay();
    const route = useRoute();
    const router = useRouter();
    const auth = useAuthStore();

    const drawer = ref(true);
    const rail = ref(false);
    const roles = ref([]);
    const systemGroup = ref(true);
    const systemRolesGroup = ref(true);

    const showSystemManageMenu = computed(() => {
        return auth.hasRoleSlug('admin') || auth.can('roles.view') || auth.can('roles.manage');
    });
    const showRolesMenu = computed(() => auth.can('roles.view') || auth.can('roles.manage') || auth.hasRoleSlug('admin'));

    const pageTitle = computed(() => route.meta.title ?? 'Dashboard');

    async function loadRoles() {
        if (!showRolesMenu.value) {
            return;
        }

        try {
            const { data } = await axios.get('/api/roles');
            roles.value = data.data ?? [];
        } catch {
            roles.value = [];
        }
    }

    function openRole(role) {
        router.push({ name: 'roles.detail', params: { id: role.id } });
    }

    function isSelectedRole(role) {
        return route.name === 'roles.detail' && Number(route.params.id) === role.id;
    }

    onMounted(loadRoles);

    const initials = computed(() => {
        const name = auth.user?.name ?? '?';
        return name
            .split(' ')
            .map((p) => p[0])
            .join('')
            .slice(0, 2)
            .toUpperCase();
    });

    async function onLogout() {
        await auth.logout();
        await router.push({ name: 'login' });
    }
</script>

<style scoped>
.bg-navi {
    background-color:#3B4860 !important; 
}
.text-caption{
    color: #FFFFFF!important;
}
</style>
