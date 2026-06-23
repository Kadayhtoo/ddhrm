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
            </v-col>
        </v-row>

        <v-row class="mb-4">
            <v-col cols="12" md="6">
                <v-card rounded="lg" class="pa-4">
                    <div class="text-subtitle-1 font-weight-bold mb-3">Employee Information</div>
                    <v-list density="compact" lines="none">
                        <v-list-item>
                            <v-list-item-title class="text-caption text-medium-emphasis">Name</v-list-item-title>
                            <v-list-item-subtitle class="font-weight-medium">{{ payroll.user?.name ?? '-' }}</v-list-item-subtitle>
                        </v-list-item>
                        <v-list-item>
                            <v-list-item-title class="text-caption text-medium-emphasis">Email</v-list-item-title>
                            <v-list-item-subtitle class="font-weight-medium">{{ payroll.user?.email ?? '-' }}</v-list-item-subtitle>
                        </v-list-item>
                        <v-list-item>
                            <v-list-item-title class="text-caption text-medium-emphasis">Department</v-list-item-title>
                            <v-list-item-subtitle class="font-weight-medium">{{ payroll.user?.department?.name ?? '-' }}</v-list-item-subtitle>
                        </v-list-item>
                        <v-list-item>
                            <v-list-item-title class="text-caption text-medium-emphasis">Employee ID</v-list-item-title>
                            <v-list-item-subtitle class="font-weight-medium">{{ payroll.user?.id ?? '-' }}</v-list-item-subtitle>
                        </v-list-item>
                    </v-list>
                </v-card>
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
                            <v-list-item-subtitle class="text-medium-emphasis">Standard monthly
                                rate</v-list-item-subtitle>
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
                                <span class="text-error font-weight-medium">-{{ formatCurrency(payroll.late_penalty)
                                    }}</span>
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
                                <span class="text-error font-weight-medium">-{{
                                    formatCurrency(payroll.unpaid_leave_deduction) }}</span>
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
                                <span class="text-error font-weight-medium">-{{
                                    formatCurrency(payroll.paid_leave_deduction) }}</span>
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
                                <span class="text-success font-weight-bold text-h6">{{
                                    formatCurrency(payroll.net_salary) }}</span>
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
                                <td class="text-right font-weight-medium text-warning">{{ payroll.total_late_minutes }}
                                </td>
                            </tr>
                            <tr>
                                <td class="text-medium-emphasis">Unpaid Leave Days</td>
                                <td class="text-right font-weight-medium text-error">{{ payroll.total_unpaid_leave_days
                                    }}</td>
                            </tr>
                            <tr>
                                <td class="text-medium-emphasis">Paid Leave Days</td>
                                <td class="text-right font-weight-medium">{{ payroll.total_paid_leave_days }}</td>
                            </tr>
                            <tr class="bg-grey-lighten-4">
                                <td class="font-weight-bold">Total Deductions</td>
                                <td class="text-right font-weight-bold text-error">{{
                                    formatCurrency(payroll.total_deductions) }}
                                </td>
                            </tr>
                            <tr class="bg-grey-lighten-4">
                                <td class="font-weight-bold">Net Salary</td>
                                <td class="text-right font-weight-bold text-success">{{
                                    formatCurrency(payroll.net_salary) }}</td>
                            </tr>
                        </tbody>
                    </v-table>
                </v-card>

                <v-card v-if="canOverride.value || (auth.can('payroll.manage'))" rounded="lg" class="pa-6">
                    <v-row>
                        <v-col cols="12" md="6">
                            <v-btn block color="success" size="small" @click="markPaid"
                                :disabled="payroll.status === 'paid'">
                                {{ payroll.status === 'paid' ? 'Paid' : 'Mark as Paid' }}
                            </v-btn>
                        </v-col>
                        <v-col cols="12" md="6">
                            <v-btn block color="primary" size="small" class="mb-3" prepend-icon="mdi-download"
                                @click="downloadPayslip">
                                Download Payslip
                            </v-btn>
                            <v-btn block color="secondary" size="small" class="mb-3" prepend-icon="mdi-email" @click="sendPayslipEmail" :loading="sendingEmail">
                                Send Email
                            </v-btn>
                            <v-btn block color="warning" size="small" prepend-icon="mdi-pencil"
                                @click="openOverrideDialog">
                                Override Payslip
                            </v-btn>
                        </v-col>
                    </v-row>
                </v-card>
            </v-col>
        </v-row>

        <v-dialog v-model="overrideDialog" max-width="700">
            <v-card>
                <v-card-title>Override Payslip</v-card-title>
                <v-card-text>
                    <v-row>
                        <v-col cols="12" sm="6">
                            <v-text-field v-model="overrideForm.base_salary" label="Base Salary" type="number" />
                        </v-col>
                        <v-col cols="12" sm="6">
                            <v-text-field v-model="overrideForm.gross_salary" label="Gross Salary" type="number" />
                        </v-col>
                        <v-col cols="12" sm="6">
                            <v-text-field v-model="overrideForm.late_penalty" label="Late Penalty" type="number" />
                        </v-col>
                        <v-col cols="12" sm="6">
                            <v-text-field v-model="overrideForm.unpaid_leave_deduction" label="Unpaid Leave Deduction"
                                type="number" />
                        </v-col>
                        <v-col cols="12" sm="6">
                            <v-text-field v-model="overrideForm.paid_leave_deduction" label="Paid Leave Deduction"
                                type="number" />
                        </v-col>
                        <v-col cols="12" sm="6">
                            <v-text-field v-model="overrideForm.total_dedudtions" label="Total Deductions"
                                type="number" />
                        </v-col>
                        <v-col cols="12" sm="6">
                            <v-text-field v-model="overrideForm.net_salary" label="Net Salary" type="number" />
                        </v-col>
                        <v-col cols="12">
                            <v-textarea v-model="overrideForm.note" label="Note" rows="3" />
                        </v-col>
                    </v-row>
                </v-card-text>
                <v-card-actions>
                    <v-spacer />
                    <v-btn text @click="overrideDialog = false">Cancel</v-btn>
                    <v-btn color="warning" @click="saveOverride">Save Override</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

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
const overrideDialog = ref(false);
const overrideForm = ref({
    base_salary: 0,
    gross_salary: 0,
    total_deductions: 0,
    net_salary: 0,
    late_penalty: 0,
    unpaid_leave_deduction: 0,
    paid_leave_deduction: 0,
    note: '',
});
const canOverride = computed(() => auth.can('payroll.manage'));

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
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'MMK', minimumFractionDigits: 0 }).format(num);
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

function openOverrideDialog() {
    if (!payroll.value) return;

    overrideForm.value = {
        base_salary: payroll.value.base_salary || 0,
        gross_salary: payroll.value.gross_salary || 0,
        total_deductions: payroll.value.total_deductions || 0,
        net_salary: payroll.value.net_salary || 0,
        late_penalty: payroll.value.late_penalty || 0,
        unpaid_leave_deduction: payroll.value.unpaid_leave_deduction || 0,
        paid_leave_deduction: payroll.value.paid_leave_deduction || 0,
        note: payroll.value.note || '',
    };

    overrideDialog.value = true;
}

async function saveOverride() {
    if (!payroll.value) return;

    try {
        const { data } = await axios.put(`/api/payroll/${payroll.value.id}/override`, overrideForm.value);
        payroll.value = data.data;
        overrideDialog.value = false;
    } catch (error) {
        console.error('Override save failed', error);
    }
}

async function markPaid() {
    try {
        await axios.post(`/api/payroll/${payroll.value.id}/mark-paid`);
        payroll.value.status = 'paid';
        payroll.value.paid_at = new Date().toISOString();
    } catch {
    }
}

async function downloadPayslip() {
    if (!payroll.value) return;

    try {
        const url = `/api/payroll/${payroll.value.id}/payslip`;
        const response = await axios.get(url, { responseType: 'blob' });
        const blob = new Blob([response.data], { type: 'application/pdf' });
        const downloadUrl = window.URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = downloadUrl;
        link.download = `payslip_${payroll.value.id}.pdf`;
        document.body.appendChild(link);
        link.click();
        link.remove();
        window.URL.revokeObjectURL(downloadUrl);
    } catch (error) {
        console.error('Payslip download failed', error);
    }
}

const sendingEmail = ref(false); 

async function sendPayslipEmail() {
    if (!payroll.value) return;

    if (!confirm('Are you sure you want to send this payslip to ' + payroll.value.user.email + '?')) {
        return;
    }

    sendingEmail.value = true;
    try {
        await axios.post(`/api/payroll/${payroll.value.id}/send-email`);
        alert('Payslip sent successfully to ' + payroll.value.user.email);
    } catch (error) {
        console.error('Email sending failed', error);
        alert(error.response?.data?.message || 'Failed to send email');
    } finally {
        sendingEmail.value = false;
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
