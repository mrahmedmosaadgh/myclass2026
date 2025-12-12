<template>
  <div class="q-pa-md">
    <div class="text-h5 q-mb-md">تقارير الإنجاز 📊</div>

    <div class="row q-col-gutter-md q-mb-lg">
      <div class="col-12 col-md-6">
        <q-card>
          <q-card-section>
            <div class="text-h6">اليوم</div>
          </q-card-section>
          <q-card-section>
            <div class="text-h4 text-center text-primary">
              {{ daily.completed }} / {{ daily.total }}
            </div>
            <div class="text-caption text-center">مهمة منجزة</div>
            <q-separator class="q-my-md" />
            <div class="text-h4 text-center text-secondary">
              {{ daily.focus_minutes }}
            </div>
            <div class="text-caption text-center">دقيقة تركيز</div>
          </q-card-section>
        </q-card>
      </div>

      <div class="col-12 col-md-6">
        <q-card>
          <q-card-section>
            <div class="text-h6">هذا الأسبوع</div>
          </q-card-section>
          <q-card-section>
            <div class="text-h4 text-center text-primary">
              {{ weekly.completed }}
            </div>
            <div class="text-caption text-center">مهمة منجزة</div>
            <q-separator class="q-my-md" />
            <div class="text-h4 text-center text-secondary">
              {{ weekly.focus_minutes }}
            </div>
            <div class="text-caption text-center">دقيقة تركيز</div>
          </q-card-section>
        </q-card>
      </div>
    </div>

    <!-- Placeholder for detailed chart or table -->
    <dp-report-table 
      title="سجل المهام اليومي" 
      :rows="dailyTasks" 
      :columns="columns" 
    />
  </div>
</template>

<script setup>
import DpReportTable from '@/Components/dailyTasks/dp_ReportTable.vue'

const props = defineProps({
  daily: Object,
  weekly: Object
})

// Mock data for table for now, or pass from props
const dailyTasks = [] 
const columns = [
  { name: 'title', label: 'المهمة', field: 'title', align: 'left' },
  { name: 'status', label: 'الحالة', field: 'status', align: 'center' },
]
</script>
