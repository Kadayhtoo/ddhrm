<template>
    <div>
        <v-row class="mb-4">
            <v-col cols="12">
                <div class="text-h4 font-weight-bold mb-1">
                    Welcome back, {{ auth.user?.name?.split(' ')[0] ?? 'there' }}
                </div>
                <div class="text-body-1 text-medium-emphasis mb-2">
                    Overview aligned with your HRMS PDF (attendance, payroll, leave, invoices).
                </div>
                <div v-if="roleSlugs.length" class="d-flex flex-wrap ga-2">
                    <v-chip
                        v-for="slug in roleSlugs"
                        :key="slug"
                        size="small"
                        color="primary"
                        variant="tonal"
                    >
                        {{ slug }}
                    </v-chip>
                </div>
            </v-col>
        </v-row>

        <v-row v-if="loading">
            <v-col cols="12" class="text-center py-12">
                <v-progress-circular indeterminate color="primary" size="48" />
            </v-col>
        </v-row>

        <v-row v-else>
            <v-col
                v-for="(card, key) in cards"
                :key="key"
                cols="12"
                sm="6"
                md="4"
            >
                <AttendanceTodayActions v-if="card.label === 'Attendance' && auth.can('attendance.view') && auth.user?.email !== 'ceo@ddhrm.local'" />
                <v-card v-else rounded="lg" class="pa-5 h-100">
                    <div class="text-caption text-medium-emphasis text-uppercase">{{ card.label }}</div>
                    
                    <div v-if="card.link" class="mt-2">
                        <v-btn :to="card.link" color="primary" variant="text" class="px-0">
                            {{ card.value }}
                        </v-btn>
                    </div>

                    <div v-else-if="typeof card.value === 'object' && card.value !== null" class="mt-2">
                        <div v-for="(count, type) in card.value" :key="type" class="text-h6 font-weight-semibold">
                            {{ type }}: {{ count }}
                        </div>
                    </div>

                    <div v-else class="text-h5 font-weight-semibold mt-2">{{ card.value }}</div>
                </v-card>
            </v-col>
        </v-row>

        <v-row v-if="auth.can('attendance.manage')" class="mt-2">
            <v-col v-for="widget in attendanceWidgets" :key="widget.key" cols="12" sm="6" md="3">
                <v-card rounded="lg" class="pa-5 h-100">
                    <div class="d-flex align-center justify-space-between">
                        <div>
                            <div class="text-caption text-medium-emphasis text-uppercase">{{ widget.label }}</div>
                            <div class="text-h5 font-weight-semibold mt-2">{{ widget.value }}</div>
                        </div>
                        <v-icon :color="widget.color" size="32">{{ widget.icon }}</v-icon>
                    </div>
                </v-card>
            </v-col>
        </v-row>
    </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import axios from 'axios';
import AttendanceTodayActions from '@/components/attendance/AttendanceTodayActions.vue';
import { useAuthStore } from '@/stores/auth';

const auth = useAuthStore();
const loading = ref(true);
const cards = ref({});
const roleSlugs = ref([]);
const attendanceSummary = ref({ present: 0, absent: 0, late: 0, attendance_percentage: 0 });

// const attendanceWidgets = computed(() => [
//     { key: 'present', label: 'Present Today', value: attendanceSummary.value.present, icon: 'mdi-account-check-outline', color: 'success' },
//     { key: 'absent', label: 'Absent Today', value: attendanceSummary.value.absent, icon: 'mdi-account-off-outline', color: 'error' },
//     { key: 'late', label: 'Late Today', value: attendanceSummary.value.late, icon: 'mdi-clock-alert-outline', color: 'warning' },
//     { key: 'percentage', label: 'Attendance %', value: `${attendanceSummary.value.attendance_percentage}%`, icon: 'mdi-chart-donut', color: 'primary' },
// ]);

const attendanceWidgets = computed(() => {
    const isStaff = roleSlugs.value.includes('staff');
    const periodLabel = isStaff ? 'This Month' : 'Today';

    return [
        {
            key: 'present',
            label: `(${periodLabel}) Present`,
            value: attendanceSummary.value.present,
            icon: 'mdi-account-check-outline',
            color: 'success',
        },
        {
            key: 'absent',
            label: `(${periodLabel}) Absent`,
            value: attendanceSummary.value.absent,
            icon: 'mdi-account-off-outline',
            color: 'error',
        },
        {
            key: 'late',
            label: `(${periodLabel}) Late `,
            value: attendanceSummary.value.late,
            icon: 'mdi-clock-alert-outline',
            color: 'warning',
        },
        {
            key: 'percentage',
            label: `(${periodLabel}) Attendance % `,
            value: `${attendanceSummary.value.attendance_percentage}%`,
            icon: 'mdi-chart-donut',
            color: 'primary',
        },
    ];
});

onMounted(async () => {
    try {
        const { data } = await axios.get('/api/dashboard/summary');
        cards.value = data.modules ?? {};
        roleSlugs.value = data.role_slugs ?? [];
        if (auth.can('attendance.view')) {
            const widgets = await axios.get('/api/attendance/reports/widgets');
            attendanceSummary.value = widgets.data.data ?? attendanceSummary.value;
        }
    } finally {
        loading.value = false;
    }
});
</script>
