<template>
    <div>
        <v-row class="mb-4" align="center">
            <v-col cols="12" md="8">
                <div class="text-h4 font-weight-bold">Role details</div>
                <div class="text-body-2 text-medium-emphasis">Permissions for this role.</div>
            </v-col>

        </v-row>

        <v-card class="mb-4 rounded-lg" elevation="1">
            <v-card-text>
                <div class="text-h6 mb-2">{{ role.name }}</div>
                <!-- <div class="text-body-2 text-medium-emphasis">Slug: {{ role.slug }}</div> -->
            </v-card-text>
        </v-card>

        <v-card rounded="lg" elevation="1">
            <v-card-title>Permissions</v-card-title>
            <v-card-text>
                <div v-if="loading">
                    Loading permissions...
                </div>
                <div v-else-if="!role.id" class="text-body-2 text-medium-emphasis">
                    Select a role from the sidebar to view permissions.
                </div>
                <div v-else>
                    <div v-if="!permissions.length" class="text-body-2 text-medium-emphasis">
                        No permissions available.
                    </div>
                    <v-row v-else class="ga-2">
                        <v-col cols="12" sm="6" md="4" v-for="permission in permissions" :key="permission.id">
                            <v-checkbox
                                :label="permission.name"
                                :value="permission.id"
                                v-model="selectedPermissionIds"
                                :disabled="!canManageRoles"
                                color="#6F72FF"
                                hide-details
                                density="comfortable"
                            />
                        </v-col>
                    </v-row>
                </div>
            </v-card-text>
        </v-card>

        <v-row class="mb-4 mt-3" align="center" >
             <v-col cols="12" md="4" class="d-flex">
                <div class="d-flex gap-2">
                    <!-- <v-btn variant="outlined" @click="backToList">Back to roles</v-btn> -->
                    <v-btn
                        v-if="canManageRoles"
                        color="primary"
                        :loading="saving"
                        :disabled="saving"
                        @click="savePermissions"
                    >
                        Save permissions
                    </v-btn>
                </div>
            </v-col>
        </v-row>
    </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import { useAuthStore } from '@/stores/auth';

const auth = useAuthStore();
const route = useRoute();
const router = useRouter();
const loading = ref(false);
const saving = ref(false);
const role = reactive({ id: null, name: '', slug: '', permissions: [] });
const permissions = ref([]);
const selectedPermissionIds = ref([]);

const canManageRoles = computed(() => auth.can('roles.manage') || auth.hasRoleSlug('admin'));

async function loadRole() {
    if (!route.params.id) {
        return;
    }

    loading.value = true;
    try {
        const { data } = await axios.get(`/api/roles/${route.params.id}`);
        Object.assign(role, data.data ?? {});
        permissions.value = data.permissions ?? [];
        selectedPermissionIds.value = (data.data?.permissions ?? []).map((permission) => permission.id);
    } finally {
        loading.value = false;
    }
}

watch(
    () => route.params.id,
    () => {
        loadRole();
    }
);

async function savePermissions() {
    if (!canManageRoles.value || !role.id) {
        return;
    }

    saving.value = true;

    try {
        const { data } = await axios.put(`/api/roles/${role.id}`, {
            permission_ids: selectedPermissionIds.value,
        });

        Object.assign(role, data.data ?? {});
        selectedPermissionIds.value = (data.data?.permissions ?? []).map((permission) => permission.id);
    } finally {
        saving.value = false;
    }
}

function backToList() {
    router.push({ name: 'roles' });
}

onMounted(loadRole);
</script>
