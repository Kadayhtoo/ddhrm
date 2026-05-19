<template>
    <div>
        <v-row class="mb-4">
            <v-col cols="12" md="8">
                <div class="text-h4 font-weight-bold mb-1">Admin panel</div>
                <p class="text-body-1 text-medium-emphasis">
                    Manage roles, staff, and system areas. Your access is controlled by permissions (CEO / Admin / HR / Staff).
                </p>
            </v-col>
        </v-row>

        <v-row>
            <v-col cols="12" md="6" lg="4">
                <v-card rounded="lg" border class="pa-5">
                    <v-icon color="primary" size="40" class="mb-3">mdi-account-group-outline</v-icon>
                    <div class="text-h6 font-weight-semibold mb-2">Staff directory</div>
                    <p class="text-body-2 text-medium-emphasis mb-4">
                        Create, update, and deactivate users. HR can create Staff accounts; Admin/CEO can assign any role.
                    </p>
                    <v-btn color="primary" variant="flat" rounded="lg" :to="{ name: 'staff' }">
                        Open staff
                    </v-btn>
                </v-card>
            </v-col>
            <v-col cols="12" md="6" lg="4">
                <v-card rounded="lg" border class="pa-5">
                    <v-icon color="secondary" size="40" class="mb-3">mdi-shield-key-outline</v-icon>
                    <div class="text-h6 font-weight-semibold mb-2">Roles &amp; permissions</div>
                    <p class="text-body-2 text-medium-emphasis mb-4">
                        Roles: <strong>CEO</strong>, <strong>Admin</strong>, <strong>HR</strong>, <strong>Staff</strong>. Permissions are seeded in Laravel (<code>RolePermissionSeeder</code>).
                    </p>
                    <v-btn variant="tonal" rounded="lg" disabled>Configure (API)</v-btn>
                </v-card>
            </v-col>
        </v-row>
    </div>
</template>

<script setup>
</script>
