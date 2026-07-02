<template>
    <div >
        <v-navigation-drawer
            v-model="drawer"
            :rail="rail && mdAndUp"
            :permanent="mdAndUp"
            :temporary="!mdAndUp"
            color="surface"
            background-color="#641C55"
            border="end"
            v-if="!isPreviewPage"
            app
        >
        <div class="pa-4">
          <div v-if="!rail || !mdAndUp" class="d-flex align-center justify-space-between">
            <div>
                <img
                    src="/assets/logo/dd_logo.png"
                    alt="DDHRM Logo"
                    style="width:150px;"
                >
                <div class="text-subtitle-1">
                    HR + Payroll + Invoices
                </div>
            </div>
            <v-btn v-if="mdAndUp" icon variant="text" @click="rail = !rail">
                <v-icon size="24" class="menu-icon">mdi-menu</v-icon>
            </v-btn>
          </div>
          <div v-else class="d-flex flex-column align-center">
              <v-btn
                  icon
                  variant="text"
                  class="mb-2"
                  @click="rail = !rail"
              >
                  <v-icon size="24">mdi-menu</v-icon>
              </v-btn>

              <v-avatar
                  color="primary"
                  size="40"
                  rounded="lg"
              >
                  <img src="/assets/logo/d.png" alt="DDHRM Logo">
              </v-avatar>
          </div>
        </div>
                  
        <v-list density="compact" nav class="sidebar-nav">
          <v-list-item
            v-if="auth.can('dashboard.view')"
            title="Dashboard"
            :to="{ name: 'dashboard' }"
            rounded="xl"
            exact
          >
            <template #prepend>
              <v-icon size="20">mdi-view-dashboard-outline</v-icon>
            </template>
          </v-list-item>

          <v-list-item
            v-if="auth.can('admin.access')"
            title="Admin Panel"
            :to="{ name: 'admin' }"
            rounded="xl"
          >
            <template #prepend>
              <v-icon size="20">mdi-shield-crown-outline</v-icon>
            </template>
          </v-list-item>

          <v-list-item
            v-if="auth.can('company-info.view')"
            title="Company Info"
            :to="{ name: 'company-info' }"
            rounded="xl"
          >
            <template #prepend>
              <v-icon size="20">mdi-domain</v-icon>
            </template>
          </v-list-item>

          <v-list-item
            v-if="auth.can('departments.view')"
            title="Department"
            :to="{ name: 'department' }"
            rounded="xl"
          >
            <template #prepend>
              <v-icon size="20">mdi-office-building-outline</v-icon>
            </template>
          </v-list-item>

          <v-list-item
            v-if="auth.can('positions.view')"
            title="Position"
            :to="{ name: 'position' }"
            rounded="xl"
          >
            <template #prepend>
              <v-icon size="20">mdi-sitemap-outline</v-icon>
            </template>
          </v-list-item>

          <v-list-item
            v-if="auth.can('staff.view')"
            title="Staff"
            :to="{ name: 'staff' }"
            rounded="xl"
          >
            <template #prepend>
              <v-icon size="20">mdi-account-group-outline</v-icon>
            </template>
          </v-list-item>

          <v-list-group
            value="attendance"
            v-if="auth.can('attendance.view')"
          >
            <template #activator="{ props }">
              <v-list-item
                v-bind="props"
                title="Attendance Management"
                rounded="xl"
              >
                <template #prepend>
                  <v-icon size="15">mdi-format-list-bulleted</v-icon>                
                </template>
              </v-list-item>
            </template>

            <v-list-item
              v-if="auth.can('attendance.view')"
              title="Attendance"
              :to="{ name: 'attendance' }"
              class="submenu-item"
            >
              <template #prepend>
                <v-icon size="15">mdi-clock-outline</v-icon>
              </template>
            </v-list-item>

            <v-list-item
              v-if="auth.can('attendance.manage')"
              title="Attendance Settings"
              :to="{ name: 'attendance.settings' }"
              class="submenu-item"
            >
              <template #prepend>
                <v-icon size="15">mdi-cog</v-icon>              
              </template>
            </v-list-item>
          </v-list-group>

          <v-list-group value="leave_management">
            <template #activator="{ props }">
              <v-list-item
                v-bind="props"
                title="Leave Management"
                rounded="xl"
              >
                <template #prepend>
                  <v-icon size="20">mdi-calendar-clock</v-icon>
                </template>
              </v-list-item>
            </template>

            <v-list-item
              title="Leave Rules"
              :to="{ name: 'leave-rules' }"
              class="submenu-item"
            >
              <template #prepend>
                <v-icon size="15">mdi-book-cog</v-icon>
              </template>
            </v-list-item>

            <v-list-item
              title="Leave Requests"
              :to="{ name: 'leave-requests' }"
              class="submenu-item"
            >
              <template #prepend>
                <v-icon size="15">mdi-file-document-edit</v-icon>              
              </template>
            </v-list-item>
          </v-list-group>

          <v-list-item
            v-if="auth.can('payroll.view')"
            title="Payroll"
            :to="{ name: 'payroll' }"
            rounded="xl"
          >
            <template #prepend>
              <v-icon size="20">mdi-cash-multiple</v-icon>
            </template>
          </v-list-item>
          
            <v-list-item
            v-if="auth.can('payroll.history')"
            title="Payroll"
            :to="{ name: 'payroll/history' }"
            rounded="xl"
          >
            <template #prepend>
              <v-icon size="20">mdi-cash-multiple</v-icon>
            </template>
          </v-list-item>
          
          <v-list-item
            v-if="auth.can('clients.view')"
            title="Clients"
            :to="{ name: 'clients' }"
            rounded="xl"
          >
            <template #prepend>
              <v-icon size="20">mdi-account-group-outline</v-icon>
            </template>
          </v-list-item>

          <v-list-item
            v-if="auth.can('invoices.view')"
            title="Invoices"
            :to="{ name: 'invoices' }"
            rounded="xl"
          >
            <template #prepend>
              <v-icon size="20">mdi-file-chart-outline</v-icon>
            </template>
          </v-list-item>

          <v-list-item
            v-if="auth.can('estimates.view')"
            title="Estimates"
            :to="{ name: 'estimates' }"
            rounded="xl"
          >
            <template #prepend>
              <v-icon size="20">mdi-file-edit-outline</v-icon>
            </template>
          </v-list-item>

          <v-list-group
            value="system-management"
            v-if="showSystemManageMenu"
          >
            <template #activator="{ props }">
              <v-list-item
                v-bind="props"
                title="System Management"
                rounded="xl"
              >
                <template #prepend>
                  <v-icon size="20">mdi-cog-outline</v-icon>
                </template>
              </v-list-item>
            </template>

            <v-list-item
              v-if="showRolesMenu"
              title="Roles"
              :to="{ name: 'roles' }"
              class="submenu-item"
            >
              <template #prepend>
                <v-icon size="20">mdi-shield-account</v-icon>
              </template>
            </v-list-item>
          </v-list-group>
        </v-list>
    </v-navigation-drawer>

    <v-app-bar flat color="white" border="b" elevation="0" height="72" app v-if="!isPreviewPage">
        <v-app-bar-nav-icon v-if="!mdAndUp" class="ms-1" @click="drawer = !drawer" />

        <v-toolbar-title class="text-h6 font-weight-semibold">
            {{ pageTitle }}
        </v-toolbar-title>
        <v-spacer />
        <v-menu>
          <!-- <template #activator="{ props }">
            <v-btn v-bind="props" variant="text" class="me-2">
                <v-avatar color="primary" size="32" class=" me-2">
                    {{ initials }}
                </v-avatar>
                <span class="d-none d-sm-inline text-body-2">{{ auth.user?.name }}</span>
                <v-icon end>mdi-chevron-down</v-icon>
            </v-btn>
          </template> -->
          <template #activator="{ props }">
    <v-btn v-bind="props" variant="text" class="me-2">
        <v-avatar size="32" class="me-2" color="primary">
            <v-img
                v-if="auth.user?.profile_image_url"
                :src="auth.user.profile_image_url"
                cover
            />
            <span v-else>
                {{ initials }}
            </span>
        </v-avatar>

        <span class="d-none d-sm-inline text-body-2">
            {{ auth.user?.name }}
        </span>
        <v-icon end>mdi-chevron-down</v-icon>
    </v-btn>
</template>

          <v-list density="compact" min-width="200">
              <v-list-item 
                  v-if="auth.user?.id" 
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
  
    const isPreviewPage = computed(() => route.name === 'InvoicePreview' || route.name === 'EstimatePreview');

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

    const initials = computed(() => {
      const name = auth.user?.name ?? '?';
        return name
            .split(' ')
            .map((p) => p[0])
            .join('')
            .slice(0, 2)
            .toUpperCase();
    });

    function openRole(role) {
        router.push({ name: 'roles.detail', params: { id: role.id } });
    }

    function isSelectedRole(role) {
        return route.name === 'roles.detail' && Number(route.params.id) === role.id;
    }

    async function onLogout() {
        await auth.logout();
        await router.push({ name: 'login' });
    }
</script>

<style scoped>
.v-list-group__items .v-list-item {
    padding: 5px 10px !important;
}

.v-navigation-drawer .v-btn--icon {
    min-width: 40px;
}
.menu-icon {
  color: #641C55 !important;
}
</style>
