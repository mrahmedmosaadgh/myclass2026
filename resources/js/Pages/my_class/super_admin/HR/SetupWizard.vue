<template>
    <AppLayout title="HR Setup Wizard">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                HR Initial Setup Wizard
            </h2>
        </template>

        <div class="py-12">
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
                            <!-- Step 1: HR Information (Profile) -->
                            <q-step
                                :name="1"
                                title="HR Profile Setup"
                                icon="business"
                                :done="currentStep > 1"
                            >
                                <div class="q-gutter-md">
                                    <q-banner class="bg-blue-1 text-blue-9 q-mb-md rounded-borders">
                                        <template v-slot:avatar>
                                            <q-icon name="account_circle" color="primary" />
                                        </template>
                                        You are currently logged in as <strong>{{ formData.step1.user_name }}</strong>. 
                                        Below is your public HR profile information.
                                    </q-banner>

                                    <q-input
                                        v-model="formData.step1.hr_name"
                                        label="HR Public Name *"
                                        outlined
                                        :rules="[val => !!val || 'HR Name is required']"
                                    />

                                    <div class="row q-gutter-md">
                                        <q-input
                                            v-model="formData.step1.user_name"
                                            label="Linked User Account"
                                            outlined
                                            readonly
                                            class="col"
                                            hint="Read-only"
                                        />
                                        <q-input
                                            v-model="formData.step1.user_email"
                                            label="Email Address"
                                            outlined
                                            readonly
                                            class="col"
                                            hint="Read-only"
                                        />
                                    </div>

                                    <q-input
                                        v-model="formData.step1.phone"
                                        label="Phone"
                                        outlined
                                    />
                                    <q-input
                                        v-model="formData.step1.address"
                                        label="Address"
                                        outlined
                                        type="textarea"
                                        rows="3"
                                    />
                                </div>
                            </q-step>

                            <!-- Step 2: School Information -->
                            <q-step
                                :name="2"
                                title="School Information"
                                icon="school"
                                :done="currentStep > 2"
                            >
                                <div class="q-gutter-md">
                                    <!-- School Selector -->
                                    <div v-if="hrSchools && hrSchools.length > 0" class="q-mb-lg bg-indigo-50 p-4 rounded-lg border border-indigo-200">
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
                                            dense
                                            bg-color="white"
                                            @update:model-value="switchSchoolContext"
                                        >
                                            <template v-slot:append>
                                                <q-icon name="school" color="indigo" />
                                            </template>
                                        </q-select>
                                        <div class="text-caption text-indigo-700 q-mt-xs">
                                            Selecting a school will reload the wizard with that school's data.
                                        </div>
                                    </div>

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
            // Let's check MySchoolsController::selectSchool behavior.
            // It `return redirect()->back();` or dashboard.
            // If it returns back(), we are good.
            // But we specifically want the Wizard to reload with new data.
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
</script>
