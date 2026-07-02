<template>
    <div>
        <v-row class="mb-4">
            <v-col cols="12">
                <div class="d-flex align-center justify-space-between flex-wrap ga-2">
                    <div>
                        <div class="text-h4 font-weight-bold mb-1">Payroll</div>
                        <div class="text-body-1 text-medium-emphasis">Calculate and manage employee payroll</div>
                    </div>
                    <v-btn
                        v-if="auth.can('payroll.manage')"
                        color="primary"
                        prepend-icon="mdi-cog-outline"
                        variant="tonal"
                        @click="openSettings"
                    >
                        Settings
                    </v-btn>
                </div>
            </v-col>
        </v-row>

        <v-row>
            <v-col v-for="stat in statCards" :key="stat.key" cols="12" sm="6" md="3">
                <v-card rounded="lg" class="pa-4 h-100">
                    <div class="d-flex align-center justify-space-between">
                        <div>
                            <div class="text-caption text-medium-emphasis text-uppercase">{{ stat.label }}</div>
                            <div class="text-h5 font-weight-semibold mt-1">{{ stat.value }}</div>
                        </div>
                        <v-icon :color="stat.color" size="32">{{ stat.icon }}</v-icon>
                    </div>
                </v-card>
            </v-col>
        </v-row>

        <v-card rounded="lg" class="mt-4 pa-6">
           <v-row align="center" class="ga-0">
                <v-col cols="6" md="2">
                    <v-select
                        v-model="filters.employee"
                        :items="employees"
                        item-title="name"
                        item-value="id"
                        label="Select Employee"
                        clearable
                        density="compact"
                        variant="outlined"
                        hide-details
                    >
                    </v-select>
                </v-col>

                <v-col cols="6" md="2">
                    <v-select
                        v-model="filters.periodType"
                        :items="periodOptions"
                        label="Period"
                        density="compact"
                        variant="outlined"
                        hide-details
                    />
                </v-col>

                <v-col cols="6" md="2" v-if="filters.periodType === 'monthly'">
                    <v-select v-model="filters.month" :items="monthOptions" label="Month" density="compact" variant="outlined" hide-details />
                </v-col>
                <v-col cols="6" md="2" v-if="filters.periodType === 'monthly'">
                    <v-select v-model="filters.year" :items="yearOptions" label="Year" density="compact" variant="outlined" hide-details />
                </v-col>
                <v-col cols="12" md="2" v-if="filters.periodType === 'daily'">
                    <v-text-field v-model="filters.date" type="date" label="Date" density="compact" variant="outlined" hide-details />
                </v-col>

                <v-col cols="12" md="2">
                    <v-btn
                        v-if="auth.can('payroll.manage')"
                        color="primary"
                        prepend-icon="mdi-calculator"
                        :loading="calculating"
                        :disabled="!canCalculate"
                        @click="calculatePayroll"
                        height="40"
                    >
                        Calculate
                    </v-btn>
                </v-col>

                <v-col cols="12" md=""2>
                    <v-text-field
                        v-model="search"
                        prepend-inner-icon="mdi-magnify"
                        placeholder="Search ..."
                        density="compact"
                        variant="outlined"
                        hide-details
                    />
                </v-col>
            </v-row>

            <v-data-table
                :headers="tableHeaders"
                :items="payrolls"
                :loading="loading"
                :items-per-page="perPage"
                item-value="id"
                class="elevation-0"
                @click:row="goToDetail"
            >
                <template #item.user.name="{ item }">
                    <div class="font-weight-medium">{{ item.user?.name }}</div>
                    <div class="text-caption text-medium-emphasis">{{ item.user?.department?.name }}</div>
                </template>
                <template #item.base_salary="{ value }">{{ formatCurrency(value) }}</template>
                <template #item.gross_salary="{ value }">{{ formatCurrency(value) }}</template>
                <template #item.total_deductions="{ value }">
                    <span class="text-error">{{ formatCurrency(value) }}</span>
                </template>
                <template #item.net_salary="{ value }">
                    <span class="text-success font-weight-bold">{{ formatCurrency(value) }}</span>
                </template>
                <template #item.status="{ value }">
                    <v-chip :color="statusColor(value)" size="small">{{ value }}</v-chip>
                </template>
                <template #item.period_type="{ value }">
                    <v-chip size="small" variant="tonal">{{ value }}</v-chip>
                </template>
                <template #item.actions="{ item }">
                    <v-btn
                        v-if="item.status === 'calculated' && auth.can('payroll.manage')"
                        size="small"
                        color="success"
                        variant="tonal"
                        prepend-icon="mdi-check"
                        @click.stop="markPaid(item)"
                    >
                        Mark Paid
                    </v-btn>
                   <v-btn
                    size="small"
                    color="primary"
                    variant="text"
                    prepend-icon="mdi-eye"
                    @click.stop="goToDetail($event, { item })"
                >
                    View
                </v-btn>
                </template>
            </v-data-table>
        </v-card>

        <v-dialog v-model="settingsDialog" max-width="520">
            <v-card rounded="lg">
                <v-card-title class="text-h5 font-weight-bold pa-6">Payroll Settings</v-card-title>
                <v-card-text class="pa-6 pt-0">
                    <v-row>
                        <v-col cols="12">
                            <v-text-field
                                v-model.number="settings.daily_work_minutes"
                                type="number"
                                label="Daily Work Minutes"
                                hint="Total work minutes per day (e.g., 570 for 9.5h)"
                                persistent-hint
                            />
                        </v-col>
                        <v-col cols="12">
                            <v-text-field
                                v-model.number="settings.late_penalty_rate"
                                type="number"
                                step="1"
                                label="Late Penalty Amount per 30 min"
                                hint="Amount in MMK to deduct for each 30 minutes late"
                                persistent-hint
                            />
                        </v-col>
                        <v-col cols="12">
                            <v-text-field
                                v-model.number="settings.paid_leave_deduction_rate"
                                type="number"
                                step="0.01"
                                label="Paid Leave Deduction Rate"
                                hint="0 = fully paid, 0.5 = half deducted, 1 = fully deducted"
                                persistent-hint
                            />
                        </v-col>
                        <v-col cols="12">
                            <v-text-field
                                v-model.number="settings.days_per_month"
                                type="number"
                                label="Days per Month"
                                hint="Standard days used for daily rate calculation"
                                persistent-hint
                            />
                        </v-col>
                    </v-row>
                </v-card-text>
                <v-card-actions class="pa-6 pt-0">
                    <v-spacer />
                    <v-btn variant="text" @click="settingsDialog = false">Cancel</v-btn>
                    <v-btn color="primary" :loading="savingSettings" @click="saveSettings">Save</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { useAuthStore } from '@/stores/auth';

const router = useRouter();
const auth = useAuthStore();

const loading = ref(false);
const calculating = ref(false);
const savingSettings = ref(false);
const settingsDialog = ref(false);
const search = ref('');
const perPage = ref(15);
const payrolls = ref([]);
const employees = ref([]);
const stats = ref({
    total_payroll: 0,
    total_deductions: 0,
    total_late_penalties: 0,
    total_leave_deductions: 0,
    calculated_count: 0,
    pending_count: 0,
});

const settings = ref({
    daily_work_minutes: 570,
    late_penalty_rate: 1.0,
    paid_leave_deduction_rate: 0,
    days_per_month: 30,
});

const filters = ref({
    employee: null,
    periodType: 'monthly',
    month: new Date().getMonth() + 1,
    year: new Date().getFullYear(),
    date: new Date().toISOString().split('T')[0],
});

const periodOptions = [
    { title: 'Monthly', value: 'monthly' },
    { title: 'Daily', value: 'daily' },
];

const monthOptions = [
    { title: 'January', value: 1 },
    { title: 'February', value: 2 },
    { title: 'March', value: 3 },
    { title: 'April', value: 4 },
    { title: 'May', value: 5 },
    { title: 'June', value: 6 },
    { title: 'July', value: 7 },
    { title: 'August', value: 8 },
    { title: 'September', value: 9 },
    { title: 'October', value: 10 },
    { title: 'November', value: 11 },
    { title: 'December', value: 12 },
];

const yearOptions = Array.from({ length: 5 }, (_, i) => new Date().getFullYear() - 2 + i);

const canCalculate = computed(() => filters.value.employee);

const statCards = computed(() => [
    { key: 'total', label: 'Total Payroll', value: formatCurrency(stats.value.total_payroll), icon: 'mdi-cash-multiple', color: 'primary' },
    { key: 'deductions', label: 'Total Deductions', value: formatCurrency(stats.value.total_deductions), icon: 'mdi-arrow-down-bold', color: 'error' },
    { key: 'late', label: 'Late Penalties', value: formatCurrency(stats.value.total_late_penalties), icon: 'mdi-clock-alert', color: 'warning' },
    { key: 'leave', label: 'Leave Deductions', value: formatCurrency(stats.value.total_leave_deductions), icon: 'mdi-calendar-clock', color: 'info' },
]);

const tableHeaders = [
    { title: 'Employee', key: 'user.name', sortable: false },
    { title: 'Period', key: 'period_type' },
    { title: 'Start', key: 'period_start' },
    { title: 'End', key: 'period_end' },
    { title: 'Base Salary', key: 'base_salary', align: 'end' },
    { title: 'Late Mins', key: 'total_late_minutes', align: 'end' },
    { title: 'Unpaid Leave', key: 'total_unpaid_leave_days', align: 'end' },
    { title: 'Deductions', key: 'total_deductions', align: 'end' },
    { title: 'Net Salary', key: 'net_salary', align: 'end' },
    { title: 'Status', key: 'status' },
    { title: 'Actions', key: 'actions', sortable: false },
];

function formatCurrency(value) {
    const num = Number(value) || 0;
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'MMK', minimumFractionDigits: 0 }).format(num);
}

function statusColor(status) {
    switch (status) {
        case 'calculated': return 'success';
        case 'paid': return 'primary';
        case 'draft': return 'warning';
        default: return 'grey';
    }
}

async function fetchPayrolls() {
    loading.value = true;
    try {
        const params = { per_page: perPage.value };
        if (search.value) params.search = search.value;

        const { data } = await axios.get('/api/payroll', { params });
        payrolls.value = data.data ?? [];
    } finally {
        loading.value = false;
    }
}

async function fetchStats() {
    try {
        const { data } = await axios.get('/api/payroll/stats');
        stats.value = data.data ?? stats.value;
    } catch {
        // ignore
    }
}

async function fetchEmployees() {
    try {
        const { data } = await axios.get('/api/staff-dropdown');
        
        const allEmployeesOption = {
            id: 'all', 
            name: 'All Employees',
            department: { name: 'Company-wide' } 
        };

        employees.value = [allEmployeesOption, ...(data ?? [])];
        
    } catch {
        employees.value = [];
    }
    console.log(employees.value);
}

async function fetchSettings() {
    try {
        const { data } = await axios.get('/api/payroll/settings');
        settings.value = { ...settings.value, ...data.data };
    } catch {
        // ignore
    }
}

async function calculatePayroll() {
    if (!canCalculate.value) return;
    calculating.value = true;
    try {
        const payload = {
            user_id: filters.value.employee === 'all' ? null : filters.value.employee,
            period_type: filters.value.periodType,
        };

        if (payload.period_type === 'monthly') {
            payload.year = filters.value.year;
            payload.month = filters.value.month;
        } else {
            payload.date = filters.value.date;
        }

        await axios.post('/api/payroll/calculate', payload);
        await fetchPayrolls();
        await fetchStats();
    } catch (error) {
        if (error.response?.status === 422) {
            console.error('Validation Errors:', error.response.data.errors);
            alert('Please check your inputs: ' + JSON.stringify(error.response.data.errors));
        }
    } finally {
        calculating.value = false;
    }
}

async function markPaid(item) {
    try {
        await axios.post(`/api/payroll/${item.id}/mark-paid`);
        await fetchPayrolls();
        await fetchStats();
    } catch {
        // ignore
    }
}

function openSettings() {
    fetchSettings();
    settingsDialog.value = true;
}

async function saveSettings() {
    savingSettings.value = true;
    try {
        await axios.put('/api/payroll/settings', settings.value);
        settingsDialog.value = false;
    } finally {
        savingSettings.value = false;
    }
}

function goToDetail(_event, { item }) {
    router.push({ name: 'payroll.detail', params: { id: item.id } });
}

watch(search, () => { fetchPayrolls(); });

onMounted(() => {
    fetchPayrolls();
    fetchStats();
    fetchEmployees();
});
</script>
