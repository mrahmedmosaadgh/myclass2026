<script setup>
import { ref, computed, watch } from 'vue';
import { usePresentationStore } from '../stores/presentationStore';
import { useUIStore } from '../stores/uiStore';
import draggable from 'vuedraggable';

const presentation = usePresentationStore();
const ui = useUIStore();

const canGoPrev = computed(() => presentation.currentSlideIndex > 0);
const canGoNext = computed(() => presentation.currentSlideIndex < presentation.slides.length - 1);

function goPrev() {
  if (!canGoPrev.value) return;
  presentation.selectSlide(presentation.currentSlideIndex - 1);
}

function goNext() {
  if (!canGoNext.value) return;
  presentation.selectSlide(presentation.currentSlideIndex + 1);
}

const isOrganizerOpen = ref(false);
const organizerSlides = ref([]);

function syncOrganizerSlidesFromStore() {
  organizerSlides.value = (presentation.slides || []).map((s) => ({ id: s.id }));
}

watch(
  () => presentation.slides?.length,
  () => {
    if (isOrganizerOpen.value) {
      syncOrganizerSlidesFromStore();
    }
  }
);

function openOrganizer() {
  syncOrganizerSlidesFromStore();
  isOrganizerOpen.value = true;
}

function applyOrganizerOrder() {
  const currentId = presentation.currentSlide?.id;
  const byId = new Map((presentation.slides || []).map((s) => [s.id, s]));
  const ordered = organizerSlides.value
    .map((x) => byId.get(x.id))
    .filter(Boolean);
  if (!ordered.length) return;

  presentation.slides = ordered;
  if (currentId) {
    const idx = presentation.slides.findIndex((s) => s.id === currentId);
    if (idx !== -1) {
      presentation.selectSlide(idx);
    }
  }
  isOrganizerOpen.value = false;
}

function organizerDeleteAt(index) {
  if ((presentation.slides || []).length <= 1) return;
  const item = organizerSlides.value[index];
  if (!item) return;
  const storeIndex = presentation.slides.findIndex((s) => s.id === item.id);
  if (storeIndex !== -1) {
    presentation.deleteSlide(storeIndex);
  }
  syncOrganizerSlidesFromStore();
}
</script>

<template>
  <div class="slide-nav-simple">
    <div class="nav-top">
      <q-btn flat dense round icon="chevron_left" :disable="!canGoPrev" @click="goPrev" />
      <div class="nav-counter">{{ presentation.currentSlideIndex + 1 }}/{{ presentation.slides.length }}</div>
      <q-btn flat dense round icon="chevron_right" :disable="!canGoNext" @click="goNext" />
    </div>

    <div class="nav-actions">
      <q-btn color="primary" dense no-caps label="Organize" @click="openOrganizer" />
      <q-btn color="positive" dense no-caps label="Add" @click="presentation.addSlide()" />
      <q-btn flat dense no-caps label="Hide" icon="chevron_left" @click="ui.toggleSlideNav()" />
    </div>

    <div class="nav-list">
      <q-btn
        v-for="(slide, idx) in presentation.slides"
        :key="slide.id"
        dense
        flat
        class="nav-slide-btn"
        :class="{ active: presentation.currentSlideIndex === idx }"
        @click="presentation.selectSlide(idx)"
        :label="String(idx + 1)"
      />
    </div>

    <q-dialog v-model="isOrganizerOpen">
      <q-card style="width: 720px; max-width: 95vw;">
        <q-card-section class="row items-center">
          <div class="text-h6">Slides Organizer</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>

        <q-card-section>
          <div class="q-mb-sm" style="color:#6b7280; font-weight:600;">Drag to reorder. Use delete to remove slides.</div>

          <draggable
            v-model="organizerSlides"
            item-key="id"
            handle=".drag-handle"
          >
            <template #item="{ element, index }">
              <div class="organizer-row">
                <div class="drag-handle">⋮⋮</div>
                <div class="organizer-title">Slide {{ index + 1 }}</div>
                <q-space />
                <q-btn
                  flat
                  dense
                  no-caps
                  label="Open"
                  @click="() => { presentation.selectSlide(index); isOrganizerOpen = false; }"
                />
                <q-btn
                  flat
                  dense
                  color="negative"
                  icon="delete"
                  @click="organizerDeleteAt(index)"
                  :disable="presentation.slides.length <= 1"
                />
              </div>
            </template>
          </draggable>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat no-caps label="Cancel" v-close-popup />
          <q-btn color="primary" no-caps label="Apply" @click="applyOrganizerOrder" />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </div>
</template>

<style scoped>
.slide-nav-simple {
  width: 120px;
  height: calc(100vh - 90px);
  position: sticky;
  top: 90px;
  background: rgba(255, 255, 255, 0.9);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(229, 231, 235, 0.8);
  border-radius: 14px;
  padding: 10px;
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.nav-top {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.nav-counter {
  font-size: 12px;
  font-weight: 700;
  color: #374151;
}

.nav-actions {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.nav-list {
  flex: 1;
  overflow: auto;
  display: flex;
  flex-direction: column;
  gap: 4px;
  padding-right: 2px;
}

.nav-slide-btn {
  justify-content: center;
  border-radius: 10px;
  border: 1px solid rgba(0, 0, 0, 0.06);
}

.nav-slide-btn.active {
  background: rgba(59, 130, 246, 0.12);
  border-color: rgba(59, 130, 246, 0.4);
  color: #1d4ed8;
  font-weight: 800;
}

.organizer-row {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 10px;
  border: 1px solid rgba(0, 0, 0, 0.06);
  border-radius: 12px;
  margin-bottom: 8px;
  background: white;
}

.drag-handle {
  cursor: grab;
  user-select: none;
  color: #6b7280;
  font-weight: 900;
}

.organizer-title {
  font-weight: 700;
  color: #111827;
}
</style>
  
