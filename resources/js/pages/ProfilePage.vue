<template>
  <v-container fluid max-width="600px">
    <v-alert v-if="notification" :type="notificationType" variant="tonal" class="mb-4" rounded="lg">
      {{ notification }}
    </v-alert>

    <v-card :loading="loading" rounded="lg" elevation="1" class="pa-4">
      <v-card-title class="px-0 pt-0 text-h6 font-weight-bold">
        Account Profile
      </v-card-title>
      <v-card-subtitle class="px-0 pb-4">
        Update your account profile details and login credentials.
      </v-card-subtitle>
      
      <v-divider class="mb-6" />

      <v-form ref="profileForm" @submit.prevent="saveProfile">
        <v-text-field
          v-model="form.name"
          label="Full Name"
          variant="outlined"
          density="comfortable"
          class="mb-2"
          required
        />

        <v-text-field
          v-model="form.email"
          label="Email Address"
          type="email"
          variant="outlined"
          density="comfortable"
          class="mb-2"
          required
        />

        <v-divider class="my-6" />
        <div class="text-subtitle-2 font-weight-bold mb-2 text-medium-emphasis">Change Password (Optional)</div>

        <v-text-field
          v-model="form.password"
          label="New Password"
          :type="showPassword ? 'text' : 'password'"
          variant="outlined"
          density="comfortable"
          class="mb-2"
          :append-inner-icon="showPassword ? 'mdi-eye' : 'mdi-eye-off'"
          hint="Leave blank to keep your current password"
          persistent-hint
          @click:append-inner="showPassword = !showPassword"
        />

        <v-text-field
          v-model="form.password_confirmation"
          label="Confirm New Password"
          :type="showConfirmPassword ? 'text' : 'password'"
          variant="outlined"
          density="comfortable"
          class="mb-4"
          :append-inner-icon="showConfirmPassword ? 'mdi-eye' : 'mdi-eye-off'"
          :rules="[passwordConfirmationRule]"
          validate-on="input"
          @click:append-inner="showConfirmPassword = !showConfirmPassword"
        />

        <v-btn
          type="submit"
          color="primary"
          variant="flat"
          :loading="saving"
          :disabled="saving"
          block
        >
          Update Profile
        </v-btn>
      </v-form>
    </v-card>
  </v-container>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useAuthStore } from '@/stores/auth'; // Adjust path based on your pinia store setup

const auth = useAuthStore();
const profileForm = ref(null);
const loading = ref(false);
const saving = ref(false);

const notification = ref('');
const notificationType = ref('success');

// Password Visibility States
const showPassword = ref(false);
const showConfirmPassword = ref(false);

const form = ref({
  name: '',
  email: '',
  password: '',
  password_confirmation: ''
});

// Vuetify validation rule to check if passwords match reactively
const passwordConfirmationRule = (value) => {
  if (form.value.password && value !== form.value.password) {
    return 'Passwords do not match.';
  }
  return true;
};

function setNotification(message, type = 'success') {
  notification.value = message;
  notificationType.value = type;
  setTimeout(() => { notification.value = ''; }, 4000);
}

// 1. Show the logged-in user's current data upon loading the component
function initProfileForm() {
  if (auth.user) {
    form.value.name = auth.user.name || '';
    form.value.email = auth.user.email || '';
  }
}

async function saveProfile() {
  // 2. Client-side check before updating: Validate form rules
  if (profileForm.value) {
    const { valid } = await profileForm.value.validate();
    if (!valid) {
      setNotification('Please fix the validation errors before saving.', 'error');
      return;
    }
  }

  // Double check password matching logic explicitly
  if (form.value.password !== form.value.password_confirmation) {
    setNotification('Passwords do not match.', 'error');
    return;
  }

  saving.value = true;
  try {
    const { data } = await axios.put('/api/profile', form.value);
    
    if (auth.setUser) {
      auth.setUser(data.user);
    }

    form.value.password = '';
    form.value.password_confirmation = '';

    setNotification('Your profile details have been successfully saved.', 'success');
  } catch (error) {
    const errorMsg = error?.response?.data?.message ?? 'Failed to update profile.';
    setNotification(errorMsg, 'error');
  } finally {
    saving.value = false;
  }
}

onMounted(() => {
  initProfileForm();
});
</script>