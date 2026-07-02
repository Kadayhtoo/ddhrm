<template>
    <v-container>
        <div class="text-h4 mb-4">Payroll History</div>
        <v-card class="pa-4">
            <v-row class="mb-4">
                <v-col cols="12" md="4">
                    <v-select
                        v-model="timeframe"
                        :items="timeframeOptions"
                        label="Filter by Period"
                        variant="outlined"
                        density="compact"
                        hide-details
                        @update:modelValue="getHistory"
                    ></v-select>
                </v-col>
            </v-row>
            <v-data-table
                :headers="tableHeaders"
                :items="payrolls"
                :loading="loading"
                @click:row="goToDetail" 
                hover
                class="cursor-pointer"
            >
                <template #item.status="{ item }">
                    <v-chip size="small" :color="item.status === 'paid' ? 'success' : 'warning'">
                        {{ item.status }}
                    </v-chip>
                </template>
            </v-data-table>
        </v-card>
    </v-container>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const payrolls = ref([]);
const loading = ref(true);

import { useRouter } from 'vue-router';

const router = useRouter();

function goToDetail(event, { item }) {
    router.push(`/my-payroll/${item.id}`);
}

const tableHeaders = [
    { title: 'Period', key: 'period_type' },
    { title: 'Start', key: 'period_start' },
    { title: 'End', key: 'period_end' },
    { title: 'Base Salary', key: 'base_salary', align: 'end' },
    { title: 'Late Mins', key: 'total_late_minutes', align: 'end' },
    { title: 'Unpaid Leave', key: 'total_unpaid_leave_days', align: 'end' },
    { title: 'Deductions', key: 'total_deductions', align: 'end' },
    { title: 'Net Salary', key: 'net_salary', align: 'end' },
    { title: 'Status', key: 'status' },
];

const timeframe = ref('all'); 

const timeframeOptions = [
    { title: 'All', value: 'all' },
    { title: 'Last 3 Months', value: '3_months' },
    { title: 'Last 6 Months', value: '6_months' },
    { title: 'Current Year', value: 'current_year' }
];

async function getHistory() {
    loading.value = true;
    try {
        const response = await axios.get('/api/my-payroll/history', {
            params: { filter: timeframe.value }
        });
        payrolls.value = response.data.data;
    } catch (error) {
        console.error('Error fetching payroll:', error);
    } finally {
        loading.value = false;
    }
}

onMounted(() => {
    getHistory();
});
</script>

<style scoped>
.cursor-pointer { cursor: pointer; }
</style>