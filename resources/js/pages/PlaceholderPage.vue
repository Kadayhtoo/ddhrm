<template>
    <v-card rounded="lg" class="pa-8" border max-width="720">
        <div class="text-h5 font-weight-bold mb-2">{{ title }}</div>
        <p class="text-body-1 text-medium-emphasis mb-6">{{ subtitle }}</p>
        <v-alert type="info" variant="tonal" rounded="lg" border="start" border-color="primary">
            This section is scaffolded for the MVP described in your DDHRM specification. Add controllers,
            services, and repositories per module following the same pattern as <code>AuthService</code>
            and <code>UserRepository</code>.
        </v-alert>
    </v-card>
</template>

<script setup>
defineProps({
    title: { type: String, required: true },
    subtitle: { type: String, required: true },
});
</script>
