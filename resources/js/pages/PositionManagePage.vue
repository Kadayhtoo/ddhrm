<template>
    <div>
        <v-row class="mb-4" align="center">
            <v-col cols="12" md="6">
                <div class="text-h4 font-weight-bold">Position Management</div>
                <div class="text-body-2 text-medium-emphasis">Manage company job positions and link them to departments.</div>
            </v-col>
            <v-col cols="12" md="6" class="d-flex flex-wrap ga-2 justify-md-end align-center">
                <v-text-field
                    v-model="searchInput"
                    density="comfortable"
                    variant="outlined"
                    hide-details
                    label="Search Position"
                    prepend-inner-icon="mdi-magnify"
                    clearable
                    class="custom-search-btn-width"
                    @keyup.enter="applySearch"
                />
                
                <v-btn color="#702E62" rounded="lg" size="large" class="text-none rounded-lg px-5 py-2 elevation-2 font-weight-bold" :disabled="!auth.can('positions.create')"@click="openCreate"
                >
                    <v-icon start>mdi-plus</v-icon> Add Position
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
            class="rounded-lg border"
            @update:options="loadItems"
        >
            <template #item.department="{ item }">
                <v-chip color="secondary" size="small" variant="tonal" class="text-capitalize">
                    {{ item.department?.name ?? 'No Department' }}
                </v-chip>
            </template>

            <template #item.actions="{ item }">
                <v-btn icon variant="text" size="small" color="#702E62" :disabled="!auth.can('positions.update')" @click="openEdit(item)" >
                    <v-icon>mdi-pencil</v-icon>
                </v-btn>
                <v-btn
                    icon
                    variant="text"
                    size="small"
                    color="error"
                    :disabled="!auth.can('positions.delete')"
                    @click="confirmDelete(item)"
                >
                    <v-icon>mdi-delete</v-icon>
                </v-btn>
            </template>
        </v-data-table-server>

        <v-dialog v-model="dialog" max-width="520" persistent>
            <v-card rounded="lg">
                <v-card-title class="px-6 pt-4 font-weight-bold">
                    {{ editingId ? 'Edit Position' : 'Add New Position' }}
                </v-card-title>
                <v-card-text class="px-6">
                    <v-alert v-if="formError" type="error" variant="tonal" class="mb-4" rounded="lg">
                        {{ formError }}
                    </v-alert>
                    
                    <v-text-field 
                        v-model="form.name" 
                        label="Position Name" 
                        variant="outlined" 
                        class="mb-3" 
                        hide-details="auto"
                    />

                    <v-select
                        v-model="form.department_id"
                        :items="departmentOptions"
                        item-title="name"
                        item-value="id"
                        label="Select Department"
                        variant="outlined"
                        class="mb-3"
                        hide-details="auto"
                        :loading="loadingDepartments"
                        no-data-text="No departments found"
                    />
                </v-card-text>
                <v-card-actions class="px-6 pb-4">
                    <v-spacer />
                    <v-btn variant="text" class="text-none font-weight-bold" @click="dialog = false">Cancel</v-btn>
                    <v-btn color="#702E62" variant="flat" rounded="lg" class="text-none px-6 elevation-2 font-weight-bold" :loading="saving" @click="save">Save</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <v-dialog v-model="deleteDialog" max-width="420">
            <v-card rounded="lg">
                <v-card-title class="px-6 pt-4 font-weight-bold">Delete position?</v-card-title>
                <v-card-text class="px-6">This action cannot be undone and will detach from any linked staff.</v-card-text>
                <v-card-actions class="px-6 pb-4">
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

    const departmentOptions = ref([]);
    const loadingDepartments = ref(false);

    const dialog = ref(false);
    const deleteDialog = ref(false);
    const editingId = ref(null);
    const deleteId = ref(null);
    const formError = ref('');
    const notification = ref('');
    const notificationType = ref('success');
    const saving = ref(false);
    const deleting = ref(false);

    const headers = [
        { title: 'Position Name', key: 'name', sortable: false },
        { title: 'Department', key: 'department', sortable: false },
        { title: '', key: 'actions', sortable: false, align: 'end' },
    ];

    const form = reactive({
        name: '',
        department_id: null,
    });

    async function loadDepartmentOptions() {
        loadingDepartments.value = true;
        try {
            const { data } = await axios.get('/api/department', { params: { per_page: 100 } });
            departmentOptions.value = data.data ?? [];
        } catch (e) {
            console.error('Failed to load departments', e);
        } finally {
            loadingDepartments.value = false;
        }
    }
    
    async function loadItems(options) {
        loading.value = true;
        try {
            const p = options?.page ?? page.value;
            const per = options?.itemsPerPage ?? itemsPerPage.value;
            
            const { data } = await axios.get('/api/position', {
                params: {
                    page: p,
                    per_page: per,
                    search: search.value || undefined,
                },
            });
            items.value = data.data ?? [];
            total.value = data.meta?.total ?? data.data?.length ?? 0;
            page.value = data.meta?.current_page ?? p;
            itemsPerPage.value = data.meta?.per_page ?? per;
        } catch (e) {
            console.error('Failed to load positions', e);
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
        form.department_id = null;
        formError.value = '';
    }

    async function openCreate() {
        await loadDepartmentOptions();
        editingId.value = null;
        resetForm();
        dialog.value = true;
    }

    async function openEdit(row) {
        await loadDepartmentOptions();
        editingId.value = row.id;
        form.name = row.name;
        form.department_id = row.department_id;
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
            const payload = {
                name: form.name,
                department_id: form.department_id,
            };

            if (editingId.value) {
                await axios.put(`/api/position/${editingId.value}`, payload);
                setNotification('Position updated successfully.', 'success');
            } else {
                await axios.post('/api/position', payload);
                setNotification('Position created successfully.', 'success');
            }

            dialog.value = false;
            await loadItems({ page: page.value, itemsPerPage: itemsPerPage.value });
        } catch (e) {
            const msg = e?.response?.data?.message;
            const errs = e?.response?.data?.errors;
            formError.value = msg
                ?? (errs && Object.values(errs).flat().join(' '))
                ?? 'Unable to save position.';
        } finally {
            saving.value = false;
        }
    }

    async function doDelete() {
        deleting.value = true;
        try {
            await axios.delete(`/api/position/${deleteId.value}`);
            deleteDialog.value = false;
            setNotification('Position deleted successfully.', 'success');
            await loadItems({ page: page.value, itemsPerPage: itemsPerPage.value });
        } catch (e) {
            const msg = e?.response?.data?.message ?? 'Unable to delete position.';
            setNotification(msg, 'error');
        } finally {
            deleting.value = false;
        }
    }

    onMounted(() => {
        loadItems();
    });
</script>