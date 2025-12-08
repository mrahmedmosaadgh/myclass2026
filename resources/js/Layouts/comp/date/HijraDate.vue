<template>
  <div class="hijri-date">
    <div v-if="loading" class="loading">
      <span class="spinner"></span> Loading Hijri date...
    </div>
    <div v-else-if="error" class="error">
      {{ error }}
      <button @click="fetchHijriDate" class="retry-btn">Retry</button>
    </div>
    <div v-else class="date-display flex flex-row gap-2">
        <div class="   " dir="rtl">{{ hijriDateArabic.year }}</div>
        <div class=" " dir="rtl">{{ hijriDateArabic.month }}</div>
      <div color="black" text-color="white" class="px-1 m-0 bg-black text-white "  >{{ hijriDateArabic.day }}</div>
      <div class="   " dir="rtl">{{ hijriDateEnglish.month }}</div>
      <!-- <div class="english">{{ hijriDateEnglish }}</div> -->
    </div>
  </div>
</template>

<script>
export default {
  name: 'HijriDate',
  data() {
    return {
      loading: true,
      error: null,
      hijriDateArabic: '',
      hijriDateEnglish: ''
    }
  },
  methods: {
    async fetchHijriDate() {
      this.loading = true;
      this.error = null;

      try {
        const response = await fetch('https://api.aladhan.com/v1/gToH', {
          method: 'GET'
        });

        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();

        if (!data.data || !data.data.hijri) {
          throw new Error('Invalid data received from API');
        }

        const hijri = data.data.hijri;

        // Format Arabic date
        this.hijriDateArabic = {
            day: hijri.day,
            month: hijri.month.ar,
            year: hijri.year
        }
        //  `${hijri.day} ${hijri.month.ar} ${hijri.year}`;
        // this.hijriDateArabic = `${hijri.day} ${hijri.month.ar} ${hijri.year}`;

        // Format English date
        this.hijriDateEnglish ={
            day: hijri.day,
            month: hijri.month.en,
            year: hijri.year
        }
        // `${hijri.day} ${hijri.month.en} ${hijri.year}H`;

      } catch (err) {
        this.error = `Unable to load Hijri date: ${err.message}`;
        console.error('Error:', err);
      } finally {
        this.loading = false;
      }
    }
  },
  created() {
    this.fetchHijriDate();
  }
}
</script>

<style scoped>
.hijri-date {
  /* padding: 1rem; */
  font-family: Arial, sans-serif;
}

.date-display {
  text-align: center;
}

.arabic {
  font-size: 1.5rem;
  margin-bottom: 0.5rem;
  font-family: 'Traditional Arabic', 'Noto Naskh Arabic', Arial, sans-serif;
}

.english {
  font-size: 1.2rem;
}

.error {
  color: #dc3545;
  text-align: center;
  margin: 1rem 0;
}

.retry-btn {
  margin-left: 1rem;
  padding: 0.25rem 0.75rem;
  background-color: #007bff;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.loading {
  text-align: center;
  color: #666;
}

.spinner {
  display: inline-block;
  width: 20px;
  height: 20px;
  border: 3px solid #f3f3f3;
  border-top: 3px solid #3498db;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-right: 0.5rem;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}
</style>
