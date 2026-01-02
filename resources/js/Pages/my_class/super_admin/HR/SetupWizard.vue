<template>
    <AppLayout title="HR Setup Wizard">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                HR Initial Setup Wizard
            </h2>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- School Selector -->
                <div class="q-mb-lg bg-indigo-50 p-4 rounded-lg border border-indigo-200">
                    <div class="flex justify-between items-center">
                        <div>
                            <div class="text-subtitle1 text-indigo-900 q-mb-sm font-bold">Select School to Setup</div>
                            <q-select
                                v-model="selectedSchoolId"
                                :options="hrSchools"
                                option-value="id"
                                option-label="name"
                                label="Choose a School"
                                outlined
                                emit-value
                                map-options
                                bg-color="white"
                                class="q-mb-sm"
                                @update:model-value="switchSchoolContext"
                            >
                                <template v-slot:append>
                                    <q-icon name="school" color="indigo" />
                                </template>
                            </q-select>
                            <div class="text-caption text-indigo-700">
                                Selecting a school will reload the wizard with that school's data.
                            </div>
                        </div>
                        
                        <div class="flex q-gutter-sm">
                            <q-btn 
                                label="Load Default Data" 
                                icon="download_for_offline" 
                                color="secondary"
                                @click="loadDefaultData"
                                :disable="currentStep !== 1 && !isExistingSetup"
                            />
                            <q-btn 
                                label="Load & Add All Grades" 
                                icon="playlist_add_check" 
                                color="accent"
                                @click="loadDefaultDataAndAddAllGrades"
                                :disable="currentStep !== 1 && !isExistingSetup"
                            />
                            <q-btn 
                                label="Add New School" 
                                icon="add" 
                                color="primary"
                                @click="showAddSchoolDialog = true"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <q-card class="shadow-xl">
                    <q-card-section>
                        <q-stepper
                            v-model="currentStep"
                            ref="stepper"
                            color="primary"
                            animated
                            header-nav
                            @before-transition="validateCurrentStep"
                        >
 

                            <!-- Step 2: School Information -->
                            <q-step
                                :name="2"
                                title="School Information"
                                icon="school"
                                :done="currentStep > 2"
                            >
                                <div class="q-gutter-md">

                                    <div class="text-h6 q-mb-md">School Details</div>

                                    <q-input
                                        v-model="formData.step2.name"
                                        label="School Name (English) *"
                                        outlined
                                        :rules="[val => !!val || 'School name is required']"
                                    />
                                    <q-input
                                        v-model="formData.step2.name_ar"
                                        label="School Name (Arabic)"
                                        outlined
                                    />

                                    <q-select
                                        v-model="formData.step2.section"
                                        :options="sectionOptions"
                                        label="School Section"
                                        outlined
                                        emit-value
                                        map-options
                                    />

                                    <q-input
                                        v-model="formData.step2.address"
                                        label="Address"
                                        outlined
                                        type="textarea"
                                        rows="3"
                                    />
                                    <q-input
                                        v-model="formData.step2.phone"
                                        label="Phone"
                                        outlined
                                    />
                                    <q-input
                                        v-model="formData.step2.email"
                                        label="Email"
                                        type="email"
                                        outlined
                                    />
                                    <q-input
                                        v-model="formData.step2.established_year"
                                        label="Established Year"
                                        type="number"
                                        outlined
                                    />
                                </div>
                            </q-step>

                            <!-- Step 3: Stages & Grades -->
                            <q-step
                                :name="3"
                                title="Stages & Grades"
                                icon="stairs"
                                :done="currentStep > 3"
                            >
                                <div class="q-gutter-md">
                                    <div v-for="(stage, stageIndex) in formData.step3.stages" :key="stageIndex" class="q-pa-md bg-grey-1 rounded-borders">
                                        <div class="row q-gutter-md items-center">
                                            <q-input
                                                v-model="stage.name"
                                                label="Stage Name *"
                                                outlined
                                                class="col"
                                            />
                                            <q-input
                                                v-model="stage.name_ar"
                                                label="Stage Name (Arabic)"
                                                outlined
                                                class="col"
                                            />
                                            <q-btn
                                                icon="delete"
                                                color="negative"
                                                flat
                                                round
                                                @click="removeStage(stageIndex)"
                                                v-if="formData.step3.stages.length > 1"
                                            />
                                        </div>

                                        <div class="q-mt-md">
                                            <div class="text-subtitle2 q-mb-sm">Grades</div>
                                            <div v-for="(grade, gradeIndex) in stage.grades" :key="gradeIndex" class="row q-gutter-sm q-mb-sm">
                                                <q-input
                                                    v-model="grade.name"
                                                    label="Grade Name *"
                                                    outlined
                                                    dense
                                                    class="col"
                                                />
                                                <q-input
                                                    v-model="grade.name_ar"
                                                    label="Grade Name (Arabic)"
                                                    outlined
                                                    dense
                                                    class="col"
                                                />
                                                <q-btn
                                                    icon="delete"
                                                    color="negative"
                                                    flat
                                                    round
                                                    dense
                                                    @click="removeGrade(stageIndex, gradeIndex)"
                                                    v-if="stage.grades.length > 1"
                                                />
                                            </div>
                                            <q-btn
                                                label="Add Grade"
                                                icon="add"
                                                color="primary"
                                                flat
                                                dense
                                                @click="addGrade(stageIndex)"
                                            />
                                        </div>
                                    </div>

                                    <q-btn
                                        label="Add Stage"
                                        icon="add"
                                        color="primary"
                                        @click="addStage"
                                    />
                                </div>
                            </q-step>

                            <!-- Step 4: Subjects -->
                            <q-step
                                :name="4"
                                title="Subjects"
                                icon="menu_book"
                                :done="currentStep > 4"
                            >
                                <div class="q-gutter-md">
                                    <div v-for="(subject, index) in formData.step4.subjects" :key="index" class="row q-gutter-md items-center">
                                        <q-input
                                            v-model="subject.name"
                                            label="Subject Name *"
                                            outlined
                                            dense
                                            class="col-3"
                                        />
                                        <q-input
                                            v-model="subject.name_ar"
                                            label="Subject Name (Arabic)"
                                            outlined
                                            dense
                                            class="col-3"
                                        />
                                        <q-input
                                            v-model="subject.color_bg"
                                            label="Background Color"
                                            outlined
                                            dense
                                            type="color"
                                            class="col-2"
                                        />
                                        <q-input
                                            v-model="subject.color_text"
                                            label="Text Color"
                                            outlined
                                            dense
                                            type="color"
                                            class="col-2"
                                        />
                                        <q-btn
                                            icon="delete"
                                            color="negative"
                                            flat
                                            round
                                            dense
                                            @click="removeSubject(index)"
                                            v-if="formData.step4.subjects.length > 1"
                                        />
                                    </div>

                                    <q-btn
                                        label="Add Subject"
                                        icon="add"
                                        color="primary"
                                        @click="addSubject"
                                    />
                                </div>
                            </q-step>

                            <!-- Step 5: Classrooms -->
                            <q-step
                                :name="5"
                                title="Classrooms"
                                icon="class"
                                :done="currentStep > 5"
                            >
                                <div class="q-gutter-md">
                                    <q-banner class="bg-info text-white">
                                        <template v-slot:avatar>
                                            <q-icon name="info" />
                                        </template>
                                        You can modify classroom names and add/remove classrooms later
                                    </q-banner>

                                    <div v-for="(classroom, index) in formData.step5.classrooms" :key="index" class="q-pa-md bg-grey-1 rounded-borders">
                                        <div class="row q-gutter-md items-center">
                                            <q-select
                                                v-model="classroom.grade_id"
                                                :options="availableGrades"
                                                option-value="id"
                                                option-label="name"
                                                emit-value
                                                map-options
                                                label="Grade *"
                                                outlined
                                                dense
                                                class="col-4"
                                            />
                                            <q-input
                                                v-model.number="classroom.capacity"
                                                label="Capacity per Classroom"
                                                type="number"
                                                outlined
                                                dense
                                                class="col-3"
                                            />
                                            <q-btn
                                                icon="delete"
                                                color="negative"
                                                flat
                                                round
                                                @click="removeClassroom(index)"
                                                v-if="formData.step5.classrooms.length > 1"
                                            />
                                        </div>

                                        <div class="q-mt-sm">
                                            <div class="text-caption q-mb-xs">Select Sections (Type custom values and press Enter)</div>
                                            <q-select
                                                v-model="classroom.sections"
                                                :options="defaultSections"
                                                use-input
                                                use-chips
                                                multiple
                                                filled
                                                dense
                                                new-value-mode="add-unique"
                                                label="Sections"
                                                hint="Manage sections (e.g., A, B, F, Custom)"
                                            />
                                        </div>
                                    </div>

                                    <q-btn
                                        label="Add Grade Classrooms"
                                        icon="add"
                                        color="primary"
                                        @click="addClassroom"
                                    />
                                    <q-btn
                                        label="Add All Grades"
                                        icon="playlist_add"
                                        color="accent"
                                        @click="addAllGrades"
                                        class="q-ml-md"
                                    />
                                </div>
                            </q-step>

                            <!-- Step 6: Academic Year & Semesters -->
                            <q-step
                                :name="6"
                                title="Academic Year"
                                icon="calendar_today"
                                :done="currentStep > 6"
                            >
                                <div class="q-gutter-md">
                                    <q-input
                                        v-model="formData.step6.academic_year_name"
                                        label="Academic Year Name *"
                                        outlined
                                        hint="e.g., 2026-2027"
                                        :rules="[val => !!val || 'Academic year name is required']"
                                    />
                                    <div class="row q-gutter-md">
                                        <q-input
                                            v-model="formData.step6.start_date"
                                            label="Start Date *"
                                            type="date"
                                            outlined
                                            class="col"
                                            :rules="[val => !!val || 'Start date is required']"
                                        />
                                        <q-input
                                            v-model="formData.step6.end_date"
                                            label="End Date *"
                                            type="date"
                                            outlined
                                            class="col"
                                            :rules="[val => !!val || 'End date is required']"
                                        />
                                    </div>

                                    <div class="text-h6 q-mt-md">Semesters</div>
                                    <div v-for="(semester, index) in formData.step6.semesters" :key="index" class="q-pa-md bg-grey-1 rounded-borders q-mb-md">
                                        <div class="row q-gutter-md items-center">
                                            <q-input
                                                v-model="semester.name"
                                                label="Semester Name *"
                                                outlined
                                                dense
                                                class="col-3"
                                            />
                                            <q-input
                                                v-model="semester.start_date"
                                                label="Start Date *"
                                                type="date"
                                                outlined
                                                dense
                                                class="col-3"
                                            />
                                            <q-input
                                                v-model="semester.end_date"
                                                label="End Date *"
                                                type="date"
                                                outlined
                                                dense
                                                class="col-3"
                                            />
                                            <q-input
                                                v-model.number="semester.total_weeks"
                                                label="Total Weeks"
                                                type="number"
                                                outlined
                                                dense
                                                class="col-2"
                                            />
                                            <q-btn
                                                icon="delete"
                                                color="negative"
                                                flat
                                                round
                                                @click="removeSemester(index)"
                                                v-if="formData.step6.semesters.length > 1"
                                            />
                                        </div>
                                        <div v-if="index === 0" class="text-caption text-positive q-mt-xs">
                                            <q-icon name="check_circle" /> This semester will be set as active
                                        </div>
                                    </div>

                                    <q-btn
                                        label="Add Semester"
                                        icon="add"
                                        color="primary"
                                        @click="addSemester"
                                        v-if="formData.step6.semesters.length < 4"
                                    />
                                </div>
                            </q-step>

                            <template v-slot:navigation>
                                <q-stepper-navigation>
                                    <q-btn
                                        v-if="currentStep < 6"
                                        @click="nextStep"
                                        color="primary"
                                        label="Continue"
                                        :loading="validating"
                                    />
                                    <q-btn
                                        v-if="currentStep === 6"
                                        @click="submitWizard"
                                        color="primary"
                                        label="Complete Setup"
                                        :loading="submitting"
                                    />
                                    <q-btn
                                        v-if="currentStep > 1"
                                        flat
                                        color="primary"
                                        @click="$refs.stepper.previous()"
                                        label="Back"
                                        class="q-ml-sm"
                                    />
                                </q-stepper-navigation>
                            </template>
                        </q-stepper>
                    </q-card-section>
                </q-card>
            </div>
        </div>
        
        <!-- Add School Dialog -->
        <q-dialog v-model="showAddSchoolDialog" persistent>
            <q-card style="min-width: 400px">
                <q-card-section class="row items-center q-pb-none">
                    <div class="text-h6">Add New School</div>
                    <q-space />
                    <q-btn icon="close" flat round dense v-close-popup />
                </q-card-section>

                <q-card-section>
                    <q-input
                        v-model="newSchoolData.name"
                        label="School Name *"
                        outlined
                        :rules="[val => !!val || 'School name is required']"
                        class="q-mb-md"
                    />
                    <q-input
                        v-model="newSchoolData.name_ar"
                        label="School Name (Arabic)"
                        outlined
                        class="q-mb-md"
                    />
                    <q-select
                        v-model="newSchoolData.section"
                        :options="sectionOptions"
                        label="School Section"
                        outlined
                        emit-value
                        map-options
                    />
                </q-card-section>

                <q-card-actions align="right">
                    <q-btn label="Cancel" color="grey" v-close-popup />
                    <q-btn 
                        label="Create School" 
                        color="primary" 
                        @click="createNewSchool"
                        :loading="creatingSchool"
                    />
                </q-card-actions>
            </q-card>
        </q-dialog>
    </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import axios from 'axios';
import { useQuasar } from 'quasar';

const $q = useQuasar();

const props = defineProps({
    users: Array,
    defaultData: Object,
    existingSetup: Object,
    hrSchools: Array,
});

const isExistingSetup = computed(() => !!props.existingSetup);

// Initialize selected school from existing setup or first available or null
const selectedSchoolId = ref(props.existingSetup?.step2?.id || null);

const currentStep = ref(props.existingSetup ? 2 : 1);
const validating = ref(false);
const submitting = ref(false);
const showAddSchoolDialog = ref(false);

const sectionOptions = [
    { label: 'Boys', value: 'boys' },
    { label: 'Girls', value: 'girls' },
    { label: 'Mixed', value: 'mixed' },
];

const defaultSections = props.defaultData.sections;

const formData = ref({
    step1: props.existingSetup?.step1 || {
        hr_name: '',
        user_name: '', // Will be populated if existing
        user_email: '',
        user_password: '',
        user_id: null,
        phone: '',
        address: '',
    },
    step2: props.existingSetup?.step2 || {
        name: '',
        name_ar: '',
        section: null,
        section_ar: '',
        address: '',
        phone: '',
        email: '',
        established_year: new Date().getFullYear(),
    },
    step3: props.existingSetup?.step3 || {
        stages: JSON.parse(JSON.stringify(props.defaultData.stages)),
    },
    step4: props.existingSetup?.step4 || {
        subjects: JSON.parse(JSON.stringify(props.defaultData.subjects)),
    },
    step5: props.existingSetup?.step5 || {
        classrooms: [],
    },
    step6: props.existingSetup?.step6 || {
        academic_year_name: `${new Date().getFullYear()}-${new Date().getFullYear() + 1}`,
        start_date: '',
        end_date: '',
        semesters: JSON.parse(JSON.stringify(props.defaultData.semesters)),
    },
});

// Data for the new school form
const newSchoolData = ref({
    name: '',
    name_ar: '',
    section: null,
});

const creatingSchool = ref(false);

const availableGrades = computed(() => {
    const grades = [];
    formData.value.step3.stages.forEach((stage, stageIndex) => {
        stage.grades.forEach((grade, gradeIndex) => {
            grades.push({
                id: `${stageIndex}-${gradeIndex}`,
                name: grade.name,
                stageIndex,
                gradeIndex,
            });
        });
    });
    return grades;
});

const switchSchoolContext = (schoolId) => {
    // We use the existing "Select School" route to update the user's session/context
    // Then reload the wizard page
    router.post(route('admin.hr.my-schools.select', schoolId), {}, {
        onSuccess: () => {
             $q.notify({
                type: 'positive',
                message: 'Switched to selected school',
                position: 'top'
            });
            // The select route redirects to dashboard usually, but we want to stay here?
            // If the backend redirects to dashboard, we might be forced there.
            // So we specifically want the Wizard to reload with new data.
            // So we might need to manually visit safely if the redirect isn't what we want.
            // Actually, if we use Inertia router.post, it follows redirects.
            // If we want to ensure we come back to Wizard, we might need to visit Wizard explicitly after.
            
            // Let's assume standard behavior: update context -> revisit wizard
            router.get(route('admin.hr.setup-wizard')); 
        }
    });
};

const validateCurrentStep = async () => {
    validating.value = true;
    try {
        // Client-side validation first
        let isValid = true;
        let validationErrors = [];

        switch(currentStep.value) {
            case 1: // HR Profile Setup
                if (!formData.value.step1.hr_name?.trim()) {
                    validationErrors.push('HR Name is required');
                    isValid = false;
                }
                
                // Check if either an existing user is selected or a new user is being created
                const hasExistingUser = formData.value.step1.user_id;
                const creatingNewUser = formData.value.step1.create_new_user;
                
                if (!hasExistingUser && !creatingNewUser) {
                    validationErrors.push('Please select an existing user or choose to create a new user');
                    isValid = false;
                }
                
                // If creating a new user, validate the required fields
                if (creatingNewUser) {
                    if (!formData.value.step1.user_name?.trim()) {
                        validationErrors.push('New user name is required when creating a new user');
                        isValid = false;
                    }
                    if (!formData.value.step1.user_email?.trim()) {
                        validationErrors.push('New user email is required when creating a new user');
                        isValid = false;
                    }
                    // Only validate password if we're creating a new user
                    if (!formData.value.step1.user_password?.trim()) {
                        validationErrors.push('New user password is required when creating a new user');
                        isValid = false;
                    }
                } else if (hasExistingUser && !formData.value.step1.user_id) {
                    // If not creating new user, an existing user must be selected
                    validationErrors.push('An existing user must be selected if not creating a new user');
                    isValid = false;
                }
                break;
                
            case 2: // School Information
                if (!formData.value.step2.name?.trim()) {
                    validationErrors.push('School Name is required');
                    isValid = false;
                }
                break;
                
            case 3: // Stages and Grades
                if (formData.value.step3.stages.length === 0) {
                    validationErrors.push('At least one stage is required');
                    isValid = false;
                } else {
                    for (let i = 0; i < formData.value.step3.stages.length; i++) {
                        const stage = formData.value.step3.stages[i];
                        if (!stage.name?.trim()) {
                            validationErrors.push(`Stage ${i+1} name is required`);
                            isValid = false;
                        }
                        if (stage.grades.length === 0) {
                            validationErrors.push(`Stage ${i+1} must have at least one grade`);
                            isValid = false;
                        } else {
                            for (let j = 0; j < stage.grades.length; j++) {
                                const grade = stage.grades[j];
                                if (!grade.name?.trim()) {
                                    validationErrors.push(`Grade ${j+1} in stage ${i+1} name is required`);
                                    isValid = false;
                                }
                            }
                        }
                    }
                }
                break;
                
            case 4: // Subjects
                if (formData.value.step4.subjects.length === 0) {
                    validationErrors.push('At least one subject is required');
                    isValid = false;
                } else {
                    for (let i = 0; i < formData.value.step4.subjects.length; i++) {
                        const subject = formData.value.step4.subjects[i];
                        if (!subject.name?.trim()) {
                            validationErrors.push(`Subject ${i+1} name is required`);
                            isValid = false;
                        }
                    }
                }
                break;
                
            case 5: // Classrooms
                if (formData.value.step5.classrooms.length === 0) {
                    validationErrors.push('At least one classroom is required');
                    isValid = false;
                } else {
                    for (let i = 0; i < formData.value.step5.classrooms.length; i++) {
                        const classroom = formData.value.step5.classrooms[i];
                        if (!classroom.grade_id) {
                            validationErrors.push(`Classroom ${i+1} grade selection is required`);
                            isValid = false;
                        }
                        if (!classroom.sections || classroom.sections.length === 0) {
                            validationErrors.push(`Classroom ${i+1} must have at least one section`);
                            isValid = false;
                        }
                    }
                }
                break;
                
            case 6: // Academic Year
                if (!formData.value.step6.academic_year_name?.trim()) {
                    validationErrors.push('Academic year name is required');
                    isValid = false;
                }
                if (!formData.value.step6.start_date) {
                    validationErrors.push('Start date is required');
                    isValid = false;
                }
                if (!formData.value.step6.end_date) {
                    validationErrors.push('End date is required');
                    isValid = false;
                }
                if (formData.value.step6.semesters.length === 0) {
                    validationErrors.push('At least one semester is required');
                    isValid = false;
                } else {
                    for (let i = 0; i < formData.value.step6.semesters.length; i++) {
                        const semester = formData.value.step6.semesters[i];
                        if (!semester.name?.trim()) {
                            validationErrors.push(`Semester ${i+1} name is required`);
                            isValid = false;
                        }
                    }
                }
                break;
        }

        // Show client-side validation errors if any
        if (!isValid) {
            validationErrors.forEach(error => {
                $q.notify({
                    type: 'negative',
                    message: error,
                    position: 'top'
                });
            });
            return false;
        }

        // If client-side validation passes, check with server
        const stepData = formData.value[`step${currentStep.value}`];
        await axios.post(route('admin.hr.setup-wizard.validate-step'), {
            step: currentStep.value,
            data: stepData,
        });
        return true;
    } catch (error) {
        if (error.response?.data?.errors) {
            // Show validation errors
            Object.values(error.response.data.errors).forEach(errors => {
                errors.forEach(error => {
                    $q.notify({
                        type: 'negative',
                        message: error,
                        position: 'top'
                    });
                });
            });
        }
        return false;
    } finally {
        validating.value = false;
    }
};

const nextStep = async () => {
    const isValid = await validateCurrentStep();
    if (isValid) {
        currentStep.value++;
    }
};

const submitWizard = async () => {
    submitting.value = true;
    try {
        // Process classrooms to include actual grade IDs
        const processedClassrooms = formData.value.step5.classrooms.map(classroom => {
            const [stageIndex, gradeIndex] = classroom.grade_id.split('-').map(Number);
            const stage = formData.value.step3.stages[stageIndex];
            const grade = stage.grades[gradeIndex];
            
            return {
                ...classroom,
                grade_name: grade.name,
                stage_name: stage.name,
            };
        });

        router.post(route('admin.hr.setup-wizard.store'), {
            ...formData.value,
            step5: {
                classrooms: processedClassrooms,
            },
        }, {
            onSuccess: () => {
                $q.notify({
                    type: 'positive',
                    message: 'School setup completed successfully!',
                });
            },
            onError: (errors) => {
                Object.values(errors).forEach(error => {
                    $q.notify({
                        type: 'negative',
                        message: error,
                    });
                });
            },
            onFinish: () => {
                submitting.value = false;
            },
        });
    } catch (error) {
        submitting.value = false;
        $q.notify({
            type: 'negative',
            message: 'An error occurred during setup',
        });
    }
};

// Helper functions
const addStage = () => {
    formData.value.step3.stages.push({
        name: '',
        name_ar: '',
        description: '',
        grades: [{ name: '', name_ar: '' }],
    });
};

const removeStage = (index) => {
    formData.value.step3.stages.splice(index, 1);
};

const addGrade = (stageIndex) => {
    formData.value.step3.stages[stageIndex].grades.push({ name: '', name_ar: '' });
};

const removeGrade = (stageIndex, gradeIndex) => {
    formData.value.step3.stages[stageIndex].grades.splice(gradeIndex, 1);
};

const addSubject = () => {
    formData.value.step4.subjects.push({
        name: '',
        name_ar: '',
        color_bg: '#3B82F6',
        color_text: '#FFFFFF',
    });
};

const removeSubject = (index) => {
    formData.value.step4.subjects.splice(index, 1);
};

const addClassroom = () => {
    formData.value.step5.classrooms.push({
        grade_id: null,
        sections: ['A', 'B', 'C', 'D', 'E'],
        capacity: 30,
    });
};

const addAllGrades = () => {
    // Get all available grades
    const allGrades = availableGrades.value;
    
    // Add each grade as a new classroom entry if it doesn't already exist
    allGrades.forEach(grade => {
        const existingClassroom = formData.value.step5.classrooms.find(classroom => 
            classroom.grade_id === grade.id
        );
        
        if (!existingClassroom) {
            formData.value.step5.classrooms.push({
                grade_id: grade.id,
                sections: ['A', 'B', 'C', 'D', 'E'],
                capacity: 30,
            });
        }
    });
    
    if (allGrades.length === 0) {
        $q.notify({
            type: 'info',
            message: 'Please add stages and grades first before adding all grades to classrooms',
            position: 'top'
        });
    } else {
        $q.notify({
            type: 'positive',
            message: `Added ${allGrades.length} grade classrooms`,
            position: 'top'
        });
    }
};

const removeClassroom = (index) => {
    formData.value.step5.classrooms.splice(index, 1);
};

const addSemester = () => {
    const nextNumber = formData.value.step6.semesters.length + 1;
    formData.value.step6.semesters.push({
        name: `Semester ${nextNumber}`,
        semester_number: nextNumber,
        start_date: '',
        end_date: '',
        total_weeks: null,
    });
};

const removeSemester = (index) => {
    formData.value.step6.semesters.splice(index, 1);
};

// Function to create a new school
const createNewSchool = async () => {
    creatingSchool.value = true;
    
    try {
        const response = await axios.post(route('admin.hr.my-schools.store'), {
            name: newSchoolData.value.name,
            name_ar: newSchoolData.value.name_ar,
            section: newSchoolData.value.section,
        });

        // Add the new school to the list
        props.hrSchools.push({
            id: response.data.id,
            name: newSchoolData.value.name,
            name_ar: newSchoolData.value.name_ar,
            section: newSchoolData.value.section,
            is_active: true,
            has_stages: false,
            has_subjects: false,
            has_classrooms: false,
            can_delete: true
        });

        // Select the new school
        selectedSchoolId.value = response.data.id;
        
        // Close the dialog and reset form
        showAddSchoolDialog.value = false;
        newSchoolData.value = {
            name: '',
            name_ar: '',
            section: null,
        };
        
        $q.notify({
            type: 'positive',
            message: 'School created successfully!',
            position: 'top'
        });
        
        // Switch to the newly created school context
        switchSchoolContext(response.data.id);
        
    } catch (error) {
        console.error('Error creating school:', error);
        $q.notify({
            type: 'negative',
            message: error.response?.data?.message || 'Error creating school',
            position: 'top'
        });
    } finally {
        creatingSchool.value = false;
    }
};

// Function to load default data into the form
const loadDefaultData = async () => {
    try {
        const response = await axios.get(route('admin.hr.setup-wizard.default-data'));
        const defaultData = response.data;
        
        // Load stages and grades
        formData.value.step3.stages = JSON.parse(JSON.stringify(defaultData.stages));
        
        // Load subjects
        formData.value.step4.subjects = JSON.parse(JSON.stringify(defaultData.subjects));
        
        // Load semesters for academic year step
        formData.value.step6.semesters = JSON.parse(JSON.stringify(defaultData.semesters));
        
        $q.notify({
            type: 'positive',
            message: 'Default data loaded successfully!',
            position: 'top'
        });
    } catch (error) {
        console.error('Error loading default data:', error);
        $q.notify({
            type: 'negative',
            message: error.response?.data?.message || 'Error loading default data',
            position: 'top'
        });
    }
};

// Function to load default data and add all grades to classrooms
const loadDefaultDataAndAddAllGrades = async () => {
    try {
        const response = await axios.get(route('admin.hr.setup-wizard.default-data'));
        const defaultData = response.data;
        
        // Load stages and grades
        formData.value.step3.stages = JSON.parse(JSON.stringify(defaultData.stages));
        
        // Load subjects
        formData.value.step4.subjects = JSON.parse(JSON.stringify(defaultData.subjects));
        
        // Load semesters for academic year step
        formData.value.step6.semesters = JSON.parse(JSON.stringify(defaultData.semesters));
        
        // Now add all grades to classrooms
        addAllGrades();
        
        $q.notify({
            type: 'positive',
            message: 'Default data loaded and all grades added to classrooms!',
            position: 'top'
        });
    } catch (error) {
        console.error('Error loading default data:', error);
        $q.notify({
            type: 'negative',
            message: error.response?.data?.message || 'Error loading default data',
            position: 'top'
        });
    }
};

</script>
