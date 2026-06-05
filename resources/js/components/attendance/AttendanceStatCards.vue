<template>
    <v-row>
        <v-col v-for="card in cards" :key="card.key" cols="12" sm="6" md="3">
            <v-card class="rounded-lg bg-white h-100">
                <v-card-text class="pa-4 d-flex align-center justify-space-between">
                    <div>
                        <div class="text-caption text-medium-emphasis text-uppercase">{{ card.label }}</div>
                        <div class="text-h5 font-weight-bold mt-1">{{ card.value }}</div>
                    </div>
                    <v-avatar :color="card.color" variant="tonal" size="44">
                        <v-icon>{{ card.icon }}</v-icon>
                    </v-avatar>
                </v-card-text>
            </v-card>
        </v-col>
    </v-row>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    widgets: {
        type: Object,
        required: true,
    },
});

const cards = computed(() => [
    { key: 'present', label: 'Present', value: props.widgets.present ?? 0, icon: 'mdi-account-check-outline', color: 'success' },
    { key: 'absent', label: 'Absent', value: props.widgets.absent ?? 0, icon: 'mdi-account-off-outline', color: 'error' },
    { key: 'late', label: 'Late', value: props.widgets.late ?? 0, icon: 'mdi-clock-alert-outline', color: 'warning' },
    { key: 'percentage', label: 'Attendance', value: `${props.widgets.attendance_percentage ?? 0}%`, icon: 'mdi-chart-donut', color: 'primary' },
]);
</script>
