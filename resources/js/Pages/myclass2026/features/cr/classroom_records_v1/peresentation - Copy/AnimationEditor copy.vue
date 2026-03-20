<template>
  <div class="animation-editor">
    <div class="slide-canvas">
      <SlideElement
        v-for="element in slide.elements"
        :key="element.id"
        :element="element"
        :mode="'animation'"
        @select="selectElement"
      />
    </div>

    <!-- Animation Panel -->
    <div class="animation-panel" v-if="selectedElement">
      <h3>Animation Settings</h3>
      
      <div class="setting-group">
        <label>Initial State</label>
        <div class="button-group">
          <button 
            @click="updateInitialState('visible')"
            :class="['state-btn', selectedElement.initialState === 'visible' ? 'active' : '']"
          >
            👁 Visible
          </button>
          <button 
            @click="updateInitialState('hidden')"
            :class="['state-btn', selectedElement.initialState === 'hidden' ? 'active' : '']"
          >
            🔒 Hidden
          </button>
        </div>
      </div>

      <div class="setting-group">
        <label>Animation on Click</label>
        <div class="button-group vertical">
          <button 
            @click="updateAnimation(null)"
            :class="['anim-btn', selectedElement.animation === null ? 'active' : '']"
          >
            None
          </button>
          <button 
            @click="updateAnimation('fadeIn')"
            :class="['anim-btn', selectedElement.animation === 'fadeIn' ? 'active' : '']"
          >
            ✨ Fade In
          </button>
          <button 
            @click="updateAnimation('fadeOut')"
            :class="['anim-btn', selectedElement.animation === 'fadeOut' ? 'active' : '']"
          >
            💫 Fade Out
          </button>
        </div>
      </div>

      <div class="info-box">
        <p><strong>🎯 Two Animation Modes:</strong></p>
        
        <div style="background: rgba(106, 74, 226, 0.2); padding: 12px; border-radius: 8px; margin-bottom: 12px;">
          <p style="margin: 0 0 8px 0; color: #4ade80; font-weight: 600;">👁 Mode 1: Start Hidden → Click to SHOW</p>
          <ul style="margin: 0; padding-left: 20px; font-size: 13px;">
            <li><strong>Initial State:</strong> 🔒 Hidden</li>
            <li><strong>Animation:</strong> ✨ Fade In (or Bounce In)</li>
            <li><strong>Dot:</strong> 👁 Purple (waiting to show) → ✅ Green (shown)</li>
            <li><strong>Use:</strong> Reveal points gradually during your presentation!</li>
          </ul>
        </div>
        
        <div style="background: rgba(251, 146, 60, 0.2); padding: 12px; border-radius: 8px;">
          <p style="margin: 0 0 8px 0; color: #fb923c; font-weight: 600;">✨ Mode 2: Start Visible → Click to HIDE</p>
          <ul style="margin: 0; padding-left: 20px; font-size: 13px;">
            <li><strong>Initial State:</strong> 👁 Visible</li>
            <li><strong>Animation:</strong> 💫 Fade Out</li>
            <li><strong>Dot:</strong> ✨ Orange (waiting to hide) → ❌ Red (hidden)</li>
            <li><strong>Use:</strong> Make elements disappear after explaining them!</li>
          </ul>
        </div>
        
        <p style="margin-top: 12px; font-size: 13px; color: #aaa;">
          <strong>💡 Tip:</strong> The colored dot is always visible (low opacity) so you know which elements have animations. Hover over it or click to interact!
        </p>
      </div>
    </div>

    <div class="no-selection" v-else>
      <p>👆 Click an element on the slide to configure its animation</p>
    </div>
  </div>
</template>

<script>
import SlideElement from './SlideElement.vue';

export default {
  name: 'AnimationEditor',
  components: {
    SlideElement
  },
  props: {
    slide: {
      type: Object,
      required: true
    }
  },
  data() {
    return {
      selectedElementId: null
    };
  },
  computed: {
    selectedElement() {
      if (!this.selectedElementId) return null;
      return this.slide.elements.find(el => el.id === this.selectedElementId);
    }
  },
  methods: {
    selectElement(elementId) {
      this.selectedElementId = elementId;
    },
    updateInitialState(state) {
      if (!this.selectedElement) return;

      const updatedElements = this.slide.elements.map(el => 
        el.id === this.selectedElementId 
          ? { ...el, initialState: state }
          : el
      );

      this.$emit('update:slide', {
        ...this.slide,
        elements: updatedElements
      });
    },
    updateAnimation(animation) {
      if (!this.selectedElement) return;

      const updatedElements = this.slide.elements.map(el => 
        el.id === this.selectedElementId 
          ? { ...el, animation: animation }
          : el
      );

      this.$emit('update:slide', {
        ...this.slide,
        elements: updatedElements
      });
    }
  }
};
</script>

<style scoped>
.animation-editor {
  display: flex;
  gap: 20px;
  width: 100%;
  max-width: 1400px;
}

.slide-canvas {
  flex: 0 0 900px;
  height: 506px;
  background: #ffffff;
  border-radius: 8px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
  position: relative;
  overflow: hidden;
}

.animation-panel {
  flex: 1;
  background: #2a2a2a;
  border-radius: 8px;
  padding: 20px;
  overflow-y: auto;
}

.animation-panel h3 {
  margin: 0 0 20px 0;
  color: #fff;
  font-size: 20px;
}

.setting-group {
  margin-bottom: 24px;
}

.setting-group label {
  display: block;
  margin-bottom: 8px;
  color: #aaa;
  font-size: 13px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.button-group {
  display: flex;
  gap: 8px;
}

.button-group.vertical {
  flex-direction: column;
}

.state-btn,
.anim-btn {
  flex: 1;
  padding: 12px 16px;
  background: #3a3a3a;
  border: 2px solid #4a4a4a;
  color: #fff;
  border-radius: 6px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 600;
  transition: all 0.2s;
  text-align: left;
}

.state-btn:hover,
.anim-btn:hover {
  background: #4a4a4a;
  border-color: #5a5a5a;
}

.state-btn.active {
  background: #4ade80;
  border-color: #4ade80;
  color: #064e3b;
}

.anim-btn.active {
  background: #6a4ae2;
  border-color: #7a5af2;
  color: #fff;
}

.info-box {
  background: #3a3a3a;
  border-left: 4px solid #6a4ae2;
  padding: 16px;
  border-radius: 6px;
  margin-top: 24px;
}

.info-box p {
  margin: 0 0 12px 0;
  color: #fff;
  font-size: 14px;
}

.info-box ul {
  margin: 0;
  padding-left: 20px;
  color: #aaa;
  font-size: 13px;
  line-height: 1.6;
}

.info-box li {
  margin-bottom: 6px;
}

.info-box strong {
  color: #fff;
}

.no-selection {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #2a2a2a;
  border-radius: 8px;
  border: 2px dashed #4a4a4a;
}

.no-selection p {
  color: #aaa;
  font-size: 16px;
  text-align: center;
  padding: 40px;
}
</style>
