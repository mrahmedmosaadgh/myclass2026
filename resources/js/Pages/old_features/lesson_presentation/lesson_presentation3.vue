<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Top Navigation Bar -->
        <div
            class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-40"
        >
            <div class="px-4 sm:px-6 lg:px-8">
                <!-- Breadcrumbs & Title Section -->
                <div class="py-4 border-b border-gray-100">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <!-- Mobile Menu Toggle -->
                            <button
                                @click="sidebarOpen = !sidebarOpen"
                                class="lg:hidden p-2 rounded-md hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            >
                                <q-icon
                                    name="menu"
                                    size="24px"
                                    class="text-gray-600"
                                />
                            </button>

                            <!-- Breadcrumbs -->
                            <nav class="flex" aria-label="Breadcrumb">
                                <ol class="flex items-center space-x-2">
                                    <li>
                                        <a
                                            href="/dashboard"
                                            class="text-gray-400 hover:text-gray-500"
                                        >
                                            <q-icon name="home" size="16px" />
                                        </a>
                                    </li>
                                    <li class="flex items-center">
                                        <q-icon
                                            name="chevron_right"
                                            size="16px"
                                            class="text-gray-400 mx-2"
                                        />
                                        <a
                                            href="/lesson-presentation"
                                            class="text-sm text-gray-500 hover:text-gray-700"
                                            >Lessons</a
                                        >
                                    </li>
                                    <li class="flex items-center">
                                        <q-icon
                                            name="chevron_right"
                                            size="16px"
                                            class="text-gray-400 mx-2"
                                        />
                                        <span
                                            class="text-sm text-gray-900 font-medium"
                                        >
                                            {{
                                                activeId
                                                    ? "Edit Lesson"
                                                    : "New Lesson"
                                            }}
                                        </span>
                                    </li>
                                </ol>
                            </nav>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex items-center space-x-3">
                            <q-btn
                                flat
                                dense
                                icon="list"
                                label="All Lessons"
                                color="gray-600"
                                size="sm"
                                to="/lesson-presentation"
                                class="hidden sm:inline-flex"
                            >
                                <q-tooltip>View all lessons</q-tooltip>
                            </q-btn>

                            <q-btn
                                flat
                                dense
                                icon="add_circle"
                                label="New"
                                color="positive"
                                size="sm"
                                @click="createNewLesson"
                                class="hidden sm:inline-flex"
                            >
                                <q-tooltip>Create new lesson</q-tooltip>
                            </q-btn>

                            <q-btn
                                v-if="activeId"
                                flat
                                dense
                                icon="content_copy"
                                color="secondary"
                                size="sm"
                                @click="duplicateLesson"
                                class="hidden sm:inline-flex"
                            >
                                <q-tooltip>Duplicate lesson</q-tooltip>
                            </q-btn>

                            <!-- Mobile Actions Menu -->
                            <q-btn-dropdown
                                flat
                                dense
                                icon="more_vert"
                                color="gray-600"
                                class="sm:hidden"
                            >
                                <q-list>
                                    <q-item
                                        clickable
                                        v-close-popup
                                        to="/lesson-presentation"
                                    >
                                        <q-item-section avatar>
                                            <q-icon name="list" />
                                        </q-item-section>
                                        <q-item-section
                                            >All Lessons</q-item-section
                                        >
                                    </q-item>
                                    <q-item
                                        clickable
                                        v-close-popup
                                        @click="createNewLesson"
                                    >
                                        <q-item-section avatar>
                                            <q-icon name="add_circle" />
                                        </q-item-section>
                                        <q-item-section
                                            >New Lesson</q-item-section
                                        >
                                    </q-item>
                                    <q-item
                                        v-if="activeId"
                                        clickable
                                        v-close-popup
                                        @click="duplicateLesson"
                                    >
                                        <q-item-section avatar>
                                            <q-icon name="content_copy" />
                                        </q-item-section>
                                        <q-item-section
                                            >Duplicate</q-item-section
                                        >
                                    </q-item>
                                </q-list>
                            </q-btn-dropdown>
                        </div>
                    </div>
                </div>

                <!-- Lesson Info Section -->
                <div class="py-4 space-y-4">
                    <!-- Title & Description -->
                    <div class="space-y-3">
                        <input
                            v-model="presentation.name"
                            class="block w-full text-2xl font-bold text-gray-900 border-0 border-b-2 border-transparent hover:border-gray-200 focus:border-blue-500 focus:ring-0 bg-transparent placeholder-gray-400 transition-colors"
                            placeholder="Enter lesson title..."
                        />

                        <div
                            class="flex flex-col sm:flex-row sm:items-center gap-4"
                        >
                            <input
                                v-model="presentation.description"
                                class="flex-1 text-sm text-gray-600 border-0 border-b border-transparent hover:border-gray-200 focus:border-blue-500 focus:ring-0 bg-transparent placeholder-gray-400 transition-colors"
                                placeholder="Add lesson description..."
                            />

                            <div class="flex items-center space-x-3">
                                <select
                                    v-model="presentation.grade_id"
                                    class="text-sm border border-gray-300 rounded-lg px-3 py-2 bg-white text-gray-700 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                >
                                    <option :value="null" disabled>
                                        Select Grade
                                    </option>
                                    <option
                                        v-for="grade in teacherStore.grades"
                                        :key="grade.id"
                                        :value="grade.id"
                                    >
                                        {{ grade.name }}
                                    </option>
                                </select>

                                <!-- Save & Preview Actions -->
                                <div class="flex space-x-2">
                                    <q-btn
                                        outline
                                        color="primary"
                                        icon="visibility"
                                        :label="
                                            $q.screen.gt.sm ? 'Preview' : ''
                                        "
                                        @click="showPreview = true"
                                        :disable="!activeId"
                                        size="sm"
                                    >
                                        <q-tooltip v-if="!activeId"
                                            >Save the lesson first to
                                            preview</q-tooltip
                                        >
                                    </q-btn>

                                    <q-btn
                                        color="positive"
                                        icon="save"
                                        :label="$q.screen.gt.sm ? 'Save' : ''"
                                        @click="savePresentation"
                                        :loading="isSaving"
                                        size="sm"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Progress Indicators -->
                    <div class="flex flex-wrap gap-2">
                        <div
                            v-for="section in sections"
                            :key="section.id"
                            class="flex items-center space-x-2 px-3 py-1.5 rounded-full text-xs font-medium transition-all cursor-pointer hover:shadow-sm"
                            :class="[
                                currentSection === section.id
                                    ? `${section.bg} ${section.textColor} ring-2 ring-offset-1 ring-${section.borderColor}`
                                    : 'bg-gray-100 text-gray-600 hover:bg-gray-200',
                            ]"
                            @click="currentSection = section.id"
                        >
                            <span class="text-sm">{{ section.icon }}</span>
                            <span>{{ section.title }}</span>
                            <q-badge
                                :color="
                                    currentSection === section.id
                                        ? 'white'
                                        : 'gray-400'
                                "
                                :text-color="
                                    currentSection === section.id
                                        ? section.borderColor
                                        : 'white'
                                "
                                :label="getSectionSlideCount(section.id)"
                                size="xs"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="flex h-[calc(100vh-200px)] relative">
            <!-- Sidebar -->
            <div
                class="fixed inset-y-0 left-0 z-30 w-80 bg-white shadow-lg transform transition-transform duration-300 ease-in-out lg:relative lg:translate-x-0 lg:shadow-none lg:border-r border-gray-200"
                :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
                style="top: 200px"
            >
                <!-- Sidebar Header -->
                <div
                    class="sticky top-0 bg-white z-10 px-4 py-4 border-b border-gray-100"
                >
                    <div class="flex items-center justify-between">
                        <h3
                            class="text-lg font-semibold text-gray-900 flex items-center gap-2"
                        >
                            <q-icon name="layers" color="primary" size="20px" />
                            {{ currentSectionTitle }}
                        </h3>
                        <button
                            @click="sidebarOpen = false"
                            class="lg:hidden p-1.5 rounded-md hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                            <q-icon
                                name="close"
                                size="20px"
                                class="text-gray-500"
                            />
                        </button>
                    </div>

                    <!-- Add Slide Button -->
                    <q-btn
                        color="primary"
                        icon="add"
                        label="Add New Slide"
                        @click="addSlide"
                        class="w-full mt-3"
                        size="sm"
                        unelevated
                    />
                </div>

                <!-- Slides List -->
                <div
                    class="flex-1 overflow-y-auto px-4 py-2 space-y-2 custom-scrollbar"
                >
                    <div
                        v-for="(slide, index) in filteredSlides"
                        :key="slide.id || index"
                        @click="currentSlideIndex = index"
                        class="group relative p-3 rounded-lg cursor-pointer transition-all duration-200 hover:shadow-sm"
                        :class="
                            currentSlideIndex === index
                                ? 'bg-blue-50 border-2 border-blue-300 shadow-sm'
                                : 'bg-gray-50 border-2 border-transparent hover:border-gray-200 hover:bg-gray-100'
                        "
                    >
                        <!-- Slide Header -->
                        <div class="flex justify-between items-start mb-2">
                            <div class="flex items-center space-x-2">
                                <div
                                    class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold"
                                    :class="
                                        currentSlideIndex === index
                                            ? 'bg-blue-500 text-white'
                                            : 'bg-gray-300 text-gray-700'
                                    "
                                >
                                    {{ index + 1 }}
                                </div>
                                <span class="font-medium text-sm text-gray-900"
                                    >Slide {{ index + 1 }}</span
                                >
                            </div>

                            <div class="flex items-center space-x-1">
                                <q-badge
                                    :color="getSlideTypeColor(slide.slide_type)"
                                    :label="slide.slide_type"
                                    size="xs"
                                    class="capitalize"
                                />

                                <!-- Slide Actions -->
                                <q-btn-dropdown
                                    flat
                                    dense
                                    round
                                    icon="more_vert"
                                    size="xs"
                                    class="opacity-0 group-hover:opacity-100 transition-opacity"
                                >
                                    <q-list dense>
                                        <q-item
                                            clickable
                                            v-close-popup
                                            @click="duplicateSlide(index)"
                                        >
                                            <q-item-section avatar
                                                ><q-icon
                                                    name="content_copy"
                                                    size="16px"
                                            /></q-item-section>
                                            <q-item-section
                                                >Duplicate</q-item-section
                                            >
                                        </q-item>
                                        <q-item
                                            clickable
                                            v-close-popup
                                            @click="deleteSlide(index)"
                                        >
                                            <q-item-section avatar
                                                ><q-icon
                                                    name="delete"
                                                    size="16px"
                                            /></q-item-section>
                                            <q-item-section
                                                >Delete</q-item-section
                                            >
                                        </q-item>
                                    </q-list>
                                </q-btn-dropdown>
                            </div>
                        </div>

                        <!-- Slide Preview -->
                        <div class="text-xs text-gray-600 line-clamp-2 pl-8">
                            {{ getSlideSummary(slide) }}
                        </div>

                        <!-- Active Indicator -->
                        <div
                            v-if="currentSlideIndex === index"
                            class="absolute left-0 top-0 bottom-0 w-1 bg-blue-500 rounded-r-full"
                        ></div>
                    </div>

                    <!-- Empty State -->
                    <div
                        v-if="filteredSlides.length === 0"
                        class="text-center py-12"
                    >
                        <q-icon
                            name="layers_clear"
                            size="48px"
                            color="gray-400"
                            class="mb-3"
                        />
                        <p class="text-gray-500 text-sm mb-4">
                            No slides in this section yet
                        </p>
                        <q-btn
                            outline
                            color="primary"
                            icon="add"
                            label="Create First Slide"
                            @click="addSlide"
                            size="sm"
                        />
                    </div>
                </div>
            </div>

            <!-- Mobile Sidebar Overlay -->
            <div
                v-if="sidebarOpen"
                class="fixed inset-0 bg-black bg-opacity-25 z-20 lg:hidden"
                @click="sidebarOpen = false"
                style="top: 200px"
            ></div>

            <!-- Main Editor Area -->
            <div class="flex-1 flex flex-col overflow-hidden lg:ml-0">
                <div class="flex-1 overflow-y-auto bg-gray-50">
                    <div class="p-4 sm:p-6 lg:p-8">
                        <!-- Editor Card -->
                        <div
                            class="bg-white rounded-xl shadow-sm border border-gray-200 min-h-[600px]"
                        >
                            <!-- Editor Header -->
                            <div
                                class="px-6 py-4 border-b border-gray-100 bg-gray-50 rounded-t-xl"
                            >
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-4">
                                        <div
                                            class="flex items-center space-x-2"
                                        >
                                            <q-icon
                                                name="edit"
                                                color="primary"
                                                size="20px"
                                            />
                                            <h3
                                                class="text-lg font-medium text-gray-900"
                                            >
                                                {{
                                                    currentSlide
                                                        ? `Slide ${currentSlideIndex + 1} Editor`
                                                        : "Slide Editor"
                                                }}
                                            </h3>
                                        </div>

                                        <div
                                            v-if="currentSlide"
                                            class="flex items-center space-x-2"
                                        >
                                            <q-badge
                                                :color="
                                                    getSlideTypeColor(
                                                        currentSlide.slide_type,
                                                    )
                                                "
                                                :label="currentSlide.slide_type"
                                                class="capitalize"
                                            />
                                        </div>
                                    </div>

                                    <!-- Slide Type Selector -->
                                    <div
                                        v-if="currentSlide"
                                        class="flex items-center space-x-3"
                                    >
                                        <label
                                            class="text-sm font-medium text-gray-700"
                                            >Type:</label
                                        >
                                        <select
                                            v-model="currentSlide.slide_type"
                                            class="text-sm border border-gray-300 rounded-lg px-3 py-1.5 bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                        >
                                            <option value="text">
                                                📄 Text Slide
                                            </option>
                                            <option value="image">
                                                🖼️ Image Slide
                                            </option>
                                            <option value="video">
                                                🎥 Video Slide
                                            </option>
                                            <option value="audio">
                                                🎵 Audio Slide
                                            </option>
                                            <option value="pdf">
                                                📋 PDF Slide
                                            </option>
                                            <option value="question">
                                                ❓ Question Slide
                                            </option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Editor Content -->
                            <div class="p-6">
                                <div v-if="currentSlide" class="min-h-[500px]">
                                    <component
                                        :is="
                                            getSlideComponent(
                                                currentSlide.slide_type,
                                            )
                                        "
                                        v-model="currentSlide.slide_content"
                                        class="h-full"
                                    />
                                </div>

                                <!-- Empty State -->
                                <div
                                    v-else
                                    class="flex flex-col items-center justify-center h-[500px] text-gray-400"
                                >
                                    <div class="text-center max-w-md">
                                        <q-icon
                                            name="layers"
                                            size="64px"
                                            class="mb-4 text-gray-300"
                                        />
                                        <h3
                                            class="text-xl font-medium text-gray-600 mb-2"
                                        >
                                            No slide selected
                                        </h3>
                                        <p class="text-gray-500 mb-6">
                                            Select a slide from the sidebar to
                                            start editing, or create a new one.
                                        </p>
                                        <q-btn
                                            color="primary"
                                            icon="add"
                                            label="Create First Slide"
                                            @click="addSlide"
                                            unelevated
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Preview Dialog -->
        <q-dialog
            v-model="showPreview"
            maximized
            transition-show="slide-up"
            transition-hide="slide-down"
        >
            <q-card class="bg-white">
                <q-card-section
                    class="row items-center q-pb-none bg-gradient-to-r from-blue-600 to-blue-700 text-white"
                >
                    <div class="flex items-center space-x-3">
                        <q-icon name="visibility" size="28px" />
                        <div>
                            <div class="text-lg font-semibold">
                                Student View Preview
                            </div>
                            <div class="text-sm opacity-90">
                                {{ presentation.name || "Untitled Lesson" }}
                            </div>
                        </div>
                    </div>
                    <q-space />
                    <q-btn
                        icon="close"
                        flat
                        round
                        dense
                        v-close-popup
                        class="text-white hover:bg-white hover:bg-opacity-20"
                    />
                </q-card-section>

                <q-card-section
                    class="q-pa-none"
                    style="height: calc(100vh - 80px)"
                >
                    <iframe
                        v-if="activeId"
                        :src="`/lesson-presentation/student/${activeId}`"
                        class="w-full h-full border-0"
                        title="Student View Preview"
                    ></iframe>
                    <div v-else class="flex items-center justify-center h-full">
                        <div class="text-center">
                            <q-icon name="info" size="64px" color="gray-400" />
                            <p class="mt-4 text-gray-600">
                                Please save the lesson first to preview
                            </p>
                        </div>
                    </div>
                </q-card-section>
            </q-card>
        </q-dialog>

        <!-- Loading Overlay -->
        <q-dialog v-model="isSaving" persistent>
            <q-card class="flex items-center justify-center p-6 min-w-[200px]">
                <q-circular-progress
                    indeterminate
                    size="50px"
                    color="primary"
                    class="q-ma-md"
                />
                <div class="text-center mt-4">
                    <div class="font-medium">Saving lesson...</div>
                    <div class="text-sm text-gray-500 mt-1">Please wait</div>
                </div>
            </q-card>
        </q-dialog>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from "vue";
import { useForm, router } from "@inertiajs/vue3";
import { useQuasar } from "quasar";
import axios from "axios";
import TextSlide from "./components/slides/TextSlide.vue";
import MediaSlide from "./components/slides/MediaSlide.vue";
import QuestionSlide from "./components/slides/QuestionSlide.vue";
import { useTeacherStore } from "@/Stores/teacherStore";

// Props
const props = defineProps({
    presentationId: {
        type: [String, Number],
        default: null,
    },
    defaultContext: {
        type: Object,
        default: () => ({
            school_id: null,
            teacher_id: null,
            subject_id: null,
        }),
    },
});

// Composables
const teacherStore = useTeacherStore();
const $q = useQuasar();

// Reactive State
const sidebarOpen = ref(false);
const urlParams = new URLSearchParams(window.location.search);
const idFromUrl = urlParams.get("id");
const activeId = ref(props.presentationId || idFromUrl);

const presentation = ref({
    name: "",
    description: "",
    grade_id: null,
    quiz_id: null,
    slides: [],
});

const sections = ref([
    {
        id: "objectives",
        title: "Objectives",
        icon: "🎯",
        qIcon: "flag",
        bg: "bg-amber-100",
        bgActive: "bg-amber-200",
        borderColor: "amber-500",
        textColor: "text-amber-800",
    },
    {
        id: "warmup",
        title: "Warm-Up",
        icon: "🔥",
        qIcon: "whatshot",
        bg: "bg-orange-100",
        bgActive: "bg-orange-200",
        borderColor: "orange-500",
        textColor: "text-orange-800",
    },
    {
        id: "learn",
        title: "Learn",
        icon: "📚",
        qIcon: "school",
        bg: "bg-blue-100",
        bgActive: "bg-blue-200",
        borderColor: "blue-500",
        textColor: "text-blue-800",
    },
    {
        id: "practice",
        title: "Practice",
        icon: "✍️",
        qIcon: "edit",
        bg: "bg-purple-100",
        bgActive: "bg-purple-200",
        borderColor: "purple-500",
        textColor: "text-purple-800",
    },
    {
        id: "homework",
        title: "Homework",
        icon: "📖",
        qIcon: "assignment",
        bg: "bg-indigo-100",
        bgActive: "bg-indigo-200",
        borderColor: "indigo-500",
        textColor: "text-indigo-800",
    },
    {
        id: "quiz",
        title: "Quiz",
        icon: "📝",
        qIcon: "quiz",
        bg: "bg-green-100",
        bgActive: "bg-green-200",
        borderColor: "green-500",
        textColor: "text-green-800",
    },
]);

const currentSection = ref("learn");
const slides = ref([]);
const currentSlideIndex = ref(-1);
const isSaving = ref(false);
const showPreview = ref(false);

// Computed Properties
const currentSectionTitle = computed(() => {
    const section = sections.value.find((s) => s.id === currentSection.value);
    return section ? section.title : "Lesson Content";
});

const getSectionSlideCount = (sectionId) => {
    return slides.value.filter((slide) => slide.section === sectionId).length;
};

const filteredSlides = computed(() => {
    return slides.value.filter(
        (slide) => slide.section === currentSection.value,
    );
});

const currentSlide = computed(() => {
    const filtered = filteredSlides.value;
    return currentSlideIndex.value >= 0 &&
        currentSlideIndex.value < filtered.length
        ? filtered[currentSlideIndex.value]
        : null;
});

// Methods
const getSlideComponent = (slideType) => {
    switch (slideType) {
        case "text":
            return TextSlide;
        case "image":
        case "video":
        case "audio":
        case "pdf":
            return MediaSlide;
        case "question":
            return QuestionSlide;
        default:
            return TextSlide;
    }
};

const getSlideTypeColor = (slideType) => {
    const colors = {
        text: "blue",
        image: "green",
        video: "red",
        audio: "purple",
        pdf: "orange",
        question: "pink",
    };
    return colors[slideType] || "gray";
};

const getSlideSummary = (slide) => {
    if (!slide.slide_content) return "Empty slide";

    const content = slide.slide_content;
    if (typeof content === "string") {
        const div = document.createElement("div");
        div.innerHTML = content;
        const text = div.textContent || div.innerText || "";
        return text.substring(0, 100) + (text.length > 100 ? "..." : "");
    }

    if (typeof content === "object") {
        if (content.title) return content.title;
        if (content.question) return content.question;
        if (content.url) return content.url;
    }

    return `${slide.slide_type} content`;
};

const addSlide = () => {
    const newSlide = {
        slide_type: "text",
        slide_content: "",
        section: currentSection.value,
    };
    slides.value.push(newSlide);
    currentSlideIndex.value = filteredSlides.value.length - 1;
};

const duplicateSlide = (index) => {
    const slideToClone = filteredSlides.value[index];
    const newSlide = {
        ...slideToClone,
        slide_content: JSON.parse(JSON.stringify(slideToClone.slide_content)),
    };
    slides.value.push(newSlide);
    currentSlideIndex.value = filteredSlides.value.length - 1;

    $q.notify({
        type: "positive",
        message: "Slide duplicated successfully",
        position: "top-right",
    });
};

const deleteSlide = (index) => {
    $q.dialog({
        title: "Confirm Delete",
        message: `Are you sure you want to delete Slide ${index + 1}? This action cannot be undone.`,
        cancel: true,
        persistent: true,
    }).onOk(() => {
        const slideToDelete = filteredSlides.value[index];
        const globalIndex = slides.value.indexOf(slideToDelete);
        slides.value.splice(globalIndex, 1);

        if (currentSlideIndex.value >= filteredSlides.value.length) {
            currentSlideIndex.value = Math.max(
                0,
                filteredSlides.value.length - 1,
            );
        }
        if (filteredSlides.value.length === 0) {
            currentSlideIndex.value = -1;
        }

        $q.notify({
            type: "positive",
            message: "Slide deleted successfully",
            position: "top-right",
        });
    });
};

const validatePresentation = () => {
    if (!presentation.value.name.trim()) {
        $q.notify({
            type: "negative",
            message: "Please enter a lesson title",
            icon: "error",
            position: "top-right",
        });
        return false;
    }

    if (!presentation.value.grade_id) {
        $q.notify({
            type: "negative",
            message: "Please select a grade for this lesson",
            icon: "error",
            position: "top-right",
        });
        return false;
    }

    return true;
};

const savePresentation = async () => {
    if (!validatePresentation()) return;

    isSaving.value = true;

    try {
        const payload = {
            ...presentation.value,
            slides: slides.value,
            school_id: props.defaultContext.school_id,
            teacher_id: props.defaultContext.teacher_id,
            subject_id: props.defaultContext.subject_id,
            grade_id: presentation.value.grade_id,
        };

        let response;
        if (activeId.value) {
            response = await axios.put(
                `/lesson-presentation/${activeId.value}`,
                payload,
            );
        } else {
            response = await axios.post("/lesson-presentation", payload);
            activeId.value = response.data.id;
        }

        $q.notify({
            type: "positive",
            message: "Lesson saved successfully!",
            icon: "check_circle",
            position: "top-right",
            timeout: 3000,
        });

        if (!props.presentationId) {
            const newUrl = `/lesson-presentation/edit?id=${activeId.value}`;
            window.history.pushState(null, "", newUrl);
        }
    } catch (error) {
        console.error("Save error:", error);
        $q.notify({
            type: "negative",
            message: "Failed to save lesson. Please try again.",
            icon: "error",
            position: "top-right",
            timeout: 5000,
        });
    } finally {
        isSaving.value = false;
    }
};

const createNewLesson = () => {
    router.get("/lesson-presentation/edit");
};

const duplicateLesson = async () => {
    if (!activeId.value) {
        $q.notify({
            type: "warning",
            message: "Please save the lesson first",
            icon: "warning",
            position: "top-right",
            timeout: 3000,
        });
        return;
    }

    try {
        const duplicateData = {
            ...presentation.value,
            name: `${presentation.value.name} (Copy)`,
            school_id: props.defaultContext.school_id,
            teacher_id: props.defaultContext.teacher_id,
            subject_id: props.defaultContext.subject_id,
        };

        const response = await axios.post(
            "/lesson-presentation",
            duplicateData,
        );
        const newPresentation = response.data;

        for (const slide of slides.value) {
            const slideData = {
                slide_type: slide.slide_type,
                slide_content: slide.slide_content,
                section: slide.section,
            };
            await axios.post(
                `/lesson-presentation/${newPresentation.id}/slides`,
                slideData,
            );
        }

        $q.notify({
            type: "positive",
            message: "Lesson duplicated successfully!",
            icon: "content_copy",
            position: "top-right",
            timeout: 3000,
        });

        router.get(`/lesson-presentation/edit?id=${newPresentation.id}`);
    } catch (error) {
        console.error("Duplicate error:", error);
        $q.notify({
            type: "negative",
            message: "Failed to duplicate lesson. Please try again.",
            icon: "error",
            position: "top-right",
            timeout: 5000,
        });
    }
};

const fetchPresentation = async () => {
    if (!activeId.value) return;

    try {
        const response = await axios.get(
            `/lesson-presentation/${activeId.value}`,
        );
        const data = response.data;

        presentation.value.name = data.name || "";
        presentation.value.description = data.description || "";
        presentation.value.grade_id = data.grade_id || null;
        presentation.value.quiz_id = data.quiz_id || null;
        slides.value = data.slides || [];

        // Auto-select first slide if available
        if (filteredSlides.value.length > 0) {
            currentSlideIndex.value = 0;
        }
    } catch (error) {
        console.error("Fetch error:", error);
        $q.notify({
            type: "negative",
            message: "Failed to load lesson data.",
            icon: "error",
            position: "top-right",
        });
    }
};

// Watch for changes in current section
watch(currentSection, () => {
    currentSlideIndex.value = filteredSlides.value.length > 0 ? 0 : -1;
    sidebarOpen.value = false; // Close sidebar on mobile when section changes
});

// Watch for screen size changes
watch(
    () => $q.screen.gt.lg,
    (isLarge) => {
        if (isLarge) {
            sidebarOpen.value = false;
        }
    },
);

// Lifecycle
onMounted(async () => {
    await teacherStore.fetchGrades();
    if (activeId.value) {
        await fetchPresentation();
    } else {
        // Set default section and prepare for new lesson
        currentSection.value = "learn";
        currentSlideIndex.value = -1;
    }
});
</script>

<style scoped>
/* Custom scrollbar for better UX */
.custom-scrollbar {
    scrollbar-width: thin;
    scrollbar-color: #cbd5e1 #f1f5f9;
}

.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 3px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 3px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

/* Line clamp utility for slide previews */
.line-clamp-2 {
    overflow: hidden;
    display: -webkit-box;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 2;
}

/* Smooth transitions */
.transition-all {
    transition-property: all;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 200ms;
}

/* Focus styles for accessibility */
.focus\:ring-2:focus {
    box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.5);
}

/* Hover effects */
.hover\:shadow-sm:hover {
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
}

/* Mobile responsive adjustments */
@media (max-width: 1024px) {
    .sidebar-overlay {
        backdrop-filter: blur(2px);
    }
}

/* Animation for section indicators */
.section-indicator {
    transition: all 0.2s ease-in-out;
    transform-origin: center;
}

.section-indicator:hover {
    transform: scale(1.02);
}

/* Gradient backgrounds for better visual hierarchy */
.bg-gradient-primary {
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
}

.bg-gradient-success {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
}

/* Enhanced button styles */
.btn-enhanced {
    transition: all 0.2s ease-in-out;
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
}

.btn-enhanced:hover {
    box-shadow:
        0 4px 6px -1px rgba(0, 0, 0, 0.1),
        0 2px 4px -1px rgba(0, 0, 0, 0.06);
    transform: translateY(-1px);
}

/* Loading animation */
@keyframes pulse-soft {
    0%,
    100% {
        opacity: 1;
    }
    50% {
        opacity: 0.7;
    }
}

.animate-pulse-soft {
    animation: pulse-soft 2s ease-in-out infinite;
}
</style>
