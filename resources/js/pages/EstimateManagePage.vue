<template>
  <v-container fluid class="pa-6 bg-grey-lighten-4" style="min-height: 100vh;">
    <v-row class="mb-4 align-center justify-space-between">
      <v-col cols="12" sm="auto">
        <h1 class="text-h4 font-weight-bold text-grey-darken-4">Estimates Management</h1>
        <p class="text-body-1 text-medium-emphasis mb-0">Create, edit, manage and track your client estimates.</p>
      </v-col>
      <v-col cols="12" sm="auto" class="text-sm-right">
        <v-btn color="indigo-darken-2" rounded="lg" size="large" class="text-none rounded-lg px-5 py-2 elevation-2 font-weight-bold" @click="openCreateDialog(null)">
          <v-icon start>mdi-plus</v-icon> Create Estimate
        </v-btn>
      </v-col>
    </v-row>

    <v-card class="rounded-xl mb-6 pa-4 border-0 elevation-1 bg-white">
      <v-row class="align-center" dense>
          <v-col cols="12" sm="6" md="3" class="pa-2">
            <v-text-field v-model="searchQuery" prepend-inner-icon="mdi-magnify" label="Search ID or Client..." variant="outlined" density="comfortable" hide-details rounded="lg" color="indigo-lighten-1" clearable></v-text-field>
          </v-col>
          
          <v-col cols="12" sm="6" md="2" class="pa-2">
            <v-select 
              v-model="filterStatus" 
              :items="[
                { label: 'All', value: null },
                { label: 'Open', value: 'open' },
                { label: 'Sent', value: 'sent' },
                { label: 'Accepted', value: 'accepted' },
                { label: 'Rejected', value: 'rejected' },
                { label: 'Cancelled', value: 'cancelled' }
              ]"
              item-title="label"
              item-value="value"
              label="Status"
              variant="outlined"
              density="comfortable"
              hide-details
              rounded="lg"
              clearable
            ></v-select>        
          </v-col>

        <v-col cols="12" lg="3" md="6" class="pa-2">
          <v-select 
            v-model="dateRangeFilter" 
            :items="[
              { label: 'This Month', value: 'this_month' },
              { label: 'Within 3 Months', value: '3_months' },
              { label: 'Within 6 Months', value: '6_months' }
            ]" 
            item-title="label" 
            item-value="value" 
            label="Filter by Date" 
            variant="outlined" 
            density="comfortable" 
            hide-details 
            rounded="lg" 
            clearable
          ></v-select>
        </v-col>
        <v-col cols="12" md="3" class="pa-2 text-md-right">
          <v-btn color="grey-darken-1" variant="text" class="text-none rounded-lg font-weight-bold" prepend-icon="mdi-filter-off" @click="resetFilters">Clear Filters</v-btn>
        </v-col>
      </v-row>
    </v-card>

    <v-snackbar v-model="snackbar.show" :color="snackbar.color" timeout="4000" rounded="lg" elevation="4">
      <div class="d-flex align-center">
        <v-icon class="mr-2">{{ snackbar.color === 'success' ? 'mdi-check-circle' : 'mdi-alert-circle' }}</v-icon>
        <span class="font-weight-medium">{{ snackbar.text }}</span>
      </div>
    </v-snackbar>

    <v-card class="rounded-xl border-0 elevation-1 bg-white overflow-hidden">
      <v-data-table :headers="headers" :items="filteredEstimates" :loading="loading" hover class="bg-transparent text-grey-darken-3">
        <template #loading>
          <v-linear-progress indeterminate color="indigo-darken-2" height="3"></v-linear-progress>
        </template>
        
        <template #item.estimate_id="{ item }">
          <span class="font-weight-bold text-indigo-darken-3">{{ item.estimate_id }}</span>
        </template>

        <template #item.client_name="{ item }">
          <div>
            <div class="font-weight-medium text-grey-darken-4">{{ item.client_name }}</div>
            <div class="text-caption text-medium-emphasis">{{ item.client_email || '-' }}</div>
          </div>
        </template>

        <template #item.sub_total="{ item }">
          <span class="font-weight-bold">
            {{ formatAmount(item.sub_total) }} <span class="text-caption text-medium-emphasis ml-0.5">{{ item.currency }}</span>
          </span>
        </template>

        <template #item.grand_total="{ item }">
          <span class="font-weight-bold">
            {{ formatAmount(item.grand_total) }} <span class="text-caption text-medium-emphasis ml-0.5">{{ item.currency }}</span>
          </span>
        </template>

        <template #item.dates="{ item }">
          <div class="text-body-2">
            <div><v-icon size="14" class="mr-1 text-medium-emphasis">mdi-calendar-export</v-icon>{{ item.issue_date }}</div>
            <div class="text-caption text-error mt-0.5"><v-icon size="14" class="mr-1">mdi-calendar-clock</v-icon>Due: {{ item.due_date }}</div>
          </div>
        </template>

        <template #item.status="{ item }">
          <v-chip :color="getStatusColor(item.status)" size="small" variant="flat" class="text-capitalize font-weight-bold rounded-lg px-3">
            {{ item.status }}
          </v-chip>
        </template>

       <template #item.actions="{ item }">
        <div class="d-flex justify-end gap-1">
          <v-btn icon="mdi-pencil-outline" variant="text" size="small" color="indigo-darken-2" title="Edit Estimate" @click="openEditDialog(item)"></v-btn>
          <v-btn icon="mdi-delete-outline" variant="text" size="small" color="error" title="Delete Estimate" @click="confirmDelete(item.estimate_id)"></v-btn>
          
          <v-btn icon="mdi-eye-outline" variant="text" size="small" color="indigo-darken-2" title="Preview" @click="previewEstimate(item.estimate_id)"></v-btn>
        </div>
      </template>
      </v-data-table>
    </v-card>

    <v-dialog v-model="dialog" max-width="650px" persistent>
      <v-card class="rounded-xl pa-2">
        <v-card-title class="d-flex align-center justify-space-between px-4 pt-4 pb-2">
          <span class="text-h5 font-weight-bold text-grey-darken-4">
            {{ isEditMode ? 'Edit Estimate: ' + form.estimate_id : 'Create New Estimate' }}
          </span>
          <v-btn icon="mdi-close" variant="text" size="small" @click="closeDialog"></v-btn>
        </v-card-title>
        <v-divider class="mx-4"></v-divider>
        
        <v-card-text class="px-4 py-4">
          <v-alert v-if="backendErrors.length > 0" type="error" variant="tonal" class="rounded-lg mb-4 text-body-2" closable @click:close="backendErrors = []">
            <div class="font-weight-bold mb-1">Please check again :</div>
            <ul class="pl-4 mb-0">
              <li v-for="(err, idx) in backendErrors" :key="idx">{{ err }}</li>
            </ul>
          </v-alert>

          <v-form ref="formRef">
            <v-row>
              <v-col cols="12" sm="6" class="py-1">
                <v-text-field v-model="form.estimate_id" label="Estimate ID *" variant="outlined" density="comfortable" :rules="[v => !!v || 'Estimate ID is required']" :disabled="isEditMode" rounded="lg" color="indigo-lighten-1" persistent-hint></v-text-field>
              </v-col>

              <v-col cols="12" sm="6" class="py-1">
                <v-select v-model="form.client_id" :items="clientOptions" item-title="full_name" item-value="id" label="Select Client *" variant="outlined" density="comfortable" :rules="[v => !!v || 'Client selection is required']" :disabled="isEditMode" rounded="lg" color="indigo-lighten-1"></v-select>
              </v-col>

              <v-col cols="12" sm="6" class="py-1">
                <v-text-field v-model="form.issue_date" label="Issue Date *" type="date" variant="outlined" density="comfortable" :rules="[v => !!v || 'Issue date is required']" rounded="lg" color="indigo-lighten-1"></v-text-field>
              </v-col>

              <v-col cols="12" sm="6" class="py-1">
                <v-text-field v-model="form.due_date" label="Due Date *" type="date" variant="outlined" density="comfortable" :rules="[v => !!v || 'Due date is required', validateDueDate]" rounded="lg" color="indigo-lighten-1"></v-text-field>
              </v-col>

              <v-col cols="12" sm="6" class="py-1">
                <v-select v-model="form.currency" :items="currencyOptions" label="Currency *" variant="outlined" density="comfortable" :rules="[v => !!v || 'Currency is required']" rounded="lg" color="indigo-lighten-1"></v-select>
              </v-col>

              <v-col cols="12" sm="6" class="py-1">
                <v-select v-model="form.status" :items="['open', 'sent', 'accepted', 'rejected', 'cancelled']" label="Status *" variant="outlined" density="comfortable" :rules="[v => !!v || 'Status is required']" class="text-capitalize" rounded="lg" color="indigo-lighten-1"></v-select>
              </v-col>
              
              <v-col cols="12" class="mt-6">
                <div class="text-subtitle-1 font-weight-bold mb-2">Terms and Conditions</div>
                <RichTextEditor v-model="form.terms" label="Terms and Conditions" />
              </v-col>
            </v-row>
          </v-form>

          <v-divider class="my-4"></v-divider>
          <div class="text-subtitle-1 font-weight-bold mb-2">Estimate Items</div>

          <v-row v-for="(item, index) in form.items" :key="index" class="align-center">
            <v-col cols="6">
              <v-text-field v-model="item.name" label="Item Name" variant="outlined" density="compact" hide-details></v-text-field>
            </v-col>
              <v-col cols="6">
                <v-text-field v-model="item.description" label="Item Description" variant="outlined" density="compact" hide-details></v-text-field>
              </v-col>
            <v-col cols="4">
              <v-text-field v-model="item.item_type" label="Item Type" variant="outlined" density="compact" hide-details></v-text-field>
            </v-col>
            <v-col cols="3">
              <v-text-field v-model.number="item.quantity" label="Qty" type="number" variant="outlined" density="compact" hide-details></v-text-field>
            </v-col>
            <v-col cols="4">
              <v-text-field v-model.number="item.price" label="Price" type="number" variant="outlined" density="compact" hide-details></v-text-field>
            </v-col>            
            <v-col cols="1">
              <v-btn icon="mdi-delete" variant="text" color="error" size="small" @click="removeItem(index)"></v-btn>
            </v-col>
          </v-row>
          <v-btn color="indigo" class="mt-2" variant="tonal" prepend-icon="mdi-plus" @click="addItem">Add Item</v-btn>
        </v-card-text>
        
        <v-card-actions class="px-6 pb-4">
            <v-spacer />   
            <v-btn variant="text" rounded="lg" class="text-none px-5 font-weight-bold" :disabled="submitLoading" @click="closeDialog">Cancel</v-btn>
            <v-btn color="indigo-darken-2" variant="flat" rounded="lg" class="text-none px-6 elevation-2 font-weight-bold" :loading="submitLoading" @click="saveEstimate">
                {{ isEditMode ? 'Update' : 'Create Estimate' }}
            </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
    <v-dialog v-model="deleteDialog" max-width="400">
      <v-card class="rounded-xl pa-2">
        <v-card-title class="text-h5 font-weight-bold text-grey-darken-4 pt-4 px-4">Delete Estimate?</v-card-title>
        <v-card-text class="text-body-1 text-medium-emphasis px-4 py-2">Are you sure you want to permanently delete estimate <span class="font-weight-bold text-indigo-darken-3">{{ estimateToDelete }}</span>? This action cannot be reversed.</v-card-text>
        <v-card-actions class="px-4 pb-2 pt-4">
          <v-spacer></v-spacer>
          <v-btn variant="text" class="text-none font-weight-bold rounded-lg" @click="deleteDialog = false">Cancel</v-btn>
          <v-btn color="error" class="text-none font-weight-bold rounded-lg px-4" :loading="deleteLoading" @click="executeDelete">Delete</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-container>
</template>

<script setup>
  import { ref, onMounted, computed } from 'vue';
  import { useRouter, useRoute } from 'vue-router'; 
  import axios from 'axios';
  import RichTextEditor from '@/components/RichTextEditor.vue';

  const router = useRouter();
  const route = useRoute(); 
  const estimates = ref([]);
  const clients = ref([]);
  const loading = ref(false);
  const dialog = ref(false);
  const isEditMode = ref(false); 
  const isClientFixed = ref(false); 
  const submitLoading = ref(false);
  const formRef = ref(null);

  const deleteDialog = ref(false);
  const deleteLoading = ref(false);
  const estimateToDelete = ref(null);

  const searchQuery = ref('');
  const filterStatus = ref(null);
  const backendErrors = ref([]);
  const dateRangeFilter = ref(null);

  const snackbar = ref({ show: false, text: '', color: 'success' });
  const currencyOptions = ref([]);
  const defaultForm = {
    estimate_id: '',
    client_id: null,
    issue_date: new Date().toISOString().substr(0, 10), 
    due_date: '',
    currency: 'MMK',
    status: 'open',
    terms: 'Please send this estimate. Payment terms to be agreed.',
    items: [{ name: '', quantity: 1, price: 0 }]
  };
  const form = ref({ ...defaultForm });
  const headers = [
    { title: 'Estimate ID', key: 'estimate_id', sortable: true },
    { title: 'Client Details', key: 'client_name', sortable: true },
    { title: 'Dates (Issue/Due)', key: 'dates', sortable: false },
    { title: 'Subtotal', key: 'sub_total', sortable: true },
    { title: 'Total', key: 'grand_total', sortable: true },
    { title: 'Status', key: 'status', sortable: true },
    { title: 'Actions', key: 'actions', align: 'end', sortable: false },
  ];

  async function fetchCurrencies() {
    try {
      const response = await axios.get('/api/currencies'); 
      currencyOptions.value = Object.keys(response.data);  } catch (error) {
      console.error("Error fetching currencies:", error);
    }
  }

  function addItem() {
    form.value.items.push({ name: '', quantity: 1, price: 0 });
  }

  function removeItem(index) {
    form.value.items.splice(index, 1);
  }

  async function fetchEstimates() {
    loading.value = true;
    try {
      const response = await axios.get('/api/estimates');
      estimates.value = response.data.data || response.data;
    } catch (error) {
      console.error("Error fetching estimates:", error);
      showToast("Failed to fetch estimates list.", "error");
    } finally {
      loading.value = false;
    }
  }

  async function fetchClients() {
    try {
      const response = await axios.get('/api/client');
      const rawClients = response.data.data || response.data;
      clients.value = rawClients.map(c => ({
        id: c.id,
        full_name: `${c.first_name || ''} ${c.last_name || ''}`.trim() || 'Unnamed Client'
      }));
    } catch (error) {
      console.error("Error fetching clients list:", error);
    }
  }

  async function fetchNextEstimateNumber() {
    try {
      const response = await axios.get('/api/estimates/next-number');
      if (response.data && response.data.estimate_id) {
        form.value.estimate_id = response.data.estimate_id;
      }
    } catch (error) {
      console.error("Error getting next estimate number:", error);
    }
  }

  function openCreateDialog(fixedClientId = null) {
    resetForm();
    isEditMode.value = false;
    backendErrors.value = [];
    
    form.value.estimate_id = 'Generating...'; 

    if (fixedClientId) {
      form.value.client_id = Number(fixedClientId); 
      isClientFixed.value = true;
    } else {
      isClientFixed.value = false; 
    }

    dialog.value = true; 
    fetchNextEstimateNumber(); 
  }

  function openEditDialog(estimate) {
    resetForm();
    isEditMode.value = true;
    backendErrors.value = [];
    
    form.value = {
      estimate_id: estimate.estimate_id,
      client_id: estimate.client_id,
      issue_date: estimate.issue_date,
      due_date: estimate.due_date,
      currency: estimate.currency,
      status: estimate.status,
      terms: estimate.terms ?? defaultForm.terms,
      items: estimate.items && estimate.items.length > 0 
            ? JSON.parse(JSON.stringify(estimate.items)) 
            : [{ name: '', quantity: 1, price: 0 }]
    };
    
    isClientFixed.value = false; 
    dialog.value = true;
  }

  async function saveEstimate() {
    const { valid } = await formRef.value.validate();
    if (!valid) return;

    submitLoading.value = true;
    backendErrors.value = [];

    try {
      let response;
      if (isEditMode.value) {
        response = await axios.put(`/api/estimates/${form.value.estimate_id}`, form.value);
        showToast(response.data.message || "Estimate updated successfully!", "success");
      } else {
        response = await axios.post('/api/estimates', form.value);
        showToast(response.data.message || "Estimate created successfully!", "success");
      }
      await fetchEstimates();
      closeDialog();
    } catch (error) {
      console.error("Error saving estimate header:", error);
      if (error.response && error.response.status === 422) {
        const validationErrors = error.response.data.errors || {};
        const errorList = [];
        for (const key in validationErrors) {
          if (Array.isArray(validationErrors[key])) {
            errorList.push(...validationErrors[key]);
          }
        }
        backendErrors.value = errorList;
      } else {
        showToast(error.response?.data?.message || "An error occurred while saving estimate.", "error");
      }
    } finally {
      submitLoading.value = false;
    }
  }

  function confirmDelete(estimateId) {
    estimateToDelete.value = estimateId;
    deleteDialog.value = true;
  }

  async function executeDelete() {
    deleteLoading.value = true;
    try {
      await axios.delete(`/api/estimates/${estimateToDelete.value}`);
      showToast("Estimate deleted successfully.", "success");
      await fetchEstimates();
      deleteDialog.value = false;
    } catch (error) {
      console.error("Error deleting estimate:", error);
      showToast("Failed to delete estimate.", "error");
    } finally {
      deleteLoading.value = false;
      estimateToDelete.value = null;
    }
  }

  function validateDueDate(value) {
    if (!value) return true;
    if (!form.value.issue_date) return true;
    const issue = new Date(form.value.issue_date);
    const due = new Date(value);
    return due >= issue || 'Due date cannot be earlier than issue date';
  }

  function showToast(text, color = 'success') {
    snackbar.value = { show: true, text, color };
  }

  const clientOptions = computed(() => clients.value);

  const filteredEstimates = computed(() => {
    if (!Array.isArray(estimates.value)) return [];

    return estimates.value.filter(inv => {
      if (!inv || !inv.issue_date) return false;

      const query = searchQuery.value?.toLowerCase() || '';
      const invIdStr = String(inv.estimate_id || '').toLowerCase();
      const clientNameStr = String(inv.client_name || '').toLowerCase();
      const matchesSearch = !query || invIdStr.includes(query) || clientNameStr.includes(query);
      const matchesStatus = !filterStatus.value || inv.status === filterStatus.value;

      let matchesDate = true;
      if (dateRangeFilter.value) {
        const issueDate = new Date(inv.issue_date);
        const now = new Date();
        
        const diffMonths = (now.getFullYear() - issueDate.getFullYear()) * 12 + (now.getMonth() - issueDate.getMonth());

        if (dateRangeFilter.value === 'this_month') {
          matchesDate = diffMonths === 0;
        } else if (dateRangeFilter.value === '3_months') {
          matchesDate = diffMonths >= 0 && diffMonths <= 2;
        } else if (dateRangeFilter.value === '6_months') {
          matchesDate = diffMonths >= 0 && diffMonths <= 5; 
        }
      }

      return matchesSearch && matchesStatus && matchesDate;
    });
  });

  function getStatusColor(status) {
    switch (status) {
      case 'accepted': return 'success';
      case 'sent': return 'info';
      case 'open': return 'warning';
      case 'cancelled': return 'grey';
      default: return 'primary';
    }
  }

  function formatAmount(value) {
    if (value === undefined || value === null) return '0.00';
    return new Intl.NumberFormat('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(value);
  }

  function resetFilters() {
    searchQuery.value = '';
    filterStatus.value = null;
    dateRangeFilter.value = null;
  }

  function closeDialog() {
    dialog.value = false;
    resetForm();
  }

  function resetForm() {
    form.value = { ...defaultForm };
    isEditMode.value = false;
    isClientFixed.value = false; 
    backendErrors.value = [];
    if (formRef.value) formRef.value.resetValidation();
  }

  function previewEstimate(id) {
    const routeData = router.resolve({ path: `/estimates/${id}/preview` });
    window.open(routeData.href, '_blank');
  }


  onMounted(async () => {
    await fetchCurrencies();
    loading.value = true;
    await fetchEstimates();
    await fetchClients();
    loading.value = false;

    if (route.query.client_id) {
      const passedClientId = route.query.client_id;
      openCreateDialog(passedClientId);
    }
  });

  defineExpose({
    openCreateDialog
  });
</script>

<style scoped>
.gap-1 { gap: 8px; }
</style>
