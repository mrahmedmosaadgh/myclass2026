<template>
  <q-card flat bordered class="q-pa-md">
    <div class="row items-center q-col-gutter-md q-mb-sm">
      <div class="col">
        <q-btn flat round dense icon="settings" @click="showCols = !showCols" />
        <q-btn flat round dense icon="filter_alt" @click="showFilters = !showFilters" />
        <q-btn flat round dense icon="download" @click="exportCsv" title="Export CSV" />
      </div>

      <div class="col-auto">
        <q-input dense debounce="300" v-model="search" placeholder="Search student or notes" />
      </div>
    </div>

    <q-expansion-item v-model="showFilters" icon="tune" label="Filters" class="q-mb-sm">
      <div class="row q-col-gutter-md">
        <div class="col-12 col-md-3">
          <q-select dense outlined label="Attend" v-model="filters.attend" :options="attendOptions" />
        </div>
        <div class="col-12 col-md-3">
          <q-input dense outlined label="Min Total" type="number" v-model.number="filters.minTotal" />
        </div>
        <div class="col-12 col-md-3">
          <q-input dense outlined label="Max Total" type="number" v-model.number="filters.maxTotal" />
        </div>
        <div class="col-12 col-md-3">
          <q-btn label="Apply Filters" @click="applyFilters" color="primary" />
          <q-btn flat label="Reset" @click="resetFilters" />
        </div>
      </div>
    </q-expansion-item>

    <q-card-section class="q-pa-none">
      <div class="q-my-sm" v-if="showCols">
        <q-checkbox
          v-for="col in allColumns"
          :key="col.name"
          v-model="visibleColsMap[col.name]"
          :label="col.label"
          inline
          dense
        />
        <q-btn flat label="Save Layout" @click="saveLayout" />
        <q-btn flat label="Reset Layout" @click="resetLayout" />
      </div>

      <q-table
        :rows="displayRows"
        :columns="activeColumns"
        row-key="id"
        virtual-scroll
        selection="none"
        class="shadow-1"
      >
        <template v-slot:body-cell="props">
          <q-td :props="props">
            <component :is="cellRenderer(props.col.name, props.row)" :row="props.row" :col="props.col" @update="onCellUpdate" />
          </q-td>
        </template>
      </q-table>
    </q-card-section>
  </q-card>
</template>

<script setup>
import { ref, reactive, computed, watch, onMounted } from 'vue';
import axios from 'axios';
import { useQuasar } from 'quasar';

const props = defineProps({
  teacherId: { type: Number, required: false },
  subjectId: { type: Number, required: false },
  classroomId: { type: Number, required: false },
  periodCode: { type: String, required: false },
  date: { type: String, required: false },
  yearId: { type: Number, required: false }
});

const emit = defineEmits(['record-updated','record-added','filter-changed','columns-changed']);

const $q = useQuasar();

const rows = ref([]);
const loading = ref(false);
const search = ref('');

const showCols = ref(true);
const showFilters = ref(false);

const filters = reactive({
  attend: null,
  minTotal: null,
  maxTotal: null
});

const attendOptions = [
  { label: 'All', value: null },
  { label: 'Present', value: 1 },
  { label: 'Absent', value: 0 }
];

// Define all possible columns (matches DB fields)
const allColumns = [
  { name: 'student_name', label: 'Student Name', field: row => row.student?.name || row.student_name, sortable: true },
  { name: 'attend', label: 'Attend', field: 'attend', sortable: true },
  { name: 'book', label: 'Book', field: 'book' },
  { name: 'homework', label: 'Homework', field: 'homework' },
  { name: 'out_classroom', label: 'Out-Class', field: 'out_classroom' },
  { name: 'turn1', label: 'Turn 1', field: 'turn1' },
  { name: 'turn2', label: 'Turn 2', field: 'turn2' },
  { name: 'turn3', label: 'Turn 3', field: 'turn3' },
  { name: 'plus', label: 'Plus', field: 'plus' },
  { name: 'minus', label: 'Minus', field: 'minus' },
  { name: 'total', label: 'Total', field: 'total', sortable: true },
  { name: 'notes', label: 'Notes', field: 'notes' },
  { name: 'date', label: 'Date', field: 'date', sortable: true }
];

// visible columns map (persisted)
const visibleColsMap = reactive({});
allColumns.forEach(c => visibleColsMap[c.name] = true);

function loadLayout(){
  try {
    const raw = localStorage.getItem('classroom_records_layout');
    if(raw){
      const parsed = JSON.parse(raw);
      allColumns.forEach(c => visibleColsMap[c.name] = parsed[c.name] ?? true);
    }
  } catch(e){}
}
function saveLayout(){
  const obj = {};
  allColumns.forEach(c => obj[c.name] = visibleColsMap[c.name]);
  localStorage.setItem('classroom_records_layout', JSON.stringify(obj));
  emit('columns-changed', obj);
}
function resetLayout(){
  allColumns.forEach(c => visibleColsMap[c.name] = true);
  localStorage.removeItem('classroom_records_layout');
  emit('columns-changed', null);
}

const activeColumns = computed(() => {
  return allColumns.filter(c => visibleColsMap[c.name]).map(c => ({
    name: c.name,
    label: c.label,
    field: c.field,
    align: 'left'
  }));
});

const displayRows = computed(() => {
  let data = rows.value.slice();

  // basic search
  if(search.value){
    const q = search.value.toLowerCase();
    data = data.filter(r => {
      return (r.student?.name || r.student_name || '').toLowerCase().includes(q) ||
             (r.notes || '').toLowerCase().includes(q);
    });
  }

  // filters
  if(filters.attend !== null){
    data = data.filter(r => Number(r.attend) === Number(filters.attend));
  }
  if(filters.minTotal !== null){
    data = data.filter(r => Number(r.total || 0) >= Number(filters.minTotal));
  }
  if(filters.maxTotal !== null){
    data = data.filter(r => Number(r.total || 0) <= Number(filters.maxTotal));
  }

  return data;
});

// Watch for prop changes to reload data
watch([() => props.teacherId, () => props.subjectId, () => props.classroomId, () => props.periodCode, () => props.date], fetchData);

onMounted(() => {
  loadLayout();
  if(props.teacherId || props.periodCode) fetchData();
});

async function fetchData(){
  loading.value = true;
  try{
    const params = {
      teacher_id: props.teacherId,
      subject_id: props.subjectId,
      classroom_id: props.classroomId,
      period_code: props.periodCode,
      date: props.date
    };
    const resp = await axios.get('/api/classroom-records', { params });
    rows.value = resp.data.map(r => {
      // ensure numeric fields and computed total
      r.turn1 = Number(r.turn1 || 0);
      r.turn2 = Number(r.turn2 || 0);
      r.turn3 = Number(r.turn3 || 0);
      r.plus = Number(r.plus || 0);
      r.minus = Number(r.minus || 0);
      r.total = r.turn1 + r.turn2 + r.turn3 + r.plus - r.minus;
      return r;
    });
  } catch(e){
    console.error(e);
    $q.notify({ type: 'negative', message: 'Could not load records' });
  } finally {
    loading.value = false;
  }
}

function applyFilters(){
  emit('filter-changed', { ...filters });
}

function resetFilters(){
  filters.attend = null;
  filters.minTotal = null;
  filters.maxTotal = null;
  applyFilters();
}

// cell renderer chooses component based on column
function cellRenderer(colName, row){
  // simple inline editors as local components
  const editors = {
    student_name: {
      props: ['row','col'],
      template: `<div>{{ row.student?.name || row.student_name }}</div>`
    },
    attend: {
      props: ['row','col'],
      template: `<q-toggle dense v-model="row.attend" @input="emitUpdate" />`,
      setup(props, { emit }){
        function emitUpdate(){ emit('update', props.row); }
        return { emitUpdate };
      }
    },
    default_editor: {
      props: ['row','col'],
      template: `<q-input dense v-model="row[col.name]" @blur="emitUpdate" />`,
      setup(props, { emit }){
        function emitUpdate(){ emit('update', props.row); }
        return { emitUpdate };
      }
    }
  };

  if(colName === 'student_name') return editors.student_name;
  if(['attend','book','homework','out_classroom'].includes(colName)) return editors.attend;
  if(['turn1','turn2','turn3','plus','minus','notes','date'].includes(colName)) return editors.default_editor;
  if(colName === 'total') {
    return {
      props: ['row','col'],
      template: `<div><strong>{{ computedTotal }}</strong></div>`,
      computed: {
        computedTotal(){ return (Number(this.row.turn1||0)+Number(this.row.turn2||0)+Number(this.row.turn3||0)+Number(this.row.plus||0)-Number(this.row.minus||0)); }
      }
    };
  }
  return editors.default_editor;
}

// handle cell updates
let pending = {};
async function onCellUpdate(updatedRow){
  // recompute total
  updatedRow.total = Number(updatedRow.turn1||0)+Number(updatedRow.turn2||0)+Number(updatedRow.turn3||0)+Number(updatedRow.plus||0)-Number(updatedRow.minus||0);

  // optimistic UI update already applied, now save
  try{
    await axios.patch(`/api/classroom-records/${updatedRow.id}`, updatedRow);
    emit('record-updated', updatedRow);
  } catch(e){
    console.error(e);
    $q.notify({ type: 'negative', message: 'Could not save change' });
    // ideally refetch, but keep it simple
  }
}

// simple CSV export
function exportCsv(){
  const cols = activeColumns.value;
  const header = cols.map(c => `"${c.label}"`).join(',');
  const lines = displayRows.value.map(r => {
    return cols.map(c => {
      const val = typeof c.field === 'function' ? c.field(r) : (r[c.name] ?? '');
      return `"${String(val).replace(/"/g,'""')}"`;
    }).join(',');
  });
  const csv = [header, ...lines].join('\n');
  const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = `classroom_records_${Date.now()}.csv`;
  a.click();
  URL.revokeObjectURL(url);
}
</script>
