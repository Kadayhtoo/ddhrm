<template>
    <div class="pa-2">
        <v-row class="mb-4" align="center">
            <v-col cols="12" md="4">
                <div class="text-h5 font-weight-bold mb-1">
                    {{ isManagement ? 'Leave Requests Management' : 'My Leave Requests' }}
                </div>
                <div class="text-body-2 text-medium-emphasis">
                    {{ isManagement ? 'Review, approve, or manage staff leave applications seamlessly' : 'Track and manage your submitted leave applications' }}
                </div>
            </v-col>
            
            <v-col cols="12" md="8" class="d-flex flex-wrap ga-2 justify-md-end align-center">
                <v-text-field
                    v-model="dateFilter"
                    type="date"
                    density="comfortable"
                    variant="outlined"
                    hide-details
                    label="Filter by Date"
                    style="max-width: 180px;"
                    clearable
                    @change="applySearch"
                    @click:clear="clearDate"
                />

                <v-text-field
                    v-if="isManagement"
                    v-model="searchInput"
                    density="comfortable"
                    variant="outlined"
                    hide-details
                    label="Search..."
                    prepend-inner-icon="mdi-magnify"
                    clearable
                    style="max-width: 170px;"
                    @keyup.enter="applySearch"
                    @click:clear="clearSearch"
                />

                <v-btn color="#702E62" rounded="lg" size="large" class="text-none rounded-lg px-5 py-2 elevation-2 font-weight-bold" @click="openLeaveFormDialog()"
                >
                    <v-icon start>mdi-plus</v-icon>Apply Leave
                </v-btn>
            </v-col>
        </v-row>

        <v-alert
            v-if="notification"
            :type="notificationType"
            variant="tonal"
            class="mb-4"
            closable
            @click:close="clearNotification"
        >
            {{ notification }}
        </v-alert>

        <v-tabs
            v-if="isManagement"
            v-model="activeTab"
            color="primary"
            class="mb-4 border-b"
            @update:model-value="onTabChange"
        >
            <v-tab value="approvals">
                <v-icon start size="small">mdi-account-multiple-outline</v-icon> Staff Approvals
            </v-tab>
            <v-tab value="my_requests">
                <v-icon start size="small">mdi-account-outline</v-icon> My Leave Requests
            </v-tab>
           
        </v-tabs>

       <v-card 
            :variant="items.length > 0 ? 'outlined' : 'flat'" 
            :class="[items.length > 0 ? 'rounded-lg border-thin elevation-0' : 'bg-transparent']"
        >
            <v-data-table-server
                v-model:items-per-page="itemsPerPage"
                v-model:page="page"
                :headers="headers"
                :items="items"
                :items-length="total"
                :loading="loading"
                item-value="id"
                density="comfortable"
                class="bg-transparent text-none"
                :hide-default-header="items.length === 0" @update:options="loadItems">
                <template #[`item.id`]="{ item }">
                    <v-chip size="small" variant="tonal" color="grey-darken-2" class="font-weight-bold">
                        #{{ item.id }}
                    </v-chip>
                </template>

                <template #[`item.user`]="{ item }">
                    <div class="d-flex align-center py-2">
                        <v-avatar color="indigo-lighten-5" size="36" class="mr-3">
                            <span class="text-indigo font-weight-bold text-body-2">{{ item.user?.name?.charAt(0).toUpperCase() }}</span>
                        </v-avatar>
                        <div>
                            <div class="font-weight-bold text-body-2">{{ item.user?.name }}</div>
                            <div class="text-caption text-medium-emphasis">{{ item.user?.email }}</div>
                        </div>
                    </div>
                </template>

                <template #[`item.leave_rule`]="{ item }">
                    <v-chip size="small" variant="tonal" color="secondary" class="font-weight-medium">
                        {{ item.leave_rule?.name }}
                    </v-chip>
                </template>

                <template #[`item.duration`]="{ item }">
                    <div class="text-body-2 font-weight-medium text-grey-darken-3">
                        <v-icon size="small" color="grey" class="mr-1">mdi-calendar-range</v-icon>
                        {{ item.start_date ? item.start_date.substring(0,10) : '' }} 
                        <span v-if="item.leave_session === 'full_day' && item.start_date !== item.end_date" class="text-grey mx-1">→</span> 
                        <span v-if="item.leave_session === 'full_day' && item.start_date !== item.end_date">{{ item.end_date ? item.end_date.substring(0,10) : '' }}</span>
                    </div>
                    <v-chip size="x-small" variant="flat" color="indigo-lighten-5" class="text-indigo-darken-2 text-capitalize font-weight-bold mt-1">
                        {{ item.leave_session ? item.leave_session.replace('_', ' ') : '' }}
                    </v-chip>
                </template>

                <template #[`item.total_days`]="{ item }">
                    <span class="font-weight-bold text-primary text-body-2">{{ item.total_days }} {{ item.total_days > 1 ? 'Days' : 'Day' }}</span>
                </template>

                <template #[`item.attachment`]="{ item }">
                    <v-btn 
                        v-if="item.attachment || item.attachment_path"
                        color="primary" 
                        variant="text" 
                        size="small" 
                        class="text-none font-weight-bold" 
                        prepend-icon="mdi-paperclip"
                        @click="viewAttachment(item.attachment ?? item.attachment_path)"
                    >
                        View File
                    </v-btn>
                    <span v-else class="text-caption text-disabled">No file</span>
                </template>

                <template #[`item.status`]="{ item }">
                    <v-chip :color="getStatusColor(item.status)" size="small" variant="flat" class="text-capitalize font-weight-bold px-3">
                        <v-icon size="x-small" class="mr-1">{{ getStatusIcon(item.status) }}</v-icon>
                        {{ item.status ? item.status.replace('_', ' ') : '' }}
                    </v-chip>
                </template>

                <template #[`item.actions`]="{ item }">
                    <div class="d-flex ga-1 justify-end align-center py-1">     
                        <template v-if="item.status === 'pending_tl' || item.status === 'pending' || item.status === 'pending_hr'">
                            <div v-if="Number(item.approver_id) === Number(auth.user?.id) && item.is_approve === 0" class="d-flex ga-1">
                                <v-btn color="success" variant="flat" size="small" class="text-none font-weight-bold" prepend-icon="mdi-check" :loading="statusLoadingId === item.id" @click="updateStatus(item.id, 'approved')">
                                    Approve
                                </v-btn>
                                <v-btn color="error" variant="tonal" size="small" class="text-none font-weight-bold" prepend-icon="mdi-close" :loading="statusLoadingId === item.id" @click="updateStatus(item.id, 'rejected')">
                                    Reject
                                </v-btn>
                            </div>
                           <div v-else-if="(auth.hasRoleSlug('admin') || auth.hasRoleSlug('hr')) && item.is_approve === 1 && item.is_approve_hr === 0" class="d-flex ga-1">
                            <v-btn 
                                color="success" 
                                variant="flat" 
                                size="small" 
                                class="text-none font-weight-bold" 
                                prepend-icon="mdi-check-all" 
                                :loading="statusLoading.id === item.id && statusLoading.action === 'approved'" 
                                @click="updateStatus(item.id, 'approved')"
                            >
                                Accept
                            </v-btn>

                            <v-btn 
                                color="error" 
                                variant="tonal" 
                                size="small" 
                                class="text-none font-weight-bold" 
                                prepend-icon="mdi-close" 
                                :loading="statusLoading.id === item.id && statusLoading.action === 'rejected'" 
                                @click="updateStatus(item.id, 'rejected')"
                            >
                                Reject
                            </v-btn>
                        </div>
                        <div v-else-if="item.user_id === auth.user?.id && item.is_approve === 0" class="d-flex ga-1">
                                <v-btn color="warning" variant="tonal" size="small" class="text-none font-weight-bold rounded-md" prepend-icon="mdi-pencil" @click="openLeaveFormDialog(item)">
                                    Edit
                                </v-btn>
                                <v-btn color="grey-darken-1" variant="outlined" size="small" class="text-none font-weight-bold rounded-md" prepend-icon="mdi-cancel" :loading="statusLoadingId === item.id" @click="cancelRequest(item.id)">
                                    Cancel
                                </v-btn>
                        </div>

                        <span v-else class="text-caption font-weight-medium text-amber-darken-3 bg-amber-lighten-5 px-2 py-1 rounded d-flex align-center">
                            <v-icon size="x-small" class="mr-1">mdi-clock-fast</v-icon>
                            Waiting for Approval
                        </span>
                    </template>

                        <span v-else-if="item.status !== 'pending' && item.status !== 'pending_tl' && item.status !== 'pending_hr'" class="text-caption text-medium-emphasis bg-grey-lighten-4 px-2 py-1 rounded d-flex align-center">
                            <v-icon size="x-small" class="mr-1">mdi-lock-outline</v-icon>Processed
                        </span>
                    </div>
                </template>

                <template #no-data>
                    <div class="d-flex flex-column align-center justify-center py-12 px-4 text-center w-100">
                        <v-avatar color="indigo-lighten-5" size="90" class="mb-4 border border-indigo-lighten-4">
                            <v-icon size="42" color="primary">mdi-file-document-remove-outline</v-icon>
                        </v-avatar>
                        <div class="text-h6 font-weight-bold text-grey-darken-3 mb-1">
                            No Leave Requests Found
                        </div>
                        <div class="text-body-2 text-medium-emphasis mb-5" style="max-width: 380px;">
                            There are currently no leave requests recorded matching your selected criteria or date range.
                        </div>
                    </div>
                </template>
            </v-data-table-server>
        </v-card>

        <v-dialog v-model="leaveFormDialog" max-width="650" persistent scrollable>
            <v-card rounded="lg">
                <v-card-title class="pa-4 font-weight-bold border-b d-flex align-center bg-grey-lighten-5">
                    <v-icon color="primary" class="mr-2">{{ isEditing ? 'mdi-pencil-box-outline' : 'mdi-file-document-edit-outline' }}</v-icon>
                    <span>{{ isEditing ? 'Edit Leave Request' : 'Apply Leave Request' }}</span>
                    <v-spacer />
                    <v-btn icon="mdi-close" variant="text" size="small" @click="closeLeaveFormDialog" />
                </v-card-title>
<v-alert v-if="formError" type="error" variant="tonal" class="mb-4" closable @click:close="formError = ''">
    {{ formError }}
</v-alert>
                <v-card-text class="pa-5" style="max-height: 70vh;">
                <v-form ref="leaveRequestForm">
                    <template v-if="canRequestForOthers">
                        <div class="text-subtitle-2 font-weight-bold text-indigo mb-2 d-flex align-center">
                            <v-icon size="small" class="mr-1">mdi-account-box-multiple-outline</v-icon>
                            Target Employee Selection
                        </div>
                        <v-row dense class="mb-4 pa-3 bg-grey-lighten-5 rounded-lg border">
                            <v-col cols="12" md="6">
                                <v-select
                                    v-model="selectedDepartmentId"
                                    :items="departmentsList"
                                    item-title="name"
                                    item-value="id"
                                    label="Select Department *"
                                    variant="outlined"
                                    density="comfortable"
                                    hide-details
                                    :rules="[rules.required]"
                                    clearable
                                    @update:model-value="onDepartmentFilterChange"
                                />
                            </v-col>

                            <v-col cols="12" md="6">
                                <v-select
                                    v-model="leaveForm.user_id"
                                    :items="filteredStaffDropdownList"
                                    :item-title="getStaffDisplay"
                                    item-value="id"
                                    label="Select Employee *"
                                    variant="outlined"
                                    density="comfortable"
                                    hide-details
                                    :rules="[rules.required]"
                                    :disabled="!selectedDepartmentId"
                                    @update:model-value="onEmployeeChange"
                                />
                            </v-col>
                        </v-row>
                    </template>

                    <div class="text-subtitle-2 font-weight-bold text-indigo mb-2 d-flex align-center">
                        <v-icon size="small" class="mr-1">mdi-shield-check-outline</v-icon>
                        Choose Approver
                    </div>
                    <v-row dense class="mb-4">
                        <v-col cols="12">
                            <v-select
                                v-model="leaveForm.approver_id"
                                :items="filteredLeaderList"
                                :item-title="getApproverDisplay"
                                item-value="id"
                                label="Assign Approver / Leader *"
                                variant="outlined"
                                density="comfortable"
                                hide-details
                                :rules="[rules.required]"
                                :loading="loadingLeaders"
                                :disabled="!leaveForm.department_id"
                            >
                                <template #item="{ props, item }">
                                    <v-list-item v-bind="props">
                                        <template #append>
                                            <v-chip 
                                                v-if="item.raw.roles?.some(r => r.slug === 'hr' || r.slug === 'admin')"
                                                size="x-small" 
                                                color="primary" 
                                                variant="flat" 
                                                class="font-weight-bold"
                                            >
                                                HR Dept Default
                                            </v-chip>
                                        </template>
                                    </v-list-item>
                                </template>
                            </v-select>
                            
                            <div class="mt-1 px-1">
                                <v-messages active color="grey-darken-1" messages="HR Management will be automatically assigned for final approval.">
                                    <template #prepend>
                                        <v-icon size="14" class="me-1">mdi-information-outline</v-icon>
                                    </template>
                                </v-messages>
                            </div>
                        </v-col>
                    </v-row>

                    <div class="text-subtitle-2 font-weight-bold text-indigo mb-2 d-flex align-center">
                        <v-icon size="small" class="mr-1">mdi-calendar-clock</v-icon>
                        Leave Schedule & Reason
                    </div>
                    <v-row dense class="mb-4">
                        <v-col cols="12" md="6">
                            <v-select
                                v-model="leaveForm.leave_rule_id"
                                :items="leaveRulesList"
                                item-title="name"
                                item-value="id"
                                label="Select Leave Type *"
                                variant="outlined"
                                density="comfortable"
                                :rules="[rules.required]"
                            />
                        </v-col>

                        <v-col cols="12" md="6">
                            <v-select
                                v-model="leaveForm.leave_session"
                                :items="sessionOptions"
                                item-title="title"
                                item-value="value"
                                label="Leave Session *"
                                variant="outlined"
                                density="comfortable"
                                :rules="[rules.required]"
                            />
                        </v-col>

                        <v-col cols="12" sm="6">
                            <v-text-field
                                v-model="leaveForm.start_date"
                                type="date"
                                label="Start Date *"
                                variant="outlined"
                                density="comfortable"
                                :rules="[rules.required]"
                            />
                        </v-col>
                        
                        <v-col cols="12" sm="6">
                            <v-text-field
                                v-model="leaveForm.end_date"
                                type="date"
                                label="End Date *"
                                variant="outlined"
                                density="comfortable"
                                :disabled="leaveForm.leave_session !== 'full_day'"
                                :rules="[rules.required]"
                            />
                        </v-col>

                        <v-col cols="12">
                            <v-text-field
                                v-model.number="leaveForm.total_days"
                                type="number"
                                step="0.5"
                                label="Total Days"
                                variant="outlined"
                                density="comfortable"
                                readonly
                                persistent-hint
                                hint="Calculated automatically excluding weekend rest days"
                            />
                        </v-col>

                        <v-col cols="12">
                            <v-textarea
                                v-model="leaveForm.reason"
                                label="Reason / Remarks Description"
                                variant="outlined"
                                density="comfortable"
                                rows="2"
                            />
                        </v-col>

                        <v-col cols="12">
                            <v-file-input
                                v-model="leaveForm.attachment"
                                label="Upload Evidence Document (Optional)"
                                variant="outlined"
                                density="comfortable"
                                prepend-icon="mdi-paperclip"
                                show-size
                                accept="image/*,.pdf,.doc,.docx"
                                clearable
                                @change="onFileSelected"
                            />
                        </v-col>
                    </v-row>
                </v-form>
                </v-card-text>
               <v-card-actions class="px-5 py-3 border-t bg-grey-lighten-5">
                    <v-spacer />
                    
                    <v-btn variant="text"  rounded="lg" class="text-none font-weight-bold"  :disabled="submittingForm" @click="closeLeaveFormDialog">Cancel</v-btn>

                    <v-btn color="#702E62" variant="flat" rounded="lg" class="text-none px-6 elevation-2 font-weight-bold" :loading="submittingForm"  @click="submitLeaveRequest" >{{ isEditing ? 'Update' : 'Save' }} </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <v-dialog v-model="previewDialog" max-width="800" scrollable>
            <v-card rounded="lg">
                <v-card-title class="pa-4 font-weight-bold border-b d-flex align-center">
                    <span>Attachment Preview</span><v-spacer /><v-btn icon="mdi-close" variant="text" size="small" @click="previewDialog = false" />
                </v-card-title>
                <v-card-text class="pa-0 bg-grey-lighten-4" style="height: 60vh;">
                    <div v-if="isImage(previewUrl)" class="d-flex justify-center align-center h-100 pa-4"><v-img :src="previewUrl" max-height="100%" contain class="rounded shadow-sm bg-white" /></div>
                    <iframe v-else-if="previewUrl" :src="previewUrl" width="100%" height="100%" style="border: none;"></iframe>
                </v-card-text>
            </v-card>
        </v-dialog>

        <v-dialog v-model="cancelDialog" max-width="400" persistent>
            <v-card rounded="lg">
                <v-card-title class="text-h5 font-weight-bold d-flex align-center pa-4"><v-icon color="error" class="mr-2">mdi-alert</v-icon>Cancel Request</v-card-title>
                <v-card-text class="pa-4 pt-0 text-body-1">Are you sure you want to cancel this leave request?</v-card-text>
                <v-card-actions class="pa-4 pt-0 justify-end ga-2">
                    <v-btn variant="outlined" color="grey" class="px-4 text-none font-weight-bold" @click="cancelDialog = false">Cancel</v-btn>
                    <v-btn color="error" variant="flat" class="px-4 text-none font-weight-bold" :loading="canceling" @click="doCancel">Confirm</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <v-snackbar v-model="snackbar" :timeout="4000" :color="notificationType">
            {{ notification }}
            <template #actions>
                <v-btn variant="text" @click="snackbar = false">Close</v-btn>
            </template>
        </v-snackbar>
    </div>
</template>

<script setup>
    import { onMounted, ref, computed, watch, onActivated } from 'vue'; 
    import axios from 'axios';
    import { useRouter } from 'vue-router'; 
    import { useAuthStore } from '@/stores/auth';

    const auth = useAuthStore();
    const router = useRouter(); 

    const items = ref([]);
    const total = ref(0);
    const page = ref(1);
    const itemsPerPage = ref(10);
    const loading = ref(false);
    
    const searchInput = ref('');
    const search = ref('');
    const dateFilter = ref(''); 
    const activeTab = ref('approvals');

    const statusLoading = ref({ id: null, action: null });
    const notification = ref('');
    const notificationType = ref('success');
    const snackbar = ref(false);
    let notificationTimer = null;
    
    const leaveFormDialog = ref(false);
    const submittingForm = ref(false);
    const loadingLeaders = ref(false);
    const formError = ref('');
    const isEditing = ref(false);
    const editingId = ref(null);
    
    const staffDropdownList = ref([]); 
    const leaderList = ref([]);        
    const leaveRulesList = ref([]);    
    const departmentsList = ref([]);        
    const selectedDepartmentId = ref(null); 

    const cancelDialog = ref(false);
    const cancelId = ref(null);
    const canceling = ref(false);

    const hasApproverRecords = ref(false);

    const leaveRequestForm = ref(null); 

    const sessionOptions = [
        { title: 'Full Day', value: 'full_day' },
        { title: 'Morning Half', value: 'morning' },
        { title: 'Afternoon Half', value: 'afternoon' }
    ];

    const leaveForm = ref({
        user_id: null, department_id: null, approver_id: null, leave_rule_id: null, leave_session: 'full_day', start_date: '', end_date: '', total_days: 1, reason: '', attachment: null 
    });

    const isManagement = computed(() => {
        if (auth.hasRoleSlug('admin') || auth.hasRoleSlug('hr')) return true;
        return hasApproverRecords.value;
    });

    const canRequestForOthers = computed(() => {
        return auth.hasRoleSlug('admin') || auth.hasRoleSlug('hr');
    });

    const filteredStaffDropdownList = computed(() => {
        if (!selectedDepartmentId.value) return [];
        return staffDropdownList.value.filter(staff => Number(staff.department_id) === Number(selectedDepartmentId.value));
    });

    const filteredLeaderList = computed(() => {
        if (!leaderList.value) return [];
        return leaderList.value.filter(leader => Number(leader.id) !== Number(leaveForm.value.user_id));
    });
    
    const headers = computed(() => {
        const baseHeaders = [
            { title: 'ID', key: 'id', sortable: false },
            { title: 'Leave Type', key: 'leave_rule', sortable: false },
            { title: 'Duration', key: 'duration', sortable: false },
            { title: 'Total', key: 'total_days', sortable: false },
            { title: 'Attachment', key: 'attachment', sortable: false },
            { title: 'Status', key: 'status', sortable: false },
        ];

        if (isManagement.value && activeTab.value === 'approvals') {
            baseHeaders.splice(1, 0, { title: 'Employee', key: 'user', sortable: false });
        }
        
        baseHeaders.push({ title: 'Actions', key: 'actions', sortable: false, align: 'end' });
        return baseHeaders;
    });

    const rules = {
        required: v => !!v || 'This field is required',
        dateCheck: v => {
            if (!v) return 'Date is required';
            if (leaveForm.value.start_date && leaveForm.value.end_date) {
                const s = new Date(leaveForm.value.start_date);
                const e = new Date(leaveForm.value.end_date);
                if (e < s) return 'End Date cannot be earlier than Start Date';
            }
            return true;
        }
    };

    watch(() => leaveForm.value.leave_session, (newVal) => {
        if (newVal !== 'full_day') { leaveForm.value.end_date = leaveForm.value.start_date; }
        calculateTotalDays();
    });

    watch([() => leaveForm.value.start_date, () => leaveForm.value.end_date], () => {
        if (leaveForm.value.leave_session !== 'full_day') { leaveForm.value.end_date = leaveForm.value.start_date; }
        calculateTotalDays();
    });

    function getStaffDisplay(item) { if (!item) return ''; return `${item.name} (${item.department?.name || 'No Department'})`; }
    function getApproverDisplay(item) { return item ? item.name : ''; }
    function onDepartmentFilterChange() { leaveForm.value.user_id = null; leaveForm.value.department_id = null; leaveForm.value.approver_id = null; leaderList.value = []; }
    
    function onEmployeeChange(id) { 
        const staff = staffDropdownList.value.find(u => u.id === id); 
        if (staff) { 
            leaveForm.value.user_id = id;
            leaveForm.value.department_id = staff.department_id; 
            leaveForm.value.approver_id = null; 
            fetchLeaders(staff.department_id); 
        } 
    }

    async function fetchLeaders(departmentId) {
        if (!departmentId) return; 
        loadingLeaders.value = true;
        try {
            if (staffDropdownList.value.length === 0) {
                try {
                    const rStaff = await axios.get('/api/staff-dropdown');
                    staffDropdownList.value = rStaff.data?.data || rStaff.data || [];
                } catch (err) {
                    console.error("fetchLeaders -> staff-dropdown error:", err);
                }
            }
            leaderList.value = staffDropdownList.value.filter(u => Number(u.department_id) === Number(departmentId));
        } catch (e) { 
            console.error(e); 
        } finally { 
            loadingLeaders.value = false; 
        }
    }

    function calculateTotalDays() {
        const { start_date, end_date, leave_session } = leaveForm.value;
        
        if (!start_date || !end_date) {
            leaveForm.value.total_days = 0;
            return;
        }

        const s = new Date(start_date);
        const e = new Date(end_date);
        
        if (e < s) {
            leaveForm.value.total_days = 0;
            return;
        }

        let w = 0;
        let curr = new Date(s);
        while (curr <= e) {
            if (curr.getDay() !== 0 && curr.getDay() !== 6) {
                w++;
            }
            curr.setDate(curr.getDate() + 1);
        }

        // Check if the selection is entirely weekends
        if (w === 0) {
            leaveForm.value.total_days = 0;
            setNotification('The selected dates fall on weekends. Please select working days.', 'warning');
            return;
        }

        if (leave_session !== 'full_day') {
            leaveForm.value.total_days = 0.5;
        } else {
            leaveForm.value.total_days = w;
        }
    }

    function onFileSelected(e) { leaveForm.value.attachment = e.target?.files[0] || e || null; }

    async function openLeaveFormDialog(item = null) {
        leaveFormDialog.value = true;
        formError.value = '';
        
        leaveForm.value = {
            user_id: null, department_id: null, approver_id: null, leave_rule_id: null, leave_session: 'full_day', start_date: '', end_date: '', total_days: 1, reason: '', attachment: null 
        };

        try {
            const rRules = await axios.get('/api/leave-rules');
            leaveRulesList.value = rRules.data?.data || rRules.data || [];
        } catch (e) {
            console.error("🚨 Leave Rules API Error:", e);
        }

        try {
            const rStaff = await axios.get('/api/staff-dropdown');
            staffDropdownList.value = rStaff.data?.data || rStaff.data || [];
        } catch (e) {
            console.error("🚨 Staff Dropdown API Error:", e);
        }

        try {
            const rDept = await axios.get('/api/department').catch(() => axios.get('/api/department'));
            departmentsList.value = rDept.data?.data || rDept.data || [];
        } catch (e) {
            console.error("🚨 Department API Error:", e);
        }

        if (item) {
            isEditing.value = true;
            editingId.value = item.id;
            selectedDepartmentId.value = item.user?.department_id;
            
            leaveForm.value = {
                user_id: item.user_id,
                department_id: item.user?.department_id,
                approver_id: item.approver_id,
                leave_rule_id: item.leave_rule_id,
                leave_session: item.leave_session || 'full_day',
                start_date: item.start_date ? item.start_date.substring(0, 10) : '',
                end_date: item.end_date ? item.end_date.substring(0, 10) : '',
                total_days: item.total_days,
                reason: item.reason || '',
                attachment: null
            };
            await fetchLeaders(item.user?.department_id);
        } else {
            isEditing.value = false;
            editingId.value = null;

            if (!canRequestForOthers.value) {
                leaveForm.value.user_id = auth.user?.id; 
                leaveForm.value.department_id = auth.user?.department_id;
                selectedDepartmentId.value = auth.user?.department_id; 
                
                if (auth.user?.department_id) {
                    await fetchLeaders(auth.user?.department_id);
                }
            } else {
                selectedDepartmentId.value = null;
            }
        }
    }

    function closeLeaveFormDialog() { 
        leaveFormDialog.value = false; 
        isEditing.value = false;
        editingId.value = null;
        selectedDepartmentId.value = null; 
        formError.value = '';
        leaveForm.value = { user_id: null, department_id: null, approver_id: null, leave_rule_id: null, leave_session: 'full_day', start_date: '', end_date: '', total_days: 1, reason: '', attachment: null }; 
    }

    async function submitLeaveRequest() {
        const { valid } = await leaveRequestForm.value.validate();
        if (!valid) return;

        submittingForm.value = true; formError.value = '';
        try {
            const fd = new FormData(); 
            Object.keys(leaveForm.value).forEach(k => { 
                if (leaveForm.value[k] !== null) fd.append(k, leaveForm.value[k]); 
            });
            
            if (isEditing.value) {
                fd.append('_method', 'PUT');
                await axios.post(`/api/leave-requests/${editingId.value}`, fd, { headers: { 'Content-Type': 'multipart/form-data' } });
                setNotification('Leave request updated successfully.', 'success');
            } else {
                await axios.post('/api/leave-requests', fd, { headers: { 'Content-Type': 'multipart/form-data' } });
                setNotification('Leave request submitted.', 'success');
            }
            closeLeaveFormDialog();
            await refreshLeaveRequests();
        } catch (e) { formError.value = e?.response?.data?.message || 'Error processing request'; } finally { submittingForm.value = false;  }
    }
    
    async function loadItems(options) {
        if (!auth.token) { router.push('/login'); return; }
        loading.value = true;
        try {
            const p = options?.page ?? page.value; const per = options?.itemsPerPage ?? itemsPerPage.value;
            
            if (!auth.hasRoleSlug('admin') && !auth.hasRoleSlug('hr')) {
                const checkRes = await axios.get('/api/leave-requests', { params: { scope: 'approvals', per_page: 1 } });
                hasApproverRecords.value = (checkRes.data?.data && checkRes.data.data.length > 0);
            }

            const { data } = await axios.get('/api/leave-requests', {
                params: { page: p, per_page: per, search: search.value || undefined, date_filter: dateFilter.value || undefined, scope: isManagement.value ? activeTab.value : 'my_requests' }
            });
            items.value = data.data ?? []; total.value = data.meta?.total ?? 0; page.value = data.meta?.current_page ?? p; itemsPerPage.value = data.meta?.per_page ?? per;
        } catch (e) { setNotification('Error fetching requests.', 'error'); } finally { loading.value = false; }
    }

    async function refreshLeaveRequests() {
        page.value = 1;
        await loadItems({ page: 1, itemsPerPage: itemsPerPage.value });
    }

    function onTabChange() { page.value = 1; loadItems({ page: 1, itemsPerPage: itemsPerPage.value }); }
    function applySearch() { search.value = searchInput.value?.trim() ?? ''; page.value = 1; loadItems({ page: 1, itemsPerPage: itemsPerPage.value }); }
    function clearSearch() { searchInput.value = ''; applySearch(); }
    function clearDate() { dateFilter.value = ''; applySearch(); }
    function getStatusColor(s) { return s === 'approved' ? 'success' : (s === 'rejected' ? 'error' : (s === 'pending_hr' ? 'info' : 'warning')); }
    function getStatusIcon(s) { return s === 'approved' ? 'mdi-check-circle' : (s === 'rejected' ? 'mdi-close-circle' : 'mdi-account-clock'); }

    async function updateStatus(id, status) {
        statusLoading.value = { id, action: status }; 
        
        try {
            await axios.patch(`/api/leave-requests/${id}/status`, { status });
            setNotification('Status updated.', 'success');
            await refreshLeaveRequests();
        } catch(e) {
            setNotification('Error updating status.', 'error');
        } finally {
            statusLoading.value = { id: null, action: null };
        }
    }
    
    function cancelRequest(id) { cancelId.value = id; cancelDialog.value = true; }
    async function doCancel() { canceling.value = true; try { await axios.post(`/api/leave-requests/${cancelId.value}/cancel`); cancelDialog.value = false; setNotification('Cancelled.', 'success'); await refreshLeaveRequests(); } catch(e) { setNotification('Error.', 'error'); } finally { canceling.value = false; } }

    function clearNotification() {
        notification.value = '';
        snackbar.value = false;
        if (notificationTimer) {
            clearTimeout(notificationTimer);
            notificationTimer = null;
        }
    }

    function setNotification(message, type = 'success') {
        notification.value = message;
        notificationType.value = type;
        snackbar.value = true;

        if (notificationTimer) {
            clearTimeout(notificationTimer);
        }

        notificationTimer = setTimeout(() => {
            clearNotification();
        }, 4000);
    }

    onMounted(() => loadItems());

    const previewDialog = ref(false); const previewUrl = ref('');
    function viewAttachment(p) { if (!p) return; previewUrl.value = p.startsWith('storage/') ? '/' + p : '/storage/' + p; previewDialog.value = true; }
    const isImage = (url) => { if (!url) return false; return ['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(url.split('.').pop().toLowerCase()); }

    const selectedLeaveRule = ref(null);
    const requestedDays = ref(0);
    const userBalances = ref([]); 

    const isBalanceExceeded = computed(() => {
    const balance = userBalances.value.find(b => b.leave_rule_id === selectedLeaveRule.value);
    return balance ? requestedDays.value > balance.remaining_days : false;
    });
</script>