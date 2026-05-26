<template>
    <v-card class="rounded-lg bg-white h-100">
        <v-card-title class="d-flex align-center">
            <v-icon color="primary" class="mr-2">mdi-calendar-clock</v-icon>
            Today Attendance
        </v-card-title>
        <v-card-text>
            <v-alert v-if="message" :type="messageType" variant="tonal" class="mb-4" density="compact" closable>
                {{ message }}
            </v-alert>

            <v-skeleton-loader v-if="attendance.loadingToday" type="article" />
            <template v-else>
                <div class="d-flex align-center mb-4">
                    <v-chip :color="statusColor(todayStatus)" variant="flat" class="text-capitalize font-weight-bold">
                        {{ todayStatus.replace('_', ' ') }}
                    </v-chip>
                    <v-spacer />
                    <span class="text-caption text-medium-emphasis">{{ todayDate }}</span>
                </div>

                <v-row dense class="mb-4">
                    <v-col cols="6" sm="3">
                        <div class="text-caption text-medium-emphasis">Clock In</div>
                        <div class="text-subtitle-2 font-weight-bold">{{ formatTime(attendance.today?.clock_in_at) }}</div>
                    </v-col>
                    <v-col cols="6" sm="3">
                        <div class="text-caption text-medium-emphasis">Clock Out</div>
                        <div class="text-subtitle-2 font-weight-bold">{{ formatTime(attendance.today?.clock_out_at) }}</div>
                    </v-col>
                    <v-col cols="6" sm="3">
                        <div class="text-caption text-medium-emphasis">Work</div>
                        <div class="text-subtitle-2 font-weight-bold">{{ attendance.today?.work_hours ?? 0 }} hrs</div>
                    </v-col>
                </v-row>

                <div class="d-flex flex-wrap ga-2">
                    <v-btn color="success" prepend-icon="mdi-login" :loading="attendance.actionLoading" :disabled="attendance.hasCheckedIn" @click="runAction('checkIn')">Check In</v-btn>
                    <v-btn color="primary" prepend-icon="mdi-logout" :loading="attendance.actionLoading" :disabled="!attendance.hasCheckedIn || attendance.hasCheckedOut" @click="runAction('checkOut')">Check Out</v-btn>
                </div>
            </template>
        </v-card-text>
    </v-card>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { useAttendanceStore } from '@/stores/attendance';

const attendance = useAttendanceStore();
const message = ref('');
const messageType = ref('success');

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
