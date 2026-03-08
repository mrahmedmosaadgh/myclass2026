<template>
  <div class="image-element w-full h-full relative" @dblclick.stop="openModal">

    <!-- Image or placeholder -->
    <img
      v-if="element.src"
      :src="element.src"
      :alt="element.alt || 'Slide image'"
      class="w-full h-full"
      :style="{
        objectFit: element.fit || 'contain',
        opacity: (element.opacity ?? 100) / 100,
        borderRadius: (element.borderRadius ?? 0) + 'px',
        border: element.borderWidth ? `${element.borderWidth}px solid ${element.borderColor || '#000'}` : 'none'
      }"
      @error="handleImageError"
      draggable="false"
    />

    <div
      v-else
      class="placeholder"
      @click.stop="openModal"
    >
      <span class="placeholder-icon">🖼️</span>
      <span class="placeholder-text">Double-click to add image</span>
    </div>

    <!-- Dropdown trigger button (only when selected) -->
    <div v-if="isSelected" class="img-dropdown-wrapper" @click.stop>
      <button class="img-dropdown-btn" @click.stop="toggleDropdown" title="Image options">
        ⚙️
      </button>

      <div v-if="showDropdown" class="img-dropdown-menu">
        <div class="dd-header">Image Options</div>

        <!-- Change image -->
        <button class="dd-action-btn" @click.stop="openModal">
          📁 Change Image
        </button>

        <!-- Fit -->
        <div class="dd-group">
          <label class="dd-label">Object Fit</label>
          <div class="dd-fit-grid">
            <button
              v-for="fit in fitOptions"
              :key="fit.value"
              class="dd-fit-btn"
              :class="{ active: (element.fit || 'contain') === fit.value }"
              @click.stop="update({ fit: fit.value })"
            >{{ fit.label }}</button>
          </div>
        </div>

        <!-- Opacity -->
        <div class="dd-group">
          <div class="dd-label-row">
            <label class="dd-label">Opacity</label>
            <span class="dd-value">{{ element.opacity ?? 100 }}%</span>
          </div>
          <input
            type="range" min="0" max="100"
            :value="element.opacity ?? 100"
            @input="update({ opacity: +$event.target.value })"
            class="dd-slider"
          />
        </div>

        <!-- Border radius -->
        <div class="dd-group">
          <div class="dd-label-row">
            <label class="dd-label">Corner Radius</label>
            <span class="dd-value">{{ element.borderRadius ?? 0 }}px</span>
          </div>
          <input
            type="range" min="0" max="50"
            :value="element.borderRadius ?? 0"
            @input="update({ borderRadius: +$event.target.value })"
            class="dd-slider"
          />
        </div>

        <!-- Border width -->
        <div class="dd-group">
          <div class="dd-label-row">
            <label class="dd-label">Border</label>
            <span class="dd-value">{{ element.borderWidth ?? 0 }}px</span>
          </div>
          <input
            type="range" min="0" max="10"
            :value="element.borderWidth ?? 0"
            @input="update({ borderWidth: +$event.target.value })"
            class="dd-slider"
          />
          <input
            v-if="(element.borderWidth ?? 0) > 0"
            type="color"
            :value="element.borderColor || '#000000'"
            @input="update({ borderColor: $event.target.value })"
            class="dd-color-input"
            title="Border color"
          />
        </div>

        <!-- Delete -->
        <button class="dd-delete-btn" @click.stop="$emit('delete')">
          🗑️ Delete Element
        </button>
      </div>
    </div>

  </div>

  <!-- Image picker modal (teleported to prevent z-index issues) -->
  <Teleport to="body">
    <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
      <div class="modal-box">
        <div class="modal-title">Add / Change Image</div>

        <!-- URL -->
        <div class="modal-group">
          <label>Image URL</label>
          <div class="modal-row">
            <input
              v-model="urlInput"
              type="url"
              placeholder="https://example.com/photo.jpg"
              class="modal-input"
              @keyup.enter="loadUrl"
            />
            <button class="modal-btn-primary" :disabled="!isValidUrl(urlInput)" @click="loadUrl">
              Load
            </button>
          </div>
        </div>

        <!-- Upload -->
        <div class="modal-group">
          <label>Upload from device</label>
          <input
            ref="fileInput"
            type="file"
            accept="image/*"
            class="modal-file"
            @change="handleFileUpload"
          />
        </div>

        <!-- Paste -->
        <div class="modal-group">
          <label>Paste from clipboard</label>
          <button class="modal-btn-secondary" :disabled="isPasting" @click="pasteClipboard">
            {{ isPasting ? 'Pasting…' : '📋 Paste Image (Ctrl+V)' }}
          </button>
        </div>

        <div class="modal-footer">
          <button class="modal-btn-cancel" @click="closeModal">Cancel</button>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

const props = defineProps({
  element: { type: Object, required: true },
  isSelected: { type: Boolean, default: false }
});

const emit = defineEmits(['update', 'select', 'delete', 'move-to-front', 'move-to-back']);

// ── state ─────────────────────────────────────────────
const showDropdown = ref(false);
const showModal    = ref(false);
const urlInput     = ref('');
const isPasting    = ref(false);
const fileInput    = ref(null);

const fitOptions = [
  { label: 'Contain',    value: 'contain'    },
  { label: 'Cover',      value: 'cover'      },
  { label: 'Fill',       value: 'fill'       },
  { label: 'Scale-Down', value: 'scale-down' },
];

// ── helpers ───────────────────────────────────────────
const update = (payload) => emit('update', payload);

const toggleDropdown = () => { showDropdown.value = !showDropdown.value; };

const openModal = () => {
  urlInput.value = props.element.src || '';
  showDropdown.value = false;
  showModal.value = true;
};
const closeModal = () => { showModal.value = false; urlInput.value = ''; };

const isValidUrl = (url) => {
  try { new URL(url); return true; } catch { return false; }
};

const loadUrl = () => {
  if (!isValidUrl(urlInput.value)) return;
  update({ src: urlInput.value, alt: 'Image from URL' });
  closeModal();
};

const handleFileUpload = (event) => {
  const file = event.target.files[0];
  if (!file?.type.startsWith('image/')) return;
  const reader = new FileReader();
  reader.onload = (e) => { update({ src: e.target.result, alt: file.name }); closeModal(); };
  reader.readAsDataURL(file);
  event.target.value = '';
};

const pasteClipboard = async () => {
  try {
    isPasting.value = true;
    const items = await navigator.clipboard.read();
    for (const item of items) {
      for (const type of item.types) {
        if (type.startsWith('image/')) {
          const blob = await item.getType(type);
          const reader = new FileReader();
          reader.onload = (e) => { update({ src: e.target.result, alt: 'Pasted image' }); closeModal(); isPasting.value = false; };
          reader.readAsDataURL(blob);
          return;
        }
      }
    }
    alert('No image found in clipboard.');
  } catch (err) {
    console.error(err);
    alert('Clipboard paste failed. Try uploading or entering a URL.');
  } finally {
    isPasting.value = false;
  }
};

const handleImageError = (e) => { e.target.src = 'https://placehold.co/300x200?text=Image+Error'; };

// Close dropdown / paste shortcut
const onKeyDown = (e) => {
  if (showModal.value && (e.ctrlKey || e.metaKey) && e.key === 'v') {
    e.preventDefault();
    pasteClipboard();
  }
  if (e.key === 'Escape') { showDropdown.value = false; closeModal(); }
};

onMounted(()  => document.addEventListener('keydown', onKeyDown));
onUnmounted(() => document.removeEventListener('keydown', onKeyDown));
</script>

<style scoped>
/* ── element root ──────────────────────────────────── */
.image-element { cursor: move; user-select: none; }

/* placeholder */
.placeholder {
  width: 100%; height: 100%;
  display: flex; flex-direction: column;
  align-items: center; justify-content: center;
  background: #f3f4f6;
  border: 2px dashed #9ca3af;
  border-radius: 6px;
  gap: 6px;
  cursor: pointer;
  transition: background .2s;
}
.placeholder:hover { background: #e9ecef; }
.placeholder-icon  { font-size: 2rem; }
.placeholder-text  { font-size: .75rem; color: #6b7280; text-align: center; padding: 0 8px; }

/* ── dropdown ──────────────────────────────────────── */
.img-dropdown-wrapper {
  position: absolute;
  top: -12px; right: -12px;
  z-index: 1100;
}

.img-dropdown-btn {
  width: 26px; height: 26px;
  background: #4f46e5;
  color: #fff;
  border: 2px solid #fff;
  border-radius: 50%;
  font-size: 12px;
  cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  box-shadow: 0 2px 6px rgba(0,0,0,.25);
  transition: background .2s;
}
.img-dropdown-btn:hover { background: #4338ca; }

.img-dropdown-menu {
  position: absolute;
  top: 30px; right: 0;
  width: 210px;
  background: #fff;
  border: 1px solid #e5e7eb;
  border-radius: 10px;
  box-shadow: 0 8px 24px rgba(0,0,0,.12);
  padding: 8px;
  display: flex; flex-direction: column; gap: 6px;
  z-index: 1200;
}

.dd-header {
  font-size: .7rem; font-weight: 700;
  color: #6b7280; text-transform: uppercase;
  letter-spacing: .04em;
  padding: 2px 4px;
}

.dd-group { display: flex; flex-direction: column; gap: 4px; }

.dd-label-row { display: flex; justify-content: space-between; }
.dd-label     { font-size: .72rem; color: #374151; font-weight: 600; }
.dd-value     { font-size: .72rem; color: #6b7280; }

.dd-slider {
  width: 100%;
  accent-color: #4f46e5;
}

.dd-color-input {
  width: 100%;
  height: 24px;
  border: 1px solid #e5e7eb;
  border-radius: 4px;
  cursor: pointer;
}

.dd-fit-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 4px;
}

.dd-fit-btn {
  padding: 4px 6px;
  font-size: .7rem;
  border: 1px solid #d1d5db;
  border-radius: 5px;
  background: #f9fafb;
  color: #374151;
  cursor: pointer;
  transition: all .15s;
}
.dd-fit-btn:hover   { background: #e0e7ff; border-color: #a5b4fc; }
.dd-fit-btn.active  { background: #4f46e5; color: #fff; border-color: #4f46e5; }

.dd-action-btn {
  width: 100%;
  padding: 6px 8px;
  font-size: .75rem;
  background: #f0fdf4;
  color: #15803d;
  border: 1px solid #bbf7d0;
  border-radius: 6px;
  cursor: pointer;
  text-align: left;
  transition: background .15s;
}
.dd-action-btn:hover { background: #dcfce7; }

.dd-delete-btn {
  width: 100%;
  padding: 6px 8px;
  font-size: .75rem;
  background: #fff1f2;
  color: #be123c;
  border: 1px solid #fecdd3;
  border-radius: 6px;
  cursor: pointer;
  text-align: left;
  transition: background .15s;
}
.dd-delete-btn:hover { background: #ffe4e6; }

/* ── modal ─────────────────────────────────────────── */
.modal-overlay {
  position: fixed; inset: 0;
  background: rgba(0,0,0,.45);
  display: flex; align-items: center; justify-content: center;
  z-index: 9999;
}

.modal-box {
  background: #fff;
  border-radius: 14px;
  padding: 24px;
  width: 420px; max-width: calc(100vw - 32px);
  display: flex; flex-direction: column; gap: 16px;
  box-shadow: 0 20px 60px rgba(0,0,0,.2);
}

.modal-title {
  font-size: 1rem; font-weight: 700; color: #111827;
}

.modal-group {
  display: flex; flex-direction: column; gap: 6px;
}
.modal-group label {
  font-size: .75rem; font-weight: 600; color: #374151;
}

.modal-row {
  display: flex; gap: 8px;
}

.modal-input {
  flex: 1;
  padding: 8px 10px;
  border: 1px solid #d1d5db;
  border-radius: 7px;
  font-size: .85rem;
  outline: none;
  transition: border-color .2s;
}
.modal-input:focus { border-color: #6366f1; }

.modal-file {
  font-size: .8rem;
  color: #374151;
}

.modal-btn-primary {
  padding: 8px 14px;
  background: #4f46e5;
  color: #fff;
  border: none;
  border-radius: 7px;
  font-size: .82rem;
  cursor: pointer;
  transition: background .15s;
  white-space: nowrap;
}
.modal-btn-primary:hover:not(:disabled) { background: #4338ca; }
.modal-btn-primary:disabled { opacity: .45; cursor: default; }

.modal-btn-secondary {
  padding: 8px 14px;
  background: #f3f4f6;
  color: #374151;
  border: 1px solid #d1d5db;
  border-radius: 7px;
  font-size: .82rem;
  cursor: pointer;
  transition: background .15s;
}
.modal-btn-secondary:hover:not(:disabled) { background: #e5e7eb; }
.modal-btn-secondary:disabled { opacity: .45; cursor: default; }

.modal-footer {
  display: flex; justify-content: flex-end;
}

.modal-btn-cancel {
  padding: 8px 18px;
  background: transparent;
  color: #6b7280;
  border: 1px solid #d1d5db;
  border-radius: 7px;
  font-size: .82rem;
  cursor: pointer;
  transition: background .15s;
}
.modal-btn-cancel:hover { background: #f3f4f6; }
</style>