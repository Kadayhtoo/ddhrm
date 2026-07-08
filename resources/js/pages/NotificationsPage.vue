<template>
  <v-container class="pa-6">
    <div class="d-flex justify-space-between align-center mb-4">
      <h2 class="text-h5">Notifications</h2>
      <v-btn 
        variant="text" 
        color="primary" 
        prepend-icon="mdi-check-all" 
        @click="markAllAsRead"
        :disabled="notifications.length === 0"
      >
        Mark all as read
      </v-btn>
    </div>

    <v-card class="elevation-1">
      <v-list lines="two" bg-color="white">
        <template v-for="(note, index) in notifications" :key="note.id">
          <v-list-item 
            @click="handleNotificationClick(note)"
            :class="!note.read_at ? 'bg-blue-lighten-5' : ''"
          >
            <template v-slot:prepend>
              <v-icon :color="!note.read_at ? 'primary' : 'grey'">
                {{ !note.read_at ? 'mdi-bell-ring' : 'mdi-bell-outline' }}
              </v-icon>
            </template>

            <v-list-item-title class="font-weight-medium">
              {{ note.data.message }}
            </v-list-item-title>
            
            <v-list-item-subtitle class="mt-1">
              {{ new Date(note.created_at).toLocaleString() }}
            </v-list-item-subtitle>

            <template v-slot:append>
              <v-chip v-if="!note.read_at" size="x-small" color="primary">New</v-chip>
            </template>
          </v-list-item>
          <v-divider v-if="index < notifications.length - 1" inset />
        </template>

        <v-list-item v-if="notifications.length === 0" class="text-center py-8 text-grey">
          <v-icon size="48" class="mb-2">mdi-bell-off-outline</v-icon>
          <v-list-item-title>All caught up!</v-list-item-title>
          <v-list-item-subtitle>No new notifications at this time.</v-list-item-subtitle>
        </v-list-item>
      </v-list>
    </v-card>
  </v-container>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';
const router = useRouter();

    const notifications = ref([]);
    const unreadCount = ref(0);

    const fetchNotifications = async () => {
        try {
            const { data } = await axios.get('/api/notifications');
            notifications.value = Array.isArray(data) ? data : (data.notifications || []);
        } catch (e) {
            console.error("Failed to load", e);
        }
    };

    const handleNotificationClick = async (note) => {
      note.read_at = new Date().toISOString(); 
      
      axios.post(`/api/notifications/${note.id}/read`)
          .then(() => fetchNotifications())
          .catch(() => note.read_at = null);

      if (note.data && note.data.url) {
          router.push(note.data.url);
      }
  };

    const markAllAsRead = async () => {
      try {
          await axios.post('/api/notifications/mark-all-read');
          
          await fetchNotifications(); 
          } catch (e) {
          console.error("Failed to mark all as read", e);
      }
    };
    

    onMounted(() => {
        fetchNotifications();
    });
</script>