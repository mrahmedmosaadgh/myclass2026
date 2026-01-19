<script setup>
import { ref, computed, watch } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Notify, copyToClipboard } from 'quasar';
import axios from 'axios';

const props = defineProps({
    schools: Array,
});

const selectedSchoolId = ref(props.schools.length > 0 ? props.schools[0].id : null);
const selectedSchool = computed(() => 
    props.schools.find(s => s.id === selectedSchoolId.value)
);

const form = useForm({
    school_name_en: '',
    school_name_ar: '',
    school_slug: '',
    colors: {
        primary: '#6366f1',
        secondary: '#8b5cf6',
        accent: '#ec4899'
    },
    login_page_settings: {
        show_particles: true,
        animation_style: 'fade',
        card_style: 'glassmorphism'
    }
});

const logoFile = ref(null);
const backgroundFile = ref(null);
const logoPreview = ref(null);
const backgroundPreview = ref(null);
const loginLink = ref('');

// Color presets
const colorPresets = [
    '#6366f1', '#8b5cf6', '#ec4899', '#f43f5e', '#ef4444',
    '#f97316', '#f59e0b', '#eab308', '#84cc16', '#22c55e',
    '#10b981', '#14b8a6', '#06b6d4', '#0ea5e9', '#3b82f6',
];

// Watch for school selection changes
watch(selectedSchoolId, (newId) => {
    const school = props.schools.find(s => s.id === newId);
    if (school) {
        loadSchoolData(school);
    }
});

// Load school data into form
function loadSchoolData(school) {
    const branding = school.branding || {};
    
    form.school_name_en = branding.school_name_en || school.name || '';
    form.school_name_ar = branding.school_name_ar || school.name_ar || '';
    form.school_slug = branding.school_slug || school.school_slug || '';
    form.colors = branding.colors || form.colors;
    form.login_page_settings = branding.login_page_settings || form.login_page_settings;
    
    logoPreview.value = school.logo_url;
    backgroundPreview.value = school.background_url;
    
    generateLoginLink();
}

// Initialize with first school
if (selectedSchool.value) {
    loadSchoolData(selectedSchool.value);
}

// Handle logo file selection
function onLogoSelect(file) {
    logoFile.value = file;
    if (file) {
        const reader = new FileReader();
        reader.onload = (e) => {
            logoPreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
        uploadLogo();
    }
}

// Handle background file selection
function onBackgroundSelect(file) {
    backgroundFile.value = file;
    if (file) {
        const reader = new FileReader();
        reader.onload = (e) => {
            backgroundPreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
        uploadBackground();
    }
}

// Upload logo
function uploadLogo() {
    if (!logoFile.value) return;
    
    const formData = new FormData();
    formData.append('logo', logoFile.value);
    
    router.post(route('admin.school-branding.upload-logo', selectedSchoolId.value), formData, {
        preserveScroll: true,
        onSuccess: (page) => {
            Notify.create({
                type: 'positive',
                message: 'Logo uploaded successfully',
                position: 'top'
            });
            
            // Reload school data to get updated logo
            const updatedSchool = page.props.schools.find(s => s.id === selectedSchoolId.value);
            if (updatedSchool) {
                logoPreview.value = updatedSchool.logo_url;
            }
        },
        onError: (errors) => {
            Notify.create({
                type: 'negative',
                message: errors.logo || 'Failed to upload logo',
                position: 'top'
            });
        }
    });
}

// Upload background
function uploadBackground() {
    if (!backgroundFile.value) return;
    
    const formData = new FormData();
    formData.append('background', backgroundFile.value);
    
    router.post(route('admin.school-branding.upload-background', selectedSchoolId.value), formData, {
        preserveScroll: true,
        onSuccess: (page) => {
            Notify.create({
                type: 'positive',
                message: 'Background uploaded successfully',
                position: 'top'
            });
            
            // Reload school data to get updated background
            const updatedSchool = page.props.schools.find(s => s.id === selectedSchoolId.value);
            if (updatedSchool) {
                backgroundPreview.value = updatedSchool.background_url;
            }
        },
        onError: (errors) => {
            Notify.create({
                type: 'negative',
                message: errors.background || 'Failed to upload background',
                position: 'top'
            });
        }
    });
}

// Save branding settings
function saveBranding() {
    form.put(route('admin.school-branding.update', selectedSchoolId.value), {
        preserveScroll: true,
        onSuccess: (page) => {
            Notify.create({
                type: 'positive',
                message: 'Branding settings saved successfully',
                position: 'top'
            });
            
            // Reload school data to get updated branding
            const updatedSchool = page.props.schools.find(s => s.id === selectedSchoolId.value);
            if (updatedSchool) {
                loadSchoolData(updatedSchool);
            }
        },
        onError: () => {
            Notify.create({
                type: 'negative',
                message: 'Failed to save branding settings',
                position: 'top'
            });
        }
    });
}

// Generate login link
function generateLoginLink() {
    if (!selectedSchoolId.value) return;
    
    axios.get(route('admin.school-branding.login-link', selectedSchoolId.value))
        .then(response => {
            loginLink.value = response.data.url;
        });
}

// Copy login link to clipboard
function copyLoginLink() {
    copyToClipboard(loginLink.value)
        .then(() => {
            Notify.create({
                type: 'positive',
                message: 'Login link copied to clipboard!',
                position: 'top',
                icon: 'content_copy'
            });
        })
        .catch(() => {
            Notify.create({
                type: 'negative',
                message: 'Failed to copy link',
                position: 'top'
            });
        });
}
</script>

<template>
    <AppLayout title="School Branding Settings">
        <div class="q-pa-md">
            <div class="row q-col-gutter-md">
                <!-- Left Column: Settings -->
                <div class="col-12 col-md-6">
                    <q-card class="q-mb-md">
                        <q-card-section>
                            <div class="text-h6">School Branding Settings</div>
                            <div class="text-subtitle2 text-grey-7">Customize your school's login page</div>
                        </q-card-section>

                        <q-separator />

                        <q-card-section>
                            <!-- School Selector -->
                            <q-select
                                v-model="selectedSchoolId"
                                :options="schools"
                                option-value="id"
                                option-label="name"
                                emit-value
                                map-options
                                label="Select School"
                                outlined
                                class="q-mb-md"
                            />

                            <!-- School Names -->
                            <q-input
                                v-model="form.school_name_en"
                                label="School Name (English)"
                                outlined
                                class="q-mb-md"
                            />

                            <q-input
                                v-model="form.school_name_ar"
                                label="School Name (Arabic)"
                                outlined
                                class="q-mb-md"
                            />

                            <!-- School Slug -->
                            <q-input
                                v-model="form.school_slug"
                                label="School URL Slug"
                                outlined
                                hint="URL-safe identifier (e.g., al-noor-school)"
                                class="q-mb-md"
                            />

                            <!-- Logo Upload -->
                            <div class="q-mb-md">
                                <div class="text-subtitle2 q-mb-sm">School Logo</div>
                                <q-file
                                    v-model="logoFile"
                                    label="Upload Logo"
                                    outlined
                                    accept="image/jpeg,image/png,image/svg+xml"
                                    max-file-size="2097152"
                                    @update:model-value="onLogoSelect"
                                >
                                    <template v-slot:prepend>
                                        <q-icon name="image" />
                                    </template>
                                </q-file>
                                <div v-if="logoPreview" class="q-mt-sm">
                                    <img :src="logoPreview" class="logo-preview" alt="Logo Preview" />
                                </div>
                            </div>

                            <!-- Background Upload -->
                            <div class="q-mb-md">
                                <div class="text-subtitle2 q-mb-sm">Background Image</div>
                                <q-file
                                    v-model="backgroundFile"
                                    label="Upload Background"
                                    outlined
                                    accept="image/jpeg,image/png,image/webp"
                                    max-file-size="5242880"
                                    @update:model-value="onBackgroundSelect"
                                >
                                    <template v-slot:prepend>
                                        <q-icon name="wallpaper" />
                                    </template>
                                </q-file>
                                <div v-if="backgroundPreview" class="q-mt-sm">
                                    <img :src="backgroundPreview" class="background-preview" alt="Background Preview" />
                                </div>
                            </div>

                            <!-- Color Pickers -->
                            <div class="q-mb-md">
                                <div class="text-subtitle2 q-mb-sm">Primary Color</div>
                                <q-input
                                    v-model="form.colors.primary"
                                    outlined
                                    :rules="['anyColor']"
                                >
                                    <template v-slot:append>
                                        <q-icon name="colorize" class="cursor-pointer">
                                            <q-popup-proxy cover transition-show="scale" transition-hide="scale">
                                                <q-color v-model="form.colors.primary" />
                                            </q-popup-proxy>
                                        </q-icon>
                                    </template>
                                </q-input>
                                <div class="row q-gutter-xs q-mt-xs">
                                    <div
                                        v-for="color in colorPresets"
                                        :key="color"
                                        class="color-preset"
                                        :style="{ backgroundColor: color }"
                                        @click="form.colors.primary = color"
                                    />
                                </div>
                            </div>

                            <div class="q-mb-md">
                                <div class="text-subtitle2 q-mb-sm">Secondary Color</div>
                                <q-input
                                    v-model="form.colors.secondary"
                                    outlined
                                    :rules="['anyColor']"
                                >
                                    <template v-slot:append>
                                        <q-icon name="colorize" class="cursor-pointer">
                                            <q-popup-proxy cover transition-show="scale" transition-hide="scale">
                                                <q-color v-model="form.colors.secondary" />
                                            </q-popup-proxy>
                                        </q-icon>
                                    </template>
                                </q-input>
                                <div class="row q-gutter-xs q-mt-xs">
                                    <div
                                        v-for="color in colorPresets"
                                        :key="color"
                                        class="color-preset"
                                        :style="{ backgroundColor: color }"
                                        @click="form.colors.secondary = color"
                                    />
                                </div>
                            </div>

                            <div class="q-mb-md">
                                <div class="text-subtitle2 q-mb-sm">Accent Color</div>
                                <q-input
                                    v-model="form.colors.accent"
                                    outlined
                                    :rules="['anyColor']"
                                >
                                    <template v-slot:append>
                                        <q-icon name="colorize" class="cursor-pointer">
                                            <q-popup-proxy cover transition-show="scale" transition-hide="scale">
                                                <q-color v-model="form.colors.accent" />
                                            </q-popup-proxy>
                                        </q-icon>
                                    </template>
                                </q-input>
                                <div class="row q-gutter-xs q-mt-xs">
                                    <div
                                        v-for="color in colorPresets"
                                        :key="color"
                                        class="color-preset"
                                        :style="{ backgroundColor: color }"
                                        @click="form.colors.accent = color"
                                    />
                                </div>
                            </div>

                            <!-- Login Page Settings -->
                            <div class="q-mb-md">
                                <div class="text-subtitle2 q-mb-sm">Login Page Style</div>
                                <q-select
                                    v-model="form.login_page_settings.card_style"
                                    :options="['glassmorphism', 'solid', 'gradient']"
                                    outlined
                                    label="Card Style"
                                />
                            </div>

                            <q-checkbox
                                v-model="form.login_page_settings.show_particles"
                                label="Show Particle Effects"
                            />
                        </q-card-section>

                        <q-separator />

                        <q-card-actions align="right">
                            <q-btn
                                label="Save Settings"
                                color="primary"
                                @click="saveBranding"
                                :loading="form.processing"
                            />
                        </q-card-actions>
                    </q-card>

                    <!-- Login Link -->
                    <q-card>
                        <q-card-section>
                            <div class="text-h6 q-mb-md">School Login Link</div>
                            <q-input
                                v-model="loginLink"
                                outlined
                                readonly
                                label="Login URL"
                            >
                                <template v-slot:append>
                                    <q-btn
                                        flat
                                        round
                                        dense
                                        icon="content_copy"
                                        @click="copyLoginLink"
                                    >
                                        <q-tooltip>Copy to clipboard</q-tooltip>
                                    </q-btn>
                                </template>
                            </q-input>
                        </q-card-section>
                    </q-card>
                </div>

                <!-- Right Column: Live Preview -->
                <div class="col-12 col-md-6">
                    <q-card class="preview-card">
                        <q-card-section>
                            <div class="text-h6 q-mb-md">Live Preview</div>
                            <div class="preview-container" :style="{
                                backgroundImage: backgroundPreview ? `url(${backgroundPreview})` : 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
                                backgroundSize: 'cover',
                                backgroundPosition: 'center'
                            }">
                                <div class="preview-overlay" :style="{
                                    background: `linear-gradient(135deg, ${form.colors.primary}CC 0%, ${form.colors.secondary}CC 100%)`
                                }"></div>
                                <div class="preview-content">
                                    <div class="preview-login-card" :class="`card-style-${form.login_page_settings.card_style}`">
                                        <div v-if="logoPreview" class="preview-logo">
                                            <img :src="logoPreview" alt="School Logo" />
                                        </div>
                                        <div class="preview-school-name">
                                            {{ form.school_name_en || 'School Name' }}
                                        </div>
                                        <div class="preview-form">
                                            <div class="preview-input"></div>
                                            <div class="preview-input"></div>
                                            <div class="preview-button" :style="{ backgroundColor: form.colors.primary }">
                                                Login
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </q-card-section>
                    </q-card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.logo-preview {
    max-width: 200px;
    max-height: 100px;
    object-fit: contain;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 8px;
}

.background-preview {
    width: 100%;
    max-height: 150px;
    object-fit: cover;
    border-radius: 8px;
}

.color-preset {
    width: 32px;
    height: 32px;
    border-radius: 4px;
    cursor: pointer;
    border: 2px solid transparent;
    transition: all 0.2s;
}

.color-preset:hover {
    border-color: #000;
    transform: scale(1.1);
}

.preview-card {
    position: sticky;
    top: 20px;
}

.preview-container {
    position: relative;
    width: 100%;
    height: 500px;
    border-radius: 12px;
    overflow: hidden;
}

.preview-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
}

.preview-content {
    position: relative;
    z-index: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100%;
    padding: 20px;
}

.preview-login-card {
    background: rgba(255, 255, 255, 0.95);
    border-radius: 16px;
    padding: 32px;
    width: 100%;
    max-width: 350px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
}

.card-style-glassmorphism {
    background: rgba(255, 255, 255, 0.15);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.card-style-solid {
    background: rgba(255, 255, 255, 0.95);
}

.card-style-gradient {
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.9) 0%, rgba(255, 255, 255, 0.7) 100%);
}

.preview-logo {
    text-align: center;
    margin-bottom: 20px;
}

.preview-logo img {
    max-width: 120px;
    max-height: 60px;
    object-fit: contain;
}

.preview-school-name {
    text-align: center;
    font-size: 20px;
    font-weight: 600;
    margin-bottom: 24px;
    color: #1a1a1a;
}

.card-style-glassmorphism .preview-school-name {
    color: #fff;
}

.preview-form {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.preview-input {
    height: 40px;
    background: rgba(0, 0, 0, 0.05);
    border-radius: 8px;
}

.card-style-glassmorphism .preview-input {
    background: rgba(255, 255, 255, 0.2);
}

.preview-button {
    height: 44px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 600;
    margin-top: 8px;
}
</style>
