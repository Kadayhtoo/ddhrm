<template>
    <div class="pa-4">
        <v-skeleton-loader v-if="loadingStaff" type="card, article" class="border rounded-lg" />
        
        <template v-else-if="staff">
            <v-card variant="outlined" class="rounded-lg mb-6 border-thin bg-white elevation-0">
                <v-card-text class="d-flex flex-wrap align-center justify-space-between pa-6 ga-4">
                    
                    <div class="d-flex align-center ga-4">
                        <v-avatar color="primary" size="72" class="elevation-1">
                            <v-img
                                v-if="staff.profile_image"
                                :src="'/storage/' + staff.profile_image"
                                alt="Profile"
                                cover
                            />
                            <span v-else class="text-h4 text-white font-weight-bold">
                                {{ staff.name?.charAt(0).toUpperCase() }}
                            </span>
                        </v-avatar>
                        
                        <div>
                            <div class="text-h5 font-weight-bold text-grey-darken-4 mb-1">{{ staff.name }}</div>
                            <div class="text-body-2 text-medium-emphasis mb-3 d-flex align-center">
                                <v-icon size="small" class="mr-1" color="grey-darken-1">mdi-email-outline</v-icon>
                                {{ staff.email }}
                            </div>
                            <div class="d-flex flex-wrap ga-2">
                                <v-chip size="small" color="indigo" variant="tonal" prepend-icon="mdi-domain" class="font-weight-bold">
                                    {{ staff.department?.name || 'No Department' }}
                                </v-chip>
                                <v-chip size="small" color="indigo" variant="tonal" prepend-icon="mdi-domain" class="font-weight-bold">
                                   Salary ~ {{ staff.salary || 'N/A' }} MMK
                                </v-chip>
                                <v-chip size="small" color="success" variant="tonal" prepend-icon="mdi-shield-account" class="font-weight-bold text-capitalize">
                                    {{ staff.roles?.[0]?.name || 'Staff' }}
                                </v-chip>
                            </div>
                        </div>
                    </div>

                    <div>
                        <v-btn
                            color="primary"
                            variant="flat"
                            prepend-icon="mdi-file-plus-outline"
                            @click="documentDialog = true"
                            class="ml-auto"
                        >
                            Add Document
                        </v-btn>
                    </div>
                </v-card-text>
            </v-card>

            <v-row class="mb-4" align="center">
                <v-col cols="12" sm="6" md="4">
                    <v-select
                        v-model="selectedYear"
                        :items="yearOptions"
                        label="Filter By Calendar Year"
                        density="comfortable"
                        variant="outlined"
                        prepend-inner-icon="mdi-calendar-filter"
                        hide-details
                        class="bg-white rounded-lg"
                    ></v-select>
                </v-col>
                <v-col cols="12" sm="6" md="8" class="text-sm-right">
                    <span class="text-body-2 text-medium-emphasis font-weight-medium">
                        Showing results for year: <v-chip size="small" color="primary" class="font-weight-bold px-3">{{ selectedYear }}</v-chip>
                    </span>
                </v-col>
            </v-row>

            <v-tabs v-model="activeTab" color="primary" class="mb-4 border-b">
                <v-tab value="balance"><v-icon start>mdi-scale-balance</v-icon> Leave Balance</v-tab>
                <v-tab value="requests"><v-icon start>mdi-file-document-outline</v-icon> Leave Requests</v-tab>
                <v-tab value="attendance"><v-icon start>mdi-calendar-check</v-icon> Attendance Records</v-tab>
                <v-tab value="documents"><v-icon start>mdi-file-document-multiple-outline</v-icon> Documents</v-tab>
            </v-tabs>

            <v-window v-model="activeTab" class="pt-2">  
                
                <v-window-item value="balance">
                    <v-card 
                        v-if="leaveBalances.length > 0"
                        :loading="loadingDetails"
                        variant="outlined" 
                        class="rounded-lg border-thin elevation-0 bg-white"
                    >
                        <v-table density="comfortable">
                            <thead>
                                <tr class="bg-grey-lighten-4">
                                    <th class="text-subtitle-2 font-weight-bold">Leave Type</th>
                                    <th class="text-subtitle-2 font-weight-bold text-center">Total Allowed</th>
                                    <th class="text-subtitle-2 font-weight-bold text-center">Used</th>
                                    <th class="text-subtitle-2 font-weight-bold text-center">Available Balance</th>
                                    <th class="text-subtitle-2 font-weight-bold text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="bal in leaveBalances" :key="bal.id">
                                    <td class="font-weight-bold text-primary text-capitalize">
                                        {{ bal.leave_rule?.name || 'Leave Type' }}
                                    </td>
                                    <td class="text-center font-weight-semibold">
                                        {{ bal.total_allowed_days }} Days
                                    </td>
                                    <td class="text-center">
                                        <v-chip size="x-small" :color="bal.used_days > 0 ? 'error' : 'grey'" variant="flat" class="font-weight-bold">
                                            {{ bal.used_days }} Days
                                        </v-chip>
                                    </td>
                                    <td class="text-center font-weight-bold text-success text-subtitle-1">
                                        {{ bal.total_allowed_days - bal.used_days }} Days
                                    </td>
                                    <td class="text-center">
                                        <v-btn
                                            size="x-small"
                                            color="info"
                                            variant="tonal"
                                            prepend-icon="mdi-eye-outline"
                                            class="font-weight-bold"
                                            @click="openLeaveDetail(bal)"
                                        >
                                            View Detail
                                        </v-btn>
                                    </td>
                                </tr>
                            </tbody>
                        </v-table>
                    </v-card>

                    <div v-else-if="!loadingDetails" class="d-flex flex-column align-center justify-center py-12 px-4 text-center">
                        <v-avatar color="red-lighten-5" size="70" class="mb-3">
                            <v-icon size="36" color="error">mdi-scale-balance</v-icon>
                        </v-avatar>
                        <div class="text-subtitle-1 font-weight-bold text-grey-darken-3 mb-1">
                            No Leave Balance Allocated for {{ selectedYear }}
                        </div>
                        <div class="text-body-2 text-medium-emphasis mx-auto px-4" style="max-width: 420px;">
                            There are no active leave allocation records assigned to this employee for the selected year <strong class="text-grey-darken-4">{{ selectedYear }}</strong>.
                        </div>
                    </div>
                    
                    <div v-if="loadingDetails" class="d-flex justify-center py-12">
                        <v-progress-circular indeterminate color="primary"></v-progress-circular>
                    </div>

                    <v-dialog v-model="detailDialog" max-width="700px">
                        <v-card rounded="lg" class="pa-2">
                            <v-card-title class="d-flex justify-space-between align-center font-weight-bold text-h6 text-grey-darken-4">
                                <span>
                                    <v-icon color="primary" class="mr-1">mdi-calendar-check</v-icon> 
                                    {{ selectedLeaveType }} - Approved History ({{ selectedYear }})
                                </span>
                                <v-btn icon="mdi-close" variant="text" size="small" @click="detailDialog = false"></v-btn>
                            </v-card-title>
                            <v-divider></v-divider>
                            
                            <v-card-text class="pa-4">
                                <v-row v-if="loadingApprovedDetail" justify="center" class="my-4">
                                    <v-progress-circular indeterminate color="primary"></v-progress-circular>
                                </v-row>
                                
                                <v-table v-else density="comfortable" class="border rounded-lg">
                                    <thead>
                                        <tr class="bg-grey-lighten-4">
                                            <th class="text-subtitle-2 font-weight-bold">Duration Period</th>
                                            <th class="text-subtitle-2 font-weight-bold text-center">Session</th>
                                            <th class="text-subtitle-2 font-weight-bold text-center">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="req in filteredApprovedRequests" :key="req.id">
                                            <td class="font-weight-medium py-2">
                                                <v-icon size="small" color="grey" class="mr-1">mdi-calendar-range</v-icon>
                                                {{ req.start_date?.substring(0,10) }} <span class="text-grey mx-1">→</span> {{ req.end_date?.substring(0,10) }}
                                            </td>
                                            <td class="text-center text-capitalize">
                                                <v-chip size="x-small" variant="flat" color="indigo-lighten-5" class="text-indigo font-weight-bold">
                                                    {{ req.leave_session?.replace('_', ' ') || 'Full Day' }}
                                                </v-chip>
                                            </td>
                                            <td class="text-center font-weight-bold text-primary">
                                                {{ req.total_days }} {{ req.total_days > 1 ? 'Days' : 'Day' }}
                                            </td>
                                        </tr>
                                        <tr v-if="filteredApprovedRequests.length === 0">
                                            <td colspan="4" class="text-center text-medium-emphasis py-8 font-italic">
                                                No approved leave consumption logs found for this type in year {{ selectedYear }}.
                                            </td>
                                        </tr>
                                    </tbody>
                                </v-table>
                            </v-card-text>
                        </v-card>
                    </v-dialog>
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
                                        No Leave History Applications Found
                                    </div>
                                    <div class="text-body-2 text-medium-emphasis mx-auto" style="max-width: 400px;">
                                        This staff member has not submitted or processed any leave request applications during the calendar year <strong class="text-primary">{{ selectedYear }}</strong>.
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
                                        <v-icon size="36" color="amber-darken-3">mdi-calendar-blank-outline</v-icon>
                                    </v-avatar>
                                    <div class="text-subtitle-1 font-weight-bold text-grey-darken-3 mb-1">
                                        No Shift Attendance Logs Generated
                                    </div>
                                    <div class="text-body-2 text-medium-emphasis mx-auto" style="max-width: 400px;">
                                        There are no recorded punch logs, biometric check-ins, or shift duties detected for this profile in <strong class="text-amber-darken-4">{{ selectedYear }}</strong>.
                                    </div>
                                </div>
                            </template>
                        </v-data-table>
                    </v-card>
                </v-window-item>

                <v-window-item value="documents">
                    <v-alert v-if="errorMessage" type="error" density="compact" variant="tonal" class="mb-4">
                        {{ errorMessage }}
                    </v-alert>

                    <v-card variant="outlined" class="rounded-lg border-thin elevation-0 bg-white pa-4">
                        <v-row v-if="Object.keys(staffDocuments).length > 0">
                        <v-col v-for="(path, type) in staffDocuments" :key="type" cols="6" md="3">
                            <v-card class="border-thin" variant="outlined">
                                <v-card-text class="pa-3">
                                    <div class="text-caption font-weight-bold text-uppercase text-primary mb-2">
                                        {{ type.replace('_', ' ') }}
                                    </div>

                                    <div v-if="documentImages[type]">
                                        <div
                                            v-if="documentImages[type]?.mimeType?.toLowerCase().includes('pdf')"
                                            class="text-center py-4 rounded border border-error-lighten-3 bg-error-lighten-5"
                                            @click="openPreview(documentImages[type], 'pdf')"
                                            style="cursor: pointer;"
                                        >
                                            <v-icon size="64" color="error">mdi-file-pdf-box</v-icon>
                                            <div class="text-caption font-weight-bold mt-2 text-error-darken-2">View PDF</div>
                                            <div class="text-caption mt-1 text-medium-emphasis">Open document preview</div>
                                        </div>

                                        <v-img 
                                            v-else-if="documentImages[type]?.mimeType?.startsWith('image/')"
                                            :src="documentImages[type].url" 
                                            height="150" 
                                            cover 
                                            class="rounded mb-2 border" 
                                            @click="openPreview(documentImages[type], 'image')"
                                            style="cursor: pointer;"
                                        ></v-img>

                                        <div v-else class="text-center py-6 rounded border border-dashed" @click="openPreview(documentImages[type], 'file')">
                                            <v-icon size="48" color="primary">mdi-file-document-outline</v-icon>
                                            <div class="text-caption mt-2">Open file</div>
                                        </div>
                                    </div>
                                    
                                    <v-skeleton-loader v-else type="image" height="150" class="rounded mb-2" />

                                    <div class="d-flex ga-1">
                                        <v-btn size="x-small" variant="text" color="warning" @click="updateDocument(type)">Change</v-btn>
                                        <v-btn size="x-small" variant="text" color="error" icon="mdi-delete" @click="deleteDocument(type)"></v-btn>
                                    </div>
                                </v-card-text>
                            </v-card>
                        </v-col>
                        </v-row>
                        <div v-else class="text-center py-10 text-grey">No documents uploaded yet.</div>
                    </v-card>
                </v-window-item>
            </v-window>

            <v-dialog v-model="documentDialog" max-width="500px">
                <v-card class="rounded-lg">
                    <v-toolbar color="primary" density="compact" flat>
                        <v-toolbar-title class="text-subtitle-1">Document Upload</v-toolbar-title>
                        <v-spacer></v-spacer>
                        <v-btn icon="mdi-close" @click="documentDialog = false" size="small"></v-btn>
                    </v-toolbar>

                    <v-card-text class="pa-6">
                        <v-form @submit.prevent="submitAllDocuments">
                            <v-row dense>
                                <v-col cols="12">
                                    <p class="text-caption text-grey-darken-1 mb-2">Select files to upload for this staff member:</p>
                                </v-col>
                                
                                <v-col cols="12">
                                    <v-file-input v-model="docForm.nrc_front" label="NRC Front" variant="outlined" density="compact" prepend-icon="mdi-card-account-details-outline"></v-file-input>
                                </v-col>
                                <v-col cols="12">
                                    <v-file-input v-model="docForm.nrc_back" label="NRC Back" variant="outlined" density="compact" prepend-icon="mdi-card-account-details-outline"></v-file-input>
                                </v-col>
                                <v-col cols="12">
                                    <v-file-input v-model="docForm.household_front" label="Household Front" variant="outlined" density="compact" prepend-icon="mdi-home-account"></v-file-input>
                                </v-col>
                                <v-col cols="12">
                                    <v-file-input
                                        v-model="docForm.cv"
                                        label="CV File"
                                        variant="outlined"
                                        density="compact"
                                        prepend-icon="mdi-file-document-multiple-outline"
                                        accept="application/pdf,image/*"
                                    ></v-file-input>
                                </v-col>
                            </v-row>
                        </v-form>
                    </v-card-text>

                    <v-card-actions class="pa-4 pt-0">
                        <v-spacer></v-spacer>
                        <v-btn variant="text" @click="documentDialog = false">Cancel</v-btn>
                        <v-btn 
                            color="primary" 
                            variant="flat" 
                            @click="submitAllDocuments" 
                            :loading="uploading"
                            class="px-6"
                        >
                            Upload Documents
                        </v-btn>
                    </v-card-actions>
                </v-card>
            </v-dialog>
            <v-dialog v-model="editDialog" max-width="450px">
                <v-card>
                    <v-toolbar color="primary" density="compact">
                        <v-toolbar-title class="text-subtitle-1">Update {{ editingType?.replace('_', ' ') }}</v-toolbar-title>
                    </v-toolbar>
                    
                    <v-card-text class="pt-4">
                        <v-alert density="compact" variant="tonal" type="info" class="mb-4 text-caption">
                            Select a new file to replace your existing document.
                        </v-alert>
                        
                        <v-file-input 
                            v-model="fileToUpload" 
                            label="Choose new file" 
                            prepend-icon="mdi-paperclip"
                            variant="outlined"
                            density="comfortable"
                            chips
                            show-size
                        ></v-file-input>
                    </v-card-text>
                    
                    <v-card-actions class="pa-4">
                        <v-spacer></v-spacer>
                        <v-btn variant="text" @click="editDialog = false">Cancel</v-btn>
                        <v-btn color="primary" variant="flat" @click="confirmUpdate" :loading="uploading">
                            Update Document
                        </v-btn>
                    </v-card-actions>
                </v-card>
            </v-dialog>
            <v-dialog v-model="previewDialog" max-width="900px">
                <v-card>
                    <v-toolbar color="primary" density="compact">
                        <v-toolbar-title>{{ previewType === 'pdf' ? 'PDF Preview' : 'Image Preview' }}</v-toolbar-title>
                        <v-spacer></v-spacer>
                        <v-btn icon="mdi-close" @click="previewDialog = false"></v-btn>
                    </v-toolbar>
                    <v-card-text class="pa-0">
                        <iframe
                            v-if="previewType === 'pdf' && previewUrl"
                            :src="previewUrl"
                            style="width: 100%; height: 75vh; border: 0;"
                        >
                    
                        </iframe>
                        <v-img v-else-if="previewUrl" :src="previewUrl" contain max-height="75vh"></v-img>
                    </v-card-text>
                </v-card>
            </v-dialog>
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

    const currentYear = new Date().getFullYear(); 

    const selectedYear = ref(currentYear);
    const yearOptions = ref(Array.from({ length: 5 }, (_, i) => currentYear - i));
    const staff = ref(null);
    const leaveBalances = ref([]);
    const leaveRequests = ref([]);
    const attendances = ref([]);

    const loadingStaff = ref(true);
    const loadingDetails = ref(false);

    const detailDialog = ref(false);
    const selectedLeaveType = ref('');
    const filteredApprovedRequests = ref([]);
    const loadingApprovedDetail = ref(false);
    const currentActiveBalanceItem = ref(null); 

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
            const params = { year: selectedYear.value };

            if (activeTab.value === 'balance') {
                const { data } = await axios.get(`/api/staff/${staffId}/leave-balances`, { params });
                leaveBalances.value = data.data || [];
                
            } else if (activeTab.value === 'requests') {
                const { data } = await axios.get(`/api/staff/${staffId}/leave-requests`, { params });
                leaveRequests.value = data.data?.data || data.data || [];
                
            } else if (activeTab.value === 'attendance') {
                const { data } = await axios.get(`/api/staff/${staffId}/attendances`, { params });
                attendances.value = data.data?.data || data.data || [];

            }else if (activeTab.value === 'documents') {
                await fetchDocuments(); 
            }
        } catch (e) {
            console.error(`Tab fetch data error:`, e);
            leaveBalances.value = [];
        } finally {
            loadingDetails.value = false;
        }
    }

    async function openLeaveDetail(balanceItem) {
        currentActiveBalanceItem.value = balanceItem; 
        selectedLeaveType.value = balanceItem.leave_rule?.name || 'Leave Type';
        detailDialog.value = true;
        loadingApprovedDetail.value = true;
        
        try {
            const { data } = await axios.get(`/api/staff/${staffId}/leave-requests`, {
                params: { year: selectedYear.value }
            });
            const allRequests = data.data?.data || data.data || [];
            
            filteredApprovedRequests.value = allRequests.filter(req => {
                const reqYear = req.year || req.start_date?.substring(0, 4);
                return req.leave_rule_id === balanceItem.leave_rule_id && 
                       req.status === 'approved' && 
                       String(reqYear) === String(selectedYear.value);
            });
        } catch (error) {
            console.error("Error filtering approved requests:", error);
        } finally {
            loadingApprovedDetail.value = false;
        }
    }

    function getStatusColor(s) {
        return s === 'approved' ? 'success' : (s === 'rejected' ? 'error' : (s === 'pending_hr' ? 'info' : 'warning'));
    }

    async function fetchDocuments() {
        try {
            const { data } = await axios.get(`/api/staff/${staffId}/documents`);
            staffDocuments.value = {};
            data.data.forEach(doc => {
                staffDocuments.value[doc.document_type] = doc.file_path;
                loadDecryptedImage(doc.file_path, doc.document_type);
            });
        } catch (e) { /* ... */ }
    }

    const documentImages = ref({});

    async function loadDecryptedImage(path, type) {
        try {
            const response = await axios.get('/api/staff/documents/view', {
                params: { path },
                responseType: 'blob' 
            });

            const mimeType = response.headers['content-type'] || response.data?.type || 'application/octet-stream';
            documentImages.value[type] = {
                path,
                url: URL.createObjectURL(response.data),
                mimeType,
            };
        } catch (e) { console.error("Decryption failed", e); }
    }

    const uploading = ref(false);
    const documentDialog = ref(false);
    const staffDocuments = ref({});
    const docForm = ref({
        nrc_front: null,
        nrc_back: null,
        household_front: null,
        cv: null
    });

    async function submitAllDocuments() {
        uploading.value = true;
        const formData = new FormData();
        
        const fields = ['nrc_front', 'nrc_back', 'household_front', 'cv'];
        fields.forEach(field => {
            if (docForm.value[field]) {
                formData.append(`documents[${field}]`, docForm.value[field]);
            }
        });

        try {
            await axios.post(`/api/staff/${staffId}/documents/bulk`, formData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            });
            documentDialog.value = false;
            fetchDocuments(); 
        } catch (e) {
            console.error('Upload failed:', e);
        } finally {
            uploading.value = false;
        }
    }

    const viewDocument = (path) => {
    const fileUrl = '/storage/' + path;
    window.open(fileUrl, '_blank');
    };

    const errorMessage = ref(null);
    const snackbar = ref({ show: false, text: '' });

    const handleError = (error) => {
        if (error.response && error.response.status === 422) {
            const errors = error.response.data.errors;
            errorMessage.value = Object.values(errors).flat().join(', ');
        } else {
            errorMessage.value = 'An unexpected error occurred.';
        }
    };

    const errors = ref({});
    async function deleteDocument(type) {
        try {
            await axios.delete(`/api/staff/${staffId}/documents/${type}`);
            fetchDocuments(); // Refresh list
        } catch (e) {
            // Show notification: "Failed to delete"
        }
    }

    const editDialog = ref(false);
    const editingType = ref(null);
    const fileToUpload = ref(null);

    function updateDocument(type) {
        editingType.value = type;
        editDialog.value = true;
    }

    async function confirmUpdate() {
        if (!fileToUpload.value) return;

        uploading.value = true; 
        const formData = new FormData();
        formData.append('document', fileToUpload.value);

    try {
        await axios.post(`/api/staff/${staffId}/documents/${editingType.value}`, formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });
        
        editDialog.value = false;
        fileToUpload.value = null; 
        fetchDocuments(); 
    } catch (e) {
        handleError(e); 
    } finally {
        uploading.value = false; 
    }
}
    watch([activeTab, selectedYear], () => {
        if (activeTab.value === 'documents') {
            fetchDocuments();
        } else {
            fetchTabData();
        }
        
        if (detailDialog.value && currentActiveBalanceItem.value) {
            openLeaveDetail(currentActiveBalanceItem.value);
        }
    });

    const previewDialog = ref(false);
    const previewUrl = ref(null);
    const previewType = ref('image');

    const openPreview = async (documentData, type) => {
        if (!documentData) return;

        if (previewUrl.value?.startsWith('blob:')) {
            URL.revokeObjectURL(previewUrl.value);
        }

        if (type === 'pdf') {
            try {
                const response = await axios.get('/api/staff/documents/view', {
                    params: { path: documentData.path },
                    responseType: 'blob',
                });

                previewUrl.value = URL.createObjectURL(response.data);
                previewType.value = 'pdf';
                previewDialog.value = true;
            } catch (error) {
                console.error('Failed to load PDF preview:', error);
                previewUrl.value = null;
                previewType.value = 'pdf';
                previewDialog.value = true;
            }
        } else {
            previewUrl.value = documentData.url;
            previewType.value = 'image';
            previewDialog.value = true;
        }
    };
    
    onMounted(() => {
        fetchStaffProfile();
        fetchTabData();
    });
</script>