<template>
  <v-container fluid class="pa-6 bg-grey-lighten-4" style="min-height: 100vh;">
    <v-row class="mb-4">
      <v-btn variant="text" prepend-icon="mdi-arrow-left" @click="$router.push('/clients')">Back to Clients</v-btn>
    </v-row>

    <div v-if="loading" class="text-center">
      <v-progress-circular indeterminate color="indigo"></v-progress-circular>
    </div>

    <v-row v-else-if="client">
      <v-col cols="12" md="4">
        <v-card class="rounded-xl pa-6 elevation-1">
          <h3 class="text-h6 font-weight-bold mb-4">Client Information</h3>
          <v-btn icon="mdi-pencil" variant="text" size="small" @click="openEditClientDialog"></v-btn>
          <v-list density="comfortable" bg-color="transparent" class="py-0">
            <v-list-item class="px-0">
              <v-list-item-title class="font-weight-bold text-grey-darken-1 text-caption">Name</v-list-item-title>
              <v-list-item-subtitle class="text-body-1 text-black font-weight-medium">
                {{ client.first_name }} {{ client.last_name }}
              </v-list-item-subtitle>
            </v-list-item>

            <v-list-item class="px-0">
              <v-list-item-title class="font-weight-bold text-grey-darken-1 text-caption">Email</v-list-item-title>
              <v-list-item-subtitle class="text-body-1 text-black">{{ client.email }}</v-list-item-subtitle>
            </v-list-item>

            <v-list-item class="px-0">
              <v-list-item-title class="font-weight-bold text-grey-darken-1 text-caption">Phone</v-list-item-title>
              <v-list-item-subtitle class="text-body-1 text-black">{{ client.phone }}</v-list-item-subtitle>
            </v-list-item>

            <v-list-item class="px-0">
              <v-list-item-title class="font-weight-bold text-grey-darken-1 text-caption">Location</v-list-item-title>
              <v-list-item-subtitle class="text-body-1 text-black">
                {{ client.township ? client.township + ', ' : '' }}
                {{ client.city ? client.city + ', ' : '' }}
                {{ client.country }}
              </v-list-item-subtitle>
            </v-list-item>

            <v-list-item class="px-0">
              <v-list-item-title class="font-weight-bold text-grey-darken-1 text-caption">Address</v-list-item-title>
              <v-list-item-subtitle class="text-body-1 text-black text-wrap">
                {{ client.address }}
              </v-list-item-subtitle>
            </v-list-item>
            <v-list-item class="px-0" v-if="hasSocialLinks">
              <v-list-item-title class="font-weight-bold text-grey-darken-1 text-caption">Social</v-list-item-title>
              <v-list-item-subtitle>
                <div class="d-flex flex-wrap gap-2">
                  <a
                    v-for="link in socialLinkButtons"
                    :key="link.key"
                    :href="link.url"
                    target="_blank"
                    rel="noopener noreferrer"
                  >
                    <v-btn icon :color="link.color" variant="tonal" size="small" class="rounded-xl">
                      <v-icon>{{ link.icon }}</v-icon>
                    </v-btn>
                  </a>
                </div>
              </v-list-item-subtitle>
            </v-list-item>
          </v-list>
        </v-card>
      </v-col>

      <v-col cols="12" md="8">
        <v-card class="rounded-xl elevation-1 pa-4">
          <h3 class="text-h6 font-weight-bold mb-4">Contact Persons</h3>
          <v-row>
            <v-col cols="12" md="5">
              <v-form ref="contactFormRef">
                <v-text-field v-model="contactForm.name" label="Name *" variant="outlined" density="comfortable" :rules="[v => !!v || 'Required']" rounded="lg" class="mb-2"></v-text-field>
                <v-text-field v-model="contactForm.phone" label="Phone *" variant="outlined" density="comfortable" :rules="[v => !!v || 'Required']" rounded="lg" class="mb-2"></v-text-field>
                <v-text-field v-model="contactForm.email" label="Email *" variant="outlined" density="comfortable" rounded="lg" class="mb-2"></v-text-field>
                <div class="d-flex align-center gap-2 mt-2">
                  <v-btn v-if="isContactEdit" variant="text" class="text-none rounded-lg" @click="resetContactForm">Cancel</v-btn>
                  <v-btn color="#702E62" @click="saveContactPerson" :loading="contactSubmitLoading" rounded="lg" class="px-6">{{ isContactEdit ? 'Update' : 'Add' }}</v-btn>
                </div>
              </v-form>
            </v-col>
            <v-col cols="12" md="7">
              <v-list>
                <v-list-item v-for="contact in contactList" :key="contact.id" class="border mb-2 rounded-lg">
                  <v-list-item-title class="font-weight-bold">{{ contact.name }}</v-list-item-title>
                  <v-list-item-subtitle>{{ contact.phone }} | {{ contact.email }}</v-list-item-subtitle>
                  <template #append>
                    <v-btn icon="mdi-pencil" color="#702E62" size="small" variant="text" @click="editContact(contact)"></v-btn>
                    <v-btn icon="mdi-delete" size="small" variant="text" color="error" @click="confirmDeleteContact(contact.id)"></v-btn>
                  </template>
                </v-list-item>
              </v-list>
            </v-col>
          </v-row>
        </v-card>
      </v-col>

      <v-col cols="12">
        <v-card class="rounded-xl elevation-1 pa-4">
          <div class="d-flex justify-space-between align-center mb-4">
            <h3 class="text-h6 font-weight-bold">Invoice History</h3>
            <v-btn color="#702E62" rounded="lg" size="large" class="text-none rounded-lg px-5 py-2 elevation-2 font-weight-bold" @click="openCreateInvoiceForm(null)">
              <v-icon start>mdi-plus</v-icon> Create Invoice
            </v-btn>
          </div>

          <v-card class="rounded-xl elevation-1 pa-4 mb-4">
            <v-row dense>
              <v-col cols="12" md="4">
                <v-text-field v-model="searchQuery" label="Search Invoice ID" variant="outlined" density="comfortable" hide-details clearable></v-text-field>
              </v-col>
              <v-col cols="12" md="3">
                <v-select v-model="filterStatus" :items="statusOptions" item-title="label" item-value="value" label="Status" variant="outlined" density="comfortable" hide-details clearable></v-select>
              </v-col>
              <v-col cols="12" md="3">
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
              <v-col cols="12" md="2">
                <v-btn color="grey-darken-1" variant="text" class="text-none rounded-lg font-weight-bold" prepend-icon="mdi-filter-off" @click="resetFilters">Clear Filters</v-btn>
              </v-col>
            </v-row>
          </v-card>

          <v-data-table :headers="invoiceHeaders" :items="filteredInvoices" :loading="invoiceLoading" class="border rounded-lg">
            <template #item.invoice_id="{ item }">
              <span class="font-weight-bold text-indigo-darken-3">{{ item.invoice_id }}</span>
            </template>

            <template #item.dates="{ item }">
              <div class="d-flex flex-column">
                <div><v-icon size="14" class="mr-1 text-medium-emphasis">mdi-calendar-export</v-icon>{{ item.issue_date }}</div>
                <div class="text-caption text-error mt-0.5"><v-icon size="14" class="mr-1">mdi-calendar-clock</v-icon>Due: {{ item.due_date }}</div>
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

            <template #item.status="{ item }">
              <v-chip :color="getStatusColor(item.status)" size="small" variant="flat">
                {{ item.status }}
              </v-chip>
            </template>

            <template #item.actions="{ item }">
              <div class="d-flex justify-end gap-1">
                <v-btn icon="mdi-pencil-outline" variant="text"  size="small" color="#702E62" title="Edit Invoice" @click="openEditDialog(item)"></v-btn>
                <v-btn icon="mdi-delete-outline" variant="text" size="small" color="error" title="Delete Invoice" @click="confirmDelete(item.invoice_id)"></v-btn>
                <v-btn icon="mdi-eye-outline" variant="text" size="small" color="#702E62" title="Preview" @click="previewInvoice(item.invoice_id)"></v-btn>
              </div>
            </template>
          </v-data-table>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
  <v-dialog v-model="clientDialog" max-width="600px">
    <v-card class="rounded-xl">
      <v-toolbar color="" desity="compact" class="px-4">
        <v-toolbar-title class="text-subtitle-1 font-weight-bold">Edit Client Information</v-toolbar-title>
        <v-spacer></v-spacer>
        <v-btn icon="mdi-close" variant="text" size="small" @click="clientDialog = false"></v-btn>
      </v-toolbar>
      <v-card-text class="pa-6">
        <v-alert
          v-if="backendErrors.length > 0"
          type="error"
          variant="tonal"
          class="rounded-lg mb-4"
          density="comfortable"
          dismissible
          @click:close="backendErrors = []"
        >
          <div class="font-weight-bold mb-2">Please fix the following:</div>
          <ul class="pl-4 mb-0">
            <li v-for="(error, index) in backendErrors" :key="index">{{ error }}</li>
          </ul>
        </v-alert>
        <v-row>
          <v-col cols="12" sm="6" class="pb-0">
            <v-text-field v-model="clientForm.first_name" label="First Name *" variant="outlined" density="comfortable" rounded="lg" :rules="[v => !!v || 'Required']"></v-text-field>
          </v-col>
          <v-col cols="12" sm="6" class="pb-0">
            <v-text-field v-model="clientForm.last_name" label="Last Name" variant="outlined" density="comfortable" rounded="lg"></v-text-field>
          </v-col>
          <v-col cols="12" sm="6" class="pb-0">
            <v-text-field v-model="clientForm.email" label="Email" variant="outlined" density="comfortable" rounded="lg" prepend-inner-icon="mdi-email-outline"></v-text-field>
          </v-col>
          <v-col cols="12" sm="6" class="pb-0">
            <v-text-field v-model="clientForm.phone" label="Phone" variant="outlined" density="comfortable" rounded="lg" prepend-inner-icon="mdi-phone-outline"></v-text-field>
          </v-col>
          
          <v-col cols="12">
            <v-divider class="mb-4"></v-divider>
            <div class="text-subtitle-2 text-grey-darken-1 mb-2 font-weight-bold">Location Details</div>
          </v-col>
          
          <v-col cols="12" sm="4" class="pb-0">
            <v-select v-model="clientForm.country" :items="countryOptions" item-title="name" item-value="id" label="Country" variant="outlined" density="comfortable" rounded="lg"></v-select>
          </v-col>
          <v-col cols="12" sm="4" class="pb-0">
            <v-select v-model="clientForm.city" :items="cityOptions" item-title="name" item-value="id" label="City" variant="outlined" density="comfortable" rounded="lg" :disabled="!clientForm.country"></v-select>
          </v-col>

          <v-col cols="12" sm="4" class="pb-0">
            <v-select v-model="clientForm.township" :items="townshipOptions" item-title="name" item-value="id" label="Township" variant="outlined" density="comfortable" rounded="lg" :disabled="!clientForm.city"></v-select>
          </v-col>

          <v-col cols="12" class="pt-0">
            <v-textarea v-model="clientForm.address" label="Full Address" variant="outlined" density="comfortable" rounded="lg" rows="3"></v-textarea>
          </v-col>
          <v-col cols="12" class="pt-0">
            <div class="text-subtitle-2 text-grey-darken-1 mb-2 font-weight-bold">Social Links</div>
          </v-col>
          <v-col cols="12" sm="6" class="pb-0">
            <v-text-field v-model="clientForm.social_links.facebook" label="Facebook URL" variant="outlined" density="comfortable" rounded="lg" prepend-inner-icon="mdi-facebook"></v-text-field>
          </v-col>
          <v-col cols="12" sm="6" class="pb-0">
            <v-text-field v-model="clientForm.social_links.instagram" label="Instagram URL" variant="outlined" density="comfortable" rounded="lg" prepend-inner-icon="mdi-instagram"></v-text-field>
          </v-col>
          <v-col cols="12" sm="6" class="pb-0">
            <v-text-field v-model="clientForm.social_links.linkedin" label="LinkedIn URL" variant="outlined" density="comfortable" rounded="lg" prepend-inner-icon="mdi-linkedin"></v-text-field>
          </v-col>
          <v-col cols="12" sm="6" class="pb-0">
            <v-text-field v-model="clientForm.social_links.tiktok" label="TikTok URL" variant="outlined" density="comfortable" rounded="lg" prepend-inner-icon="mdi-tiktok"></v-text-field>
          </v-col>
          <v-col cols="12" sm="6" class="pb-0">
            <v-text-field v-model="clientForm.social_links.telegram" label="Telegram URL" variant="outlined" density="comfortable" rounded="lg" prepend-inner-icon="mdi-telegram"></v-text-field>
          </v-col>
          <v-col cols="12" sm="6" class="pb-0">
            <v-text-field v-model="clientForm.social_links.viber" label="Viber URL" variant="outlined" density="comfortable" rounded="lg" prepend-inner-icon="mdi-viber"></v-text-field>
          </v-col>
        </v-row>
      </v-card-text>
      <v-card-actions class="px-5 py-3 border-t bg-grey-lighten-5">
        <v-spacer />
        
        <v-btn variant="text" rounded="lg" class="text-none px-5 font-weight-bold" :disabled="clientSubmitLoading" @click="clientDialog = false">Cancel</v-btn>

        <v-btn color="#702E62" variant="flat" rounded="lg" class="text-none px-6 elevation-2 font-weight-bold" :loading="clientSubmitLoading" @click="updateClient">Save</v-btn>
    </v-card-actions>
    </v-card>
  </v-dialog>

  <v-dialog v-model="invoiceDialog" max-width="800px" persistent>
    <v-card class="pa-4 rounded-xl">
      <v-card-title class="d-flex justify-space-between align-center">
        <span>{{ selectedInvoice ? 'Edit Invoice' : 'Create New Invoice' }}</span>
        <v-btn icon="mdi-close" variant="text" @click="invoiceDialog = false"></v-btn>
      </v-card-title>
      <v-card-text>
        <v-alert v-if="backendErrors.length > 0" type="error" variant="tonal" class="rounded-lg mb-4 text-body-2" closable @click:close="backendErrors = []">
          <div class="font-weight-bold mb-1">Please check again:</div>
          <ul class="pl-4 mb-0">
            <li v-for="(err, idx) in backendErrors" :key="idx">{{ err }}</li>
          </ul>
        </v-alert>
        <InvoiceForm 

          v-if="invoiceDialog" 
          :invoiceData="selectedInvoice" 
          :fixedClientId="route.params.id" 
          :clientOptions="[client]" 
          @saved="handleSaved" 
          @validation-error="handleValidationError" 
          @close="invoiceDialog = false"
        />
      </v-card-text>
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
</template>

<script setup>
 import { ref, onMounted, watch, nextTick, computed } from 'vue';
  import { useRoute, useRouter } from 'vue-router'; 
  import axios from 'axios';
  import InvoiceForm from '@/pages/InvoiceForm.vue';
  import '@mdi/font/css/materialdesignicons.css'

  const route = useRoute(); 
  const router = useRouter();

  const searchQuery = ref('');
  const filterStatus = ref(null);
  const dateRangeFilter = ref(null); 
  const dateFilterOptions = [
    { label: 'This Month', value: 'this_month' },
    { label: 'Previous 3 Months', value: '3_months' },
    { label: 'Previous 6 Months', value: '6_months' }
  ];

  const statusOptions = [
    { label: 'All', value: null },
    { label: 'Open', value: 'open' },
    { label: 'Sent', value: 'sent' },
    { label: 'Paid', value: 'paid' },
    { label: 'Cancelled', value: 'cancelled' }
  ];
  
  const filteredInvoices = computed(() => {
    if (!Array.isArray(invoiceList.value)) return [];

    return invoiceList.value.filter(inv => {
      const query = searchQuery.value?.toLowerCase() || '';
      const matchesSearch = !query || inv.invoice_id.toString().includes(query);

      const matchesStatus = !filterStatus.value || inv.status === filterStatus.value;

      let matchesDate = true;
      if (dateRangeFilter.value && inv.issue_date) {
        const issueDate = new Date(inv.issue_date);
        const now = new Date();
        const diffMonths = (now.getFullYear() - issueDate.getFullYear()) * 12 + (now.getMonth() - issueDate.getMonth());

        if (dateRangeFilter.value === 'this_month') matchesDate = diffMonths === 0;
        else if (dateRangeFilter.value === '3_months') matchesDate = diffMonths >= 0 && diffMonths <= 2;
        else if (dateRangeFilter.value === '6_months') matchesDate = diffMonths >= 0 && diffMonths <= 5;
      }

      return matchesSearch && matchesStatus && matchesDate;
    });
  });

  function resetFilters() {
    searchQuery.value = '';
    filterStatus.value = null;
    dateRangeFilter.value = null;
  }

  const clientDialog = ref(false);

  const defaultSocialLinks = {
    facebook: '',
    instagram: '',
    linkedin: '',
    tiktok: '',
    telegram: '',
    viber: ''
  };

  const clientForm = ref({ first_name: '', last_name: '', email: '', phone: '', address: '', country: null, city: null, township: null, social_links: { ...defaultSocialLinks } });
  const clientSubmitLoading = ref(false);
  const client = ref(null); 
  const tab = ref('contacts');
  const loading = ref(false);
  
  const locationData = ref(null);
  const countryOptions = ref([]);
  const cityOptions = ref([]);
  const townshipOptions = ref([]);

  const contactList = ref([]);
  const contactFormRef = ref(null);
  const isContactEdit = ref(false);
  const currentContactId = ref(null);
  const contactSubmitLoading = ref(false);
  const contactForm = ref({ name: '', phone: '', email: '' });

  const backendErrors = ref([]);

  const invoiceList = ref([]);
  const invoiceLoading = ref(false);
  const invoiceDialog = ref(false);
  const selectedInvoice = ref(null);

  const deleteDialog = ref(false);
  const deleteLoading = ref(false);
  const invoiceToDelete = ref(null);

  async function fetchClientDetail() {
    try {
      const response = await axios.get(`/api/client/${route.params.id}`);
      const data = response.data.data;
      
      client.value = {
        ...data,
        country: data.country ?? data.country_id,
        city: data.city ?? data.city_id,
        township: data.township ?? data.township_id,
        social_links: data.social_links ?? { ...defaultSocialLinks }
      };
    } catch (error) { console.error(error); }
  }

  async function fetchLocationConfig() {
    try {
      const response = await axios.get('/api/locations/config');
      locationData.value = response.data.data;
      if (locationData.value) {
        countryOptions.value = Object.keys(locationData.value).map(key => ({ 
          id: key, 
          name: locationData.value[key].name 
        }));
        console.log('Country Options:', countryOptions.value)
      }
    } catch (error) { console.error("Error fetching locations:", error); }
    console.log('Full locationData:', locationData.value);
  }

  watch(() => clientForm.value.country, (newCountry) => {
    if (newCountry && locationData.value && locationData.value[newCountry]) {
      const cities = locationData.value[newCountry].cities || {};
      cityOptions.value = Object.entries(cities).map(([id, data]) => ({
        id: String(id),
        name: data.name
      }));
    } else {
      cityOptions.value = [];
    }
  }, { immediate: true });

  watch(() => clientForm.value.city, (newCity) => {
    const country = clientForm.value.country;
    if (country && newCity && locationData.value?.[country]?.cities?.[newCity]?.townships) {
      const townships = locationData.value[country].cities[newCity].townships;
      townshipOptions.value = Object.entries(townships).map(([id, name]) => ({
        id: id,
        name: name
      }));
    } else {
      townshipOptions.value = [];
    }
  }, { immediate: true });

  async function openEditClientDialog() {
    if (!client.value) {
      console.error("Client data is not loaded yet.");
      return;
    }
    if (!locationData.value) {
      await fetchLocationConfig();
    }

    clientForm.value.first_name = client.value.first_name;
    clientForm.value.last_name = client.value.last_name;
    clientForm.value.email = client.value.email;
    clientForm.value.phone = client.value.phone;
    clientForm.value.address = client.value.address;
    clientForm.value.social_links = {
      ...defaultSocialLinks,
      ...(client.value.social_links || {})
    };

    clientForm.value.country = client.value.country ? String(client.value.country) : null;
    await nextTick();

    clientForm.value.city = client.value.city ? String(client.value.city) : null;
    await nextTick();

    clientForm.value.township = client.value.township ? String(client.value.township) : null;
    clientDialog.value = true;
    console.log("Dialog opened with form:", clientForm.value);
  }

  async function updateClient() {
    clientSubmitLoading.value = true;
    backendErrors.value = [];
    try {
      const payload = {
        ...clientForm.value,
        social_links: normalizeSocialLinks(clientForm.value.social_links)
      };

      await axios.put(`/api/client/${route.params.id}`, payload);
      await fetchClientDetail();
      clientDialog.value = false;
    } catch (error) {
      console.error("Error updating client:", error);
      if (error.response?.data?.errors) {
        const errors = error.response.data.errors;
        backendErrors.value = Object.values(errors).flat();
      } else if (error.response?.data?.message) {
        backendErrors.value = [error.response.data.message];
      } else {
        backendErrors.value = ['Unable to update client. Please try again.'];
      }
    } finally {
      clientSubmitLoading.value = false;
    }
  }

  async function fetchClientInvoices() {
    invoiceLoading.value = true;
    try {
        const response = await axios.get(`/api/clients/${route.params.id}/invoices`);
        invoiceList.value = response.data.data || [];
    } catch (error) {
        console.error("Error fetching invoices:", error);
    } finally {
        invoiceLoading.value = false;
    }
  }

  function openCreateInvoiceForm() {
    backendErrors.value = [];
      selectedInvoice.value = null; 
      invoiceDialog.value = true;
  }

  function openEditDialog(invoice) {
    backendErrors.value = [];
    selectedInvoice.value = invoice;
    invoiceDialog.value = true;    
  }
  
  function onInvoiceSaved() {
    invoiceDialog.value = false;
    fetchClientInvoices(); 
  }

  async function handleSaved(savedInvoice) {
    invoiceDialog.value = false; 
    selectedInvoice.value = null; 
    await fetchClientInvoices();
  }

  function handleValidationError(errors) {
    const errorList = [];
    for (const key in errors) {
      if (Array.isArray(errors[key])) {
        errorList.push(...errors[key]);
      }
    }
    backendErrors.value = errorList;
  }

  function confirmDelete(invoiceId) {
    invoiceToDelete.value = invoiceId;
    deleteDialog.value = true;
  }

  async function executeDelete() {
    deleteLoading.value = true;
    try {
      await axios.delete(`/api/invoices/${invoiceToDelete.value}`);
      await fetchClientInvoices();
      deleteDialog.value = false;
    } catch (error) {
      console.error("Error deleting invoice:", error);
      showToast("Failed to delete invoice.", "error");
    } finally {
      deleteLoading.value = false;
      invoiceToDelete.value = null;
    }
  }
  
  const invoiceHeaders = [
    { title: 'Invoice ID', key: 'invoice_id' },
    { title: 'Dates (Issue/Due)', key: 'dates', sortable: false },
    { title: 'Subtotal', key: 'sub_total', sortable: true },
    { title: 'Discount', key: 'discount_value', sortable: false },
    { title: 'Total Billing', key: 'grand_total', sortable: true },
    { title: 'Status', key: 'status', sortable: true },
    { title: 'Actions', key: 'actions', sortable: false }
  ];

  async function fetchContactPersons() {
    try {
      const response = await axios.get(`/api/clients/${route.params.id}/contacts`);
      contactList.value = response.data.data || [];
    } catch (error) { console.error(error); }
  }

  async function saveContactPerson() {
    const { valid } = await contactFormRef.value.validate();
    if (!valid) return;
    contactSubmitLoading.value = true;
    try {
      if (isContactEdit.value) {
        await axios.put(`/api/contacts/${currentContactId.value}`, contactForm.value);
      } else {
        await axios.post(`/api/clients/${route.params.id}/contacts`, contactForm.value);
      }
      await fetchContactPersons();
      resetContactForm();
    } finally { contactSubmitLoading.value = false; }
  }

  function editContact(contact) {
    isContactEdit.value = true;
    currentContactId.value = contact.id;
    contactForm.value = { name: contact.name, phone: contact.phone, email: contact.email };
  }

  async function confirmDeleteContact(id) {
    if(confirm('Are you sure?')) {
      await axios.delete(`/api/contacts/${id}`);
      await fetchContactPersons();
    }
  }

  function resetContactForm() {
    isContactEdit.value = false;
    currentContactId.value = null; 
    contactForm.value = { name: '', phone: '', email: '' }; 
    
    if (contactFormRef.value) {
      contactFormRef.value.resetValidation();
    }
  }

  function goToCreateInvoice() {
    router.push({ path: '/invoices', query: { client_id: route.params.id } });
  }
  
  function getStatusColor(status) {
    switch (status) {
      case 'paid': return 'success';
      case 'sent': return 'info';
      case 'open': return 'warning';
      case 'cancelled': return 'grey';
      default: return 'primary';
    }
  }

  function normalizeUrl(value) {
    const raw = String(value || '').trim();
    if (!raw) return '';
    if (/^https?:\/\//i.test(raw)) {
      return raw;
    }
    if (/^\/\//.test(raw)) {
      return `https:${raw}`;
    }
    return `https://${raw}`;
  }

  function normalizeSocialLinks(links) {
    if (!links || typeof links !== 'object') {
      return { ...defaultSocialLinks };
    }

    return {
      ...defaultSocialLinks,
      facebook: normalizeUrl(links.facebook),
      instagram: normalizeUrl(links.instagram),
      linkedin: normalizeUrl(links.linkedin),
      tiktok: normalizeUrl(links.tiktok),
      telegram: normalizeUrl(links.telegram),
      viber: normalizeUrl(links.viber),
    };
  }

  const socialLinkButtons = computed(() => {
    const rawLinks = client.value?.social_links || {};
    const links = normalizeSocialLinks(rawLinks);
    const mapping = {
      facebook: { icon: 'mdi-facebook', color: '#1877F2', label: 'Facebook' },
      instagram: { icon: 'mdi-instagram', color: '#E4405F', label: 'Instagram' },
      linkedin: { icon: 'mdi-linkedin', color: '#0A66C2', label: 'LinkedIn' },
      tiktok: { icon: 'mdi-music-note', color: '#000000', label: 'TikTok' },
      telegram: { icon: 'mdi-telegram', color: '#0088CC', label: 'Telegram' },
      viber: { icon: 'mdi-viber', color: '#7C4DFF', label: 'Viber' },
    };

    return Object.keys(mapping)
      .filter(key => links[key])
      .map(key => ({
        key,
        url: links[key],
        icon: mapping[key].icon,
        color: mapping[key].color,
        label: mapping[key].label,
      }));
  });

  const hasSocialLinks = computed(() => socialLinkButtons.value.length > 0);

  function formatAmount(value) {
    if (value === undefined || value === null) return '0.00';
    return new Intl.NumberFormat('en-US', { 
      minimumFractionDigits: 2, 
      maximumFractionDigits: 2 
    }).format(value);
  }

  function previewInvoice(id) {
    const routeData = router.resolve({ path: `/invoices/${id}/preview` });
    window.open(routeData.href, '_blank');
  }

  onMounted(() => {
    fetchClientDetail();
    fetchContactPersons();
    fetchClientInvoices();
    fetchLocationConfig();
  });
</script>