<template>
    <div>
        <v-row class="mb-4" align="center">
            <v-col cols="12" md="6">
                <div class="text-h4 font-weight-bold">Leave Rules</div>
                <div class="text-body-2 text-medium-emphasis">Configure annual leave types and limits</div>
            </v-col>
            <v-col cols="12" md="6" class="d-flex flex-wrap ga-2 justify-md-end align-center">
                <v-text-field
                    v-model="searchInput"
                    density="comfortable"
                    variant="outlined"
                    hide-details
                    label="Search Rules"
                    prepend-inner-icon="mdi-magnify"
                    clearable
                    class="custom-search-btn-width"
                    @keyup.enter="applySearch"
                />

                <v-btn color="#702E62" rounded="lg" size="large" class="text-none rounded-lg px-5 py-2 elevation-2 font-weight-bold" :disabled="!auth.can('leave-rules.create')" @click="openCreate"
                >
                    <v-icon start>mdi-plus</v-icon> Add Leave Rule
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

            <template #[`item.type`]="{ item }">
                <v-chip :color="item.type === 'paid' ? 'success' : 'warning'" size="small" variant="flat" class="text-capitalize">
                    {{ item.type }}
                </v-chip>
            </template>

            <template #[`item.days`]="{ item }">
                <span v-if="item.days !== null" class="font-weight-bold">{{ item.days }} Days</span>
                <span v-else class="text-caption text-grey">Unlimited</span>
            </template>

            <template #[`item.actions`]="{ item }">
                <v-btn icon variant="text" size="small" :disabled="!auth.can('leave-rules.update')" @click="openEdit(item)">
                    <v-icon color="primary">mdi-pencil</v-icon>
                </v-btn>
                <v-btn icon variant="text" size="small" color="error" :disabled="!auth.can('leave-rules.delete')" @click="confirmDelete(item)">
                    <v-icon>mdi-delete</v-icon>
                </v-btn>
            </template>
        </v-data-table-server>

        <v-dialog v-model="requestDialog" max-width="550" persistent>
            <v-card rounded="lg">
                <v-card-title class="pa-4 font-weight-bold border-b text-h6">
                    Apply Leave: <span class="text-primary">{{ selectedRuleName }}</span>
                </v-card-title>
                
                <v-card-text class="pa-4">
                    <v-alert v-if="requestError" type="error" variant="tonal" class="mb-4" rounded="lg">
                        {{ requestError }}
                    </v-alert>

                    <v-radio-group v-model="requestForm.leave_session" inline label="Leave Duration" class="mb-2">
                        <v-radio label="Full Day" value="full_day" color="primary" />
                        <v-radio label="Morning (Half Day)" value="morning" color="primary" />
                        <v-radio label="Afternoon (Half Day)" value="afternoon" color="primary" />
                    </v-radio-group>

                    <v-row>
                        <v-col cols="12" :md="requestForm.leave_session === 'full_day' ? 6 : 12">
                            <v-text-field v-model="requestForm.start_date" type="date" label="Start Date" variant="outlined" density="comfortable" />
                        </v-col>
                        <v-col cols="12" md="6" v-if="requestForm.leave_session === 'full_day'">
                            <v-text-field v-model="requestForm.end_date" type="date" label="End Date" variant="outlined" density="comfortable" />
                        </v-col>
                    </v-row>

                    <v-textarea v-model="requestForm.reason" label="Reason for Leave" variant="outlined" rows="3" class="mt-2" density="comfortable" />

                    <v-file-input
                        v-model="attachmentFile"
                        label="Attach Document / Medical Certificate (Optional)"
                        variant="outlined"
                        prepend-icon="mdi-paperclip"
                        accept=".pdf, image/*"
                        density="comfortable"
                        clearable
                        class="mt-2"
                    />
                </v-card-text>
                
                <v-card-actions class="px-6 pb-4">
                    <v-spacer />
                    <v-btn variant="text" @click="requestDialog = false">Cancel</v-btn>
                    <v-btn color="success" variant="flat" class="text-none px-4" :loading="requestSaving" @click="submitLeaveRequest">
                        Submit Request
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <v-dialog v-model="dialog" max-width="500" persistent>
            <v-card rounded="lg">
                <v-card-title class="pa-4 font-weight-bold border-b text-h6">
                    {{ editingId ? 'Edit Leave Rule' : 'Add Leave Rule' }}
                </v-card-title>
                <v-card-text class="pa-4">
                    <v-alert v-if="formError" type="error" variant="tonal" class="mb-4" rounded="lg">
                        {{ formError }}
                    </v-alert>

                    <v-text-field v-model="form.name" label="Rule Name" variant="outlined" density="comfortable" class="mb-2" />
                    
                    <v-radio-group v-model="form.type" inline label="Type" class="mb-2">
                        <v-radio label="Paid" value="paid" color="primary" />
                        <v-radio label="Unpaid" value="unpaid" color="primary" />
                    </v-radio-group>

                    <v-text-field
                        v-model="form.days"
                        label="Allowed Days"
                        type="number"
                        variant="outlined"
                        density="comfortable"
                        hint="Leave blank for unlimited days"
                        persistent-hint
                    />
                </v-card-text>
                <v-card-actions class="px-6 pb-4">
                    <v-spacer />
                    
                    <v-btn variant="text" rounded="lg" class="text-none px-5 font-weight-bold" :disabled="saving" @click="dialog = false">Cancel</v-btn>
                    <v-btn color="#702E62" variant="flat" rounded="lg" class="text-none px-6 elevation-2 font-weight-bold" :loading="saving" @click="save">
                        {{ editingId ? 'Update' : 'Save' }}
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <v-dialog v-model="deleteDialog" max-width="400" persistent>
            <v-card rounded="lg">
                <v-card-title class="pa-4 font-weight-bold text-h6">Delete Leave Rule</v-card-title>
                <v-card-text class="px-4 pb-4 text-body-1">
                    Are you sure you want to delete this leave rule? This action cannot be undone.
                </v-card-text>
                <v-card-actions class="px-6 pb-4">
                    <v-spacer />
                    <v-btn variant="text" @click="deleteDialog = false">Cancel</v-btn>
                    <v-btn color="error" variant="flat" class="text-none px-4" :loading="deleting" @click="doDelete">
                        Delete
                    </v-btn>
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
        { title: 'ID', key: 'id', sortable: false },
        { title: 'Rule Name', key: 'name', sortable: false },
        { title: 'Type', key: 'type', sortable: false },
        { title: 'Allowed Days', key: 'days', sortable: false },
        { title: 'Actions', key: 'actions', sortable: false, align: 'end' },
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

    const form = reactive({ name: '', type: 'unpaid', days: null });

    const requestDialog = ref(false);
    const requestSaving = ref(false);
    const requestError = ref('');
    const selectedRuleName = ref('');
    const attachmentFile = ref(null);

    const requestForm = reactive({
        leave_rule_id: null,
        leave_session: 'full_day',
        start_date: '',
        end_date: '',
        reason: ''
    });

    async function loadItems(options) {
        loading.value = true;
        try {
            const p = options?.page ?? page.value;
            const per = options?.itemsPerPage ?? itemsPerPage.value;
            const { data } = await axios.get('/api/leave-rules', {
                params: { page: p, per_page: per, search: search.value || undefined }
            });
            items.value = data.data ?? [];
            total.value = data.meta?.total ?? 0;
            page.value = data.meta?.current_page ?? p;
            itemsPerPage.value = data.meta?.per_page ?? per;
        } catch(e) {
            setNotification('Unable to fetch leave rules.', 'error');
        } finally { loading.value = false; }
    }

    function applySearch() {
        search.value = searchInput.value?.trim() ?? '';
        page.value = 1;
        loadItems({ page: 1, itemsPerPage: itemsPerPage.value });
    }

    function openRequestLeave(rule) {
        selectedRuleName.value = rule.name;
        requestForm.leave_rule_id = rule.id;
        requestForm.leave_session = 'full_day';
        requestForm.start_date = '';
        requestForm.end_date = '';
        requestForm.reason = '';
        attachmentFile.value = null;
        requestError.value = '';
        requestDialog.value = true;
    }

    async function submitLeaveRequest() {
        requestError.value = '';
        requestSaving.value = true;
        
        try {
            const formData = new FormData();
            formData.append('leave_rule_id', requestForm.leave_rule_id);
            formData.append('leave_session', requestForm.leave_session);
            formData.append('start_date', requestForm.start_date);
            formData.append('end_date', requestForm.leave_session === 'full_day' ? requestForm.end_date : requestForm.start_date);
            formData.append('reason', requestForm.reason ?? '');
            
            if (attachmentFile.value) {
                formData.append('attachment', attachmentFile.value);
            }

            await axios.post('/api/leave-requests', formData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            });

            setNotification('Leave request submitted successfully.', 'success');
            requestDialog.value = false;
        } catch (e) {
            requestError.value = e.response?.data?.message ?? 'Failed to submit leave request.';
        } finally {
            requestSaving.value = false;
        }
    }

    function openCreate() {
        editingId.value = null;
        form.name = '';
        form.type = 'unpaid';
        form.days = null;
        formError.value = '';
        dialog.value = true;
    }

    function openEdit(item) {
        editingId.value = item.id;
        form.name = item.name;
        form.type = item.type;
        form.days = item.days;
        formError.value = '';
        dialog.value = true;
    }

    function confirmDelete(item) {
        deleteId.value = item.id;
        deleteDialog.value = true;
    }

    async function save() {
        formError.value = '';
        saving.value = true;
        try {
            const payload = {
                name: form.name,
                type: form.type,
                days: form.days !== '' && form.days !== null ? parseInt(form.days) : null,
            };

            if (editingId.value) {
                await axios.put(`/api/leave-rules/${editingId.value}`, payload);
                setNotification('Leave rule updated successfully.', 'success');
            } else {
                await axios.post('/api/leave-rules', payload);
                setNotification('Leave rule created successfully.', 'success');
            }

            dialog.value = false;
            await loadItems({ page: page.value, itemsPerPage: itemsPerPage.value });
        } catch (e) {
            const msg = e?.response?.data?.message;
            const errs = e?.response?.data?.errors;
            formError.value = msg ?? (errs && Object.values(errs).flat().join(' ')) ?? 'Unable to save.';
        } finally { saving.value = false; }
    }

    async function doDelete() {
        deleting.value = true;
        try {
            await axios.delete(`/api/leave-rules/${deleteId.value}`);
            deleteDialog.value = false;
            setNotification('Leave rule deleted successfully.', 'success');
            await loadItems({ page: page.value, itemsPerPage: itemsPerPage.value });
        } catch (e) {
            const msg = e?.response?.data?.message ?? 'Unable to delete leave rule.';
            setNotification(msg, 'error');
        } finally { deleting.value = false; }
    }

    function setNotification(message, type = 'success') {
        notification.value = message;
        notificationType.value = type;
        setTimeout(() => { notification.value = ''; }, 4000);
    }

    onMounted(() => {
        loadItems();
    });
</script>