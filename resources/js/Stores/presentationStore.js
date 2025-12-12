import { defineStore } from 'pinia';

export const usePresentationStore = defineStore('presentation', {
  state: () => ({
    slides: [],
    currentSlideIndex: 0,
    presentationTitle: 'Untitled Presentation',
    isPresentationMode: false,
  }),

  actions: {
    setPresentationMode(mode) {
      console.log('Setting presentation mode:', mode);
      this.isPresentationMode = mode;
    },

    setCurrentSlideIndex(index) {
      if (index >= 0 && index < this.slides.length) {
        this.currentSlideIndex = index;
      }
    },

    deleteSlide(index) {
      if (index >= 0 && index < this.slides.length) {
        this.slides.splice(index, 1);
        if (this.currentSlideIndex >= this.slides.length) {
          this.currentSlideIndex = Math.max(0, this.slides.length - 1);
        }
      }
    },

    duplicateSlide(index) {
      if (index >= 0 && index < this.slides.length) {
        const slideToClone = this.slides[index];
        const newSlide = {
          ...JSON.parse(JSON.stringify(slideToClone)),
          id: Date.now(),
          title: `${slideToClone.title} (Copy)`
        };
        this.slides.splice(index + 1, 0, newSlide);
        this.currentSlideIndex = index + 1;
      }
    },

    updateSlideContent(content) {
      if (this.currentSlide) {
        this.currentSlide.content = content;
      }
    },

    updateSlideTitle(index, title) {
      if (index >= 0 && index < this.slides.length) {
        this.slides[index].title = title;
      }
    },

    setTitle(title) {
      this.presentationTitle = title;
    },

    setSlides(slides) {
      console.log('Setting slides:', slides);
      this.slides = slides.map(slide => ({
        id: slide.id || Date.now(),
        title: slide.title || '',
        content: {
          elements: slide.content?.elements || [],
          backgroundImage: slide.content?.backgroundImage || ''
        }
      }));
    },

    loadPresentation(data) {
      console.log('Loading presentation data:', data);
      this.setSlides(data.slides || []);
      this.presentationTitle = data.title || 'Untitled Presentation';
      this.currentSlideIndex = 0;
    },
    addElement(element) {
      if (!this.currentSlide) {
        throw new Error('No current slide selected');
      }

      if (!this.currentSlide.content) {
        this.currentSlide.content = {
          elements: [],
          backgroundImage: ''
        };
      }

      if (!Array.isArray(this.currentSlide.content.elements)) {
        this.currentSlide.content.elements = [];
      }

      this.currentSlide.content.elements.push(element);
    },
    addSlide2(slide = null) {  // Make slide parameter optional with default null
      const newSlide = {
        id: Date.now(),
        title: '',
        content: {
          elements: [],
          backgroundImage: ''
        }
      };

      // Only spread additional properties if slide parameter exists
      if (slide) {
        Object.assign(newSlide, JSON.parse(JSON.stringify(slide)));
      }

      this.slides.push(newSlide);
    },
    addSlide() {
      const newSlide = {
        id: Date.now(),
        title: `Slide ${this.slides.length + 1}`,
        content: {
          elements: [],
          backgroundImage: ''
        }
      };
      this.slides.push(newSlide);
      this.currentSlideIndex = this.slides.length - 1;
    },

    updateElementInCurrentSlide(elementId, updates) {
      if (!this.currentSlide?.content?.elements) return;

      const elementIndex = this.currentSlide.content.elements.findIndex(el => el.id === elementId);
      if (elementIndex !== -1) {
        const element = this.currentSlide.content.elements[elementIndex];
        this.currentSlide.content.elements[elementIndex] = {
          ...element,
          ...updates
        };
        console.log('Element updated:', this.currentSlide.content.elements[elementIndex]);
      }
    },

  },

  getters: {
    currentSlide: (state) => {
      if (!state.slides.length) return null;
      return state.slides[state.currentSlideIndex] || null;
    }
  }
});








