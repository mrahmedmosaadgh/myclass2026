<template>
    <AppLayout title="My Schools">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                My Schools Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Welcome Banner -->
                <div class="bg-gradient-to-r from-blue-500 to-indigo-600 rounded-lg shadow-xl p-8 mb-8 text-white">
                    <h3 class="text-2xl font-bold mb-2">Welcome, {{ hr.name }}!</h3>
                    <p class="opacity-90">Manage your educational institutions and configure their settings from this central dashboard.</p>
                </div>

                <!-- Action Bar -->
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-medium text-gray-900">Your Schools</h3>
                    <PrimaryButton @click="showAddModal = true">
                        <span class="flex items-center">
                            <i class="material-icons mr-2">add</i> Add New School
                        </span>
                    </PrimaryButton>
                </div>

                <!-- Schools Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div v-for="school in schools" :key="school.id" 
                        class="bg-white rounded-lg shadow border border-gray-100 hover:shadow-lg transition-shadow duration-300 overflow-hidden"
                        :class="{'ring-2 ring-indigo-500': school.id === currentSchoolId}"
                    >
                        <div class="p-6">
                            <div class="flex justify-between items-start mb-4">
                                <div class="bg-indigo-50 p-3 rounded-full text-indigo-600">
                                    <span class="material-icons">school</span>
                                </div>
                                <div class="flex flex-col items-end">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                        :class="school.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                                        {{ school.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                    <span v-if="school.id === currentSchoolId" class="mt-1 text-xs text-indigo-600 font-bold">
                                        Current Context
                                    </span>
                                </div>
                            </div>

                            <h4 class="text-xl font-bold text-gray-800 mb-1">{{ school.name }}</h4>
                            <p class="text-sm text-gray-500 mb-4">{{ school.name_ar || 'No Arabic Name' }}</p>

                            <div class="grid grid-cols-3 gap-2 mb-6 text-center text-sm">
                                <div class="bg-gray-50 p-2 rounded">
                                    <div class="font-bold text-gray-800">{{ school.students_count || 0 }}</div>
                                    <div class="text-gray-500 text-xs">Students</div>
                                </div>
                                <div class="bg-gray-50 p-2 rounded">
                                    <div class="font-bold text-gray-800">{{ school.teachers_count || 0 }}</div>
                                    <div class="text-gray-500 text-xs">Teachers</div>
                                </div>
                                <div class="bg-gray-50 p-2 rounded">
                                    <div class="font-bold text-gray-800">{{ school.classrooms_count || 0 }}</div>
                                    <div class="text-gray-500 text-xs">Classes</div>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <button 
                                    @click="selectAndSetup(school)"
                                    class="w-full flex justify-center items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                >
                                    <span class="material-icons text-sm mr-2">settings</span>
                                    Setup Wizard
                                </button>
                                <button 
                                    @click="selectSchool(school)"
                                    v-if="school.id !== currentSchoolId"
                                    class="w-full flex justify-center items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                >
                                    Switch Dashboard Context
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add School Modal -->
        <DialogModal :show="showAddModal" @close="showAddModal = false">
            <template #title>Add New School</template>
            <template #content>
                <div class="space-y-4">
                    <div>
                        <InputLabel for="name" value="School Name (English) *" />
                        <TextInput id="name" v-model="form.name" type="text" class="mt-1 block w-full" required />
                        <InputError :message="form.errors.name" class="mt-2" />
                    </div>
                    <div>
                        <InputLabel for="name_ar" value="School Name (Arabic)" />
                        <TextInput id="name_ar" v-model="form.name_ar" type="text" class="mt-1 block w-full" />
                    </div>
                    <div>
                        <InputLabel for="year" value="Established Year" />
                        <TextInput id="year" v-model="form.established_year" type="number" class="mt-1 block w-full" />
                    </div>
                </div>
            </template>
            <template #footer>
                <SecondaryButton @click="showAddModal = false">Cancel</SecondaryButton>
                <PrimaryButton class="ml-3" @click="addSchool" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    Create School
                </PrimaryButton>
            </template>
        </DialogModal>
    </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DialogModal from '@/Components/DialogModal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    schools: Array,
    hr: Object,
    currentSchoolId: Number,
});

const showAddModal = ref(false);

const form = useForm({
    name: '',
    name_ar: '',
    established_year: new Date().getFullYear(),
});

const addSchool = () => {
    form.post(route('admin.hr.my-schools.store'), {
        onSuccess: () => {
            showAddModal.value = false;
            form.reset();
        },
    });
};

const selectSchool = (school) => {
    router.post(route('admin.hr.my-schools.select', school.id));
};

const selectAndSetup = (school) => {
    // Determine if we need to switch context first
    if (school.id !== props.currentSchoolId) {
        router.post(route('admin.hr.my-schools.select', school.id), {}, {
            onSuccess: () => {
                // After switching, navigate to wizard
                router.visit(route('admin.hr.setup-wizard'));
            }
        });
    } else {
        router.visit(route('admin.hr.setup-wizard'));
    }
};
</script>
