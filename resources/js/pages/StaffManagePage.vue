<template>
    <div>
        <v-row class="mb-4" align="center">
            <v-col cols="12" md="6">
                <div class="text-h4 font-weight-bold">Staff</div>
                <div class="text-body-2 text-medium-emphasis">Users, departments and role assignment</div>
            </v-col>
            <v-col cols="12" md="6" class="d-flex flex-wrap ga-2 justify-md-end align-center">
            <v-text-field
                v-model="searchInput"
                density="comfortable"
                variant="outlined"
                hide-details
                label="Search"
                prepend-inner-icon="mdi-magnify"
                clearable
                class="custom-search-btn-width"
                @keyup.enter="applySearch"
            />

            <v-btn 
                color="#702E62" rounded="lg" size="large" class="text-none rounded-lg px-5 py-2 elevation-2 font-weight-bold" :disabled="!auth.can('staff.create')" @click="openCreate"
            >
                <v-icon start>mdi-plus</v-icon> Add Staff
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
            <template #[`item.id`]="{ item }">
                <span class="font-weight-medium text-body-2">#{{ item.id }}</span>
            </template>

            <template #[`item.position`]="{ item }">
                <span v-if="item.position" class="text-body-2 font-weight-medium">{{ item.position.name }}</span>
                <span v-else class="text-caption text-grey">No Position</span>
            </template>

            <template #[`item.department`]="{ item }">
                <v-chip
                    v-if="item.department"
                    size="small"
                    color="info"
                    variant="tonal"
                >
                    {{ item.department.name }}
                </v-chip>
                <span v-else class="text-caption text-grey">No Department</span>
            </template>

            <template #[`item.role`]="{ item }">
                <v-chip
                    v-for="role in item.roles"
                    :key="role.id"
                    size="small"
                    color="secondary"
                    variant="flat"
                    class="mr-1"
                >
                    {{ role.name }}
                </v-chip>
            </template>

            <template #[`item.status`]="{ item }">
                <v-chip :color="item.is_active ? 'success' : 'error'" size="small" variant="tonal">
                    {{ item.is_active ? 'Active' : 'Inactive' }}
                </v-chip>
            </template>

            <template #[`item.actions`]="{ item }">
                <v-btn 
                color="indigo-darken-2" 
                variant="tonal" 
                size="small" 
                rounded="lg"
               :to="{ name: 'staff.detail', params: { user: item.id } }"
                >
                View Details
                </v-btn>
                
                <v-icon
                    size="small"
                    class="me-2"
                    color="#702E62"
                    :disabled="!auth.can('staff.update')"
                    @click="openEdit(item)"
                >
                    mdi-pencil
                </v-icon>
                <v-icon
                    size="small"
                    color="error"
                    :disabled="!auth.can('staff.delete')"
                    @click="openDelete(item)"
                >
                    mdi-delete
                </v-icon>
            </template>
        </v-data-table-server>

        <v-dialog v-model="dialog" max-width="500px" persistent>
            <v-card rounded="lg" :loading="saving">
                <v-card-title class="pa-4 font-weight-bold border-b text-h6">
                    {{ isEdit ? 'Edit Staff Account' : 'Add New Staff' }}
                </v-card-title>

                <v-card-text class="pa-4">
                    <v-alert v-if="formError" type="error" variant="tonal" class="mb-4" rounded="md" density="comfortable">
                        {{ formError }}
                    </v-alert>
                    <v-text-field
                        v-model="form.name"
                        label="Full Name"
                        variant="outlined"
                        density="comfortable"
                        class="mb-1"
                    />
                    <v-text-field
                        v-model="form.email"
                        label="Email Address"
                        variant="outlined"
                        density="comfortable"
                        type="email"
                        class="mb-1"
                    />
                    <v-select
                        v-model="form.department_id"
                        :items="departments"
                        item-title="name"
                        item-value="id"
                        label="Choose Department"
                        variant="outlined"
                        density="comfortable"
                        clearable
                        class="mb-1"
                        @update:model-value="onDepartmentChange"
                    />
                    <v-select
                        v-model="form.position_id"
                        :items="positionOptions"
                        item-title="name"
                        item-value="id"
                        label="Select Position"
                        variant="outlined"
                        density="comfortable"
                        clearable
                        class="mb-1"
                        :disabled="!form.department_id"
                        :loading="loadingPositions"
                        no-data-text="Please select department first / No positions found"
                    />
                    <v-text-field
                        v-model.number="form.salary"
                        label="Monthly Salary"
                        variant="outlined"
                        density="comfortable"
                        type="number"
                        suffix="MMK"
                        prepend-inner-icon="mdi-cash"
                        class="mb-1"
                    />
                    <v-select
                        v-model="form.role_id"
                        :items="assignableRoles"
                        item-title="name"
                        item-value="id"
                        label="System Role"
                        variant="outlined"
                        density="comfortable"
                        class="mb-1"
                    />
                    <v-text-field
                        v-if="!isEdit"
                        v-model="form.password"
                        label="Password"
                        variant="outlined"
                        density="comfortable"
                        type="password"
                        class="mb-1"
                    />
                    <v-text-field
                        v-else
                        v-model="form.password"
                        label="New Password (leave blank to keep current)"
                        variant="outlined"
                        density="comfortable"
                        type="password"
                        class="mb-1"
                    />
                    <v-checkbox
                        v-model="form.is_active"
                        label="Account Active"
                        color="success"
                        hide-details
                        density="comfortable"
                    />
                </v-card-text>
                <v-divider />
                <v-card-actions class="pa-4 border-t">
                    <v-spacer />
                    
                    <v-btn variant="text" rounded="lg" class="text-none px-5 font-weight-bold" :disabled="saving" @click="dialog = false">Cancel</v-btn>
                    <v-btn color="#702E62" variant="flat" rounded="lg" class="text-none px-6 elevation-2 font-weight-bold" :loading="saving" @click="save">
                        {{ isEdit ? 'Update' : 'Save' }}
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
        <v-dialog v-model="deleteDialog" max-width="400px">
            <v-card rounded="lg" :loading="deleting">
                <v-card-title class="pa-4 font-weight-bold border-b text-h6">Delete Account</v-card-title>
                <v-card-text class="pa-4 text-body-1">
                    Are you sure you want to permanently delete this staff member's account? This action cannot be undone.
                </v-card-text>
                <v-card-actions class="pa-4 border-t">
                    <v-spacer />
                    <v-btn variant="text" rounded="lg" class="text-none px-4" :disabled="deleting" @click="deleteDialog = false">
                        Cancel
                    </v-btn>
                    <v-btn color="error" variant="flat" rounded="lg" class="text-none px-6" :loading="deleting" @click="doDelete">
                        Confirm Delete
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </div>
</template>

<script setup>
    import { ref, reactive, onMounted } from 'vue';
    import axios from 'axios';
    import { useAuthStore } from '@/stores/auth';

    const auth = useAuthStore();

    const loading = ref(false);
    const saving = ref(false);
    const deleting = ref(false);
    const loadingPositions = ref(false); 

    const dialog = ref(false);
    const deleteDialog = ref(false);
    const isEdit = ref(false);
    const editId = ref(null);
    const deleteId = ref(null);

    const itemsPerPage = ref(15);
    const page = ref(1);
    const total = ref(0);
    const items = ref([]);
    const searchInput = ref('');
    const activeSearch = ref('');

    const notification = ref('');
    const notificationType = ref('success');
    const formError = ref('');

    const assignableRoles = ref([]);
    const departments = ref([]); 
    const positionOptions = ref([]); 

    const headers = [
        { title: 'ID', key: 'id', sortable: false },
        { title: 'Name', key: 'name', sortable: false },
        { title: 'Email', key: 'email', sortable: false },
        { title: 'Position', key: 'position', sortable: false },
        { title: 'Department', key: 'department', sortable: false },
        { title: 'Status', key: 'status', sortable: false },
        { title: 'Actions', key: 'actions', sortable: false },
    ];

    const form = reactive({
        name: '',
        email: '',
        password: '',
        department_id: null, 
        position_id: null, 
        salary: null,
        role_id: null,
        is_active: true,
    });

    function setNotification(message, type = 'success') {
        notification.value = message;
        notificationType.value = type;
        setTimeout(() => {
            notification.value = '';
        }, 4000);
    }

    async function loadAssignableRoles() {
        try {
            const response = await axios.get('/api/roles/assignable');
            assignableRoles.value = Array.isArray(response.data) 
                ? response.data 
                : (response.data.data ?? response.data ?? []);
        } catch (e) {
            console.error('Failed to load assignable roles:', e);
        }
    }

    async function loadDepartments() {
        try {
            const response = await axios.get('/api/department');
            departments.value = response.data.data ?? response.data ?? [];
        } catch (e) {
            console.error('Failed to load departments:', e);
        }
    }

    async function onDepartmentChange(departmentId) {
        form.position_id = null;
        positionOptions.value = [];
        if (!departmentId) return;

        loadingPositions.value = true;
        try {
            const response = await axios.get(`/api/department/${departmentId}/positions`);
            positionOptions.value = response.data.data ?? response.data ?? [];
        } catch (e) {
            console.error('Failed to load positions for department:', e);
        } finally {
            loadingPositions.value = false;
        }
    }

    async function loadItems({ page: targetPage, itemsPerPage: targetLimit }) {
        loading.value = true;
        try {
            page.value = targetPage;
            itemsPerPage.value = targetLimit;

            const response = await axios.get('/api/staff', {
                params: {
                    page: targetPage,
                    per_page: targetLimit,
                    search: activeSearch.value || undefined,
                },
            });

            items.value = response.data.data ?? [];
            total.value = response.data.meta?.total ?? response.data.total ?? 0;
        } catch (e) {
            console.error('Failed to load staff list:', e);
            setNotification('Unable to fetch staff data.', 'error');
        } finally {
            loading.value = false;
        }
    }

    function applySearch() {
        activeSearch.value = searchInput.value ?? '';
        loadItems({ page: 1, itemsPerPage: itemsPerPage.value });
    }

    function openCreate() {
        isEdit.value = false;
        formError.value = '';
        form.name = '';
        form.email = '';
        form.password = '';
        form.department_id = null;
        form.position_id = null;   
        form.salary = null;     
        form.role_id = assignableRoles.value[0]?.id ?? null;
        form.is_active = true;
        positionOptions.value = [];
        dialog.value = true;
    }

    async function openEdit(item) {
        isEdit.value = true;
        editId.value = item.id;
        formError.value = '';
        form.name = item.name;
        form.email = item.email;
        form.password = '';
        form.department_id = item.department_id ?? null; 
        
        if (item.department_id) {
            await onDepartmentChange(item.department_id);
        }
        
        form.position_id = item.position_id ?? null; 
        form.salary = item.salary ?? null;           
        form.role_id = item.roles[0]?.id ?? null;
        form.is_active = !!item.is_active;
        dialog.value = true;
    }

    function openDelete(item) {
        deleteId.value = item.id;
        deleteDialog.value = true;
    }

    async function save() {
        formError.value = '';
        saving.value = true;

        const payload = {
            name: form.name,
            email: form.email,
            password: form.password,
            department_id: form.department_id,
            position_id: form.position_id, 
            salary: form.salary,
            role_id: form.role_id,
            is_active: form.is_active,
        };

        try {
            if (isEdit.value) {
                await axios.put(`/api/staff/${editId.value}`, payload);
                setNotification('Staff updated successfully.', 'success');
            } else {
                if (!form.password || form.password.length < 8) {
                    formError.value = 'Password is required (min 8 characters) for new users.';
                    saving.value = false;
                    return;
                }
                await axios.post('/api/staff', payload);
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
                ?? 'Unable to save data.';
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
        loadDepartments(); 
    });
</script>