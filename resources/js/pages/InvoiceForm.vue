<template>
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
    <v-row v-for="(item, index) in form.items" :key="index" class="align-center">
      <v-col cols="6">
        <v-text-field v-model="item.name" label="Item Name" variant="outlined" density="compact" hide-details></v-text-field>
      </v-col>
      <v-col cols="6">
        <v-text-field v-model="item.description" label="Item Description" variant="outlined" density="compact" hide-details></v-text-field>
      </v-col>
      <v-col cols="4">
        <v-text-field v-model="item.item_type" label="Item Type" variant="outlined" density="compact" hide-details></v-text-field>
      </v-col>
      <v-col cols="3">
        <v-text-field v-model.number="item.quantity" label="Qty" type="number" variant="outlined" density="compact" hide-details></v-text-field>
      </v-col>
      <v-col cols="4">
        <v-text-field v-model.number="item.price" label="Price" type="number" variant="outlined" density="compact" hide-details></v-text-field>
      </v-col>
      <v-col cols="1">
        <v-btn icon="mdi-delete" variant="text" color="error" size="small" @click="removeItem(index)"></v-btn>
      </v-col>
    </v-row>
    <v-btn color="indigo" variant="tonal" prepend-icon="mdi-plus" @click="addItem" class="mt-2">Add Item</v-btn>
    <v-card-actions class="px-0 pt-6">
  <v-spacer />
  
  <v-btn variant="text" rounded="lg" class="text-none font-weight-bold" :disabled="loading"@click="$emit('close')"> Cancel</v-btn>

  <v-btn color="indigo-darken-2" variant="flat" rounded="lg" elevation="2"class="text-none px-6 font-weight-bold" :loading="loading" @click="submitForm"
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

async function submitForm() {
  const { valid } = await formRef.value.validate();
  if (!valid) return;

  loading.value = true;
  try {
    const url = isEdit ? `/api/invoices/${form.value.invoice_id}` : '/api/invoices';
    const method = isEdit ? 'put' : 'post';
    const response = await axios[method](url, form.value);

    emit('saved', response.data); 
  } catch (error) {
    console.error("Save error:", error);
    if (error.response?.data?.errors) {
       emit('validation-error', error.response.data.errors);
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
  //fetchClientData();
  
});
</script>