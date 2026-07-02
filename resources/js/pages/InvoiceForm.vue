<template>
    <v-alert v-if="backendErrors.length > 0" type="error" variant="tonal" class="rounded-lg mb-4 text-body-2" closable @click:close="backendErrors = []">
      <ul class="pl-4 mb-0">
        <li v-for="(err, idx) in backendErrors" :key="idx">{{ err }}</li>
      </ul>
    </v-alert>
  <v-form ref="formRef">
    <v-row>
      <v-col cols="12" sm="6" class="py-1">
        <v-text-field v-model="form.invoice_id" label="Invoice ID *" variant="outlined" density="comfortable" :rules="[v => !!v || 'Invoice ID is required']" :disabled="isEditMode" rounded="lg" color="indigo-lighten-1" persistent-hint></v-text-field>
      </v-col>

      <v-col cols="12" sm="6" class="py-1">
        <v-text-field 
          :model-value="getClientDisplayName" 
          label="Client Name" 
          variant="outlined" 
          density="comfortable" 
          disabled 
          rounded="lg" 
          color="indigo-lighten-1"
        ></v-text-field>
      </v-col>
      <v-col cols="12" sm="6" class="py-1">
        <v-text-field v-model="form.issue_date" label="Issue Date *" type="date" variant="outlined" density="comfortable" :rules="[v => !!v || 'Issue date is required']" rounded="lg" color="indigo-lighten-1"></v-text-field>
      </v-col>

      <v-col cols="12" sm="6" class="py-1">
        <v-text-field v-model="form.due_date" label="Due Date *" type="date" variant="outlined" density="comfortable" :rules="[v => !!v || 'Due date is required', validateDueDate]" rounded="lg" color="indigo-lighten-1"></v-text-field>
      </v-col>

      <v-col cols="12" sm="6" class="py-1">
        <v-select v-model="form.currency" :items="currencyOptions" label="Currency *" variant="outlined" density="comfortable" :rules="[v => !!v || 'Currency is required']" rounded="lg" color="indigo-lighten-1"></v-select>
      </v-col>

      <v-col cols="12" sm="6" class="py-1">
        <v-select v-model="form.status" :items="['open', 'sent', 'paid', 'cancelled']" label="Status *" variant="outlined" density="comfortable" :rules="[v => !!v || 'Status is required']" class="text-capitalize" rounded="lg" color="indigo-lighten-1"></v-select>
      </v-col>
      <v-col cols="12" class="py-1">
        <v-file-input 
          v-if="form.status === 'paid'" 
          v-model="paymentFile" 
          label="Upload Attachment" 
          prepend-icon="mdi-camera" 
          variant="outlined"
          rounded="lg"
          density="comfortable"
        ></v-file-input>

        <div v-if="imagePreview" class="mt-2 mb-4">
          <div class="text-caption mb-1 font-weight-bold">Attachment:</div>
          <v-img
            :src="imagePreview"
            max-width="300"
            rounded="lg"
            elevation="2"
            class="border"
          />
        </div>
      </v-col>

      <v-col cols="12" sm="6" class="py-1">
        <v-select v-model="form.discount_type" :items="[{title: 'Fixed Amount', value: 'fixed'}, {title: 'Percentage (%)', value: 'percentage'}]" item-title="title" item-value="value" label="Discount Type" variant="outlined" density="comfortable" rounded="lg" color="indigo-lighten-1"></v-select>
      </v-col>

      <v-col cols="12" sm="6" class="py-1">
        <v-text-field v-model.number="form.discount_value" label="Discount Value" type="number" min="0" variant="outlined" density="comfortable" rounded="lg" color="indigo-lighten-1" prepend-inner-icon="mdi-tag-outline"></v-text-field>
      </v-col>
    </v-row>

    <div class="mb-4">
      <div class="text-subtitle-1 font-weight-bold mb-2">Terms and Conditions</div>
      <RichTextEditor v-model="form.terms" label="Terms and Conditions" minHeight="140px" />
    </div>

    <div class="text-subtitle-1 font-weight-bold mb-2">Invoice Items</div>
    <v-row class="px-2 mb-2" dense>
      <v-col cols="4"><div class="text-caption font-weight-bold text-grey">ITEM NAME</div></v-col>
      <v-col cols="2"><div class="text-caption font-weight-bold text-grey">TYPE</div></v-col>
      <v-col cols="2"><div class="text-caption font-weight-bold text-grey">QTY</div></v-col>
      <v-col cols="3"><div class="text-caption font-weight-bold text-grey">PRICE</div></v-col>
      <v-col cols="1"></v-col>
    </v-row>
    <v-card variant="outlined" class="pa-2 mb-4 rounded-lg">
      <v-row v-for="(item, index) in form.items" :key="index" class="align-center mb-2" dense>
        <v-col cols="4">
          <v-text-field v-model="item.name" placeholder="Name" variant="underlined" density="compact" hide-details></v-text-field>
        </v-col>
        <v-col cols="2">
          <v-text-field v-model="item.item_type" placeholder="Type" variant="underlined" density="compact" hide-details></v-text-field>
        </v-col>
        <v-col cols="2">
          <v-text-field v-model.number="item.quantity" type="number" variant="underlined" density="compact" hide-details></v-text-field>
        </v-col>
        <v-col cols="3">
          <v-text-field v-model.number="item.price" type="number" variant="underlined" density="compact" hide-details prefix=""></v-text-field>
        </v-col>
        <v-col cols="1" class="text-center">
          <v-btn icon="mdi-delete" variant="text" color="error" size="x-small" @click="removeItem(index)"></v-btn>
        </v-col>
        
        <v-col cols="12" class="pt-0">
          <v-text-field v-model="item.description" placeholder="Add description(optional)" variant="plain" density="compact" class="text-caption mt-n2"></v-text-field>
        </v-col>
      </v-row>
    </v-card>
    <v-btn variant="text" prepend-icon="mdi-plus" color="indigo" @click="addItem" class="text-none">Add New Item</v-btn>
    <v-card-actions class="px-0 pt-6">
    <v-spacer />
    
    <v-btn variant="text" rounded="lg" class="text-none font-weight-bold" :disabled="loading"@click="$emit('close')"> Cancel</v-btn>

    <v-btn color="#702E62" variant="flat" rounded="lg" elevation="2"class="text-none px-6 font-weight-bold" :loading="loading" @click="submitForm"
    >
      {{ isEdit ? 'Update' : 'Create' }}
    </v-btn>
  </v-card-actions>
  </v-form>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { computed } from 'vue';
import axios from 'axios';
import { watch } from 'vue';
import RichTextEditor from '@/components/RichTextEditor.vue';

const props = defineProps(['fixedClientId', 'invoiceData', 'clientOptions']);
const emit = defineEmits(['saved', 'close']);

const defaultTerms = 'Please send payment within 7 days of receiving this invoice.';
const formRef = ref(null);
const loading = ref(false);
const isEdit = !!props.invoiceData;
const isEditMode = ref(isEdit);
const dialog = ref(false);
const backendErrors = ref([]);
const isClientFixed = ref(!!props.fixedClientId);
const currencyOptions = ref([]);

const getClientDisplayName = computed(() => {
  if (!props.clientOptions || props.clientOptions.length === 0) return 'Loading...';

  const client = props.clientOptions.find(
    c => String(c.id) === String(form.value.client_id)
  );

  return client ? `${client.first_name} ${client.last_name}` : 'Client not found';
});

const form = ref(props.invoiceData ? { ...props.invoiceData } : {
  invoice_id: '',
  client_id: props.fixedClientId || null,
  terms: defaultTerms,
  items: [{ name: '', quantity: 1, price: 0 }]
});

watch(() => props.invoiceData, (newData) => {
  if (newData) {
    form.value = { ...newData };
  } else {
    form.value = { 
      invoice_id: '', 
      client_id: props.fixedClientId || null, 
      terms: defaultTerms,
      due_date: '',
      currency: 'MMK',
      discount_type: 'fixed',
      discount_value: 0,
      status: 'open',
      items: [{ name: '', quantity: 1, price: 0 }] 
    };
  }
}, { immediate: true, deep: true });


function addItem() { form.value.items.push({ name: '', quantity: 1, price: 0 }); }
function removeItem(index) { form.value.items.splice(index, 1); }

async function fetchCurrencies() {
  try {
    const response = await axios.get('/api/currencies'); 
    currencyOptions.value = Object.keys(response.data);  } catch (error) {
    console.error("Error fetching currencies:", error);
  }
}

const imagePreview = computed(() => {
  if (paymentFile.value instanceof File) {
    return URL.createObjectURL(paymentFile.value);
  }
  return form.value.payment_attachment ? `/storage/${form.value.payment_attachment}` : null;
});

const paymentFile = ref(null);

async function submitForm() {
  const { valid } = await formRef.value.validate();
  if (!valid) return;

  loading.value = true;
  
  const formData = new FormData();
  Object.keys(form.value).forEach(key => {
    if (key !== 'payment_attachment') {
        if (key === 'items') {
            formData.append('items', JSON.stringify(form.value.items));
        } else {
            formData.append(key, form.value[key] ?? '');
        }
    }
});

if (paymentFile.value instanceof File) {
    formData.append('payment_attachment', paymentFile.value);
}

  const url = isEdit ? `/api/invoices/${form.value.invoice_id}` : '/api/invoices';
  if (isEdit) formData.append('_method', 'PUT');

  try {
    await axios.post(url, formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    });
    emit('saved');
  } catch (e) {
    if (e.response && e.response.status === 422) {
      backendErrors.value = Object.values(e.response.data.errors).flat();
    } else {
      backendErrors.value = ['An unexpected error occurred. Please try again.'];
    }
  } finally {
    loading.value = false;
  }
}

async function openCreateDialog(fixedClientId = null) {
  resetForm();
  isEditMode.value = false;
  backendErrors.value = [];
  
  form.value.invoice_id = 'Generating...'; 

  if (fixedClientId) {
    form.value.client_id = Number(fixedClientId); 
    isClientFixed.value = true;
  } else {
    isClientFixed.value = false; 
  }

  dialog.value = true; 
    await fetchNextInvoiceNumber(); 
}

async function fetchNextInvoiceNumber() {
  try {
    const response = await axios.get('/api/invoices/next-number');
    if (response.data && response.data.invoice_id) {
      form.value.invoice_id = response.data.invoice_id;
    } else {
      form.value.invoice_id = ''; 
    }
  } catch (error) {
    console.error("Error getting next invoice number:", error);
    form.value.invoice_id = ''; 
    showToast("Could not auto-generate Invoice ID", "warning");
  }
}

onMounted(async () => {
  await fetchCurrencies();
  if (!isEdit) {
    await fetchNextInvoiceNumber();
  }
  
});
</script>