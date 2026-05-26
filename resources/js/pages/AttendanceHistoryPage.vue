<template>
    <div class="pa-2">
        <div class="d-flex flex-wrap align-center justify-space-between ga-3 mb-4">
            <div>
                <div class="text-h5 font-weight-bold">Attendance</div>
                <div class="text-body-2 text-medium-emphasis">Review attendance records and history.</div>
            </div>
            <v-btn color="primary" variant="tonal" prepend-icon="mdi-view-dashboard-outline" :to="{ name: 'dashboard' }">Dashboard</v-btn>
        </div>

        <v-alert v-if="error" type="error" variant="tonal" class="mb-4" closable>{{ error }}</v-alert>

        <v-card  class="rounded-lg bg-white mb-5">
            <v-card-text>
                <v-row dense>
                    <v-col cols="12" sm="4">
                        <v-text-field v-model="filters.from" type="date" label="From"  density="comfortable" hide-details />
                    </v-col>
                    <v-col cols="12" sm="4">
                        <v-text-field v-model="filters.to" type="date" label="To"  density="comfortable" hide-details />
                    </v-col>
                    <v-col cols="12" sm="4" class="d-flex align-center">
                        <v-btn color="primary" block prepend-icon="mdi-filter-outline" @click="reload">Apply</v-btn>
                    </v-col>
                </v-row>
            </v-card-text>
        </v-card>

        <v-card  class="rounded-lg bg-white">
            <v-data-table-server
                v-model:items-per-page="itemsPerPage"
                v-model:page="page"
                :headers="headers"
                :items="items"
                :items-length="total"
                :loading="loading"
                item-value="id"
                density="comfortable"
                @update:options="loadItems"
            >
                <template #[`item.attendance_date`]="{ item }">
                    <span class="font-weight-bold">{{ item.attendance_date }}</span>
                </template>
                <template #[`item.status`]="{ item }">
                    <v-chip :color="statusColor(item.status)" size="small" variant="flat" class="text-capitalize font-weight-bold">{{ item.status?.replace('_', ' ') }}</v-chip>
                </template>
                <template #[`item.work_hours`]="{ item }">
                    <span class="font-weight-bold">{{ item.work_hours }} hrs</span>
                </template>
                <template #[`item.actions`]="{ item }">
                    <v-btn size="small" variant="text" color="primary" prepend-icon="mdi-eye-outline" :to="{ name: 'attendance.details', params: { id: item.id } }">View</v-btn>
                </template>
                <template #no-data>
                    <div class="text-center py-10">
                        <v-icon size="40" color="grey">mdi-calendar-blank-outline</v-icon>
                        <div class="text-subtitle-1 font-weight-bold mt-2">No attendance records</div>
                    </div>
                </template>
            </v-data-table-server>
        </v-card>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';

const items = ref([]);
const total = ref(0);
const page = ref(1);
const itemsPerPage = ref(10);
const loading = ref(false);
const error = ref('');
const filters = ref({ from: '', to: '' });

const headers = [
    { title: 'Date', key: 'attendance_date' },
    { title: 'Clock In', key: 'check_in', align: 'center' },
    { title: 'Clock Out', key: 'check_out', align: 'center' },
    { title: 'Hours', key: 'work_hours', align: 'center' },
    { title: 'Late', key: 'late_minutes', align: 'center' },
    { title: 'Status', key: 'status', align: 'center' },
    { title: 'Actions', key: 'actions', align: 'end', sortable: false },
];

async function loadItems(options = {}) {
    loading.value = true;
    error.value = '';
    try {
        const p = options.page ?? page.value;
        const per = options.itemsPerPage ?? itemsPerPage.value;
        const { data } = await axios.get('/api/attendance', {
            params: {
                page: p,
                per_page: per,
                from: filters.value.from || undefined,
                to: filters.value.to || undefined,
            },
        });
        items.value = data.data ?? [];
        total.value = data.meta?.total ?? 0;
        page.value = data.meta?.current_page ?? p;
        itemsPerPage.value = data.meta?.per_page ?? per;
    } catch {
        error.value = 'Unable to load attendance history.';
    } finally {
        loading.value = false;
    }
}

function reload() {
    page.value = 1;
    loadItems({ page: 1, itemsPerPage: itemsPerPage.value });
}

function statusColor(status) {
    return status === 'present' ? 'success' : status === 'late' ? 'warning' : status === 'absent' ? 'error' : status === 'half_day' ? 'orange' : 'info';
}
</script>
