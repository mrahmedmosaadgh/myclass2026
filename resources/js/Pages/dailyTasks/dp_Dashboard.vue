<template>
  <div class="q-pa-md bg-grey-1">
    <div class="text-h4 q-mb-md text-center text-primary">يومك، إنجازك! 🚀</div>
    
    <div class="row q-col-gutter-md">
      <div class="col-12 col-md-4">
        <q-card class="my-card bg-purple-1" clickable @click="$inertia.visit(route('dp.daily.index'))">
          <q-card-section class="text-center">
            <q-icon name="today" size="50px" color="purple" />
            <div class="text-h6 q-mt-sm">خطة اليوم</div>
          </q-card-section>
        </q-card>
      </div>

      <div class="col-12 col-md-4">
        <q-card class="my-card bg-orange-1" clickable @click="$inertia.visit(route('dp.focus.index'))">
          <q-card-section class="text-center">
            <q-icon name="timer" size="50px" color="orange" />
            <div class="text-h6 q-mt-sm">التركيز المباشر</div>
          </q-card-section>
        </q-card>
      </div>

      <div class="col-12 col-md-4">
        <q-card class="my-card bg-teal-1" clickable @click="$inertia.visit(route('dp.reports.index'))">
          <q-card-section class="text-center">
            <q-icon name="bar_chart" size="50px" color="teal" />
            <div class="text-h6 q-mt-sm">تقاريري</div>
          </q-card-section>
        </q-card>
      </div>
      
      <div class="col-12 col-md-4">
        <q-card class="my-card bg-blue-1" clickable @click="$inertia.visit(route('dp.master.index'))">
          <q-card-section class="text-center">
            <q-icon name="edit_calendar" size="50px" color="blue" />
            <div class="text-h6 q-mt-sm">الجدول الرئيسي</div>
          </q-card-section>
        </q-card>
      </div>
    </div>
  </div>
</template>

<script setup>
</script>
