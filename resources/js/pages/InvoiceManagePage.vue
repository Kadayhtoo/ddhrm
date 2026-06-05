<template>
  <v-container fluid class="pa-6 bg-grey-lighten-4" style="min-height: 100vh;">
    <v-row class="mb-4 align-center justify-space-between">
      <v-col cols="12" sm="auto">
        <h1 class="text-h4 font-weight-bold text-grey-darken-4">Invoices Management</h1>
        <p class="text-body-1 text-medium-emphasis mb-0">Create, edit, manage and track your client invoices and billing status.</p>
      </v-col>
      <v-col cols="12" sm="auto" class="text-sm-right">
        <v-btn color="#702E62" rounded="lg" size="large" class="text-none rounded-lg px-5 py-2 elevation-2 font-weight-bold" @click="openCreateDialog(null)">
          <v-icon start>mdi-plus</v-icon> Create Invoice
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
                { label: 'Paid', value: 'paid' },
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

        <!-- <v-col cols="12" sm="6" md="2" class="pa-2">
          <v-text-field v-model="filterIssueDate" type="date" label="Issue Date" variant="outlined" density="comfortable" hide-details rounded="lg" color="indigo-lighten-1" clearable persistent-placeholder></v-text-field>
        </v-col>

        <v-col cols="12" sm="6" md="2" class="pa-2">
          <v-text-field v-model="filterDueDate" type="date" label="Due Date" variant="outlined" density="comfortable" hide-details rounded="lg" color="indigo-lighten-1" clearable persistent-placeholder></v-text-field>
        </v-col> -->

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
      <v-data-table :headers="headers" :items="filteredInvoices" :loading="loading" hover class="bg-transparent text-grey-darken-3">
        <template #loading>
          <v-linear-progress indeterminate color="indigo-darken-2" height="3"></v-linear-progress>
        </template>
        
        <template #item.invoice_id="{ item }">
          <span class="font-weight-bold text-indigo-darken-3">{{ item.invoice_id }}</span>
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

        <template #item.discount_value="{ item }">
          <span class="font-weight-bold">
            {{ formatAmount(item.discount_value) }} <span class="text-caption text-medium-emphasis ml-0.5">{{ item.discount_type === 'percentage' ? '%' : item.currency }}</span>
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
          <v-btn icon="mdi-pencil-outline" variant="text" size="small" color="#702E62" title="Edit Invoice" @click="openEditDialog(item)"></v-btn>
          <v-btn icon="mdi-delete-outline" variant="text" size="small" color="error" title="Delete Invoice" @click="confirmDelete(item.invoice_id)"></v-btn>
          
          <v-btn icon="mdi-eye-outline" variant="text" size="small" color="#702E62" title="Preview" @click="previewInvoice(item.invoice_id)"></v-btn>
          <!-- <v-btn icon="mdi-download" variant="text" size="small" color="success" title="Download PDF" @click="downloadInvoice(item.invoice_id)"></v-btn> -->
        </div>
      </template>
      </v-data-table>
    </v-card>

    <v-dialog v-model="dialog" max-width="650px" persistent>
      <v-card class="rounded-xl pa-2">
        <v-card-title class="d-flex align-center justify-space-between px-4 pt-4 pb-2">
          <span class="text-h5 font-weight-bold text-grey-darken-4">
            {{ isEditMode ? 'Edit Invoice: ' + form.invoice_id : 'Create New Invoice' }}
          </span>
          <v-btn icon="mdi-close" variant="text" size="small" @click="closeDialog"></v-btn>
        </v-card-title>
        <v-divider class="mx-4"></v-divider>
        
        <v-card-text class="px-4 py-4">
          <v-alert v-if="backendErrors.length > 0" type="error" variant="tonal" class="rounded-lg mb-4 text-body-2" closable @click:close="backendErrors = []">
            <div class="font-weight-bold mb-1">Please check again Please send payment within 7 days of receiving this invoice. :</div>
            <ul class="pl-4 mb-0">
              <li v-for="(err, idx) in backendErrors" :key="idx">{{ err }}</li>
            </ul>
          </v-alert>

          <v-form ref="formRef">
            <v-row>
              <v-col cols="12" sm="6" class="py-1">
                <v-text-field v-model="form.invoice_id" label="Invoice ID *" variant="outlined" density="comfortable" :rules="[v => !!v || 'Invoice ID is required']" :disabled="isEditMode" rounded="lg" color="indigo-lighten-1" persistent-hint></v-text-field>
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
                <v-select v-model="form.status" :items="['open', 'sent', 'paid', 'cancelled']" label="Status *" variant="outlined" density="comfortable" :rules="[v => !!v || 'Status is required']" class="text-capitalize" rounded="lg" color="indigo-lighten-1"></v-select>
              </v-col>

              <v-col cols="12" sm="6" class="py-1">
                <v-select v-model="form.discount_type" :items="[{title: 'Fixed Amount', value: 'fixed'}, {title: 'Percentage (%)', value: 'percentage'}]" item-title="title" item-value="value" label="Discount Type" variant="outlined" density="comfortable" rounded="lg" color="indigo-lighten-1"></v-select>
              </v-col>

              <v-col cols="12" sm="6" class="py-1">
                <v-text-field v-model.number="form.discount_value" label="Discount Value" type="number" min="0" variant="outlined" density="comfortable" rounded="lg" color="indigo-lighten-1" prepend-inner-icon="mdi-tag-outline"></v-text-field>
              </v-col>
              
              <v-col cols="12" class="mt-6">
                <div class="text-subtitle-1 font-weight-bold mb-2">Terms and Conditions</div>
                <RichTextEditor v-model="form.terms" label="Terms and Conditions" />
              </v-col>
            </v-row>
          </v-form>

          <v-divider class="my-4"></v-divider>
          <div class="text-subtitle-1 font-weight-bold mb-2">Invoice Items</div>

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
            <v-btn color="#702E62" variant="flat" rounded="lg" class="text-none px-6 elevation-2 font-weight-bold" :loading="submitLoading" @click="saveInvoice">
                {{ isEditMode ? 'Update' : 'Save' }}
            </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
    <v-dialog v-model="deleteDialog" max-width="400">
      <v-card class="rounded-xl pa-2">
        <v-card-title class="text-h5 font-weight-bold text-grey-darken-4 pt-4 px-4">Delete Invoice?</v-card-title>
        <v-card-text class="text-body-1 text-medium-emphasis px-4 py-2">Are you sure you want to permanently delete invoice <span class="font-weight-bold text-indigo-darken-3">{{ invoiceToDelete }}</span>? This action cannot be reversed.</v-card-text>
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
  const invoices = ref([]);
  const clients = ref([]);
  const loading = ref(false);
  const dialog = ref(false);
  const isEditMode = ref(false); 
  const isClientFixed = ref(false); 
  const submitLoading = ref(false);
  const formRef = ref(null);

  const deleteDialog = ref(false);
  const deleteLoading = ref(false);
  const invoiceToDelete = ref(null);

  const searchQuery = ref('');
  const filterStatus = ref(null);
  const filterIssueDate = ref('');
  const filterDueDate = ref('');
  const backendErrors = ref([]);
  const dateFilter = ref(null);
  const dateRangeFilter = ref(null);
  const dateFilterOptions = [
    { label: 'This Month', value: 'this_month' },
    { label: 'Previous 3 Months', value: '3_months' },
    { label: 'Previous 6 Months', value: '6_months' }
  ];

  const snackbar = ref({ show: false, text: '', color: 'success' });
  const currencyOptions = ref([]);
  const defaultForm = {
    invoice_id: '',
    client_id: null,
    issue_date: new Date().toISOString().substr(0, 10), 
    due_date: '',
    currency: 'MMK',
    discount_type: 'fixed',
    discount_value: 0,
    status: 'open',
    terms: 'Please send payment within 7 days of receiving this invoice.',
    items: [{ name: '', quantity: 1, price: 0 }]
  };
  const form = ref({ ...defaultForm });
  const headers = [
    { title: 'Invoice ID', key: 'invoice_id', sortable: true },
    { title: 'Client Details', key: 'client_name', sortable: true },
    { title: 'Dates (Issue/Due)', key: 'dates', sortable: false },
    { title: 'Subtotal', key: 'sub_total', sortable: true },
    { title: 'Discount', key: 'discount_value', sortable: false },
    { title: 'Total Billing', key: 'grand_total', sortable: true },
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

  async function fetchInvoices() {
    loading.value = true;
    try {
      const response = await axios.get('/api/invoices');
      invoices.value = response.data.data || response.data;
    } catch (error) {
      console.error("Error fetching invoices:", error);
      showToast("Failed to fetch invoices list.", "error");
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

  async function fetchNextInvoiceNumber() {
    try {
      const response = await axios.get('/api/invoices/next-number');
      if (response.data && response.data.invoice_id) {
        form.value.invoice_id = response.data.invoice_id;
      }
    } catch (error) {
      console.error("Error getting next invoice number:", error);
    }
  }

  function openCreateDialog(fixedClientId = null) {
    resetForm();
    isEditMode.value = false;
    backendErrors.value = [];
    
    form.value.invoice_id = 'Generating...'; 

    if (fixedClientId) {
      form.value.client_id = Number(fixedClientId); 
      isClientFixed.value = true;
    } else {
      isClientFixed.value = false; 
    }

    dialog.value = true; 
    fetchNextInvoiceNumber(); 
  }

  function openEditDialog(invoice) {
    resetForm();
    isEditMode.value = true;
    backendErrors.value = [];
    
    form.value = {
      invoice_id: invoice.invoice_id,
      client_id: invoice.client_id,
      issue_date: invoice.issue_date,
      due_date: invoice.due_date,
      currency: invoice.currency,
      discount_type: invoice.discount_type || 'fixed',
      discount_value: Number(invoice.discount_value) || 0,
      status: invoice.status,
      terms: invoice.terms ?? defaultForm.terms,
      items: invoice.items && invoice.items.length > 0 
            ? JSON.parse(JSON.stringify(invoice.items)) 
            : [{ name: '', quantity: 1, price: 0 }]
    };
    
    isClientFixed.value = false; 
    dialog.value = true;
  }

  async function saveInvoice() {
    const { valid } = await formRef.value.validate();
    if (!valid) return;

    submitLoading.value = true;
    backendErrors.value = [];

    try {
      let response;
      if (isEditMode.value) {
        response = await axios.put(`/api/invoices/${form.value.invoice_id}`, form.value);
        showToast(response.data.message || "Invoice updated successfully!", "success");
      } else {
        response = await axios.post('/api/invoices', form.value);
        showToast(response.data.message || "Invoice created successfully!", "success");
      }
      await fetchInvoices();
      closeDialog();
    } catch (error) {
      console.error("Error saving invoice header:", error);
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
        showToast(error.response?.data?.message || "An error occurred while saving invoice.", "error");
      }
    } finally {
      submitLoading.value = false;
    }
  }

  function confirmDelete(invoiceId) {
    invoiceToDelete.value = invoiceId;
    deleteDialog.value = true;
  }

  async function executeDelete() {
    deleteLoading.value = true;
    try {
      await axios.delete(`/api/invoices/${invoiceToDelete.value}`);
      showToast("Invoice deleted successfully.", "success");
      await fetchInvoices();
      deleteDialog.value = false;
    } catch (error) {
      console.error("Error deleting invoice:", error);
      showToast("Failed to delete invoice.", "error");
    } finally {
      deleteLoading.value = false;
      invoiceToDelete.value = null;
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

  const filteredInvoices = computed(() => {
    if (!Array.isArray(invoices.value)) return [];

    return invoices.value.filter(inv => {
      if (!inv || !inv.issue_date) return false;

      const query = searchQuery.value?.toLowerCase() || '';
      const invIdStr = String(inv.invoice_id || '').toLowerCase();
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
      case 'paid': return 'success';
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
    // filterIssueDate.value = '';
    // filterDueDate.value = '';
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

  function previewInvoice(id) {
    const routeData = router.resolve({ path: `/invoices/${id}/preview` });
    window.open(routeData.href, '_blank');
  }

  onMounted(async () => {
    await fetchCurrencies();
    loading.value = true;
    await fetchInvoices();
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
