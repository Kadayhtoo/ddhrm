<template>
  <v-container fluid class="pa-6">
    <v-row class="mb-4" align="center" justify="space-between">
      <v-col cols="12" sm="6">
        <h1 class="text-h5 font-weight-bold mb-1">Leave Balance Records</h1>
        <p class="text-body-2 text-medium-emphasis mb-0">
          {{ isUserAdminOrHr ? 'Manage and monitor leave balances for all organization staff.' : 'Track your remaining leave days for the current active period.' }}
        </p>
      </v-col>
      
      <v-col cols="12" sm="4" md="3" v-if="isUserAdminOrHr">
        <v-text-field
          v-model="search"
          label="Search Staff..."
          variant="outlined"
          density="comfortable"
          prepend-inner-icon="mdi-magnify"
          hide-details
          clearable
          @update:model-value="debounceSearch"
        />
      </v-col>
    </v-row>

    <v-row v-if="loading" justify="center" class="my-8">
      <v-progress-circular indeterminate color="primary" size="50"></v-progress-circular>
    </v-row>

    <div v-else>
      <v-card v-if="isUserAdminOrHr" rounded="lg" variant="outlined" class="bg-surface">
        <v-table density="comfortable">
          <thead>
            <tr class="bg-grey-lighten-4">
              <th class="text-subtitle-2 font-weight-bold">Staff Name</th>
              <th class="text-subtitle-2 font-weight-bold">Department</th>
              <th class="text-subtitle-2 font-weight-bold">Leave Type</th>
              <th class="text-subtitle-2 font-weight-bold text-center">Allowed Days</th>
              <th class="text-subtitle-2 font-weight-bold text-center">Used Days</th>
              <th class="text-subtitle-2 font-weight-bold text-center">Remaining</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in balances" :key="item.id">
              <td class="font-weight-medium">{{ item.user?.name }}</td>
              <td>
                <v-chip size="small" color="secondary" variant="tonal">
                  {{ item.user?.department?.name ?? 'N/A' }}
                </v-chip>
              </td>
              <td class="text-capitalize">{{ item.leave_rule?.name ?? 'N/A' }}</td>
              <td class="text-center">{{ item.total_allowed_days }} days</td>
              <td class="text-center">
                <v-chip size="x-small" :color="item.used_days > 0 ? 'error' : 'grey'" variant="flat">
                  {{ item.used_days }} days
                </v-chip>
              </td>
              <td class="text-center font-weight-bold text-success">
                {{ item.remaining_days }} days
              </td>
            </tr>
            <tr v-if="balances.length === 0">
              <td colspan="6" class="text-center text-medium-emphasis py-6">No leave balance records found.</td>
            </tr>
          </tbody>
        </v-table>
      </v-card>

      <v-row v-else>
        <v-col cols="12" sm="6" md="4" v-for="item in balances" :key="item.id">
          <v-card border rounded="lg" class="pa-4 bg-surface" elevation="1">
            <div class="d-flex justify-space-between align-center mb-2">
              <span class="text-subtitle-1 font-weight-bold text-capitalize text-primary">
                {{ item.leave_rule?.name ?? 'Leave Type' }}
              </span>
              <v-chip color="success" size="x-small" variant="flat">Active</v-chip>
            </div>
            
            <v-divider class="my-2"></v-divider>
            
            <div class="d-flex justify-space-between py-2 border-b">
              <span class="text-body-2 text-medium-emphasis">Total Allowed:</span>
              <span class="font-weight-bold">{{ item.total_allowed_days }} Days</span>
            </div>
            <div class="d-flex justify-space-between py-2 border-b">
              <span class="text-body-2 text-medium-emphasis">Used:</span>
              <span class="font-weight-bold text-error">{{ item.used_days }} Days</span>
            </div>
            <div class="d-flex justify-space-between pt-3">
              <span class="text-body-2 font-weight-bold">Remaining Balance:</span>
              <span class="font-weight-bold text-success text-subtitle-1">
                {{ item.remaining_days }} Days
              </span>
            </div>
          </v-card>
        </v-col>
        <v-col cols="12" v-if="balances.length === 0" class="text-center text-medium-emphasis py-6">
          You don't have any leave balance initialized yet.
        </v-col>
      </v-row>
    </div>
  </v-container>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import { useAuthStore } from '@/stores/auth';

const auth = useAuthStore();
const balances = ref([]);
const search = ref('');
const loading = ref(false);
let searchTimeout = null;

const isUserAdminOrHr = computed(() => {
  return auth.hasRoleSlug('admin') || auth.hasRoleSlug('hr') || auth.can('leave-balances.view');
});

async function fetchBalances() {
  loading.value = true;
  try {
    const params = {};
    if (search.value) {
      params.search = search.value;
    }
    
    const response = await axios.get('/api/leave-balances', { params });
    
    if (response.data && response.data.data) {
      balances.value = response.data.data.data ?? response.data.data;
    }
  } catch (error) {
    console.error("Error fetching balances:", error);
  } finally{
    loading.value = false;
  }
}

function debounceSearch() {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    fetchBalances();
  }, 500);
}

onMounted(() => {
  fetchBalances();
});
</script>