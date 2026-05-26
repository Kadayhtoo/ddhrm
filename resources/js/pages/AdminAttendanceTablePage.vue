<template>
    <div class="pa-2">
        <div class="d-flex flex-wrap align-center justify-space-between ga-3 mb-4">
            <div>
                <div class="text-h5 font-weight-bold">Admin Attendance Table</div>
                <div class="text-body-2 text-medium-emphasis">Monitor employee attendance and review correction requests.</div>
            </div>
            <v-btn color="primary" variant="tonal" prepend-icon="mdi-chart-box-outline" :to="{ name: 'attendance.reports' }">Reports</v-btn>
        </div>

        <v-alert v-if="notice" :type="noticeType" variant="tonal" class="mb-4" closable>{{ notice }}</v-alert>

        <v-card variant="outlined" class="rounded-lg bg-white mb-5">
            <v-card-text>
                <v-row dense>
                    <v-col cols="12" md="3">
                        <v-text-field v-model="filters.search" label="Search employee" prepend-inner-icon="mdi-magnify" variant="outlined" density="comfortable" hide-details clearable />
                    </v-col>
                    <v-col cols="12" md="3">
                        <v-text-field v-model="filters.date" type="date" label="Date" variant="outlined" density="comfortable" hide-details />
                    </v-col>
                    <v-col cols="12" md="3">
                        <v-select v-model="filters.status" :items="statusOptions" label="Status" variant="outlined" density="comfortable" hide-details clearable />
                    </v-col>
                    <v-col cols="12" md="3">
                        <v-btn color="primary" block height="44" prepend-icon="mdi-filter-outline" @click="reload">Apply</v-btn>
                    </v-col>
                </v-row>
            </v-card-text>
        </v-card>

        <v-tabs v-model="tab" color="primary" class="mb-4 border-b">
            <v-tab value="records">Records</v-tab>
            <v-tab value="requests">Correction Requests</v-tab>
        </v-tabs>

        <v-window v-model="tab">
            <v-window-item value="records">
                <v-card variant="outlined" class="rounded-lg bg-white">
                    <v-data-table-server
                        v-model:items-per-page="itemsPerPage"
                        v-model:page="page"
                        :headers="recordHeaders"
                        :items="records"
                        :items-length="total"
                        :loading="loading"
                        item-value="id"
                        density="comfortable"
                        @update:options="loadRecords"
                    >
                        <template #[`item.user`]="{ item }">
                            <div class="py-2">
                                <div class="font-weight-bold">{{ item.user?.name }}</div>
                                <div class="text-caption text-medium-emphasis">{{ item.user?.department?.name || 'No Department' }}</div>
                            </div>
                        </template>
                        <template #[`item.status`]="{ item }">
                            <v-chip :color="statusColor(item.status)" size="small" variant="flat" class="text-capitalize font-weight-bold">{{ item.status?.replace('_', ' ') }}</v-chip>
                        </template>
                        <template #[`item.actions`]="{ item }">
                            <v-btn size="small" variant="text" color="primary" prepend-icon="mdi-eye-outline" :to="{ name: 'attendance.details', params: { id: item.id } }">View</v-btn>
                        </template>
                    </v-data-table-server>
                </v-card>
            </v-window-item>

            <v-window-item value="requests">
                <v-card variant="outlined" class="rounded-lg bg-white">
                    <v-data-table-server
                        v-model:items-per-page="requestItemsPerPage"
                        v-model:page="requestPage"
                        :headers="requestHeaders"
                        :items="requests"
                        :items-length="requestTotal"
                        :loading="requestLoading"
                        item-value="id"
                        density="comfortable"
                        @update:options="loadRequests"
                    >
                        <template #[`item.user`]="{ item }">
                            <div class="font-weight-bold">{{ item.user?.name }}</div>
                        </template>
                        <template #[`item.status`]="{ item }">
                            <v-chip :color="item.status === 'approved' ? 'success' : item.status === 'rejected' ? 'error' : 'warning'" size="small" variant="flat" class="text-capitalize font-weight-bold">{{ item.status }}</v-chip>
                        </template>
                        <template #[`item.actions`]="{ item }">
                            <div class="d-flex ga-1 justify-end">
                                <v-btn v-if="item.status === 'pending'" size="small" color="success" variant="flat" :loading="reviewingId === item.id" @click="review(item.id, 'approved')">Approve</v-btn>
                                <v-btn v-if="item.status === 'pending'" size="small" color="error" variant="tonal" :loading="reviewingId === item.id" @click="review(item.id, 'rejected')">Reject</v-btn>
                            </div>
                        </template>
                    </v-data-table-server>
                </v-card>
            </v-window-item>
        </v-window>
    </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import axios from 'axios';

const tab = ref('records');
const records = ref([]);
const total = ref(0);
const page = ref(1);
const itemsPerPage = ref(10);
const loading = ref(false);
const requests = ref([]);
const requestTotal = ref(0);
const requestPage = ref(1);
const requestItemsPerPage = ref(10);
const requestLoading = ref(false);
const reviewingId = ref(null);
const notice = ref('');
const noticeType = ref('success');
const filters = ref({ search: '', date: '', status: '' });

const statusOptions = [
    { title: 'Present', value: 'present' },
    { title: 'Absent', value: 'absent' },
    { title: 'Late', value: 'late' },
    { title: 'Half Day', value: 'half_day' },
    { title: 'Holiday', value: 'holiday' },
    { title: 'Weekend', value: 'weekend' },
    { title: 'On Leave', value: 'on_leave' },
];

const recordHeaders = [
    { title: 'Employee', key: 'user', sortable: false },
    { title: 'Date', key: 'attendance_date' },
    { title: 'Clock In', key: 'check_in', align: 'center' },
    { title: 'Clock Out', key: 'check_out', align: 'center' },
    { title: 'Hours', key: 'work_hours', align: 'center' },
    { title: 'Status', key: 'status', align: 'center' },
    { title: 'Actions', key: 'actions', align: 'end', sortable: false },
];

const requestHeaders = [
    { title: 'Employee', key: 'user', sortable: false },
    { title: 'Date', key: 'requested_date' },
    { title: 'Type', key: 'type' },
    { title: 'Reason', key: 'reason', sortable: false },
    { title: 'Status', key: 'status', align: 'center' },
    { title: 'Actions', key: 'actions', align: 'end', sortable: false },
];

watch(tab, () => {
    if (tab.value === 'requests' && requests.value.length === 0) {
        loadRequests();
    }
});

async function loadRecords(options = {}) {
    loading.value = true;
    try {
        const p = options.page ?? page.value;
        const per = options.itemsPerPage ?? itemsPerPage.value;
        const { data } = await axios.get('/api/attendance', {
            params: {
                page: p,
                per_page: per,
                search: filters.value.search || undefined,
                date: filters.value.date || undefined,
                status: filters.value.status || undefined,
            },
        });
        records.value = data.data ?? [];
        total.value = data.meta?.total ?? 0;
        page.value = data.meta?.current_page ?? p;
        itemsPerPage.value = data.meta?.per_page ?? per;
    } finally {
        loading.value = false;
    }
}

async function loadRequests(options = {}) {
    requestLoading.value = true;
    try {
        const p = options.page ?? requestPage.value;
        const per = options.itemsPerPage ?? requestItemsPerPage.value;
        const { data } = await axios.get('/api/attendance/requests', {
            params: {
                page: p,
                per_page: per,
                search: filters.value.search || undefined,
                date: filters.value.date || undefined,
            },
        });
        requests.value = data.data ?? [];
        requestTotal.value = data.meta?.total ?? 0;
        requestPage.value = data.meta?.current_page ?? p;
        requestItemsPerPage.value = data.meta?.per_page ?? per;
    } finally {
        requestLoading.value = false;
    }
}

async function review(id, status) {
    reviewingId.value = id;
    try {
        await axios.patch(`/api/attendance/requests/${id}/review`, { status });
        setNotice(`Request ${status}.`, 'success');
        loadRequests({ page: requestPage.value, itemsPerPage: requestItemsPerPage.value });
        loadRecords({ page: page.value, itemsPerPage: itemsPerPage.value });
    } catch {
        setNotice('Unable to review request.', 'error');
    } finally {
        reviewingId.value = null;
    }
}

function reload() {
    page.value = 1;
    requestPage.value = 1;
    loadRecords({ page: 1, itemsPerPage: itemsPerPage.value });
    if (tab.value === 'requests') {
        loadRequests({ page: 1, itemsPerPage: requestItemsPerPage.value });
    }
}

function statusColor(status) {
    return status === 'present' ? 'success' : status === 'late' ? 'warning' : status === 'absent' ? 'error' : status === 'half_day' ? 'orange' : 'info';
}

function setNotice(text, type) {
    notice.value = text;
    noticeType.value = type;
}
</script>
