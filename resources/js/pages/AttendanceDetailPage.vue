<template>
    <div class="pa-2">
        <div class="d-flex align-center justify-space-between mb-4">
            <div>
                <div class="text-h5 font-weight-bold">Attendance Details</div>
                <div class="text-body-2 text-medium-emphasis">Daily clock, and status information.</div>
            </div>
            <v-btn variant="tonal" color="primary" prepend-icon="mdi-arrow-left" :to="{ name: 'attendance.history' }">Back</v-btn>
        </div>

        <v-alert v-if="error" type="error" variant="tonal" class="mb-4">{{ error }}</v-alert>
        <v-skeleton-loader v-if="loading" type="card, table" />

        <template v-else-if="attendance">
            <v-card class="rounded-lg bg-white mb-5">
                <v-card-text>
                    <v-row>
                        <v-col cols="12" md="4">
                            <div class="text-caption text-medium-emphasis">Employee</div>
                            <div class="text-subtitle-1 font-weight-bold">{{ attendance.user?.name || 'Me' }}</div>
                        </v-col>
                        <v-col cols="12" md="4">
                            <div class="text-caption text-medium-emphasis">Date</div>
                            <div class="text-subtitle-1 font-weight-bold">{{ attendance.attendance_date }}</div>
                        </v-col>
                        <v-col cols="12" md="4">
                            <div class="text-caption text-medium-emphasis">Status</div>
                            <v-chip :color="statusColor(attendance.status)" variant="flat" class="text-capitalize font-weight-bold">{{ attendance.status?.replace('_', ' ') }}</v-chip>
                        </v-col>
                    </v-row>
                </v-card-text>
            </v-card>

            <v-row>
                <v-col cols="12" md="6">
                    <v-card class="rounded-lg bg-white h-100">
                        <v-card-title>Work Summary</v-card-title>
                        <v-card-text>
                            <v-list density="comfortable">
                                <v-list-item title="Clock In" :subtitle="formatDateTime(attendance.clock_in_at)" />
                                <v-list-item title="Clock Out" :subtitle="formatDateTime(attendance.clock_out_at)" />
                                <v-list-item title="Working Hours" :subtitle="`${attendance.work_hours} hours`" />
                                <v-list-item title="Late Minutes" :subtitle="`${attendance.late_minutes} minutes`" />
                            </v-list>
                        </v-card-text>
                    </v-card>
                </v-col>
            </v-row>
        </template>
    </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';

const route = useRoute();
const attendance = ref(null);
const loading = ref(true);
const error = ref('');

onMounted(async () => {
    try {
        const { data } = await axios.get(`/api/attendance/${route.params.id}`);
        attendance.value = data.data;
    } catch {
        error.value = 'Unable to load attendance details.';
    } finally {
        loading.value = false;
    }
});

function formatDateTime(value) {
    if (!value) return '-';
    return new Date(value).toLocaleString();
}

function statusColor(status) {
    return status === 'present' ? 'success' : status === 'late' ? 'warning' : status === 'absent' ? 'error' : status === 'half_day' ? 'orange' : 'info';
}
</script>
