<template>
  <v-container fluid max-width="650px" class="py-8">
    <v-alert v-if="notification" :type="notificationType" variant="tonal" class="mb-5" rounded="lg">
      {{ notification }}
    </v-alert>

    <v-card :loading="loading" rounded="xl" elevation="0" class="pa-6 bg-white border-thin">
      
      <div class="d-flex align-center ga-4 mb-6">
        <v-avatar color="primary-lighten-5" size="56" class="border">
          <v-icon color="primary" size="28">mdi-account-circle-outline</v-icon>
        </v-avatar>
        <div>
          <v-card-title class="pa-0 text-h6 font-weight-bold text-grey-darken-4 lh-tight">
            Account Profile
          </v-card-title>
          <v-card-subtitle class="pa-0 mt-1 text-body-2 text-medium-emphasis">
            Manage your personal details and account security.
            <router-link 
              v-if="auth.user?.id" 
              :to="{ name: 'staff.detail', params: { user: auth.user.id } }"
              class="text-primary font-weight-bold text-decoration-none d-inline-flex align-center ml-1"
              style="cursor: pointer;"
            >
              View detailed profile <v-icon size="14" class="ml-0.5">mdi-arrow-right</v-icon>
            </router-link>
          </v-card-subtitle>
        </div>
      </div>

      <v-divider class="mb-6" />

      <v-form ref="profileForm" @submit.prevent="saveProfile">
        
        <div class="text-subtitle-2 font-weight-bold mb-4 text-primary d-flex align-center ga-2">
          <v-icon size="18" color="primary">mdi-information-outline</v-icon> Personal Information
        </div>
        
        <v-row class="ma-0 pa-0 row-gap-4">
          <v-col cols="12" sm="6" class="pa-0 pr-sm-2">
            <v-text-field
              v-model="form.name"
              label="Full Name *"
              variant="outlined"
              density="comfortable"
              hide-details="auto"
              :rules="[rules.required]"
            />
          </v-col>
          <v-col cols="12" sm="6" class="pa-0 pl-sm-2">
            <v-text-field
              v-model="form.email"
              label="Email Address *"
              type="email"
              variant="outlined"
              density="comfortable"
              hide-details="auto"
              :rules="[rules.required, rules.email]"
            />
          </v-col>
        </v-row>

        <v-divider class="my-6" />
        
        <div class="text-subtitle-2 font-weight-bold mb-1 text-primary d-flex align-center ga-2">
          <v-icon size="18" color="primary">mdi-lock-outline</v-icon> Change Password
        </div>
        <div class="text-caption text-medium-emphasis mb-4">
          Leave these fields blank if you wish to keep your current password secure.
        </div>

        <v-text-field
          v-model="form.password"
          label="New Password"
          :type="showPassword ? 'text' : 'password'"
          variant="outlined"
          density="comfortable"
          class="mb-4"
          :append-inner-icon="showPassword ? 'mdi-eye' : 'mdi-eye-off'"
          hide-details="auto"
          @click:append-inner="showPassword = !showPassword"
        />

        <v-text-field
          v-model="form.password_confirmation"
          label="Confirm New Password"
          :type="showConfirmPassword ? 'text' : 'password'"
          variant="outlined"
          density="comfortable"
          class="mb-6"
          :append-inner-icon="showConfirmPassword ? 'mdi-eye' : 'mdi-eye-off'"
          :rules="[passwordConfirmationRule]"
          validate-on="input"
          hide-details="auto"
          @click:append-inner="showConfirmPassword = !showConfirmPassword"
        />

        <v-btn
          type="submit"
          color="primary"
          variant="flat"
          :loading="saving"
          :disabled="saving"
          block
          size="large"
          class="text-none font-weight-bold py-3"
          rounded="lg"
          prepend-icon="mdi-content-save-check-outline"
          elevation="0"
        >
          Save Changes
        </v-btn>
      </v-form>
    </v-card>
  </v-container>
</template>

<script setup>
  import { ref, onMounted } from 'vue';
  import axios from 'axios';
  import { useAuthStore } from '@/stores/auth'; 

  const auth = useAuthStore();
  const profileForm = ref(null);
  const loading = ref(false);
  const saving = ref(false);

  const notification = ref('');
  const notificationType = ref('success');

  const showPassword = ref(false);
  const showConfirmPassword = ref(false);

  const form = ref({
    name: '',
    email: '',
    password: '',
    password_confirmation: ''
  });

  // Regular Validation Rules
  const rules = {
    required: v => !!v || 'This field is required.',
    email: v => /.+@.+\..+/.test(v) || 'E-mail must be valid.'
  };

  // Password Match Validation Rule
  const passwordConfirmationRule = (value) => {
    if (form.value.password && !value) {
      return 'Please confirm your new password.';
    }
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

  function initProfileForm() {
    if (auth.user) {
      form.value.name = auth.user.name || '';
      form.value.email = auth.user.email || '';
    }
  }

  async function saveProfile() {
    if (profileForm.value) {
      const { valid } = await profileForm.value.validate();
      if (!valid) {
        setNotification('Please correct the highlighted fields before saving.', 'error');
        return;
      }
    }

    saving.value = true;
    try {
      const { data } = await axios.put('/api/profile', form.value);
      
      if (auth.setUser) {
        auth.setUser(data.user);
      }

      // Clear password fields on success
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

<style scoped>
/* Mobile responsive support for row item spacing gap */
.row-gap-4 {
  row-gap: 16px !important;
}
</style>