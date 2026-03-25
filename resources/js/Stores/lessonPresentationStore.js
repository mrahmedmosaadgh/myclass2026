import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import axios from 'axios';
import { useQuasar } from 'quasar';

export const useLessonPresentationStore = defineStore('lesson-presentation', () => {

    // ==========================================
    // State
    // ==========================================
    const presentation = ref({
        id: null,
        name: '',
        description: '',
        grade_id: null,
        subject_id: null,
        quiz_id: null,
    });

    const slides = ref([]);
    const sections = ref([]);
    const currentSectionId = ref(null);
    const activeSlideId = ref(null);

    const isSaving = ref(false);
    const isLoading = ref(false);
    const isDirty = ref(false);

    const templates = ref([]); // Store all templates
    const selectedTemplate = ref(null); // Store the selected template

    // Load selected template from local storage if available
    const loadSelectedTemplate = () => {
        const storedTemplate = localStorage.getItem('selectedTemplate');
        if (storedTemplate) {
            try {
                selectedTemplate.value = JSON.parse(storedTemplate);
            } catch (error) {
                console.error('Failed to parse stored template:', error);
            }
        }
    };

    // Save selected template to local storage
    const saveSelectedTemplate = () => {
        if (selectedTemplate.value) {
            localStorage.setItem('selectedTemplate', JSON.stringify(selectedTemplate.value));
        }
    };

    // Call loadSelectedTemplate on store initialization
    loadSelectedTemplate();

    // ==========================================
    // Getters
    // ==========================================

    const currentSectionSlides = computed(() => {
        if (!currentSectionId.value) return [];
        return slides.value.filter(s => s.section === currentSectionId.value);
    });

    const activeSlide = computed(() => {
        if (!activeSlideId.value) return null;
        return slides.value.find(s => s.id === activeSlideId.value) ||
            // Fallback for new slides that might rely on object reference or index
            // But ideally we use IDs. For new slides without ID, we might need a temporary ID.
            slides.value.find(s => s._tempId === activeSlideId.value);
    });

    const slideCount = computed(() => slides.value.length);

    // ==========================================
    // Actions
    // ==========================================

    /**
     * Initialize the store with data from the page props
     * @param {Object} props - Page props (sections, defaultContext, etc)
     */
    const init = (props) => {
        // Set sections
        if (props.sections) {
            sections.value = props.sections;
            // Default to first section if not set
            if (!currentSectionId.value && sections.value.length > 0) {
                currentSectionId.value = sections.value[0].id;
            }
        }

        // Initialize presentation based on context or existing data
        if (props.presentation) {
            presentation.value = { ...props.presentation };
            // If presentation exists, fetch full data (slides)
            if (presentation.value.id) {
                fetch(presentation.value.id);
            }
        } else if (props.defaultContext) {
            // New lesson
            presentation.value = {
                id: null,
                name: 'New Lesson',
                description: '',
                grade_id: props.defaultContext.grade_id || null,
                subject_id: props.defaultContext.subject_id || null,
                quiz_id: null,
            };
            slides.value = [];

            // Check for template_id in URL to apply template for new lessons
            const urlParams = new URLSearchParams(window.location.search);
            const templateId = urlParams.get('template_id');
            if (templateId) {
                applyTemplateOnCreate(templateId, props.defaultContext.subject_id);
            }
        }
    };

    /**
     * Fetch presentation data from API
     */
    const fetch = async (id) => {
        isLoading.value = true;
        try {
            const response = await axios.get(route('lesson-presentation.show', id));
            const data = response.data;

            presentation.value = {
                id: data.id,
                name: data.name,
                description: data.description,
                grade_id: data.grade_id,
                subject_id: data.subject_id,
                quiz_id: data.quiz_id,
                lesson_plan_template_id: data.lesson_plan_template_id
            };

            // Add temp IDs to slides if they strictly need them for local selection
            // But usually we can use real ID. For new slides we generate temp ID.
            slides.value = (data.slides || []).map(s => ({ ...s, _tempId: s.id || crypto.randomUUID() }));

            // Select first slide
            if (slides.value.length > 0) {
                activeSlideId.value = slides.value[0].id || slides.value[0]._tempId;
            }

        } catch (error) {
            console.error('Failed to fetch presentation:', error);
        } finally {
            isLoading.value = false;
        }
    };

    /**
     * Apply template structure when creating a new lesson
     */
    const applyTemplateOnCreate = async (templateId, subjectId) => {
        try {
            // Check if templates are already loaded
            if (!templates.value || templates.value.length === 0) {
                console.warn('No templates available. Please reload or create templates.');
                return;
            }

            const template = templates.value.find(t => t.id == templateId);

            if (!template) {
                console.warn(`Template with ID ${templateId} not found.`);
                return;
            }

            if (!template.structure || !template.structure.sections) {
                console.warn(`Template structure is invalid or missing sections for template ID ${templateId}.`);
                return;
            }

            presentation.value.name = template.name; // Optional: set name from template

            // Generate initial slides
            for (const section of template.structure.sections) {
                const slideCount = section.slides || 1;
                const slideType = section.default_slide_type || 'text';
                for (let i = 0; i < slideCount; i++) {
                    addSlide(section.id || 'learn', slideType, false); // false = don't select yet
                }
            }

            // Select first slide
            if (slides.value.length > 0) {
                activeSlideId.value = slides.value[0]._tempId;
            }
        } catch (e) {
            console.error('Failed to apply template:', e);
        }
    };

    /**
     * Save the presentation
     */
    const save = async () => {
        if (!presentation.value.name) return; // simple validation

        isSaving.value = true;
        try {
            const payload = {
                ...presentation.value,
                slides: slides.value
            };

            let response;
            if (presentation.value.id) {
                response = await axios.put(route('lesson-presentation.update', presentation.value.id), payload);
            } else {
                response = await axios.post(route('lesson-presentation.store'), payload);
            }

            const data = response.data;
            presentation.value.id = data.id; // ensure ID is set after create

            // Update URL if just created (optional, but good for UX)
            if (window.location.pathname !== '/lesson-presentation/edit' && !window.location.search.includes(`id=${data.id}`)) {
                // history.pushState({}, '', route('lesson-presentation.edit', { id: data.id })); // simplified
            }

            isDirty.value = false;
            return data;
        } catch (error) {
            console.error('Failed to save:', error);
            throw error;
        } finally {
            isSaving.value = false;
        }
    };

    /**
     * Add a new slide
     */
    const addSlide = (sectionId = null, type = 'text', select = true) => {
        const targetSection = sectionId || currentSectionId.value;
        const newSlide = {
            id: null,
            _tempId: crypto.randomUUID(),
            slide_type: type,
            slide_content: {},
            section: targetSection
        };

        slides.value.push(newSlide);
        isDirty.value = true;

        if (select) {
            activeSlideId.value = newSlide._tempId;
        }

        return newSlide;
    };

    /**
     * Delete a slide
     */
    const deleteSlide = (slide) => {
        const index = slides.value.findIndex(s => s === slide || s.id === slide.id || s._tempId === slide._tempId);
        if (index === -1) return;

        slides.value.splice(index, 1);
        isDirty.value = true;

        // Update selection if deleted slide was active
        if (activeSlideId.value === (slide.id || slide._tempId)) {
            // Try to select next, or prev, or null
            const next = slides.value[index] || slides.value[index - 1];
            activeSlideId.value = next ? (next.id || next._tempId) : null;
        }
    };

    /**
     * Set the current section
     */
    const setSection = (sectionId) => {
        currentSectionId.value = sectionId;
        // Select first slide of section if none selected or if selection was in another section
        const sectionSlides = slides.value.filter(s => s.section === sectionId);
        if (sectionSlides.length > 0) {
            const currentActive = activeSlide.value;
            // If current active slide is NOT in this section, switch
            if (!currentActive || currentActive.section !== sectionId) {
                activeSlideId.value = sectionSlides[0].id || sectionSlides[0]._tempId;
            }
        }
    };

    /**
     * Fetch templates for a specific subject
     */
    const fetchTemplates = async (subjectId) => {
        console.log(`fetchTemplates: Called with subjectId=${subjectId}`);
        if (!subjectId) {
            console.warn('fetchTemplates: No subject ID provided');
            return [];
        }

        try {
            console.log(`fetchTemplates: Fetching templates for subject ID ${subjectId}`);
            const response = await axios.get(`/api/course-management/lesson-plan-templates?subject_id=${subjectId}`);
        console.log(`fetchTemplates: response.data.data.data=${response.data.data.data}`);
 
            if (response.data.data.data)  {
            // if (response.data && Array.isArray(response.data.data)) {
                templates.value = response.data.data.data; // Update templates state
                console.log('fetchTemplates: Templates fetched successfully', templates.value);

                // Auto-select the first template if none is selected
                if (!selectedTemplate.value && templates.value.length > 0) {
                    selectedTemplate.value = templates.value[0];
                    saveSelectedTemplate(); // Persist the selection
                    console.log('fetchTemplates: Default template selected', selectedTemplate.value);
                }

                return templates.value;
            } else {
                console.warn('fetchTemplates: Unexpected templates response format', response);
                return [];
            }
        } catch (error) {
            console.error('fetchTemplates: Failed to fetch templates', error);
            return [];
        }
    };

    return {
        // State
        presentation,
        slides,
        sections,
        currentSectionId,
        activeSlideId,
        isSaving,
        isLoading,
        isDirty,
        templates,
        selectedTemplate,

        // Getters
        currentSectionSlides,
        activeSlide,
        slideCount,

        // Actions
        init,
        fetch,
        save,
        addSlide,
        deleteSlide,
        setSection,
        applyTemplateOnCreate,
        fetchTemplates
    };
});
