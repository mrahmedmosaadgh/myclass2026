<template>
  <div class="q-pa-md">
    <div class="row items-center justify-between q-mb-md">
      <div class="text-h5">الجدول الرئيسي 📅</div>
      <q-btn label="إضافة مهمة" color="primary" icon="add" @click="showAddDialog = true" />
    </div>

    <q-list bordered separator class="bg-white rounded-borders">
      <q-item v-for="task in tasks" :key="task.id">
        <q-item-section>
          <q-item-label>{{ task.title }}</q-item-label>
          <q-item-label caption>{{ task.start_time }} - {{ task.end_time }}</q-item-label>
        </q-item-section>
        <q-item-section side>
          <div class="row q-gutter-sm">
            <q-btn flat round color="primary" icon="edit" @click="editTask(task)" />
            <q-btn flat round color="negative" icon="delete" @click="deleteTask(task.id)" />
          </div>
        </q-item-section>
      </q-item>
    </q-list>

    <!-- Add/Edit Dialog -->
    <q-dialog v-model="showAddDialog">
      <q-card style="min-width: 350px">
        <q-card-section>
          <div class="text-h6">{{ isEditing ? 'تعديل مهمة' : 'مهمة جديدة' }}</div>
        </q-card-section>

        <q-card-section class="q-pt-none">
          <q-input v-model="form.title" label="عنوان المهمة" autofocus />
          <div class="row q-col-gutter-sm q-mt-sm">
            <div class="col-6">
              <q-input v-model="form.start_time" label="من" mask="time" :rules="['time']">
                <template v-slot:append>
                  <q-icon name="access_time" class="cursor-pointer">
                    <q-popup-proxy cover transition-show="scale" transition-hide="scale">
                      <q-time v-model="form.start_time" format24h>
                        <div class="row items-center justify-end">
                          <q-btn v-close-popup label="Close" color="primary" flat />
                        </div>
                      </q-time>
                    </q-popup-proxy>
                  </q-icon>
                </template>
              </q-input>
            </div>
            <div class="col-6">
              <q-input v-model="form.end_time" label="إلى" mask="time" :rules="['time']">
                <template v-slot:append>
                  <q-icon name="access_time" class="cursor-pointer">
                    <q-popup-proxy cover transition-show="scale" transition-hide="scale">
                      <q-time v-model="form.end_time" format24h>
                        <div class="row items-center justify-end">
                          <q-btn v-close-popup label="Close" color="primary" flat />
                        </div>
                      </q-time>
                    </q-popup-proxy>
                  </q-icon>
                </template>
              </q-input>
            </div>
          </div>
          <q-input v-model="form.description" label="ملاحظة تحفيزية" type="textarea" rows="2" />
        </q-card-section>

        <q-card-actions align="right" class="text-primary">
          <q-btn flat label="إلغاء" v-close-popup />
          <q-btn flat label="حفظ" @click="saveTask" />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { Notify } from 'quasar'

const props = defineProps({
  tasks: Array
})

const showAddDialog = ref(false)
const isEditing = ref(false)
const editingId = ref(null)

const form = useForm({
  title: '',
  start_time: '',
  end_time: '',
  description: '',
  type: 'general'
})

const editTask = (task) => {
  isEditing.value = true
  editingId.value = task.id
  form.title = task.title
  form.start_time = task.start_time ? task.start_time.substr(0, 5) : ''
  form.end_time = task.end_time ? task.end_time.substr(0, 5) : ''
  form.description = task.description
  showAddDialog.value = true
}

const saveTask = () => {
  if (isEditing.value) {
    form.put(route('dp.master.update', editingId.value), {
      onSuccess: () => {
        showAddDialog.value = false
        resetForm()
        Notify.create({ type: 'positive', message: 'تم التحديث بنجاح' })
      }
    })
  } else {
    form.post(route('dp.master.store'), {
      onSuccess: () => {
        showAddDialog.value = false
        resetForm()
        Notify.create({ type: 'positive', message: 'تمت الإضافة بنجاح' })
      }
    })
  }
}

const deleteTask = (id) => {
  if (confirm('هل أنت متأكد من الحذف؟')) {
    form.delete(route('dp.master.destroy', id), {
      onSuccess: () => Notify.create({ type: 'positive', message: 'تم الحذف' })
    })
  }
}

const resetForm = () => {
  isEditing.value = false
  editingId.value = null
  form.reset()
}
</script>
