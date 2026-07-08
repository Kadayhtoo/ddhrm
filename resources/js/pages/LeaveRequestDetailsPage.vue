<template>
  <v-container class="pa-4">
    <v-card v-if="leaveRequest" elevation="2" rounded="lg">
      <v-toolbar color="white" flat>
        <v-toolbar-title class="font-weight-bold">
          Leave Request #{{ leaveRequest.id }}
        </v-toolbar-title>
        <v-spacer></v-spacer>
        <v-chip 
          :color="leaveRequest.status === 'pending' ? 'warning' : 'success'" 
          variant="flat"
          class="text-uppercase"
        >
          {{ leaveRequest.status }}
        </v-chip>
      </v-toolbar>

      <v-divider></v-divider>

      <v-card-text class="pa-6">
        <v-row>
          <v-col cols="12" md="6">
            <div class="text-subtitle-2 text-grey">Requested By</div>
            <div class="text-h6 mb-4">{{ leaveRequest.user.name }}</div>
            
            <div class="text-subtitle-2 text-grey">Leave Rule</div>
            <div class="text-h6">{{ leaveRequest.leave_rule?.name || 'N/A' }}</div>
          </v-col>
          
          <v-col cols="12" md="6">
            <div class="text-subtitle-2 text-grey">Date Submitted</div>
            <div class="text-h6 mb-4">{{ new Date(leaveRequest.created_at).toLocaleDateString() }}</div>
            
            <v-btn color="primary" variant="outlined" prepend-icon="mdi-file-document-outline">
              View Attachment
            </v-btn>
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>

    <!-- Loading State -->
    <v-container v-else class="fill-height d-flex justify-center align-center">
      <v-progress-circular indeterminate color="primary"></v-progress-circular>
    </v-container>
  </v-container>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useRoute } from 'vue-router';

const route = useRoute();
const leaveRequest = ref(null);

onMounted(async () => {
    try {
        const { data } = await axios.get(`/api/leave-requests/${route.params.id}`);
        leaveRequest.value = data;
    } catch (e) {
        console.error("Failed to load leave request", e);
    }
});
</script>