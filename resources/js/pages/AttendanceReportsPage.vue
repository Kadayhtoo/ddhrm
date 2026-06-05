<template>
    <div class="pa-2">
        <div class="d-flex flex-wrap align-center justify-space-between ga-3 mb-4">
            <div>
                <div class="text-h5 font-weight-bold">Attendance Reports</div>
                <div class="text-body-2 text-medium-emphasis">Daily, monthly, and employee attendance summaries.</div>
            </div>
        </div>

        <AttendanceStatCards :widgets="widgets" class="mb-5" />

        <v-card class="rounded-lg bg-white mb-5">
            <v-card-text>
                <v-row dense>
                    <v-col cols="12" md="3">
                        <v-text-field v-model="dailyDate" type="date" label="Daily Date" variant="outlined" density="comfortable" hide-details />
                    </v-col>
                    <v-col cols="12" md="3">
                        <v-text-field v-model.number="year" type="number" label="Year" variant="outlined" density="comfortable" hide-details />
                    </v-col>
                    <v-col cols="12" md="3">
                        <v-text-field v-model.number="month" type="number" min="1" max="12" label="Month" variant="outlined" density="comfortable" hide-details />
                    </v-col>
                    <v-col cols="12" md="3">
                        <v-btn color="primary" block height="44" prepend-icon="mdi-refresh" :loading="loading" @click="loadReports">Refresh</v-btn>
                    </v-col>
                </v-row>
            </v-card-text>
        </v-card>

        <v-row>
            <v-col cols="12" md="6">
                <v-card class="rounded-lg bg-white h-100">
                    <v-card-title>Daily Report</v-card-title>
                    <v-card-text>
                        <ReportSummary :summary="daily.summary" />
                    </v-card-text>
                </v-card>
            </v-col>
            <v-col cols="12" md="6">
                <v-card class="rounded-lg bg-white h-100">
                    <v-card-title>Monthly Report</v-card-title>
                    <v-card-text>
                        <ReportSummary :summary="monthly.summary" />
                    </v-card-text>
                </v-card>
            </v-col>
        </v-row>

        <v-card class="rounded-lg bg-white mt-5">
            <v-card-title>Monthly Records</v-card-title>
            <v-data-table :headers="headers" :items="monthly.records" :loading="loading" density="comfortable">
                <template #[`item.user`]="{ item }">{{ item.user?.name }}</template>
                <template #[`item.status`]="{ item }">
                    <v-chip :color="statusColor(item.status)" size="small" variant="flat" class="text-capitalize font-weight-bold">{{ item.status?.replace('_', ' ') }}</v-chip>
                </template>
            </v-data-table>
        </v-card>
    </div>
</template>

<script setup>
import { defineComponent, h, onMounted, ref } from 'vue';
import axios from 'axios';
import AttendanceStatCards from '@/components/attendance/AttendanceStatCards.vue';

const today = new Date();
const dailyDate = ref(today.toISOString().substring(0, 10));
const year = ref(today.getFullYear());
const month = ref(today.getMonth() + 1);
const loading = ref(false);
const widgets = ref({ present: 0, absent: 0, late: 0, attendance_percentage: 0 });
const daily = ref({ summary: {}, records: [] });
const monthly = ref({ summary: {}, records: [] });

const headers = [
    { title: 'Employee', key: 'user' },
    { title: 'Date', key: 'attendance_date' },
    { title: 'Clock In', key: 'check_in', align: 'center' },
    { title: 'Clock Out', key: 'check_out', align: 'center' },
    { title: 'Hours', key: 'work_hours', align: 'center' },
    { title: 'Status', key: 'status', align: 'center' },
];

const ReportSummary = defineComponent({
    props: {
        summary: {
            type: Object,
            required: true,
        },
    },
    setup(props) {
        return () => h('div', { class: 'd-flex flex-column ga-2' }, [
            row('Total', props.summary.total ?? 0),
            row('Present', props.summary.present ?? 0),
            row('Absent', props.summary.absent ?? 0),
            row('Late', props.summary.late ?? 0),
            row('Half Day', props.summary.half_day ?? 0),
            row('Attendance', `${props.summary.attendance_percentage ?? 0}%`),
        ]);
    },
});

onMounted(loadReports);

async function loadReports() {
    loading.value = true;
    try {
        const [widgetRes, dailyRes, monthlyRes] = await Promise.all([
            axios.get('/api/attendance/reports/widgets', { params: { date: dailyDate.value } }),
            axios.get('/api/attendance/reports/daily', { params: { date: dailyDate.value } }),
            axios.get('/api/attendance/reports/monthly', { params: { year: year.value, month: month.value } }),
        ]);
        widgets.value = widgetRes.data.data ?? widgets.value;
        daily.value = dailyRes.data.data ?? daily.value;
        monthly.value = monthlyRes.data.data ?? monthly.value;
    } finally {
        loading.value = false;
    }
}

function row(label, value) {
    return h('div', { class: 'd-flex justify-space-between align-center py-1' }, [
        h('span', { class: 'text-medium-emphasis' }, label),
        h('span', { class: 'font-weight-bold' }, value),
    ]);
}

function statusColor(status) {
    return status === 'present' ? 'success' : status === 'late' ? 'warning' : status === 'absent' ? 'error' : status === 'half_day' ? 'orange' : 'info';
}
</script>
