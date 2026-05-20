<template>
  <div>
    <!-- Notification -->
    <v-alert v-if="notification" :type="notificationType" variant="tonal" class="mb-4"
rounded="lg"
    >
      {{ notification }}
    </v-alert>

    <v-row>
      <!-- Role Selector -->
      <v-col cols="12" md="4">
        <v-card
          rounded="lg"
          elevation="1"
          class="pa-4 h-100"
        >
          <div class="text-subtitle-1 font-weight-bold mb-4">
            Select System Role
          </div>

          <v-select
            v-model="selectedRoleId"
            :items="roles"
            item-title="name"
            item-value="id"
            label="Choose a role"
            variant="outlined"
            density="comfortable"
            :loading="loading"
            hide-details
            @update:model-value="onRoleChange"
          />
        </v-card>
      </v-col>

      <!-- Permissions -->
      <v-col cols="12" md="8">
        <v-card
          :loading="loading"
          rounded="lg"
          elevation="1"
        >
          <!-- Header -->
          <v-card-title class="pa-4">
            <div class="d-flex flex-column">
              <span class="text-h6 font-weight-bold">
                {{ currentRole ? currentRole.name : 'Loading Role...' }}
              </span>

              <span class="text-body-2 text-medium-emphasis">
                Manage and assign permissions for this role.
              </span>
            </div>
          </v-card-title>

          <v-divider />

          <!-- Permission List -->
          <v-card-text
            class="pa-4"
            style="max-height: 500px; overflow-y: auto;"
          >
            <v-row
              v-if="allSystemPermissions.length > 0"
              dense
            >
              <v-col
                v-for="perm in allSystemPermissions"
                :key="perm.id"
                cols="12"
                sm="6"
                lg="4"
              >
                <v-checkbox
                  v-model="selectedPermissionIds"
                  :value="perm.id"
                  :label="perm.name"
                  color="primary"
                  density="comfortable"
                  :disabled="!canManageRoles"
                  hide-details
                  class="my-0"
                />
              </v-col>
            </v-row>

            <!-- Empty State -->
            <div
              v-else-if="!loading"
              class="text-center py-8 text-body-2 text-medium-emphasis"
            >
              No permissions found in the system.
            </div>
          </v-card-text>

          <!-- Footer -->
          <template v-if="currentRole && canManageRoles">
            <v-divider />

            <v-card-actions class="pa-4">
              <v-btn
                color="primary"
                variant="flat"
                :loading="saving"
                :disabled="saving"
                class="rounded-lg text-none"
                size="large"
                block
                @click="savePermissions"
              >
                Save Permissions
              </v-btn>
            </v-card-actions>
          </template>
        </v-card>
      </v-col>
    </v-row>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()

const roles = ref([])
const allSystemPermissions = ref([])
const selectedRoleId = ref(null)

const loading = ref(false)
const saving = ref(false)

const notification = ref('')
const notificationType = ref('success')

const selectedPermissionIds = ref([])

const canManageRoles = computed(() => {
  return auth.can('roles.manage') || auth.hasRoleSlug('admin')
})

const currentRole = computed(() => {
  return roles.value.find(r => r.id === selectedRoleId.value) || null
})

function setNotification(message, type = 'success') {
  notification.value = message
  notificationType.value = type

  setTimeout(() => {
    notification.value = ''
  }, 4000)
}

async function fetchRolesData() {
  loading.value = true

  try {
    const response = await axios.get('/api/roles')

    roles.value = response.data.data ?? []
    allSystemPermissions.value = response.data.permissions ?? []

    if (roles.value.length > 0) {
      const adminRole = roles.value.find(
        r => r.slug === 'admin' ||
        r.name.toLowerCase() === 'admin'
      )

      const defaultRole = adminRole || roles.value[0]

      selectedRoleId.value = defaultRole.id

      syncSelectedPermissions(defaultRole)
    }
  } catch (error) {
    console.error(error)

    setNotification(
      'Unable to load roles data.',
      'error'
    )
  } finally {
    loading.value = false
  }
}

function syncSelectedPermissions(roleObject) {
  if (roleObject?.permissions) {
    selectedPermissionIds.value =
      roleObject.permissions.map(p => p.id)
  } else {
    selectedPermissionIds.value = []
  }
}

function onRoleChange(roleId) {
  const role = roles.value.find(r => r.id === roleId)

  syncSelectedPermissions(role)
}

async function savePermissions() {
  if (
    !canManageRoles.value ||
    !selectedRoleId.value
  ) {
    return
  }

  saving.value = true

  try {
    const { data } = await axios.put(
      `/api/roles/${selectedRoleId.value}`,
      {
        permission_ids: selectedPermissionIds.value,
      }
    )

    const updatedRole = data.data ?? {}

    const index = roles.value.findIndex(
      r => r.id === selectedRoleId.value
    )

    if (index !== -1) {
      roles.value[index] = updatedRole
    }

    syncSelectedPermissions(updatedRole)

    setNotification(
      'Role permissions updated successfully.',
      'success'
    )
  } catch (error) {
    console.error(error)

    const errorMsg =
      error?.response?.data?.message ??
      'Unable to save permissions.'

    setNotification(errorMsg, 'error')
  } finally {
    saving.value = false
  }
}

onMounted(() => {
  fetchRolesData()
})
</script>