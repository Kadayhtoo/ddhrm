<template>
    <div class="pa-2">
        <div class="d-flex flex-wrap align-center justify-space-between ga-3 mb-5">
            <div>
                <div class="text-h5 font-weight-bold">Attendance Dashboard</div>
                <div class="text-body-2 text-medium-emphasis">Track today's attendance activity and work time.</div>
            </div>
            <div class="d-flex flex-wrap ga-2">
                <v-btn variant="tonal" color="primary" prepend-icon="mdi-history" :to="{ name: 'attendance.history' }">History</v-btn>
                <v-btn v-if="canManage" variant="tonal" color="secondary" prepend-icon="mdi-table-account" :to="{ name: 'attendance.admin' }">Admin Table</v-btn>
                <v-btn v-if="canManage" variant="tonal" color="info" prepend-icon="mdi-chart-box-outline" :to="{ name: 'attendance.reports' }">Reports</v-btn>
            </div>
        </div>

        <AttendanceStatCards :widgets="attendance.widgets" class="mb-5" />

        <v-alert v-if="message" :type="messageType" variant="tonal" class="mb-4" closable>
            {{ message }}
        </v-alert>

        <v-row>
            <v-col cols="12" md="5">
                <v-card class="rounded-lg bg-white">
                    <v-card-title class="d-flex align-center">
                        <v-icon color="primary" class="mr-2">mdi-calendar-clock</v-icon>
                        Today
                    </v-card-title>
                    <v-card-text>
                        <v-skeleton-loader v-if="attendance.loadingToday" type="article" />
                        <template v-else>
                            <div class="d-flex align-center mb-4">
                                <v-chip :color="statusColor(todayStatus)" variant="flat" class="text-capitalize font-weight-bold">
                                    {{ todayStatus.replace('_', ' ') }}
                                </v-chip>
                                <v-spacer />
                                <span class="text-caption text-medium-emphasis">{{ todayDate }}</span>
                            </div>

                            <v-row dense>
                                <v-col cols="6">
                                    <div class="text-caption text-medium-emphasis">Clock In</div>
                                    <div class="text-subtitle-1 font-weight-bold">{{ formatTime(attendance.today?.clock_in_at) }}</div>
                                </v-col>
                                <v-col cols="6">
                                    <div class="text-caption text-medium-emphasis">Clock Out</div>
                                    <div class="text-subtitle-1 font-weight-bold">{{ formatTime(attendance.today?.clock_out_at) }}</div>
                                </v-col>
                                <v-col cols="6">
                                    <div class="text-caption text-medium-emphasis">Work Hours</div>
                                    <div class="text-subtitle-1 font-weight-bold">{{ attendance.today?.work_hours ?? 0 }} hrs</div>
                                </v-col>
                            </v-row>

                            <v-divider class="my-4" />

                            <div class="d-flex flex-wrap ga-2">
                                <v-btn color="success" prepend-icon="mdi-login" :loading="attendance.actionLoading" :disabled="attendance.hasCheckedIn" @click="runAction('checkIn')">Check In</v-btn>
                                <v-btn color="primary" prepend-icon="mdi-logout" :loading="attendance.actionLoading" :disabled="!attendance.hasCheckedIn || attendance.hasCheckedOut" @click="runAction('checkOut')">Check Out</v-btn>
                            </div>
                        </template>
                    </v-card-text>
                </v-card>
            </v-col>

            <v-col cols="12" md="7">
                <v-card variant="outlined" class="rounded-lg bg-white">
                    <v-card-title class="d-flex align-center">
                        <v-icon color="primary" class="mr-2">mdi-file-document-edit-outline</v-icon>
                        Correction Request
                    </v-card-title>
                    <v-card-text>
                        <v-form ref="requestForm">
                            <v-row dense>
                                <v-col cols="12" sm="6">
                                    <v-select v-model="correction.type" :items="requestTypes" label="Request Type" variant="outlined" density="comfortable" :rules="[rules.required]" />
                                </v-col>
                                <v-col cols="12" sm="6">
                                    <v-text-field v-model="correction.requested_date" type="date" label="Date" variant="outlined" density="comfortable" :rules="[rules.required]" />
                                </v-col>
                                <v-col cols="12" sm="6">
                                    <v-text-field v-model="correction.requested_clock_in_at" type="datetime-local" label="Clock In" variant="outlined" density="comfortable" />
                                </v-col>
                                <v-col cols="12" sm="6">
                                    <v-text-field v-model="correction.requested_clock_out_at" type="datetime-local" label="Clock Out" variant="outlined" density="comfortable" />
                                </v-col>
                                <v-col cols="12" sm="6">
                                    <v-select v-model="correction.requested_status" :items="statusOptions" label="Requested Status" variant="outlined" density="comfortable" clearable />
                                </v-col>
                                <v-col cols="12">
                                    <v-textarea v-model="correction.reason" label="Reason" rows="3" variant="outlined" density="comfortable" :rules="[rules.required]" />
                                </v-col>
                            </v-row>
                        </v-form>
                    </v-card-text>
                    <v-card-actions class="px-4 pb-4">
                        <v-spacer />
                        <v-btn color="primary" prepend-icon="mdi-send-outline" :loading="submittingRequest" @click="submitCorrection">Submit Request</v-btn>
                    </v-card-actions>
                </v-card>
            </v-col>
        </v-row>
    </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import axios from 'axios';
import AttendanceStatCards from '@/components/attendance/AttendanceStatCards.vue';
import { useAttendanceStore } from '@/stores/attendance';
import { useAuthStore } from '@/stores/auth';

const attendance = useAttendanceStore();
const auth = useAuthStore();
const message = ref('');
const messageType = ref('success');
const submittingRequest = ref(false);
const requestForm = ref(null);

const correction = ref({
    type: 'full_day',
    requested_date: new Date().toISOString().substring(0, 10),
    requested_clock_in_at: '',
    requested_clock_out_at: '',
    requested_status: null,
    reason: '',
});

const requestTypes = [
    { title: 'Clock In', value: 'clock_in' },
    { title: 'Clock Out', value: 'clock_out' },
    { title: 'Status', value: 'status' },
    { title: 'Full Day', value: 'full_day' },
];

const statusOptions = [
    { title: 'Present', value: 'present' },
    { title: 'Absent', value: 'absent' },
    { title: 'Late', value: 'late' },
    { title: 'Half Day', value: 'half_day' },
    { title: 'Holiday', value: 'holiday' },
    { title: 'Weekend', value: 'weekend' },
    { title: 'On Leave', value: 'on_leave' },
];

const rules = {
    required: (value) => !!value || 'This field is required',
};

const canManage = computed(() => auth.can('attendance.manage') || auth.hasRoleSlug('admin'));
const todayDate = computed(() => new Date().toLocaleDateString());
const todayStatus = computed(() => attendance.today?.status ?? 'absent');

onMounted(async () => {
    await Promise.all([attendance.fetchToday(), attendance.fetchWidgets()]);
});

async function runAction(action) {
    try {
        await attendance[action]();
        setMessage('Attendance updated successfully.', 'success');
    } catch (error) {
        setMessage(error?.response?.data?.message || firstValidationError(error) || 'Attendance action failed.', 'error');
    }
}

async function submitCorrection() {
    const result = await requestForm.value.validate();
    if (!result.valid) return;

    submittingRequest.value = true;
    try {
        await axios.post('/api/attendance/requests', correction.value);
        setMessage('Attendance correction request submitted.', 'success');
        correction.value.reason = '';
    } catch (error) {
        setMessage(error?.response?.data?.message || firstValidationError(error) || 'Request submission failed.', 'error');
    } finally {
        submittingRequest.value = false;
    }
}

function formatTime(value) {
    if (!value) return '-';
    return new Date(value).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
}

function statusColor(status) {
    return status === 'present' ? 'success' : status === 'late' ? 'warning' : status === 'absent' ? 'error' : 'info';
}

function firstValidationError(error) {
    const errors = error?.response?.data?.errors;
    if (!errors) return null;
    return Object.values(errors)?.[0]?.[0] ?? null;
}

function setMessage(text, type) {
    message.value = text;
    messageType.value = type;
}
</script>
