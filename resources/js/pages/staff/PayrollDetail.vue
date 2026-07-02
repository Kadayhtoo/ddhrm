<template>
    <v-container>
        <v-row class="mb-4">
            <v-col cols="12">
                <div class="d-flex align-center ga-3">
                    <v-btn icon variant="text" @click="$router.back()">
                        <v-icon>mdi-arrow-left</v-icon>
                    </v-btn>
                    <div>
                        <div class="text-h5 font-weight-bold">Payroll Detail</div>
                        <v-chip color="success" size="small" class="mt-1">{{ payroll?.status }}</v-chip>
                    </div>
                </div>
            </v-col>
        </v-row>
         <v-alert
            v-if="error"
            type="error"
            variant="tonal"
        >
            {{ error }}
        </v-alert>
        <v-row v-else-if="payroll">
            <v-col cols="12" md="6">
                <v-card rounded="lg" class="pa-4 mb-4">
                    <div class="text-subtitle-1 font-weight-bold mb-3"></div>
                    <v-list density="compact" lines="none">
                        <v-list-item title="Period Type" :subtitle="payroll.period_type" />
                        <v-list-item title="Period Start" :subtitle="payroll.period_start" />
                        <v-list-item title="Period End" :subtitle="payroll.period_end" />
                    </v-list>
                </v-card>
            </v-col>

            <v-col cols="12" md="6">
                <v-card rounded="lg" class="pa-6">
                    <div class="text-h6 font-weight-bold mb-4">Salary Breakdown</div>
                    
                    <v-list lines="one" density="compact">
                        <!-- Base Salary -->
                        <v-list-item>
                            <v-list-item-title>Base Salary</v-list-item-title>
                            <template #append>
                                <span class="font-weight-medium">{{ payroll.base_salary }} MMK</span>
                            </template>
                        </v-list-item>
                        
                        <v-divider />

                        <!-- Gross Salary -->
                        <v-list-item>
                            <v-list-item-title>Gross Salary</v-list-item-title>
                            <template #append>
                                <span class="font-weight-medium">{{ payroll.gross_salary }} MMK</span>
                            </template>
                        </v-list-item>

                        <v-divider />

                        <!-- Late Deductions Section -->
                        <v-list-item>
                            <v-list-item-title class="text-error">Late Penalty</v-list-item-title>
                            {{ payroll.total_late_minutes }} minutes late
                            <template #append>
                                <span class="text-error font-weight-medium">-{{ payroll.late_penalty }} MMK</span>
                            </template>
                        </v-list-item>
                        <v-divider />

                        <!-- Unpaid Deductions Section -->
                        <v-list-item>
                            <v-list-item-title class="text-error">Unpaid Leave Deductions</v-list-item-title>
                            {{ payroll.total_unpaid_leave_days }} day(s) unpaid
                            <template #append>
                                <span class="text-error font-weight-medium">-{{ payroll.unpaid_leave_deduction }} MMK</span>
                            </template>
                        </v-list-item>
                        <v-divider />


                        <!-- Paid Deductions Section -->
                        <v-list-item>
                            <v-list-item-title class="text-error">Paid Leave Deductions</v-list-item-title>
                            {{ payroll.total_paid_leave_days }} day(s) paid leave
                            <template #append>
                                <span class="text-error font-weight-medium">-{{ payroll.paid_leave_deduction }} MMK</span>
                            </template>
                        </v-list-item>

                        <v-divider />
                        <!-- Net Salary (Final Row) -->
                        <v-list-item class="bg-grey-lighten-4">
                            <v-list-item-title class="font-weight-bold">Net Salary</v-list-item-title>
                            <template #append>
                                <span class="font-weight-bold text-success">{{ payroll.net_salary }} MMK</span>
                            </template>
                        </v-list-item>
                    </v-list>
                </v-card>
            </v-col>
        </v-row>
    </v-container>
</template>
<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';

const route = useRoute();
const router = useRouter();

const payroll = ref(null);
const error = ref('');

onMounted(async () => {
    try {
        const response = await axios.get(`/api/my-payroll/${route.params.id}`);
        payroll.value = response.data.data;
    } catch (err) {
        if (err.response?.status === 403) {
            router.replace('/payroll/history');
        } else if (err.response?.status === 404) {
            router.replace('/payroll/history');
        } else {
            error.value = 'Something went wrong.';
        }
    }
});
</script>