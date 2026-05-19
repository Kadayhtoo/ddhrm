<template>
  <div>
    <v-alert v-if="notification" :type="notificationType" variant="tonal" class="mb-4" rounded="lg">
      {{ notification }}
    </v-alert>

    <v-row>
      <v-col cols="12" md="4">
        <v-card outlined class="pa-4" rounded="lg" elevation="1">
          <v-card-title class="text-subtitle-1 font-weight-bold px-0 pt-0">
            Select System Role
          </v-card-title>
          
          <v-select
            v-model="selectedRoleId"
            :items="roles"
            item-title="name"
            item-value="id"
            label="Choose a role"
            variant="outlined"
            density="comfortable"
            :loading="loading"
            @update:model-value="onRoleChange"
          />

          <div v-if="currentRole" class="mt-2">
            <div class="text-caption text-medium-emphasis mb-1">Role Identifier (Slug):</div>
            <v-chip size="small" color="secondary" label>{{ currentRole.slug }}</v-chip>
          </div>
        </v-card>
      </v-col>

      <v-col cols="12" md="8">
        <v-card :loading="loading" rounded="lg" elevation="1">
          <v-card-title class="pa-4 border-b">
            <div class="text-h6 font-weight-bold">
              {{ currentRole ? currentRole.name : 'Loading Role...' }}
            </div>
            <div class="text-caption text-medium-emphasis">
              Manage and assign permissions for this role.
            </div>
          </v-card-title>

          <v-card-text class="pa-4">
            <v-row v-if="allSystemPermissions.length > 0">
              <v-col 
                v-for="perm in allSystemPermissions" 
                :key="perm.id" 
                cols="12" 
                sm="6"
              >
                <v-checkbox
                  v-model="selectedPermissionIds"
                  :value="perm.id"
                  :label="perm.name"
                  color="primary"
                  density="comfortable"
                  :disabled="!canManageRoles"
                  hide-details
                />
              </v-col>
            </v-row>
            <v-row v-else-if="!loading" justify="center" class="py-6">
              <span class="text-body-2 text-medium-emphasis">No permissions found in the system.</span>
            </v-row>
          </v-card-text>

          <v-divider v-if="currentRole && canManageRoles" />
          
          <v-card-actions v-if="currentRole && canManageRoles" class="pa-4">
            <v-btn
              color="primary"
              variant="flat"
              :loading="saving"
              :disabled="saving"
              @click="savePermissions"
            >
              Save permissions
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-col>
    </v-row>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import { useAuthStore } from '@/stores/auth';

const auth = useAuthStore();
const roles = ref([]);
const allSystemPermissions = ref([]);
const selectedRoleId = ref(null);
const loading = ref(false);
const saving = ref(false);

const notification = ref('');
const notificationType = ref('success');

const canManageRoles = computed(() => auth.can('roles.manage') || auth.hasRoleSlug('admin'));

const currentRole = computed(() => {
  return roles.value.find(r => r.id === selectedRoleId.value) || null;
});

const selectedPermissionIds = ref([]);

function setNotification(message, type = 'success') {
  notification.value = message;
  notificationType.value = type;
  setTimeout(() => {
    notification.value = '';
  }, 4000);
}

async function fetchRolesData() {
  loading.value = true;
  try {
    const response = await axios.get('/api/roles');
    roles.value = response.data.data ?? []; 
    allSystemPermissions.value = response.data.permissions ?? []; 

    if (roles.value.length > 0) {
      const adminRole = roles.value.find(
        r => r.slug === 'admin' || r.name.toLowerCase() === 'admin'
      );
      const defaultRole = adminRole ? adminRole : roles.value[0];
      selectedRoleId.value = defaultRole.id;
      syncSelectedPermissions(defaultRole);
    }
  } catch (error) {
    console.error('API Error:', error);
    setNotification('Unable to load roles data.', 'error');
  } finally {
    loading.value = false;
  }
}

function syncSelectedPermissions(roleObject) {
  if (roleObject && roleObject.permissions) {
    selectedPermissionIds.value = roleObject.permissions.map(p => p.id);
  } else {
    selectedPermissionIds.value = [];
  }
}

function onRoleChange(roleId) {
  const role = roles.value.find(r => r.id === roleId);
  syncSelectedPermissions(role);
}

async function savePermissions() {
  if (!canManageRoles.value || !selectedRoleId.value) {
    return;
  }

  saving.value = true;
  try {
    const { data } = await axios.put(`/api/roles/${selectedRoleId.value}`, {
      permission_ids: selectedPermissionIds.value,
    });

    const updatedRole = data.data ?? {};
    const index = roles.value.findIndex(r => r.id === selectedRoleId.value);
    if (index !== -1) {
      roles.value[index] = updatedRole;
    }
    
    syncSelectedPermissions(updatedRole);
    
    setNotification('Role permissions updated successfully.', 'success');
  } catch (error) {
    console.error('Update Error:', error);
    const errorMsg = error?.response?.data?.message ?? 'Unable to save permissions.';
    setNotification(errorMsg, 'error');
  } finally {
    saving.value = false;
  }
}

onMounted(() => {
  fetchRolesData();
});
</script>