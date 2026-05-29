<template>
    <div v-if="loading" class="text-center py-12">
        <v-progress-circular indeterminate color="primary" size="48" />
    </div>
    <div v-else-if="!payroll" class="text-center py-12 text-medium-emphasis">
        Payroll not found
    </div>
    <div v-else>
        <v-row class="mb-4">
            <v-col cols="12">
                <div class="d-flex align-center ga-3 mb-2">
                    <v-btn icon variant="text" @click="goBack">
                        <v-icon>mdi-arrow-left</v-icon>
                    </v-btn>
                    <div>
                        <div class="text-h4 font-weight-bold">Payroll Detail</div>
                        <div class="text-body-1 text-medium-emphasis">
                            {{ payroll.user?.name }} · {{ payroll.period_type | capitalize }} ·
                            {{ payroll.period_start }} — {{ payroll.period_end }}
                        </div>
                    </div>
                </div>
                <v-chip :color="statusColor" size="small">{{ payroll.status }}</v-chip>
                <v-chip v-if="payroll.calculated_at" class="ml-2" size="small" variant="tonal">
                    Calculated: {{ formatDate(payroll.calculated_at) }}
                </v-chip>
                <v-chip v-if="payroll.paid_at" class="ml-2" size="small" variant="tonal" color="primary">
                    Paid: {{ formatDate(payroll.paid_at) }}
                </v-chip>
            </v-col>
        </v-row>

        <v-row>
            <v-col cols="12" md="8">
                <v-card rounded="lg" class="pa-6 mb-4">
                    <div class="text-h6 font-weight-bold mb-4">Salary Breakdown</div>
                    <v-list lines="two">
                        <v-list-item>
                            <template #prepend>
                                <v-icon color="primary">mdi-cash</v-icon>
                            </template>
                            <v-list-item-title>Base Salary (Monthly)</v-list-item-title>
                            <v-list-item-subtitle class="text-medium-emphasis">Standard monthly rate</v-list-item-subtitle>
                            <template #append>
                                <span class="font-weight-medium">{{ formatCurrency(payroll.base_salary) }}</span>
                            </template>
                        </v-list-item>
                        <v-divider />
                        <v-list-item>
                            <template #prepend>
                                <v-icon color="primary">mdi-briefcase</v-icon>
                            </template>
                            <v-list-item-title>Gross Salary</v-list-item-title>
                            <v-list-item-subtitle class="text-medium-emphasis">Before deductions</v-list-item-subtitle>
                            <template #append>
                                <span class="font-weight-medium">{{ formatCurrency(payroll.gross_salary) }}</span>
                            </template>
                        </v-list-item>
                        <v-divider />
                        <v-list-item>
                            <template #prepend>
                                <v-icon color="warning">mdi-clock-alert</v-icon>
                            </template>
                            <v-list-item-title>Late Penalty</v-list-item-title>
                            <v-list-item-subtitle class="text-medium-emphasis">
                                {{ payroll.total_late_minutes }} minutes late
                            </v-list-item-subtitle>
                            <template #append>
                                <span class="text-error font-weight-medium">-{{ formatCurrency(payroll.late_penalty) }}</span>
                            </template>
                        </v-list-item>
                        <v-divider />
                        <v-list-item>
                            <template #prepend>
                                <v-icon color="error">mdi-palm-tree</v-icon>
                            </template>
                            <v-list-item-title>Unpaid Leave Deduction</v-list-item-title>
                            <v-list-item-subtitle class="text-medium-emphasis">
                                {{ payroll.total_unpaid_leave_days }} day(s) unpaid
                            </v-list-item-subtitle>
                            <template #append>
                                <span class="text-error font-weight-medium">-{{ formatCurrency(payroll.unpaid_leave_deduction) }}</span>
                            </template>
                        </v-list-item>
                        <v-divider />
                        <v-list-item>
                            <template #prepend>
                                <v-icon color="info">mdi-palm-tree</v-icon>
                            </template>
                            <v-list-item-title>Paid Leave Deduction</v-list-item-title>
                            <v-list-item-subtitle class="text-medium-emphasis">
                                {{ payroll.total_paid_leave_days }} day(s) paid leave
                            </v-list-item-subtitle>
                            <template #append>
                                <span class="text-error font-weight-medium">-{{ formatCurrency(payroll.paid_leave_deduction) }}</span>
                            </template>
                        </v-list-item>
                        <v-divider />
                        <v-list-item class="bg-primary-light">
                            <template #prepend>
                                <v-icon color="success">mdi-check-circle</v-icon>
                            </template>
                            <v-list-item-title class="text-h6 font-weight-bold">Net Salary</v-list-item-title>
                            <v-list-item-subtitle class="text-medium-emphasis">
                                After all deductions
                            </v-list-item-subtitle>
                            <template #append>
                                <span class="text-success font-weight-bold text-h6">{{ formatCurrency(payroll.net_salary) }}</span>
                            </template>
                        </v-list-item>
                    </v-list>
                </v-card>
            </v-col>

            <v-col cols="12" md="4">
                <v-card rounded="lg" class="pa-6 mb-4">
                    <div class="text-h6 font-weight-bold mb-4">Summary</div>
                    <v-table density="compact">
                        <tbody>
                            <tr>
                                <td class="text-medium-emphasis">Working Days</td>
                                <td class="text-right font-weight-medium">{{ payroll.total_working_days }}</td>
                            </tr>
                            <tr>
                                <td class="text-medium-emphasis">Work Minutes</td>
                                <td class="text-right font-weight-medium">{{ payroll.total_work_minutes }}</td>
                            </tr>
                            <tr>
                                <td class="text-medium-emphasis">Late Minutes</td>
                                <td class="text-right font-weight-medium text-warning">{{ payroll.total_late_minutes }}</td>
                            </tr>
                            <tr>
                                <td class="text-medium-emphasis">Unpaid Leave Days</td>
                                <td class="text-right font-weight-medium text-error">{{ payroll.total_unpaid_leave_days }}</td>
                            </tr>
                            <tr>
                                <td class="text-medium-emphasis">Paid Leave Days</td>
                                <td class="text-right font-weight-medium">{{ payroll.total_paid_leave_days }}</td>
                            </tr>
                            <tr class="bg-grey-lighten-4">
                                <td class="font-weight-bold">Total Deductions</td>
                                <td class="text-right font-weight-bold text-error">{{ formatCurrency(payroll.total_deductions) }}</td>
                            </tr>
                            <tr class="bg-grey-lighten-4">
                                <td class="font-weight-bold">Net Salary</td>
                                <td class="text-right font-weight-bold text-success">{{ formatCurrency(payroll.net_salary) }}</td>
                            </tr>
                        </tbody>
                    </v-table>
                </v-card>

                <v-card v-if="auth.can('payroll.manage') && payroll.status === 'calculated'" rounded="lg" class="pa-6">
                    <v-btn
                        block
                        color="success"
                        size="large"
                        prepend-icon="mdi-check"
                        @click="markPaid"
                    >
                        Mark as Paid
                    </v-btn>
                </v-card>
            </v-col>
        </v-row>

        <v-card v-if="payroll.notes" rounded="lg" class="pa-6 mt-2">
            <div class="text-subtitle-1 font-weight-bold mb-2">Notes</div>
            <p class="text-body-1">{{ payroll.notes }}</p>
        </v-card>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import { useAuthStore } from '@/stores/auth';

const route = useRoute();
const router = useRouter();
const auth = useAuthStore();

const loading = ref(true);
const payroll = ref(null);

const statusColor = computed(() => {
    switch (payroll.value?.status) {
        case 'calculated': return 'success';
        case 'paid': return 'primary';
        case 'draft': return 'warning';
        default: return 'grey';
    }
});

function formatCurrency(value) {
    const num = Number(value) || 0;
    return new Intl.NumberFormat('my-MM', { style: 'currency', currency: 'MMK', minimumFractionDigits: 0 }).format(num);
}

function formatDate(value) {
    if (!value) return '-';
    return new Date(value).toLocaleDateString('en-GB', {
        day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit',
    });
}

function goBack() {
    router.push({ name: 'payroll' });
}

async function markPaid() {
    try {
        await axios.post(`/api/payroll/${payroll.value.id}/mark-paid`);
        payroll.value.status = 'paid';
        payroll.value.paid_at = new Date().toISOString();
    } catch {
        // ignore
    }
}

onMounted(async () => {
    try {
        const { data } = await axios.get(`/api/payroll/${route.params.id}`);
        payroll.value = data.data ?? null;
    } finally {
        loading.value = false;
    }
});
</script>
