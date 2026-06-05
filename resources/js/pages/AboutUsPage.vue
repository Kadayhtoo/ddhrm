<template>
  <v-container fluid class="pa-6 bg-grey-lighten-4" style="min-height: 100vh;">
    <v-row class="mb-4 align-center justify-space-between">
      <v-col cols="12" sm="auto">
        <h1 class="text-h4 font-weight-bold text-grey-darken-4">Company Information</h1>
        <p class="text-body-1 text-medium-emphasis mb-0">Update your invoice company profile, logo, address, phones, emails, and website here.</p>
      </v-col>
    </v-row>

    <v-card class="rounded-xl border-0 elevation-1 bg-white pa-4">
      <v-form ref="formRef" lazy-validation>
        <v-row>
          <v-col cols="12" md="8">
            <v-text-field v-model="form.company_name" label="Company Name" variant="outlined" density="comfortable" rounded="lg" :rules="[v => !!v || 'Company name is required']"></v-text-field>
            <v-textarea v-model="form.description" label="Description" variant="outlined" density="comfortable" rows="4" rounded="lg"></v-textarea>

            <v-textarea v-model="form.address" label="Address"  persistent-hint variant="outlined" density="comfortable" rows="3" rounded="lg"></v-textarea>

            <v-row>
              <v-col cols="12" sm="4" class="py-1">
                <v-text-field v-model="form.township" label="Township" variant="outlined" density="comfortable" rounded="lg"></v-text-field>
              </v-col>
              <v-col cols="12" sm="4" class="py-1">
                <v-text-field v-model="form.city" label="City" variant="outlined" density="comfortable" rounded="lg"></v-text-field>
              </v-col>
              <v-col cols="12" sm="4" class="py-1">
                <v-text-field v-model="form.country" label="Country" variant="outlined" density="comfortable" rounded="lg"></v-text-field>
              </v-col>
            </v-row>

            <v-text-field v-model="form.website" label="Website" variant="outlined" density="comfortable" rounded="lg" prepend-inner-icon="mdi-web"></v-text-field>
            <v-textarea v-model="form.phone_numbers" label="Phone Numbers" persistent-hint variant="outlined" density="comfortable" rows="3" rounded="lg"></v-textarea>
            <v-textarea v-model="form.email_addresses" label="Email Addresses" persistent-hint variant="outlined" density="comfortable" rows="3" rounded="lg"></v-textarea>
          </v-col>

          <v-col cols="12" md="4" class="d-flex flex-column justify-space-between">
            <div>
              <div class="text-subtitle-1 font-weight-bold mb-4">Company Logo</div>
              <v-file-input
                v-model="form.logo"
                label="Upload Logo"
                accept="image/*"
                truncate-length="20"
                variant="outlined"
                density="comfortable"
                rounded="lg"
              />
              <div v-if="logoPreview" class="mt-4">
                <div class="text-caption text-medium-emphasis mb-2">Logo preview</div>
                <div class="logo-preview-box">
                  <img :src="logoPreview" alt="Company Logo" class="w-100" />
                </div>
              </div>
            </div>

            <div class="mt-8">
              <div class="text-subtitle-1 font-weight-bold mb-3">Current Values</div>
              <v-chip-group column>
                <v-chip v-if="company.address" class="mb-2" color="grey-lighten-4">Address: {{ company.address }}</v-chip>
                <v-chip v-if="company.website" class="mb-2" color="grey-lighten-4">Website: {{ company.website }}</v-chip>
                <v-chip v-if="company.phone_numbers?.length" class="mb-2" color="grey-lighten-4">Phones: {{ company.phone_numbers.join(', ') }}</v-chip>
                <v-chip v-if="company.email_addresses?.length" class="mb-2" color="grey-lighten-4">Emails: {{ company.email_addresses.join(', ') }}</v-chip>
              </v-chip-group>
            </div>
          </v-col>
        </v-row>
      </v-form>

      <v-divider class="my-4"></v-divider>

      <v-card-actions class="px-0">
        <v-spacer />
        <v-btn variant="text" class="text-none rounded-lg" @click="resetForm">Reset</v-btn>
        <v-btn color="indigo-darken-2" rounded="lg" class="text-none px-6 elevation-2 font-weight-bold" :loading="loading" @click="saveCompanyInfo">Save Company Info</v-btn>
      </v-card-actions>
    </v-card>
  </v-container>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import axios from 'axios';

const loading = ref(false);
const formRef = ref(null);
const company = ref({});
const logoPreview = ref(null);

const form = ref({
  id: null,
  company_name: '',
  description: '',
  address: '',
  township: '',
  city: '',
  country: '',
  website: '',
  phone_numbers: '',
  email_addresses: '',

  logo_path: null,
});

function hydrateForm(data) {
  form.value.id = data?.id || null;
  form.value.company_name = data?.company_name || '';
  form.value.description = data?.description || '';
  form.value.address = data?.address || '';
  form.value.township = data?.township || '';
  form.value.city = data?.city || '';
  form.value.country = data?.country || '';
  form.value.website = data?.website || '';
  form.value.phone_numbers = Array.isArray(data?.phone_numbers) ? data.phone_numbers.join('\n') : '';
  form.value.email_addresses = Array.isArray(data?.email_addresses) ? data.email_addresses.join('\n') : '';
  form.value.logo = null;
  form.value.logo_path = data?.logo_path || null;
  logoPreview.value = data?.logo_url || null;
}

async function fetchCompanyInfo() {
  try {
    const response = await axios.get('/api/about-us');
    if (response.data?.success && response.data?.data) {
      company.value = response.data.data;
      hydrateForm(response.data.data);
    } else {
      company.value = {};
      resetForm();
    }
  } catch (error) {
    console.error('Unable to load company info', error);
  }
}

function resetForm() {
  form.value = {
    id: form.value.id,
    company_name: '',
    description: '',
    address: '',
    township: '',
    city: '',
    country: '',
    website: '',
    phone_numbers: '',
    email_addresses: '',
    logo: null,
    logo_path: null,
  };
  logoPreview.value = company.value.logo_url || null;
  if (formRef.value) {
    formRef.value.resetValidation();
  }
}

function buildFormData() {
  const formData = new FormData();
  formData.append('company_name', form.value.company_name);
  formData.append('description', form.value.description);
  formData.append('address', form.value.address);
  formData.append('township', form.value.township);
  formData.append('city', form.value.city);
  formData.append('country', form.value.country);
  formData.append('website', form.value.website);

  const phones = form.value.phone_numbers
    .split(/\r?\n/)
    .map(phone => phone.trim())
    .filter(Boolean);
  phones.forEach(phone => formData.append('phone_numbers[]', phone));

  const emails = form.value.email_addresses
    .split(/\r?\n/)
    .map(email => email.trim())
    .filter(Boolean);
  emails.forEach(email => formData.append('email_addresses[]', email));

  if (form.value.logo) {
    formData.append('logo', form.value.logo);
  }

  return formData;
}

async function saveCompanyInfo() {
  const { valid } = await formRef.value.validate();
  if (!valid) return;

  loading.value = true;
  try {
    const payload = buildFormData();

    if (form.value.id) {
      payload.append('_method', 'PUT'); 
    }

    await axios.post(
      form.value.id ? `/api/about-us/${form.value.id}` : '/api/about-us', 
      payload
    );

    await fetchCompanyInfo();
  } catch (error) {
    console.error('Error saving company info', error);
  } finally {
    loading.value = false;
  }
}

watch(
  () => form.value.logo,
  (file) => {
    if (!file) {
      logoPreview.value = company.value.logo_url || null;
      return;
    }

    logoPreview.value = URL.createObjectURL(file);
  }
);

onMounted(() => {
  fetchCompanyInfo();
});
</script>

<style scoped>
.logo-preview-box {
  border: 1px solid #d9dde3;
  border-radius: 12px;
  padding: 14px;
  background: #fafafa;
}
.logo-preview-box img {
  max-width: 100%;
  object-fit: contain;
}
</style>
