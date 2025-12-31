<script setup>
import { ref, onBeforeUnmount } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import ActionMessage from '@/Components/ActionMessage.vue';
import Checkbox from '@/Components/Checkbox.vue';

const props = defineProps({
    school: Object,
});

const activeTab = ref('general');
const logoFilePreviewUrl = ref(null);

const defaultData = {
    school_code: '',
    name_official: '',
    name_short: '',
    school_type: 'private',
    education_levels: [],
    gender_type: 'mixed',
    ownership_type: 'private',
    authority: '',
    year_established: null,
    status: 'active',
    contact: {
        phone_primary: '',
        phone_secondary: '',
        email_official: '',
        website: '',
    },
    address: {
        country: '',
        region: '',
        city: '',
        district: '',
        street_address: '',
        postal_code: '',
        latitude: null,
        longitude: null,
    },
    logo: {
        current_logo_url: '',
        logo_version: 1,
        last_changed_at: null,
    },
    key_personnel: {
        principal: { name: '', phone: '', email: '' },
        vice_principal: { name: '', phone: '', email: '' },
        academic_coordinator: { name: '', phone: '', email: '' },
        admin_contact: { name: '', phone: '', email: '' },
        emergency_contact: '',
    }
};

const schoolData = props.school?.data ?? {};
const initialData = {
    ...defaultData,
    ...schoolData,
    contact: { ...defaultData.contact, ...(schoolData.contact ?? {}) },
    address: { ...defaultData.address, ...(schoolData.address ?? {}) },
    logo: { ...defaultData.logo, ...(schoolData.logo ?? {}) },
    key_personnel: {
        ...defaultData.key_personnel,
        ...(schoolData.key_personnel ?? {}),
        principal: { ...defaultData.key_personnel.principal, ...(schoolData.key_personnel?.principal ?? {}) },
        vice_principal: { ...defaultData.key_personnel.vice_principal, ...(schoolData.key_personnel?.vice_principal ?? {}) },
        academic_coordinator: { ...defaultData.key_personnel.academic_coordinator, ...(schoolData.key_personnel?.academic_coordinator ?? {}) },
        admin_contact: { ...defaultData.key_personnel.admin_contact, ...(schoolData.key_personnel?.admin_contact ?? {}) },
        emergency_contact: schoolData.key_personnel?.emergency_contact ?? defaultData.key_personnel.emergency_contact,
    }
};

const form = useForm({
    name: props.school.name,
    schoolData: initialData,
    logo_file: null,
});

const handleLogoFileChange = (event) => {
    const file = event.target?.files?.[0] ?? null;
    form.logo_file = file;
    if (logoFilePreviewUrl.value) {
        URL.revokeObjectURL(logoFilePreviewUrl.value);
    }
    logoFilePreviewUrl.value = file ? URL.createObjectURL(file) : null;
};

onBeforeUnmount(() => {
    if (logoFilePreviewUrl.value) {
        URL.revokeObjectURL(logoFilePreviewUrl.value);
    }
});

const updateSchool = () => {
    form.transform((data) => ({
        name: data.name,
        data: data.schoolData,
        logo_file: data.logo_file,
        _method: 'PUT',
    })).post(route('my_school.update', props.school.id), {
        preserveScroll: true,
        onSuccess: () => {
            // Success notification handled by ActionMessage
        },
    });
};

const tabs = [
    { id: 'general', name: 'General Information' },
    { id: 'contact', name: 'Contact Info' },
    { id: 'address', name: 'Address' },
    { id: 'personnel', name: 'Key Personnel' },
    { id: 'logo', name: 'Logo & Branding' },
];
</script>

<template>
    <AppLayout title="My School">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                My School Management
            </h2>
        </template>

        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
            <!-- Tab Navigation -->
            <div class="mb-6 border-b border-gray-200">
                <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                    <button
                        v-for="tab in tabs"
                        :key="tab.id"
                        @click="activeTab = tab.id"
                        :class="[
                            activeTab === tab.id
                                ? 'border-indigo-500 text-indigo-600'
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                            'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
                        ]"
                    >
                        {{ tab.name }}
                    </button>
                </nav>
            </div>

            <form @submit.prevent="updateSchool"  v-if="form.schoolData">
                <!-- General Information -->
                <div v-show="activeTab === 'general'">
                    <div class="md:grid md:grid-cols-3 md:gap-6">
                        <div class="md:col-span-1">
                            <h3 class="text-lg font-medium text-gray-900">General Profile</h3>
                            <p class="mt-1 text-sm text-gray-600">Core identity and classification.</p>
                        </div>
                        <div class="mt-5 md:mt-0 md:col-span-2 space-y-6 bg-white p-6 shadow sm:rounded-md">
                            <div class="grid grid-cols-6 gap-6">
                                <div class="col-span-6 sm:col-span-3">
                                    <InputLabel for="name" value="Display Name" />
                                    <TextInput id="name" v-model="form.name" type="text" class="mt-1 block w-full" />
                                    <InputError :message="form.errors.name" class="mt-2" />
                                </div>
                                <div class="col-span-6 sm:col-span-3">
                                    <InputLabel for="school_code" value="School Code" />
                                    <TextInput id="school_code" v-model="form.schoolData.school_code" type="text" class="mt-1 block w-full" />
                                </div>
                                <div class="col-span-6 sm:col-span-3">
                                    <InputLabel for="name_official" value="Official Name" />
                                    <TextInput id="name_official" v-model="form.schoolData.name_official" type="text" class="mt-1 block w-full" />
                                </div>
                                <div class="col-span-6 sm:col-span-3">
                                    <InputLabel for="name_short" value="Short Name" />
                                    <TextInput id="name_short" v-model="form.schoolData.name_short" type="text" class="mt-1 block w-full" />
                                </div>
                                <div class="col-span-6 sm:col-span-2">
                                    <InputLabel value="School Type" />
                                    <select v-model="form.schoolData.school_type" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                        <option value="public">Public</option>
                                        <option value="private">Private</option>
                                        <option value="international">International</option>
                                    </select>
                                </div>
                                <div class="col-span-6 sm:col-span-2">
                                    <InputLabel value="Gender Type" />
                                    <select v-model="form.schoolData.gender_type" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                        <option value="boys">Boys</option>
                                        <option value="girls">Girls</option>
                                        <option value="mixed">Mixed</option>
                                    </select>
                                </div>
                                <div class="col-span-6 sm:col-span-2">
                                    <InputLabel for="year_established" value="Year Established" />
                                    <TextInput id="year_established" v-model="form.schoolData.year_established" type="number" class="mt-1 block w-full" />
                                </div>
                            </div>
                            
                            <div>
                                <InputLabel value="Education Levels" />
                                <div class="mt-2 grid grid-cols-2 gap-2">
                                    <label class="flex items-center">
                                        <Checkbox v-model:checked="form.schoolData.education_levels" value="kg" />
                                        <span class="ml-2 text-sm text-gray-600">KG</span>
                                    </label>
                                    <label class="flex items-center">
                                        <Checkbox v-model:checked="form.schoolData.education_levels" value="primary" />
                                        <span class="ml-2 text-sm text-gray-600">Primary</span>
                                    </label>
                                    <label class="flex items-center">
                                        <Checkbox v-model:checked="form.schoolData.education_levels" value="middle" />
                                        <span class="ml-2 text-sm text-gray-600">Middle</span>
                                    </label>
                                    <label class="flex items-center">
                                        <Checkbox v-model:checked="form.schoolData.education_levels" value="secondary" />
                                        <span class="ml-2 text-sm text-gray-600">Secondary</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contact Information -->
                <div v-show="activeTab === 'contact'">
                    <div class="md:grid md:grid-cols-3 md:gap-6" v-if="form.schoolData">
                        <div class="md:col-span-1">
                            <h3 class="text-lg font-medium text-gray-900">Contact Details</h3>
                            <p class="mt-1 text-sm text-gray-600">Official communication channels.</p>
                        </div>
                        <div class="mt-5 md:mt-0 md:col-span-2 space-y-6 bg-white p-6 shadow sm:rounded-md">
                            <div class="grid grid-cols-6 gap-6">
                                <div class="col-span-6 sm:col-span-3">
                                    <InputLabel for="phone_primary" value="Primary Phone" />
                                    <TextInput id="phone_primary" v-model="form.schoolData.contact.phone_primary" type="text" class="mt-1 block w-full" />
                                </div>
                                <div class="col-span-6 sm:col-span-3">
                                    <InputLabel for="phone_secondary" value="Secondary Phone" />
                                    <TextInput id="phone_secondary" v-model="form.schoolData.contact.phone_secondary" type="text" class="mt-1 block w-full" />
                                </div>
                                <div class="col-span-6 sm:col-span-3">
                                    <InputLabel for="email_official" value="Official Email" />
                                    <TextInput id="email_official" v-model="form.schoolData.contact.email_official" type="email" class="mt-1 block w-full" />
                                </div>
                                <div class="col-span-6 sm:col-span-3">
                                    <InputLabel for="website" value="Website" />
                                    <TextInput id="website" v-model="form.schoolData.contact.website" type="text" class="mt-1 block w-full" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Address Information -->
                <div v-show="activeTab === 'address'">
                    <div class="md:grid md:grid-cols-3 md:gap-6">
                        <div class="md:col-span-1">
                            <h3 class="text-lg font-medium text-gray-900">Physical Address</h3>
                            <p class="mt-1 text-sm text-gray-600">School location and coordinates.</p>
                        </div>
                        <div class="mt-5 md:mt-0 md:col-span-2 space-y-6 bg-white p-6 shadow sm:rounded-md">
                            <div class="grid grid-cols-6 gap-6">
                                <div class="col-span-6 sm:col-span-3">
                                    <InputLabel for="country" value="Country" />
                                    <TextInput id="country" v-model="form.schoolData.address.country" type="text" class="mt-1 block w-full" />
                                </div>
                                <div class="col-span-6 sm:col-span-3">
                                    <InputLabel for="region" value="Region / State" />
                                    <TextInput id="region" v-model="form.schoolData.address.region" type="text" class="mt-1 block w-full" />
                                </div>
                                <div class="col-span-6 sm:col-span-3">
                                    <InputLabel for="city" value="City" />
                                    <TextInput id="city" v-model="form.schoolData.address.city" type="text" class="mt-1 block w-full" />
                                </div>
                                <div class="col-span-6 sm:col-span-3">
                                    <InputLabel for="district" value="District" />
                                    <TextInput id="district" v-model="form.schoolData.address.district" type="text" class="mt-1 block w-full" />
                                </div>
                                <div class="col-span-6">
                                    <InputLabel for="street_address" value="Street Address" />
                                    <TextInput id="street_address" v-model="form.schoolData.address.street_address" type="text" class="mt-1 block w-full" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Key Personnel Information -->
                <div v-show="activeTab === 'personnel'">
                    <div class="md:grid md:grid-cols-3 md:gap-6">
                        <div class="md:col-span-1">
                            <h3 class="text-lg font-medium text-gray-900">Key Personnel</h3>
                            <p class="mt-1 text-sm text-gray-600">School leadership and coordinators.</p>
                        </div>
                        <div class="mt-5 md:mt-0 md:col-span-2 space-y-8 bg-white p-6 shadow sm:rounded-md">
                            <!-- Principal -->
                            <div class="space-y-4">
                                <h4 class="font-bold border-b pb-2">Principal / School Head</h4>
                                <div class="grid grid-cols-6 gap-4">
                                    <div class="col-span-6 sm:col-span-4">
                                        <InputLabel value="Name" />
                                        <TextInput v-model="form.schoolData.key_personnel.principal.name" type="text" class="mt-1 block w-full" />
                                    </div>
                                    <div class="col-span-6 sm:col-span-3">
                                        <InputLabel value="Phone" />
                                        <TextInput v-model="form.schoolData.key_personnel.principal.phone" type="text" class="mt-1 block w-full" />
                                    </div>
                                    <div class="col-span-6 sm:col-span-3">
                                        <InputLabel value="Email" />
                                        <TextInput v-model="form.schoolData.key_personnel.principal.email" type="email" class="mt-1 block w-full" />
                                    </div>
                                </div>
                            </div>

                            <!-- Vice Principal -->
                            <div class="space-y-4">
                                <h4 class="font-bold border-b pb-2">Vice Principal</h4>
                                <div class="grid grid-cols-6 gap-4">
                                    <div class="col-span-6 sm:col-span-4">
                                        <InputLabel value="Name" />
                                        <TextInput v-model="form.schoolData.key_personnel.vice_principal.name" type="text" class="mt-1 block w-full" />
                                    </div>
                                    <div class="col-span-6 sm:col-span-3">
                                        <InputLabel value="Phone" />
                                        <TextInput v-model="form.schoolData.key_personnel.vice_principal.phone" type="text" class="mt-1 block w-full" />
                                    </div>
                                    <div class="col-span-6 sm:col-span-3">
                                        <InputLabel value="Email" />
                                        <TextInput v-model="form.schoolData.key_personnel.vice_principal.email" type="email" class="mt-1 block w-full" />
                                    </div>
                                </div>
                            </div>

                            <!-- Academic Coordinator -->
                            <div class="space-y-4">
                                <h4 class="font-bold border-b pb-2">Academic Coordinator</h4>
                                <div class="grid grid-cols-6 gap-4">
                                    <div class="col-span-6 sm:col-span-4">
                                        <InputLabel value="Name" />
                                        <TextInput v-model="form.schoolData.key_personnel.academic_coordinator.name" type="text" class="mt-1 block w-full" />
                                    </div>
                                    <div class="col-span-6 sm:col-span-3">
                                        <InputLabel value="Phone" />
                                        <TextInput v-model="form.schoolData.key_personnel.academic_coordinator.phone" type="text" class="mt-1 block w-full" />
                                    </div>
                                    <div class="col-span-6 sm:col-span-3">
                                        <InputLabel value="Email" />
                                        <TextInput v-model="form.schoolData.key_personnel.academic_coordinator.email" type="email" class="mt-1 block w-full" />
                                    </div>
                                </div>
                            </div>

                            <!-- Administrative Contact -->
                            <div class="space-y-4">
                                <h4 class="font-bold border-b pb-2">Administrative Contact</h4>
                                <div class="grid grid-cols-6 gap-4">
                                    <div class="col-span-6 sm:col-span-4">
                                        <InputLabel value="Name" />
                                        <TextInput v-model="form.schoolData.key_personnel.admin_contact.name" type="text" class="mt-1 block w-full" />
                                    </div>
                                    <div class="col-span-6 sm:col-span-3">
                                        <InputLabel value="Phone" />
                                        <TextInput v-model="form.schoolData.key_personnel.admin_contact.phone" type="text" class="mt-1 block w-full" />
                                    </div>
                                    <div class="col-span-6 sm:col-span-3">
                                        <InputLabel value="Email" />
                                        <TextInput v-model="form.schoolData.key_personnel.admin_contact.email" type="email" class="mt-1 block w-full" />
                                    </div>
                                </div>
                            </div>

                            <!-- Emergency Contact -->
                            <div class="space-y-4">
                                <h4 class="font-bold border-b pb-2 text-red-600">Emergency Contact</h4>
                                <div class="grid grid-cols-6 gap-4">
                                    <div class="col-span-6 sm:col-span-4">
                                        <InputLabel value="Emergency Contact Number" />
                                        <TextInput v-model="form.schoolData.key_personnel.emergency_contact" type="text" class="mt-1 block w-full border-red-300" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Logo Section -->
                <div v-show="activeTab === 'logo'">
                    <div class="md:grid md:grid-cols-3 md:gap-6">
                        <div class="md:col-span-1">
                            <h3 class="text-lg font-medium text-gray-900">Logo & Branding</h3>
                            <p class="mt-1 text-sm text-gray-600">Manage school visual identity.</p>
                        </div>
                        <div class="mt-5 md:mt-0 md:col-span-2 space-y-6 bg-white p-6 shadow sm:rounded-md">
                            <div class="grid grid-cols-6 gap-6">
                                <div class="col-span-6">
                                    <InputLabel for="logo_url" value="Logo URL" />
                                    <TextInput id="logo_url" v-model="form.schoolData.logo.current_logo_url" type="text" class="mt-1 block w-full" placeholder="https://example.com/logo.png" />
                                    <p class="mt-2 text-xs text-gray-500">Version: {{ form.schoolData.logo.logo_version }} | Last Changed: {{ form.schoolData.logo.last_changed_at || 'Never' }}</p>
                                </div>
                                <div class="col-span-6">
                                    <InputLabel for="logo_file" value="Upload Logo" />
                                    <input
                                        id="logo_file"
                                        type="file"
                                        accept="image/*"
                                        class="mt-1 block w-full text-sm text-gray-700 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                                        @change="handleLogoFileChange"
                                    />
                                </div>
                                <div v-if="logoFilePreviewUrl || form.schoolData.logo.current_logo_url" class="col-span-6">
                                    <InputLabel value="Preview" />
                                    <div class="mt-2 p-4 border rounded-md flex justify-center bg-gray-50">
                                        <img :src="logoFilePreviewUrl || form.schoolData.logo.current_logo_url" alt="Logo Preview" class="h-32 object-contain" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer / Save Button -->
                <div class="mt-6 flex items-center justify-end px-4 py-3 bg-gray-50 text-end sm:px-6 shadow sm:rounded-md border border-gray-200">
                    <ActionMessage :on="form.recentlySuccessful" class="me-3">
                        School details saved.
                    </ActionMessage>

                    <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                        Save All Changes
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
