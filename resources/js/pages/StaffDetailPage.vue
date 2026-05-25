<template>
    <div class="pa-4">
        <v-skeleton-loader v-if="loadingStaff" type="card, article" class="border rounded-lg" />
        
        <template v-else-if="staff">
            <v-card variant="outlined" class="rounded-lg mb-6 border-thin bg-white elevation-0">
                <v-card-text class="d-flex flex-wrap align-center pa-6 ga-4">
                    <v-avatar color="primary" size="72" class="elevation-1">
                        <span class="text-h4 text-white font-weight-bold">
                            {{ staff.name?.charAt(0).toUpperCase() }}
                        </span>
                    </v-avatar>
                    <div>
                        <div class="text-h5 font-weight-bold text-grey-darken-4 mb-1">{{ staff.name }}</div>
                        <div class="text-body-2 text-medium-emphasis mb-3 d-flex align-center">
                            <v-icon size="small" class="mr-1">mdi-email-outline</v-icon>{{ staff.email }}
                        </div>
                        <div class="d-flex flex-wrap ga-2">
                            <v-chip size="small" color="indigo" variant="tonal" prepend-icon="mdi-domain" class="font-weight-bold">
                                {{ staff.department?.name || 'No Department' }}
                            </v-chip>
                            <v-chip size="small" color="success" variant="tonal" prepend-icon="mdi-shield-account" class="font-weight-bold text-capitalize">
                                {{ staff.roles?.[0]?.name || 'Staff' }}
                            </v-chip>
                        </div>
                    </div>
                </v-card-text>
            </v-card>

            <v-tabs v-model="activeTab" color="primary" class="mb-4 border-b">
                <v-tab value="balance"><v-icon start>mdi-scale-balance</v-icon> Leave Balance</v-tab>
                <v-tab value="requests"><v-icon start>mdi-file-document-outline</v-icon> Leave Requests</v-tab>
                <v-tab value="attendance"><v-icon start>mdi-calendar-check</v-icon> Attendance Records</v-tab>
            </v-tabs>

            <v-window v-model="activeTab" class="pt-2">  
                
                <v-window-item value="balance">
                    <v-row v-if="leaveBalances.length > 0">
                        <v-col v-for="bal in leaveBalances" :key="bal.id" cols="12" sm="6" md="4">
                            <v-card variant="outlined" class="rounded-lg border-thin bg-white">
                                <v-card-text class="pa-4">
                                    <div class="text-subtitle-1 font-weight-bold text-primary mb-1">
                                        {{ bal.leave_rule?.name || 'Leave Type' }}
                                    </div>
                                    <v-divider class="my-2" />
                                    <div class="d-flex justify-space-between text-body-2 py-1">
                                        <span class="text-medium-emphasis">Total Allocated:</span>
                                        <span class="font-weight-bold">{{ bal.total_allowed_days }} Days</span>
                                    </div>
                                    <div class="d-flex justify-space-between text-body-2 py-1">
                                        <span class="text-medium-emphasis">Used / Taken:</span>
                                        <span class="font-weight-bold text-error">{{ bal.used_days }} Days</span>
                                    </div>
                                    <v-divider class="my-2" />
                                    <div class="d-flex justify-space-between text-subtitle-2 py-1.5 bg-grey-lighten-5 px-2 rounded">
                                        <span class="font-weight-bold text-grey-darken-2">Available Balance:</span>
                                        <span class="font-weight-bold text-success">{{ bal.total_allowed_days - bal.used_days }} Days</span>
                                    </div>
                                </v-card-text>
                            </v-card>
                        </v-col>
                    </v-row>

                    <v-card v-else variant="flat" class="rounded-lg bg-transparent py-12 text-center d-flex flex-column align-center justify-center">
                        <v-avatar color="blue-lighten-5" size="70" class="mb-3">
                            <v-icon size="36" color="blue-darken-1">mdi-scale-balance</v-icon>
                        </v-avatar>
                        <div class="text-subtitle-1 font-weight-bold text-grey-darken-3 mb-1">
                            No Leave Balance Allocated
                        </div>
                        <div class="text-body-2 text-medium-emphasis" style="max-width: 380px;">
                            There are no active leave allocation or balance records set up for this employee yet.
                        </div>
                    </v-card>
                </v-window-item>

                <v-window-item value="requests">
                    <v-card 
                        :variant="leaveRequests.length > 0 ? 'outlined' : 'flat'" 
                        :class="[leaveRequests.length > 0 ? 'rounded-lg border-thin elevation-0 bg-white' : 'bg-transparent']"
                    >
                        <v-data-table
                            :headers="leaveHeaders"
                            :items="leaveRequests"
                            :loading="loadingDetails"
                            density="comfortable"
                            class="bg-transparent text-none"
                            :hide-default-header="leaveRequests.length === 0" >
                            <template #[`item.id`]="{ item }">
                                <v-chip size="small" variant="tonal" class="font-weight-bold">#{{ item.id }}</v-chip>
                            </template>

                            <template #[`item.leave_rule`]="{ item }">
                                <span class="font-weight-medium text-grey-darken-4">
                                    {{ item.leave_rule?.name || 'N/A' }}
                                </span>
                            </template>

                            <template #[`item.duration`]="{ item }">
                                <div class="text-body-2 font-weight-medium text-grey-darken-3">
                                    <v-icon size="small" color="grey" class="mr-1">mdi-calendar-range</v-icon>
                                    {{ item.start_date?.substring(0,10) }} <span class="text-grey mx-1">→</span> {{ item.end_date?.substring(0,10) }}
                                </div>
                                <v-chip size="x-small" variant="flat" color="indigo-lighten-5" class="text-indigo text-capitalize font-weight-bold mt-1">
                                    {{ item.leave_session?.replace('_', ' ') }}
                                </v-chip>
                            </template>

                            <template #[`item.total_days`]="{ item }">
                                <span class="font-weight-bold text-primary">{{ item.total_days }} {{ item.total_days > 1 ? 'Days' : 'Day' }}</span>
                            </template>

                            <template #[`item.reason`]="{ item }">
                                <span v-if="item.reason" class="text-body-2 text-grey-darken-2">{{ item.reason }}</span>
                                <span v-else class="text-caption text-disabled font-italic">— No description provided</span>
                            </template>

                            <template #[`item.status`]="{ item }">
                                <v-chip :color="getStatusColor(item.status)" size="small" variant="flat" class="text-capitalize font-weight-bold px-3">
                                    {{ item.status?.replace('_', ' ') }}
                                </v-chip>
                            </template>

                            <template #no-data>
                                <div class="d-flex flex-column align-center justify-center py-12 px-4 text-center">
                                    <v-avatar color="indigo-lighten-5" size="70" class="mb-3">
                                        <v-icon size="36" color="primary">mdi-text-box-remove-outline</v-icon>
                                    </v-avatar>
                                    <div class="text-subtitle-1 font-weight-bold text-grey-darken-3 mb-1">
                                        No Leave History Found
                                    </div>
                                    <div class="text-body-2 text-medium-emphasis" style="max-width: 360px;">
                                        This employee hasn't submitted any leave request application records yet.
                                    </div>
                                </div>
                            </template>
                        </v-data-table>
                    </v-card>
                </v-window-item>

                <v-window-item value="attendance">
                    <v-card 
                        :variant="attendances.length > 0 ? 'outlined' : 'flat'" 
                        :class="[attendances.length > 0 ? 'rounded-lg border-thin elevation-0 bg-white' : 'bg-transparent']"
                    >
                        <v-data-table
                            :headers="attendanceHeaders"
                            :items="attendances"
                            :loading="loadingDetails"
                            density="comfortable"
                            class="bg-transparent text-none"
                            :hide-default-header="attendances.length === 0" >
                            <template #[`item.date`]="{ item }">
                                <span class="font-weight-bold text-grey-darken-3">{{ item.date }}</span>
                            </template>
                            
                            <template #[`item.check_in`]="{ item }">
                                <v-chip size="small" color="success" variant="tonal" prepend-icon="mdi-login" v-if="item.check_in" class="font-weight-medium">
                                    {{ item.check_in }}
                                </v-chip>
                                <span v-else class="text-disabled">—</span>
                            </template>
                            
                            <template #[`item.check_out`]="{ item }">
                                <v-chip size="small" color="warning" variant="tonal" prepend-icon="mdi-logout" v-if="item.check_out" class="font-weight-medium">
                                    {{ item.check_out }}
                                </v-chip>
                                <span v-else class="text-disabled">—</span>
                            </template>

                            <template #[`item.work_hours`]="{ item }">
                                <span v-if="item.work_hours" class="font-weight-bold text-grey-darken-4">{{ item.work_hours }} hrs</span>
                                <span v-else class="text-disabled">-</span>
                            </template>
                            
                            <template #[`item.status`]="{ item }">
                                <v-chip :color="item.status === 'present' ? 'success' : (item.status === 'absent' ? 'error' : 'warning')" size="small" variant="flat" class="text-capitalize font-weight-bold px-3">
                                    {{ item.status }}
                                </v-chip>
                            </template>

                            <template #no-data>
                                <div class="d-flex flex-column align-center justify-center py-12 px-4 text-center">
                                    <v-avatar color="amber-lighten-5" size="70" class="mb-3">
                                        <v-icon size="36" color="amber-darken-2">mdi-calendar-blank-outline</v-icon>
                                    </v-avatar>
                                    <div class="text-subtitle-1 font-weight-bold text-grey-darken-3 mb-1">
                                        No Attendance Tracked
                                    </div>
                                    <div class="text-body-2 text-medium-emphasis" style="max-width: 360px;">
                                        There are no check-in, check-out or working shift logs generated for this month.
                                    </div>
                                </div>
                            </template>
                        </v-data-table>
                    </v-card>
                </v-window-item>
            </v-window>
        </template>
    </div>
</template>

<script setup>
    import { ref, onMounted, watch } from 'vue';
    import { useRoute, useRouter } from 'vue-router';
    import axios from 'axios';

    const route = useRoute();
    const router = useRouter();

    const staffId = route.params.user; 
    const activeTab = ref('balance');

    const staff = ref(null);
    const leaveBalances = ref([]);
    const leaveRequests = ref([]);
    const attendances = ref([]);

    const loadingStaff = ref(true);
    const loadingDetails = ref(false);

    const leaveHeaders = [
        { title: 'ID', key: 'id', width: '80px' },
        { title: 'Leave Type', key: 'leave_rule' }, 
        { title: 'Duration Period', key: 'duration' },
        { title: 'Total', key: 'total_days', align: 'center' },
        { title: 'Reason Description', key: 'reason', sortable: false },
        { title: 'Status', key: 'status', align: 'center' },
    ];

    const attendanceHeaders = [
        { title: 'Working Date', key: 'date' },
        { title: 'Clock In', key: 'check_in', align: 'center' },
        { title: 'Clock Out', key: 'check_out', align: 'center' },
        { title: 'Hours worked', key: 'work_hours', align: 'center' },
        { title: 'Attendance Status', key: 'status', align: 'center' },
    ];

    async function fetchStaffProfile() {
        loadingStaff.value = true;
        try {
            const { data } = await axios.get(`/api/staff/${staffId}`);
            staff.value = data.data || data;
        } catch (e) {
            console.error('Profile fetch failed:', e);
        } finally {
            loadingStaff.value = false;
        }
    }

    async function fetchTabData() {
        loadingDetails.value = true;
        try {
            if (activeTab.value === 'balance') {
                const { data } = await axios.get(`/api/staff/${staffId}/leave-balances`);
                leaveBalances.value = data.data || data || [];
            } else if (activeTab.value === 'requests') {
                const { data } = await axios.get(`/api/staff/${staffId}/leave-requests`);
                leaveRequests.value = data.data || data || [];
            } else if (activeTab.value === 'attendance') {
                const { data } = await axios.get(`/api/staff/${staffId}/attendances`);
                attendances.value = data.data || data || [];
            }
        } catch (e) {
            console.error(`Tab fetch data error:`, e);
        } finally {
            loadingDetails.value = false;
        }
    }

    function getStatusColor(s) {
        return s === 'approved' ? 'success' : (s === 'rejected' ? 'error' : (s === 'pending_hr' ? 'info' : 'warning'));
    }
    
    watch(activeTab, () => {
        fetchTabData();
    });

    onMounted(() => {
        fetchStaffProfile();
        fetchTabData();
    });
</script>