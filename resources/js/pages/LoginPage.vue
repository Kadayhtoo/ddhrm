<template>
    <v-row justify="center" align="center" class="fill-height">
        <v-col cols="12" sm="8" md="5" lg="4">
            <v-card class="pa-6 pa-md-8" rounded="xl" elevation="8">
                <div class="text-center mb-6">
                    <v-avatar color="primary" size="56" rounded="lg" class="text-h5 text-white mb-3">
                        D
                    </v-avatar>
                    <div class="text-h5 font-weight-bold">Welcome to DDHRM</div>
                    <div class="text-body-2 text-medium-emphasis">Sign in (Sneat-style shell)</div>
                </div>

                <v-alert v-if="error" type="error" variant="tonal" class="mb-4" rounded="lg">
                    {{ error }}
                </v-alert>

                <v-form @submit.prevent="submit">
                    <!-- <v-text-field
                        v-model="email"
                        label="Email"
                        type="email"
                        prepend-inner-icon="mdi-email-outline"
                        variant="outlined"
                        density="comfortable"
                        autocomplete="username"
                        class="mb-3"
                    />  -->
                   
                    <v-text-field
                        v-model="username" 
                        label="Username" 
                        @input="username = username.toLowerCase()" 
                        prepend-inner-icon="mdi-account-outline" 
                        variant="outlined" 
                        class="mb-3"
                    />
                    <v-text-field
                        v-model="password"
                        label="Password"
                        type="password"
                        prepend-inner-icon="mdi-lock-outline"
                        variant="outlined"
                        density="comfortable"
                        autocomplete="current-password"
                        class="mb-4"
                    />
                    <v-btn
                        type="submit"
                        color="primary"
                        size="large"
                        block
                        rounded="lg"
                        :loading="loading"
                    >
                        Login
                    </v-btn>
                </v-form>

                <div class="text-caption text-medium-emphasis text-center mt-6">
                    Seeded accounts (password <strong>password</strong>):
                    <strong>ceo</strong>,
                    <strong>admin</strong>,
                    <strong>hr</strong>,
                    <strong>staff</strong>
                </div>
            </v-card>
        </v-col>
    </v-row>
</template>

<script setup>
import { ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const auth = useAuthStore();
const router = useRouter();
const route = useRoute();

const email = ref('hr@ddhrm.local');
const username = ref('');
const password = ref('password');
const loading = ref(false);
const error = ref('');

async function submit() {
    error.value = '';
    loading.value = true;
    try {
        // await auth.login({ email: email.value, password: password.value });
         await auth.login({ username: username.value.toLowerCase(), password: password.value });
        const redirect = typeof route.query.redirect === 'string' ? route.query.redirect : '/';
        await router.push(redirect);
    } catch (e) {
        error.value = e?.response?.data?.message
            ?? e?.response?.data?.errors?.email?.[0]
            ?? 'Unable to login.';
    } finally {
        loading.value = false;
    }
}
</script>
