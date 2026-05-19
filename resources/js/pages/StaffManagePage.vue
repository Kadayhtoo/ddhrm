<template>
    <div>
        <v-row class="mb-4" align="center">
            <v-col cols="12" md="6">
                <div class="text-h4 font-weight-bold">Staff</div>
                <div class="text-body-2 text-medium-emphasis">Users and role assignment</div>
            </v-col>
            <v-col cols="12" md="6" class="d-flex flex-wrap ga-2 justify-md-end">
                <v-text-field
                    v-model="searchInput"
                    density="comfortable"
                    variant="outlined"
                    hide-details
                    label="Search"
                    prepend-inner-icon="mdi-magnify"
                    clearable
                    class="staff-search"
                    @keyup.enter="applySearch"
                />
                <v-btn color="primary" rounded="lg" :disabled="!auth.can('staff.create')" @click="openCreate">
                    Add staff
                </v-btn>
            </v-col>
        </v-row>

        <v-alert v-if="notification" :type="notificationType" variant="tonal" class="mb-4" rounded="lg">
            {{ notification }}
        </v-alert>

        <v-data-table-server
            v-model:items-per-page="itemsPerPage"
            v-model:page="page"
            :headers="headers"
            :items="items"
            :items-length="total"
            :loading="loading"
            item-value="id"
            class="rounded-lg"
            @update:options="loadItems"
        >
            <template #item.roles="{ item }">
                <v-chip
                    v-for="r in item.roles || []"
                    :key="r.id"
                    size="small"
                    class="me-1"
                    color="primary"
                    variant="tonal"
                >
                    {{ r.name }}
                </v-chip>
            </template>
            <template #item.is_active="{ item }">
                <v-chip :color="item.is_active ? 'success' : 'error'" size="small" variant="tonal">
                    {{ item.is_active ? 'Active' : 'Inactive' }}
                </v-chip>
            </template>
            <template #item.actions="{ item }">
                <v-btn icon variant="text" size="small" :disabled="!auth.can('staff.update')" @click="openEdit(item)">
                    <v-icon>mdi-pencil</v-icon>
                </v-btn>
                <v-btn
                    icon
                    variant="text"
                    size="small"
                    color="error"
                    :disabled="!auth.can('staff.delete') || item.id === auth.user?.id"
                    @click="confirmDelete(item)"
                >
                    <v-icon>mdi-delete</v-icon>
                </v-btn>
            </template>
        </v-data-table-server>

        <v-dialog v-model="dialog" max-width="520" persistent>
            <v-card rounded="lg">
                <v-card-title>{{ editingId ? 'Edit staff' : 'Add staff' }}</v-card-title>
                <v-card-text>
                    <v-alert v-if="formError" type="error" variant="tonal" class="mb-4" rounded="lg">{{ formError }}</v-alert>
                    <v-text-field v-model="form.name" label="Name" variant="outlined" class="mb-3" />
                    <v-text-field v-model="form.email" label="Email" type="email" variant="outlined" class="mb-3" />
                    <v-text-field
                        v-model="form.password"
                        :label="editingId ? 'New password (optional)' : 'Password'"
                        type="password"
                        variant="outlined"
                        class="mb-3"
                    />
                    <v-select
                        v-model="form.role_id"
                        :items="roleOptions"
                        item-title="name"
                        item-value="id"
                        label="Role"
                        variant="outlined"
                        class="mb-3"
                    />
                    <v-switch v-model="form.is_active" color="primary" hide-details label="Active" />
                </v-card-text>
                <v-card-actions class="px-6 pb-4">
                    <v-spacer />
                    <v-btn variant="text" @click="dialog = false">Cancel</v-btn>
                    <v-btn color="primary" :loading="saving" @click="save">Save</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <v-dialog v-model="deleteDialog" max-width="420">
            <v-card rounded="lg">
                <v-card-title>Delete user?</v-card-title>
                <v-card-text>This cannot be undone.</v-card-text>
                <v-card-actions>
                    <v-spacer />
                    <v-btn variant="text" @click="deleteDialog = false">Cancel</v-btn>
                    <v-btn color="error" :loading="deleting" @click="doDelete">Delete</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </div>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue';
import axios from 'axios';
import { useAuthStore } from '@/stores/auth';

const auth = useAuthStore();

const items = ref([]);
const total = ref(0);
const page = ref(1);
const itemsPerPage = ref(10);
const loading = ref(false);
const searchInput = ref('');
const search = ref('');

const headers = [
    { title: 'Name', key: 'name', sortable: false },
    { title: 'Email', key: 'email', sortable: false },
    { title: 'Roles', key: 'roles', sortable: false },
    { title: 'Status', key: 'is_active', sortable: false },
    { title: '', key: 'actions', sortable: false, align: 'end' },
];

const dialog = ref(false);
const deleteDialog = ref(false);
const editingId = ref(null);
const deleteId = ref(null);
const formError = ref('');
const notification = ref('');
const notificationType = ref('success');
const saving = ref(false);
const deleting = ref(false);
const roleOptions = ref([]);

const form = reactive({
    name: '',
    email: '',
    password: '',
    role_id: null,
    is_active: true,
});

async function loadAssignableRoles() {
    const { data } = await axios.get('/api/roles/assignable');

    roleOptions.value = data.data ?? [];
}

async function loadItems(options) {
    loading.value = true;
    try {
        const p = options?.page ?? page.value;
        const per = options?.itemsPerPage ?? itemsPerPage.value;
        const { data } = await axios.get('/api/staff', {
            params: {
                page: p,
                per_page: per,
                search: search.value || undefined,
            },
        });
        items.value = data.data ?? [];
        total.value = data.meta?.total ?? 0;
        page.value = data.meta?.current_page ?? p;
        itemsPerPage.value = data.meta?.per_page ?? per;
    } finally {
        loading.value = false;
    }
}

function applySearch() {
    search.value = searchInput.value?.trim() ?? '';
    page.value = 1;
    loadItems({ page: 1, itemsPerPage: itemsPerPage.value });
}

function resetForm() {
    form.name = '';
    form.email = '';
    form.password = '';
    form.role_id = roleOptions.value[0]?.id ?? null;
    form.is_active = true;
    formError.value = '';
}

async function openCreate() {
    await loadAssignableRoles();
    editingId.value = null;
    resetForm();
    dialog.value = true;
}

async function openEdit(row) {
    await loadAssignableRoles();
    editingId.value = row.id;
    form.name = row.name;
    form.email = row.email;
    form.password = '';
    form.is_active = Boolean(row.is_active);
    form.role_id = row.roles?.[0]?.id ?? null;
    formError.value = '';
    dialog.value = true;
}

function confirmDelete(row) {
    deleteId.value = row.id;
    deleteDialog.value = true;
}

function setNotification(message, type = 'success') {
    notification.value = message;
    notificationType.value = type;
    setTimeout(() => {
        notification.value = '';
    }, 4000);
}

async function save() {
    formError.value = '';
    saving.value = true;
    try {
        if (editingId.value) {
            const payload = {
                name: form.name,
                email: form.email,
                is_active: form.is_active,
                role_id: form.role_id,
            };
            if (form.password) {
                payload.password = form.password;
            }
            await axios.put(`/api/staff/${editingId.value}`, payload);
            setNotification('Staff updated successfully.', 'success');
        } else {
            if (!form.password || form.password.length < 8) {
                formError.value = 'Password is required (min 8 characters) for new users.';

                return;
            }
            await axios.post('/api/staff', {
                name: form.name,
                email: form.email,
                password: form.password,
                role_id: form.role_id,
                is_active: form.is_active,
            });
            setNotification('Staff created successfully.', 'success');
        }
        dialog.value = false;
        await auth.fetchMe();
        await loadItems({ page: page.value, itemsPerPage: itemsPerPage.value });
    } catch (e) {
        const msg = e?.response?.data?.message;
        const errs = e?.response?.data?.errors;
        formError.value = msg
            ?? (errs && Object.values(errs).flat().join(' '))
            ?? 'Unable to save.';
    } finally {
        saving.value = false;
    }
}

async function doDelete() {
    deleting.value = true;
    try {
        await axios.delete(`/api/staff/${deleteId.value}`);
        deleteDialog.value = false;
        setNotification('Staff deleted successfully.', 'success');
        await loadItems({ page: page.value, itemsPerPage: itemsPerPage.value });
    } catch (e) {
        const msg = e?.response?.data?.message ?? 'Unable to delete user.';
        setNotification(msg, 'error');
    } finally {
        deleting.value = false;
    }
}

onMounted(() => {
    loadAssignableRoles();
});
</script>

<style scoped>
.staff-search {
    max-width: 280px;
    min-width: 200px;
}
</style>
