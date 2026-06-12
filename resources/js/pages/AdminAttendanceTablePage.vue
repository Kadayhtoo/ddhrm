<template>
    <div class="pa-2">
        <div class="d-flex flex-wrap align-center justify-space-between ga-3 mb-4">
            <div>
                <div class="text-h5 font-weight-bold">Attendance Table</div>
                <div class="text-body-2 text-medium-emphasis">Monitor employee attendance</div>
            </div>
            <v-btn v-if="auth.can('admin.access')" color="primary" variant="tonal" prepend-icon="mdi-chart-box-outline" :to="{ name: 'attendance.reports' }">Reports</v-btn>
        </div>

        <v-alert v-if="notice" :type="noticeType" variant="tonal" class="mb-4" closable>{{ notice }}</v-alert>

        <v-card variant="elevated" class="rounded-lg bg-white mb-5">
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

        <v-card variant="elevated" class="rounded-lg bg-white">
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
                <template #[`item.check_in`]="{ item }">
                    <span>{{ formatDateTime(item.clock_in_at) }}</span>
                </template>
                <template #[`item.check_out`]="{ item }">
                    <span>{{ formatDateTime(item.clock_out_at) }}</span>
                </template>
                <template #[`item.status`]="{ item }">
                    <v-chip :color="statusColor(item.status)" size="small" variant="flat" class="text-capitalize font-weight-bold">{{ item.status?.replace('_', ' ') }}</v-chip>
                </template>
                <template #[`item.actions`]="{ item }">
                    <v-btn size="small" variant="text" color="primary" prepend-icon="mdi-eye-outline" :to="{ name: 'attendance.details', params: { id: item.id } }">View</v-btn>
                </template>
            </v-data-table-server>
        </v-card>
    </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import axios from 'axios';

import { useAuthStore } from '@/stores/auth';

const auth = useAuthStore();
const records = ref([]);
const total = ref(0);
const page = ref(1);
const itemsPerPage = ref(10);
const loading = ref(false);
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

onMounted(async () => {
    await loadRecords();
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

function reload() {
    page.value = 1;
    loadRecords({ page: 1, itemsPerPage: itemsPerPage.value });
}

function formatDateTime(value) {
    if (!value) return '-';
    return new Date(value).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
}

function statusColor(status) {
    return status === 'present' ? 'success' : status === 'late' ? 'warning' : status === 'absent' ? 'error' : status === 'half_day' ? 'orange' : 'info';
}

function setNotice(text, type) {
    notice.value = text;
    noticeType.value = type;
}
</script>
