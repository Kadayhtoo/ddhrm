<template>
    <div>
        <v-row class="mb-4" align="center">
            <v-col cols="12" md="8">
                <div class="text-h4 font-weight-bold">Attendance Settings</div>
                <div class="text-body-2 text-medium-emphasis">
                    HR can control office attendance rules, including office start/end time and minimum working hours.
                </div>
            </v-col>
        </v-row>

        <v-alert v-if="message" :type="messageType" variant="tonal" class="mb-4" rounded="lg">
            {{ message }}
        </v-alert>

        <v-card rounded="lg" class="pa-6">
            <v-row dense>
                <v-col cols="12" md="6">
                    <v-text-field
                        v-model="form.office_start"
                        type="time"
                        label="Office Start Time"
                        variant="outlined"
                        density="comfortable"
                        class="mb-4"
                    />
                </v-col>
                <v-col cols="12" md="6">
                    <v-text-field
                        v-model="form.office_end"
                        type="time"
                        label="Office End Time"
                        variant="outlined"
                        density="comfortable"
                        class="mb-4"
                    />
                </v-col>
                <v-col cols="12" md="6">
                    <v-text-field
                        v-model="form.grace_minutes"
                        type="number"
                        label="Grace Minutes"
                        variant="outlined"
                        density="comfortable"
                        min="0"
                        class="mb-4"
                    />
                </v-col>
                <v-col cols="12" md="6">
                    <v-text-field
                        v-model="form.minimum_work_minutes"
                        type="number"
                        label="Minimum Work Minutes"
                        variant="outlined"
                        density="comfortable"
                        min="1"
                        class="mb-4"
                    />
                </v-col>
            </v-row>

            <v-card-actions class="pa-0">
                <v-spacer />
                <v-btn color="primary" :loading="saving" @click="saveSettings">
                    Save Settings
                </v-btn>
            </v-card-actions>
        </v-card>
    </div>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue';
import axios from 'axios';

const message = ref('');
const messageType = ref('success');
const saving = ref(false);

const form = reactive({
    office_start: '09:00',
    office_end: '18:30',
    grace_minutes: 15,
    minimum_work_minutes: 240,
});

onMounted(fetchSettings);

async function fetchSettings() {
    try {
        const { data } = await axios.get('/api/attendance/settings');
        Object.assign(form, data.data);
    } catch (error) {
        setMessage('Unable to load attendance settings.', 'error');
    }
}

async function saveSettings() {
    saving.value = true;
    message.value = '';

    try {
        await axios.put('/api/attendance/settings', {
            office_start: form.office_start,
            office_end: form.office_end,
            grace_minutes: Number(form.grace_minutes),
            minimum_work_minutes: Number(form.minimum_work_minutes),
        });

        setMessage('Attendance settings saved successfully.', 'success');
    } catch (error) {
        setMessage(getErrorMessage(error), 'error');
    } finally {
        saving.value = false;
    }
}

function setMessage(text, type) {
    message.value = text;
    messageType.value = type;
}

function getErrorMessage(error) {
    const errors = error?.response?.data?.errors;
    if (errors) {
        return Object.values(errors).flat()[0] ?? 'Unable to save attendance settings.';
    }

    return error?.response?.data?.message || 'Unable to save attendance settings.';
}
</script>
