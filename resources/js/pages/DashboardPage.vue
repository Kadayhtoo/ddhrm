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
                <v-card rounded="lg" class="pa-5 h-100" border>
                    <div class="text-caption text-medium-emphasis text-uppercase">{{ card.label }}</div>
                    <div class="text-h5 font-weight-semibold mt-2">{{ card.value }}</div>
                </v-card>
            </v-col>
        </v-row>
    </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import axios from 'axios';
import { useAuthStore } from '@/stores/auth';

const auth = useAuthStore();
const loading = ref(true);
const cards = ref({});
const roleSlugs = ref([]);

onMounted(async () => {
    try {
        const { data } = await axios.get('/api/dashboard/summary');
        cards.value = data.modules ?? {};
        roleSlugs.value = data.role_slugs ?? [];
    } finally {
        loading.value = false;
    }
});
</script>
