<template>
  <v-container fluid class="pa-6 bg-grey-lighten-4" style="min-height: 100vh;">
    <v-row class="mb-4 align-center justify-space-between">
      <v-col cols="12" sm="auto">
        <h1 class="text-h4 font-weight-bold text-grey-darken-4">Clients Management</h1>
        <p class="text-body-1 text-medium-emphasis mb-0">Manage your system clients, locations, and contact info.</p>
      </v-col>
      <v-col cols="12" sm="auto" class="text-sm-right">
        <v-btn color="#702E62" rounded="lg" size="large" class="text-none rounded-lg px-5 py-2 elevation-2 font-weight-bold" @click="openCreateDialog">
          <v-icon start>mdi-plus</v-icon> Add Client
        </v-btn>
      </v-col>
    </v-row>

    <v-card class="rounded-xl mb-6 pa-4 border-0 elevation-1 bg-white">
      <v-row class="align-center" dense>
        <v-col cols="12" lg="2" md="6" sm="12" class="pa-2">
          <v-text-field v-model="searchQuery" prepend-inner-icon="mdi-magnify" label="Search ..." variant="outlined" density="comfortable" hide-details rounded="lg" color="indigo-lighten-1" clearable></v-text-field>
        </v-col>
        <v-col cols="12" lg="2" md="6" sm="4" class="pa-2">
          <v-select v-model="filterCountry" :items="filterCountryOptions" item-title="name" item-value="id" label="Country" variant="outlined" density="comfortable" hide-details rounded="lg" color="indigo-lighten-1" clearable></v-select>
        </v-col>
        <v-col cols="12" lg="2" md="6" sm="4" class="pa-2">
          <v-select v-model="filterCity" :items="filterCityOptions" item-title="name" item-value="id" label="City/Region" variant="outlined" density="comfortable" hide-details rounded="lg" color="indigo-lighten-1" :disabled="!filterCountry" clearable></v-select>
        </v-col>
        <v-col cols="12" lg="2" md="6" sm="4" class="pa-2">
          <v-select v-model="filterTownship" :items="filterTownshipOptions" item-title="name" item-value="id" label="Township" variant="outlined" density="comfortable" hide-details rounded="lg" color="indigo-lighten-1" :disabled="!filterCity" clearable></v-select>
        </v-col>
        <v-col cols="12" lg="3" md="6" class="pa-2 text-right">
          <v-btn color="grey-darken-1" variant="text" class="text-none rounded-lg font-weight-bold mr-2" prepend-icon="mdi-filter-off" @click="resetFilters"></v-btn>
        </v-col>
      </v-row>
    </v-card>

    <v-card class="rounded-xl border-0 elevation-1 bg-white overflow-hidden">
      <v-data-table :headers="headers" :items="filteredClients" :loading="loading" hover class="bg-transparent text-grey-darken-3">
        <template #loading>
          <v-linear-progress indeterminate color="indigo-darken-2" height="3"></v-linear-progress>
        </template>
        
        <template #item.name="{ item }">
          <div class="d-flex align-center py-2">
            <v-avatar color="indigo-lighten-5" class="mr-3" size="42">
              <span class="indigo-darken-3 font-weight-bold text-subtitle-1">
                {{ item.first_name ? item.first_name.charAt(0).toUpperCase() : 'C' }}
              </span>
            </v-avatar>
            <div>
              <div class="font-weight-bold text-body-1 text-grey-darken-4">
                {{ item.first_name }} {{ item.last_name }}
              </div>
              <div class="text-caption text-medium-emphasis d-flex align-center mt-0.5">
                <v-icon size="14" class="mr-1">mdi-email-outline</v-icon>{{ item.email || 'No email' }}
              </div>
            </div>
          </div>
        </template>

        <template #item.phone="{ item }">
          <span class="font-weight-medium text-body-2"><v-icon size="16" class="mr-1 text-medium-emphasis">mdi-phone-outline</v-icon>{{ item.phone || '-' }}</span>
        </template>

        <template #item.location="{ item }">
          <div class="text-body-2">
            <v-chip size="small" variant="flat" color="blue-grey-lighten-5" class="text-blue-grey-darken-3 font-weight-medium rounded-lg">
              <v-icon size="14" start>mdi-map-marker-outline</v-icon>
              {{ getCountryName(item.country) }} / {{ getCityName(item.country, item.city) }}
            </v-chip>
            <div class="text-caption text-medium-emphasis pl-1 mt-1">{{ getTownshipName(item.country, item.city, item.township) }}</div>
          </div>
        </template>

        <template #item.actions="{ item }">
          <div class="d-flex justify-end">
            <v-btn 
              color="indigo-darken-2" 
              variant="tonal" 
              size="small" 
              rounded="lg"
              :to="`/clients/${item.id}`"
            >
              View Details
            </v-btn>
          </div>
        </template>
      </v-data-table>
    </v-card>
    <v-dialog v-model="dialog" max-width="650px" persistent>
      <v-card class="rounded-xl pa-2">
        <v-card-title class="d-flex align-center justify-space-between px-4 pt-4 pb-2">
          <span class="text-h5 font-weight-bold text-grey-darken-4">{{ isEdit ? 'Update Client' : 'Create New Client' }}</span>
          <v-btn icon="mdi-close" variant="text" size="small" @click="closeDialog"></v-btn>
        </v-card-title>
        <v-divider class="mx-4"></v-divider>
        <v-card-text class="px-4 py-4">
          <v-form ref="formRef">
            <v-row>
              <v-col cols="12" sm="6" class="py-1">
                <v-text-field v-model="form.first_name" label="First Name *" variant="outlined" density="comfortable" :rules="[v => !!v || 'First name is required']" rounded="lg"></v-text-field>
              </v-col>
              <v-col cols="12" sm="6" class="py-1">
                <v-text-field v-model="form.last_name" label="Last Name" variant="outlined" density="comfortable"  rounded="lg"></v-text-field>
              </v-col>
              
              <v-col cols="12" sm="6" class="py-1">
                <v-text-field v-model="form.email" label="Email Address" type="email" variant="outlined" density="comfortable" rounded="lg"></v-text-field>
              </v-col>
              <v-col cols="12" sm="6" class="py-1">
                <v-text-field v-model="form.phone" label="Phone Number" variant="outlined" density="comfortable" rounded="lg"></v-text-field>
              </v-col>
              <v-col cols="12" sm="6" class="py-1">
                <v-select v-model="form.country" :items="countryOptions" item-title="name" item-value="id" label="Country" variant="outlined" density="comfortable" rounded="lg"></v-select>
              </v-col>
              <v-col cols="12" sm="6" class="py-1">
                <v-select v-model="form.city" :items="cityOptions" item-title="name" item-value="id" label="City / Region" variant="outlined" density="comfortable" :disabled="!form.country" rounded="lg"></v-select>
              </v-col>
              <v-col cols="12" sm="6" class="py-1">
                <v-select v-model="form.township" :items="townshipOptions" item-title="name" item-value="id" label="Township" variant="outlined" density="comfortable" :disabled="!form.city" rounded="lg"></v-select>
              </v-col>
              <v-col cols="12" class="py-1">
                <v-textarea v-model="form.address" label="Street Address" variant="outlined" density="comfortable" rows="2" rounded="lg"></v-textarea>
              </v-col>
              <v-col cols="12" class="py-1">
                <div class="text-subtitle-2 text-grey-darken-1 mb-2 font-weight-bold">Social Links</div>
              </v-col>
              <v-col cols="12" sm="6" class="py-1">
                <v-text-field v-model="form.social_links.facebook" label="Facebook URL" variant="outlined" density="comfortable" rounded="lg" prepend-inner-icon="mdi-facebook"></v-text-field>
              </v-col>
              <v-col cols="12" sm="6" class="py-1">
                <v-text-field v-model="form.social_links.instagram" label="Instagram URL" variant="outlined" density="comfortable" rounded="lg" prepend-inner-icon="mdi-instagram"></v-text-field>
              </v-col>
              <v-col cols="12" sm="6" class="py-1">
                <v-text-field v-model="form.social_links.linkedin" label="LinkedIn URL" variant="outlined" density="comfortable" rounded="lg" prepend-inner-icon="mdi-linkedin"></v-text-field>
              </v-col>
              <v-col cols="12" sm="6" class="py-1">
                <v-text-field v-model="form.social_links.tiktok" label="TikTok URL" variant="outlined" density="comfortable" rounded="lg" prepend-inner-icon="mdi-tiktok"></v-text-field>
              </v-col>
              <v-col cols="12" sm="6" class="py-1">
                <v-text-field v-model="form.social_links.telegram" label="Telegram URL" variant="outlined" density="comfortable" rounded="lg" prepend-inner-icon="mdi-telegram"></v-text-field>
              </v-col>
              <v-col cols="12" sm="6" class="py-1">
                <v-text-field v-model="form.social_links.viber" label="Viber URL" variant="outlined" density="comfortable" rounded="lg" prepend-inner-icon="mdi-viber"></v-text-field>
              </v-col>
            </v-row>
          </v-form>
        </v-card-text>
        <v-card-actions class="px-6 pb-4">
            <v-spacer />
            
            <v-btn variant="text" rounded="lg" class="text-none px-5 font-weight-bold" :disabled="submitLoading" @click="closeDialog">Cancel</v-btn>

            <v-btn  color="#702E62"  variant="flat"  rounded="lg" class="text-none px-6 elevation-2 font-weight-bold"  :loading="submitLoading"  @click="saveClient">{{ isEdit ? 'Update' : 'Save' }}</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <v-dialog v-model="deleteDialog" max-width="400">
      <v-card class="rounded-xl pa-2">
        <v-card-title class="text-h5 font-weight-bold text-grey-darken-4 pt-4 px-4">Delete Client?</v-card-title>
        <v-card-text class="text-body-1 text-medium-emphasis px-4 py-2">Are you sure you want to delete this client? All associated data will be removed permanently.</v-card-text>
        <v-card-actions class="px-4 pb-2 pt-4">
          <v-spacer></v-spacer>
          <v-btn variant="text" class="text-none font-weight-bold rounded-lg" @click="deleteDialog = false">Cancel</v-btn>
          <v-btn color="error" class="text-none font-weight-bold rounded-lg px-4" :loading="deleteLoading" @click="executeDelete">Delete</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <v-dialog v-model="contactDialog" max-width="800px" persistent>
      <v-card class="rounded-xl pa-2">
        <v-card-title class="d-flex align-center justify-space-between px-4 pt-4 pb-2">
          <div>
            <span class="text-h5 font-weight-bold text-grey-darken-4">Contact Persons</span>
            <div class="text-caption text-medium-emphasis font-weight-medium mt-0.5" v-if="selectedClient">
              Client: {{ selectedClient.first_name }} {{ selectedClient.last_name }}
            </div>
          </div>
          <v-btn icon="mdi-close" variant="text" size="small" @click="contactDialog = false"></v-btn>
        </v-card-title>
        <v-divider class="mx-4"></v-divider>

        <v-card-text class="px-4 py-4">
          <v-row>
            <v-col cols="12" md="5" class="border-md-e pe-md-4">
              <div class="text-subtitle-1 font-weight-bold text-grey-darken-3 mb-3">
                {{ isContactEdit ? 'Edit Contact Person' : 'Add New Contact Person' }}
              </div>
              <v-form ref="contactFormRef">
                <v-text-field v-model="contactForm.name" label="Contact Name *" variant="outlined" density="comfortable" :rules="[v => !!v || 'Name is required']" rounded="lg" class="mb-2"></v-text-field>
                <v-text-field v-model="contactForm.phone" label="Phone Number *" variant="outlined" density="comfortable" :rules="[v => !!v || 'Phone is required']" rounded="lg" class="mb-2"></v-text-field>
                <v-text-field v-model="contactForm.email" label="Email Address" type="email" variant="outlined" density="comfortable" rounded="lg" class="mb-4"></v-text-field>
                
                <div class="d-flex justify-end gap-1">
                  <v-btn v-if="isContactEdit" variant="text" size="small" class="text-none rounded-lg font-weight-bold" @click="resetContactForm">Cancel</v-btn>
                  <v-btn color="#702E62" size="comfortable" class="text-none rounded-lg font-weight-bold px-4" :loading="contactSubmitLoading" @click="saveContactPerson">
                    {{ isContactEdit ? 'Update' : 'Save Contact' }}
                  </v-btn>
                </div>
              </v-form>
            </v-col>

            <v-col cols="12" md="7" class="ps-md-4">
              <div class="text-subtitle-1 font-weight-bold text-grey-darken-3 mb-3">Existing Contacts</div>
              <v-list class="bg-transparent pa-0 overflow-y-auto" style="max-height: 320px;">
                <div v-if="contactList.length === 0" class="text-center py-8 text-medium-emphasis text-body-2">
                  <v-icon size="40" color="grey-lighten-1" class="mb-2">mdi-account-outline</v-icon>
                  <div>No contact persons registered yet.</div>
                </div>
                <v-list-item v-for="contact in contactList" :key="contact.id" class="rounded-xl bg-grey-lighten-5 border mb-2 pa-3">
                  <div class="d-flex align-center justify-space-between w-100">
                    <div>
                      <div class="font-weight-bold text-body-1 text-grey-darken-4">{{ contact.name }}</div>
                      <div class="text-body-2 text-grey-darken-2 mt-0.5"><v-icon size="14" class="mr-1">mdi-phone</v-icon>{{ contact.phone }}</div>
                      <div v-if="contact.email" class="text-caption text-medium-emphasis mt-0.5"><v-icon size="14" class="mr-1">mdi-email</v-icon>{{ contact.email }}</div>
                    </div>
                    <div class="d-flex">
                      <v-btn icon="mdi-pencil-outline" variant="text" size="small" color="indigo-darken-2" @click="editContactPerson(contact)"></v-btn>
                      <v-btn icon="mdi-delete-outline" variant="text" size="small" color="error" @click="confirmDeleteContact(contact.id)"></v-btn>
                    </div>
                  </div>
                </v-list-item>
              </v-list>
            </v-col>
          </v-row>
        </v-card-text>
      </v-card>
    </v-dialog>

    <v-dialog v-model="contactDeleteDialog" max-width="380">
      <v-card class="rounded-xl pa-2">
        <v-card-title class="text-h5 font-weight-bold text-grey-darken-4 pt-4 px-4">Delete Contact Person?</v-card-title>
        <v-card-text class="text-body-1 text-medium-emphasis px-4 py-2">Are you sure you want to remove this contact person? This action cannot be undone.</v-card-text>
        <v-card-actions class="px-4 pb-2 pt-4">
          <v-spacer></v-spacer>
          <v-btn variant="text" class="text-none font-weight-bold rounded-lg" @click="contactDeleteDialog = false">Cancel</v-btn>
          <v-btn color="error" class="text-none font-weight-bold rounded-lg px-4" :loading="contactDeleteLoading" @click="executeDeleteContact">Delete</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-container>
</template>

<script setup>
  import { ref, onMounted, watch, computed, nextTick } from 'vue';
  import { useRouter } from 'vue-router'; 
  import axios from 'axios';

  const router = useRouter(); 
  const clients = ref([]);
  const loading = ref(false);
  const dialog = ref(false);
  const isEdit = ref(false);
  const submitLoading = ref(false);
  const formRef = ref(null);
  const currentClientId = ref(null);
  const locationData = ref({});
  const deleteDialog = ref(false);
  const deleteLoading = ref(false);
  const clientIdToDelete = ref(null);

  const contactDialog = ref(false);
  const selectedClient = ref(null);
  const contactList = ref([]);
  const contactFormRef = ref(null);
  const isContactEdit = ref(false);
  const currentContactId = ref(null);
  const contactSubmitLoading = ref(false);
  const contactForm = ref({ name: '', phone: '', email: '' });

  const contactDeleteDialog = ref(false);
  const contactDeleteLoading = ref(false);
  const contactIdToDelete = ref(null);

  const searchQuery = ref('');
  const filterCountry = ref(null);
  const filterCity = ref(null);
  const filterTownship = ref(null);
  const filterCountryOptions = ref([]);
  const filterCityOptions = ref([]);
  const filterTownshipOptions = ref([]);

  const countryOptions = ref([]);
  const cityOptions = ref([]);
  const townshipOptions = ref([]);

  const form = ref({
    first_name: '',
    last_name: '',
    email: '',
    phone: '',
    address: '',
    country: null,
    city: null,
    township: null,
    social_links: {
      facebook: '',
      instagram: '',
      linkedin: '',
      tiktok: '',
      telegram: '',
      viber: ''
    }
  });

  const headers = [
    { title: 'Client Details', key: 'name', sortable: true },
    { title: 'Phone Number', key: 'phone', sortable: false },
    { title: 'Region/Location', key: 'location', sortable: true },
    { title: 'Actions', key: 'actions', align: 'end', sortable: false },
  ];

  function getCountryName(id) {
    return locationData.value[id]?.name || id || '-';
  }

  function getCityName(countryId, cityId) {
    return locationData.value[countryId]?.cities[cityId]?.name || cityId || '-';
  }

  function getTownshipName(countryId, cityId, townshipId) {
    return locationData.value[countryId]?.cities[cityId]?.townships[townshipId] || townshipId || '-';
  }

  function navigateToCreateInvoice(client) {
    router.push({
      path: '/invoices', 
      query: { client_id: client.id }
    });
  }

  async function openContactDialog(client) {
    selectedClient.value = client;
    contactList.value = [];
    resetContactForm();
    contactDialog.value = true;
    await fetchContactPersons();
  }

  async function fetchContactPersons() {
    if (!selectedClient.value) return;
    try {
      const response = await axios.get(`/api/clients/${selectedClient.value.id}/contacts`);
      contactList.value = response.data.data;
    } catch (error) {
      console.error("Error fetching contact persons:", error);
    }
  }

  async function saveContactPerson() {
    const { valid } = await contactFormRef.value.validate();
    if (!valid) return;

    contactSubmitLoading.value = true;
    try {
      if (isContactEdit.value) {
        await axios.put(`/api/contacts/${currentContactId.value}`, contactForm.value);
      } else {
        await axios.post(`/api/clients/${selectedClient.value.id}/contacts`, contactForm.value);
      }
      await fetchContactPersons();
      resetContactForm();
    } catch (error) {
      console.error("Error saving contact person:", error);
    } finally {
      contactSubmitLoading.value = false;
    }
  }

  function editContactPerson(contact) {
    isContactEdit.value = true;
    currentContactId.value = contact.id;
    contactForm.value = { name: contact.name, phone: contact.phone, email: contact.email };
  }

  function confirmDeleteContact(id) {
    contactIdToDelete.value = id;
    contactDeleteDialog.value = true;
  }

  async function executeDeleteContact() {
    if (!contactIdToDelete.value) return;
    contactDeleteLoading.value = true;
    try {
      await axios.delete(`/api/contacts/${contactIdToDelete.value}`);
      await fetchContactPersons();
      contactDeleteDialog.value = false;
    } catch (error) {
      console.error("Error deleting contact person:", error);
    } finally {
      contactDeleteLoading.value = false;
      contactIdToDelete.value = null;
    }
  }

  function resetContactForm() {
    isContactEdit.value = false;
    currentContactId.value = null;
    contactForm.value = { name: '', phone: '', email: '' };
    if (contactFormRef.value) contactFormRef.value.resetValidation();
  }

  async function fetchClients() {
    loading.value = true;
    try {
      const response = await axios.get('/api/client');
      clients.value = response.data.data || response.data;
    } catch (error) { console.error(error); }
    finally { loading.value = false; }
  }

  async function fetchLocationConfig() {
    try {
      const response = await axios.get('/api/locations/config');
      const rawData = response.data.data; locationData.value = rawData;
      if (rawData) {
        const mapped = Object.keys(rawData).map(key => ({ id: key, name: rawData[key].name }));
        countryOptions.value = mapped; filterCountryOptions.value = mapped;
      }
    } catch (error) { console.error(error); }
  }

  watch(filterCountry, (newCountry) => {
    filterCity.value = null; filterTownship.value = null; filterCityOptions.value = []; filterTownshipOptions.value = [];
    if (newCountry && locationData.value[newCountry]) {
      const cities = locationData.value[newCountry].cities;
      filterCityOptions.value = Object.keys(cities).map(key => ({ id: key, name: cities[key].name }));
    }
  });

  watch(filterCity, (newCity) => {
    filterTownship.value = null; filterTownshipOptions.value = [];
    const currentCountry = filterCountry.value;
    if (currentCountry && newCity && locationData.value[currentCountry]?.cities[newCity]) {
      const townships = locationData.value[currentCountry].cities[newCity].townships;
      filterTownshipOptions.value = Object.keys(townships).map(key => ({ id: key, name: townships[key] }));
    }
  });

  const filteredClients = computed(() => {
    return clients.value.filter(client => {
      const query = searchQuery.value ? searchQuery.value.toLowerCase() : '';
      const fullName = `${client.first_name || ''} ${client.last_name || ''}`.toLowerCase();
      const matchesSearch = !query || 
                            fullName.includes(query) || 
                            (client.email && client.email.toLowerCase().includes(query)) || 
                            (client.phone && client.phone.includes(query));
      const matchesCountry = !filterCountry.value || client.country === filterCountry.value;
      const matchesCity = !filterCity.value || client.city === filterCity.value;
      const matchesTownship = !filterTownship.value || client.township === filterTownship.value;
      return matchesSearch && matchesCountry && matchesCity && matchesTownship;
    });
  });

  function resetFilters() { searchQuery.value = ''; filterCountry.value = null; }

  watch(() => form.value.country, (newCountry) => {
    cityOptions.value = []; 
    if (newCountry && locationData.value[newCountry]) {
      const cities = locationData.value[newCountry].cities;
      cityOptions.value = Object.keys(cities).map(key => ({ id: key, name: cities[key].name }));
    }
  });

  watch(() => form.value.city, (newCity) => {
    townshipOptions.value = []; const currentCountry = form.value.country;
    if (currentCountry && newCity && locationData.value[currentCountry]?.cities[newCity]) {
      const townships = locationData.value[currentCountry].cities[newCity].townships;
      townshipOptions.value = Object.keys(townships).map(key => ({ id: key, name: townships[key] }));
    }
  });

  function openCreateDialog() { isEdit.value = false; currentClientId.value = null; resetForm(); dialog.value = true; }
  
  async function openEditDialog(client) { 
    isEdit.value = true; 
    currentClientId.value = client.id; 
    
    form.value.country = client.country;
    
    await nextTick();
    form.value.city = client.city;
    
    await nextTick();
    form.value.township = client.township;
    
    form.value.first_name = client.first_name;
    form.value.last_name = client.last_name;
    form.value.email = client.email;
    form.value.phone = client.phone;
    form.value.address = client.address;
    form.value.social_links = client.social_links ?? {
      facebook: '',
      instagram: '',
      linkedin: '',
      tiktok: '',
      telegram: '',
      viber: ''
    };
    
    dialog.value = true; 
  }
  
  function closeDialog() { dialog.value = false; resetForm(); }
  function resetForm() { form.value = { first_name: '', last_name: '', email: '', phone: '', address: '', country: null, city: null, township: null, social_links: { facebook: '', instagram: '', linkedin: '', tiktok: '', telegram: '', viber: '' } }; if (formRef.value) formRef.value.resetValidation(); }

  async function saveClient() {
    const { valid } = await formRef.value.validate(); if (!valid) return;
    submitLoading.value = true;
    try {
      if (isEdit.value) { 
        await axios.put(`/api/client/${currentClientId.value}`, form.value); 
      }
      else { 
        await axios.post('/api/client', form.value); 
      }
      fetchClients(); closeDialog();
    } catch (error) { console.error("Error saving client:", error); } finally { submitLoading.value = false; }
  }

  function confirmDelete(id) { clientIdToDelete.value = id; deleteDialog.value = true; }
  
  async function executeDelete() {
    deleteLoading.value = true;
    try { await axios.delete(`/api/client/${clientIdToDelete.value}`); fetchClients(); deleteDialog.value = false; }
    catch (error) { console.error(error); } finally { deleteLoading.value = false; clientIdToDelete.value = null; }
  }

  onMounted(() => { fetchClients(); fetchLocationConfig(); });
</script>

<style scoped>
.gap-1 { gap: 8px; }
</style>