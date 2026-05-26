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
              <th class="text-subtitle-2 font-weight-bold text-center">Year</th> <th class="text-subtitle-2 font-weight-bold text-center">Allowed Days</th>
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
              <td class="text-center font-weight-bold text-blue-grey">{{ item.year ?? 'N/A' }}</td> <td class="text-center">{{ item.total_allowed_days }} days</td>
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
              <td colspan="7" class="text-center text-medium-emphasis py-6">No leave balance records found.</td>
            </tr>
          </tbody>
        </v-table>
      </v-card>

      <v-card v-else rounded="lg" variant="outlined" class="bg-surface">
        <v-table density="comfortable">
          <thead>
            <tr class="bg-grey-lighten-4">
              <th class="text-subtitle-2 font-weight-bold">Leave Type</th>
              <th class="text-subtitle-2 font-weight-bold text-center">Year</th> <th class="text-subtitle-2 font-weight-bold text-center">Total Allowed</th>
              <th class="text-subtitle-2 font-weight-bold text-center">Used Days</th>
              <th class="text-subtitle-2 font-weight-bold text-center">Remaining Balance</th>
              <th class="text-subtitle-2 font-weight-bold text-center">Status</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in balances" :key="item.id">
              <td class="font-weight-bold text-primary text-capitalize">
                {{ item.leave_rule?.name ?? 'Leave Type' }}
              </td>
              <td class="text-center font-weight-medium">{{ item.year ?? 'N/A' }}</td> <td class="text-center font-weight-semibold">{{ item.total_allowed_days }} Days</td>
              <td class="text-center">
                <span class="font-weight-bold text-error">{{ item.used_days }} Days</span>
              </td>
              <td class="text-center font-weight-bold text-success text-subtitle-1">
                {{ item.remaining_days }} Days
              </td>
              <td class="text-center">
                <v-chip color="success" size="x-small" variant="flat">Active</v-chip>
              </td>
            </tr>
            <tr v-if="balances.length === 0">
              <td colspan="6" class="text-center text-medium-emphasis py-6">
                You don't have any leave balance initialized yet.
              </td>
            </tr>
          </tbody>
        </v-table>
      </v-card>
    </div>
  </v-container>
</template>