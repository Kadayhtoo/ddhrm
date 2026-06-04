<template>
  <v-container fluid class="invoice-page">
    <div class="invoice-wrapper">
      <div class="text-right mb-4 print-hide">
        <v-btn color="#92387B" @click="printPage">
          <v-icon start>mdi-printer</v-icon>
          Print / Save PDF
        </v-btn>
      </div>
      <v-row>
        <v-col cols="6">
         <img
          :src="company?.logo_url || '/assets/logo/logo.png'"
          alt="Company Logo"
          height="80"
        >
         <div class="company-info mt-2">
          <strong>{{ company?.company_name }}</strong><br>
          
          <div>{{ company?.address }}</div>
          <div>{{ company?.township }} Township <br> {{ company?.city }}, {{ company?.country }}</div>

          <div v-if="company?.phone_numbers?.length > 0" class="mt-1">
            P: {{ company.phone_numbers.join(', ') }}
          </div>

          <div v-if="company?.email_addresses?.length > 0" class="mt-1">
            E: {{ company.email_addresses.join(', ') }}
          </div>
          <div v-if="company?.website?.length > 0" class="mt-1">
            W: {{ company.website }}
          </div>
        </div>
        </v-col>
        <v-col cols="6" class="text-right">
          <div
            class="paid-status"
          >
            {{ estimate.status }}
          </div>
          <div class="customer-name">
            {{ estimate.client_name }}
          </div>
          <div v-if="estimate.client">
            <div>{{ estimate.client.address }}</div>
            <div>{{ estimate.client.township_name }}, {{ estimate.client.city_name }}</div>
            <div>{{ estimate.client.country_name }}</div>
          </div>
        </v-col>
      </v-row>
      <div class="invoice-title mt-2">
        ESTIMATE: {{ estimate.estimate_id }}
      </div>
      <div class="invoice-date">
        Estimate Date:
        {{ estimate.issue_date ? new Date(estimate.issue_date).toLocaleDateString('en-GB') : today }}
      </div> 
      <v-table class="invoice-table mt-2">

        <thead>
          <tr>
            <th>Item</th>
            <th>Description</th>
            <th class="text-center">Qty</th>
            <th class="text-right">Unit Price</th>
            <th class="text-right">Subtotal</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="item in estimate.items"
            :key="item.id"
          >
            <td>{{ item.name }}</td>

            <td>{{ item.description }}</td>

            <td class="text-center">
              {{ item.quantity }}
            </td>
            <td class="text-right">
              {{ formatAmount(item.price) }}
            </td>

            <td class="text-right">
              {{ formatAmount(item.quantity * item.price) }}
            </td>
          </tr>
        </tbody>
      </v-table>
      <v-row class="mt-10">
        <v-col cols="7">
          <div class="thank-you" v-html="estimate.terms || defaultTerms"></div>
        </v-col>
        <v-col cols="5">
          <div class="summary-box">
            <div class="summary-row">
              <span>Subtotal</span>
              <span>
                {{ formatAmount(estimate.sub_total) }}
                {{ estimate.currency }}
              </span>
            </div>
            <v-divider class="my-3" />
            <div class="grand-total">
              {{ estimate.currency }}
              {{ formatAmount(estimate.grand_total) }}
            </div>
          </div>
        </v-col>
      </v-row>
    </div>
  </v-container>
</template>
<script setup>
  import { ref, onMounted } from "vue";
  import { useRoute } from "vue-router";
  import axios from "axios";

  const route = useRoute();
  const today = new Date().toLocaleDateString('en-GB');
  const defaultTerms = 'Please review this estimate.';

  const estimate = ref({
    items: []
  });
  const company = ref(null);

  const printPage = () => {
    window.print();
  };

async function fetchCompanyInfo() {
  try {
    const response = await axios.get('/api/about-us');
    if (response.data?.success) {
      company.value = response.data.data;
    }
  } catch (error) {
    console.error('Error loading company info', error);
  }
}

  onMounted(async () => {
    try {
      const response = await axios.get(
        `/api/estimates/${route.params.id}`
      );

      estimate.value = response.data.data;
    } catch (error) {
      console.error(error);
    }

    await fetchCompanyInfo();
  });

  function formatAmount(value) {
    return Number(value || 0).toLocaleString(undefined, {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    });
  }
</script>
<style scoped>
.invoice-page {
  background: #ffffff;
  padding: 30px;
}

.invoice-wrapper {
  max-width: 1100px;
  margin: auto;
  background: white;
}

.paid-status {
  color: #2e7d32;
  font-size: 28px;
  font-weight: 700;
  margin-bottom: 40px;
}

.customer-name {
  font-size: 24px;
  font-weight: 600;
}

.customer-email {
  margin-top: 8px;
  color: #666;
}

.company-info {
  color: #666;
  line-height: 1.7;
}

.invoice-title {
  font-size: 46px;
  font-weight: 300;
  color: #333;
}

.invoice-date {
  font-size: 18px;
  margin-top: 6px;
  color: #555;
}

.invoice-table {
  border: 1px solid #d9dde3;
}

.invoice-table th {
  background: #edf1f4;
  font-weight: 700;
  font-size: 15px;
}

.invoice-table td {
  padding: 14px;
}

.summary-box {
  background: #edf1f4;
  border: 1px solid #d9dde3;
  padding: 25px;
}

.summary-row {
  display: flex;
  justify-content: space-between;
  margin-bottom: 12px;
  font-size: 16px;
}

.grand-total {
  text-align: right;
  font-size: 17px;
  font-weight: 500;
  color:#3E4448;
}

.thank-you {
  font-size: 16px;
  color: #666;
  line-height: 1.8;
}

@media print {

  .print-hide,
  .v-navigation-drawer,
  .v-app-bar,
  header,
  aside {
    display: none !important;
  }

  body {
    background: white !important;
  }

  .invoice-page {
    padding: 0;
  }

  .invoice-wrapper {
    max-width: 100%;
  }

  .summary-box {
    break-inside: avoid;
  }
}
</style>
