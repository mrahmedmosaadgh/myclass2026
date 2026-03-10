<template>
  <div class="master-container dark:bg-gray-800 dark:text-gray-200">
    <h1>Vue Components Gallery</h1>
    <div class="dropdown-container dark:bg-gray-700">
      <label for="component-selector">Select a component:</label>
      <select id="component-selector" v-model="selectedComponent" @change="loadComponent" 
              class="dark:bg-gray-600 dark:text-gray-200 dark:border-gray-500">
        <option value="">Choose a component...</option>
        <option v-for="comp in components" :key="comp.name" :value="comp.name">
          {{ comp.displayName }}
        </option>
      </select>
      
      <div class="navigation-buttons">
        <button @click="goBack" :disabled="!canGoBack" class="nav-button">Back</button>
        <button @click="goToPrevious" :disabled="!hasPrevious" class="nav-button">Previous</button>
        <button @click="goToNext" :disabled="!hasNext" class="nav-button">Next</button>
        <button @click="goToLast" :disabled="!components.length" class="nav-button">Last</button>
      </div>
    </div>

    <div class="component-display" v-if="selectedComponent">
      <h2 class="dark:text-gray-200">{{ selectedDisplayName }} Preview</h2>
      <div class="component-wrapper dark:bg-gray-700">
        <component :is="dynamicComponent"></component>
      </div>
    </div>
  </div>
</template>

<script>
import { defineAsyncComponent } from 'vue';

export default {
  name: 'MasterComponent',
  data() {
    return {
      selectedComponent: '',
      dynamicComponent: null,
      components: [
        { name: '1', displayName: 'Ribbon Card Design' },
        { name: '2', displayName: 'Flip Card Hover Effect' },
        { name: '11', displayName: 'Gradient Interactive Card' },
        { name: '12', displayName: 'Dual Layer Card' },
        { name: '13', displayName: 'Animated Weather Card' },
        { name: '16', displayName: 'Card Component 16' },
        { name: '17', displayName: 'Card Component 17' },
        { name: '18', displayName: 'Card Component 18' },
        { name: '19', displayName: 'Card Component 19' },
        { name: '20', displayName: 'Card Component 20' },
        { name: '21', displayName: 'Card Component 21' },
        { name: '22', displayName: 'Card Component 22' },
        { name: '23', displayName: 'Card Component 23' },
        { name: '24', displayName: 'Card Component 24' },
        { name: '25', displayName: 'Card Component 25' },
        { name: '26', displayName: 'Card Component 26' },
        { name: '27', displayName: 'Card Component 27' },
        { name: '28', displayName: 'Card Component 28' },
        { name: '29', displayName: 'Card Component 29' },
        { name: '30', displayName: 'Card Component 30' },
        { name: '31', displayName: 'Card Component 31' },
        { name: '35', displayName: 'Card Component 35' },
        { name: '4', displayName: 'Animated Card With Shadow' },
        { name: '5', displayName: 'Gradient Border Card' },
        { name: '7', displayName: 'Pricing Card Dark' },
        { name: '8', displayName: 'Interactive Menu Card' },
        { name: '9', displayName: 'Hover Reveal Card' },
        { name: '44', displayName: 'Pricing Card 44' },
        { name: 'gum1-btn', displayName: 'Gum1 Button' },
        { name: 'avatar-stack', displayName: 'Avatar Stack' },
        { name: 'book', displayName: 'Book Component' },
        { name: 'borderup', displayName: 'Border Up Component' },
        { name: 'content-page', displayName: 'Content Page' },
        { name: 'cube-gallery', displayName: 'Cube Gallery' },
        { name: 'cube', displayName: 'Cube Component' },
        { name: 'earth', displayName: 'Earth Component' },
        { name: 'flip-card', displayName: 'Flip Card' },
        { name: 'info-card', displayName: 'Info Card' },
        { name: 'notification-badge', displayName: 'Notification Badge' },
        { name: 'ribbon-card', displayName: 'Ribbon Card' },
        { name: 'rotating-card', displayName: 'Rotating Card' },
        { name: 'stats-card', displayName: 'Stats Card' },
        { name: 'text', displayName: 'Text Component' },
        { name: 'timeline', displayName: 'Timeline Component' },
        { name: '3d-book-flip-card', displayName: '3D Book Flip Card' },
        { name: '3d-rotate-card', displayName: '3D Rotate Card' },
        { name: 'animated-card-with-shadow', displayName: 'Animated Card With Shadow' },
        { name: 'animated-gradient-card', displayName: 'Animated Gradient Card' },
        { name: 'animated-weather-card', displayName: 'Animated Weather Card' },
        { name: 'dual-layer-card', displayName: 'Dual Layer Card' },
        { name: 'flip-card-hover-effect', displayName: 'Flip Card Hover Effect' },
        { name: 'gradient-border-card', displayName: 'Gradient Border Card' },
        { name: 'gradient-interactive-card', displayName: 'Gradient Interactive Card' },
        { name: 'hover-reveal-card', displayName: 'Hover Reveal Card' },
        { name: 'interactive-menu-card', displayName: 'Interactive Menu Card' },
        { name: 'pricing-card-dark', displayName: 'Pricing Card Dark' },
        { name: 'ribbon-card-design', displayName: 'Ribbon Card Design' },
      ],
      currentIndex: -1
    };
  },
  computed: {
    selectedDisplayName() {
      const component = this.components.find(comp => comp.name === this.selectedComponent);
      return component ? component.displayName : '';
    },
    hasPrevious() {
      return this.currentIndex > 0;
    },
    hasNext() {
      return this.currentIndex < this.components.length - 1 && this.currentIndex !== -1;
    },
    canGoBack() {
      return this.selectedComponent !== '';
    }
  },
  watch: {
    selectedComponent(newVal) {
      if (newVal) {
        const index = this.components.findIndex(comp => comp.name === newVal);
        this.currentIndex = index;
      } else {
        this.currentIndex = -1;
      }
    }
  },
  methods: {
    loadComponent() {
      if (!this.selectedComponent) {
        this.dynamicComponent = null;
        return;
      }

      // Dynamically import the selected component
      import(`../cards_vue/${this.selectedComponent}.vue`)
        .then(module => {
          this.dynamicComponent = defineAsyncComponent(() => Promise.resolve(module.default));
        })
        .catch(err => {
          console.error(`Error loading component ${this.selectedComponent}:`, err);
          alert(`Could not load component: ${this.selectedComponent}`);
          this.dynamicComponent = null;
          this.selectedComponent = '';
        });
    },
    goToNext() {
      if (this.hasNext) {
        this.selectedComponent = this.components[this.currentIndex + 1].name;
        this.loadComponent();
      }
    },
    goToPrevious() {
      if (this.hasPrevious) {
        this.selectedComponent = this.components[this.currentIndex - 1].name;
        this.loadComponent();
      }
    },
    goToLast() {
      if (this.components.length > 0) {
        this.selectedComponent = this.components[this.components.length - 1].name;
        this.loadComponent();
        this.currentIndex = this.components.length - 1;
      }
    },
    goBack() {
      this.selectedComponent = '';
      this.dynamicComponent = null;
      this.currentIndex = -1;
    }
  }
};
</script>

<style scoped>
.master-container {
  padding: 20px;
  max-width: 1200px;
  margin: 0 auto;
  font-family: 'Arial', sans-serif;
  background-color: #fff;
  color: #333;
  min-height: 100vh;
}

.master-container.dark {
  background-color: #1f2937;
  color: #d1d5db;
}

.dropdown-container {
  margin: 20px 0;
  padding: 20px;
  background-color: #f5f5f5;
  border-radius: 8px;
}

.dropdown-container label {
  display: block;
  margin-bottom: 10px;
  font-weight: bold;
  color: #333;
}

.dropdown-container select {
  width: 100%;
  padding: 10px;
  font-size: 16px;
  border: 1px solid #ccc;
  border-radius: 4px;
  background-color: white;
  margin-bottom: 15px;
}

.navigation-buttons {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}

.nav-button {
  flex: 1;
  padding: 10px;
  margin: 5px;
  background-color: #4F46E5;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  min-width: 80px;
  transition: background-color 0.3s;
}

.nav-button:hover:not(:disabled) {
  background-color: #4338CA;
}

.nav-button:disabled {
  background-color: #A5A5A5;
  cursor: not-allowed;
}

.component-display {
  margin-top: 30px;
  padding: 20px;
  background-color: #fafafa;
  border-radius: 8px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.component-display h2 {
  color: #333;
  margin-bottom: 20px;
}

.component-wrapper {
  padding: 20px;
  background-color: white;
  border-radius: 8px;
  min-height: 300px;
  display: flex;
  justify-content: center;
  align-items: center;
}

/* Dark mode styles */
.master-container.dark\:bg-gray-800 {
  background-color: #1f2937;
}

.master-container.dark\:text-gray-200 {
  color: #e5e7eb;
}

.dropdown-container.dark\:bg-gray-700 {
  background-color: #374151;
}

.dropdown-container select.dark\:bg-gray-600 {
  background-color: #4b5563;
  color: #e5e7eb;
  border-color: #6b7280;
}

.component-display h2.dark\:text-gray-200 {
  color: #e5e7eb;
}

.component-wrapper.dark\:bg-gray-700 {
  background-color: #374151;
}
</style>