<template>
    <Head :title="t('weeklySystem.myWeeklyPlans.title')" />
    <div class="q-pa-md">
        <!-- Page Header -->
        <div class="row items-center q-mb-lg">
            <div class="col">
                <h4 class="q-ma-none text-weight-bold">
                    <q-icon name="edit_note" class="q-mr-sm" color="primary" />
                    {{ t('weeklySystem.myWeeklyPlans.title') }}
                </h4>
                <p class="text-grey-7 q-mb-none">
                    {{ t('weeklySystem.myWeeklyPlans.subtitle') }}
                </p>
            </div>
        </div>

        <!-- Controls -->
        <q-card flat bordered class="q-pa-md q-mb-lg">
            <div class="row q-gutter-md items-center">
                <!-- Week Navigation -->
                <div class="col-auto">
                    <div class="row items-center">
                        <q-btn
                            icon="chevron_left"
                            color="primary"
                            round
                            flat
                            @click="prevWeek"
                            :disable="weekNumber <= 1"
                        />
                        <WeekSelector
                            v-model="weekNumber"
                            :max-weeks="maxWeeks"
                            :current-week="currentWeek"
                            class="q-mx-md"
                        />
                        <q-btn
                            icon="chevron_right"
                            color="primary"
                            round
                            flat
                            @click="nextWeek"
                            :disable="weekNumber >= maxWeeks"
                        />
                    </div>
                </div>

                <!-- Progress Summary -->
                <div class="col-auto q-ml-auto">
                    <q-linear-progress
                        :value="completionPercentage / 100"
                        size="25px"
                        :color="progressColor"
                        track-color="grey-3"
                        rounded
                        style="width: 150px"
                    />
                </div>
                <div class="col-auto">
                    <q-chip
                        :color="progressColor"
                        text-color="white"
                        :label="`${completionPercentage}%`"
                    />
                </div>
            </div>
        </q-card>

        <!-- View Switcher -->
        <q-tabs v-model="activeTab" dense class="text-primary q-mb-sm">
            <q-tab name="table" icon="table_chart" :label="t('weeklySystem.myWeeklyPlans.tableView')" />
        </q-tabs>
        <q-separator class="q-mb-md" />

        <q-tab-panels v-model="activeTab" animated>
            <!-- Weekly Plans Grid (Old View) -->
            <div class="weekly-plans-grid">
                <div
                    v-for="plan in sortedPlansByDay"
                    :key="plan.dayNumber"
                    class="day-section q-mb-xl"
                >
                    <h5 class="q-mb-md text-weight-bold">
                        {{ getDayName(plan.dayNumber) }}
                        <q-chip
                            dense
                            :color="progressColor"
                            text-color="white"
                            :label="plan.plans.length"
                        />
                    </h5>

                    <div class="plans-grid">
                        <q-card
                            v-for="item in plan.plans"
                            :key="item.id"
                            flat
                            bordered
                            class="plan-card q-mr-sm q-mb-sm"
                            :class="{ copied: copyingPlanId === item.id }"
                        >
                            <!-- Plan Header -->
                            <div
                                class="plan-header row items-center q-px-md q-py-sm"
                            >
                                <div class="col">
                                    <div class="text-subtitle2 text-weight-bold">
                                        <q-icon
                                            name="meeting_room"
                                            size="sm"
                                            color="primary"
                                        />
                                        <!-- Add null check for schedule and cst -->
                                        {{
                                            item.data?.schedule?.cst
                                                ?.classroom_name ||
                                            t('weeklySystem.myWeeklyPlans.noClassroom')
                                        }}
                                    </div>
                                    <div class="text-caption">
                                        <q-icon
                                            name="school"
                                            size="xs"
                                            color="primary"
                                        />
                                        {{
                                            item.data?.schedule?.cst
                                                ?.subject_name ||
                                            t('weeklySystem.myWeeklyPlans.noSubject')
                                        }}
                                    </div>
                                </div>

                                <div class="col-auto">
                                    <StatusBadge
                                        :status="item.data?.status || 'empty'"
                                    />
                                </div>
                            </div>

                            <!-- Plan Content -->
                            <div class="plan-content q-px-md q-py-sm">
                                <!-- Classwork -->
                                <div class="plan-section q-mb-sm">
                                    <div class="plan-label">{{ t('weeklySystem.myWeeklyPlans.classwork') }} (CW)</div>
                                    <div
                                        class="plan-content-text"
                                        :class="{ empty: !item.data?.cw }"
                                    >
                                        <q-icon
                                            name="school"
                                            size="xs"
                                            color="primary"
                                        />
                                        <span v-if="item.data?.cw">{{
                                            item.data.cw
                                        }}</span>
                                        <span v-else class="text-grey-5"
                                            >{{ t('weeklySystem.myWeeklyPlans.noClasswork') }}</span
                                        >
                                    </div>
                                </div>

                                <!-- Homework -->
                                <div class="plan-section q-mb-sm">
                                    <div class="plan-label">{{ t('weeklySystem.myWeeklyPlans.homework') }} (HW)</div>
                                    <div
                                        class="plan-content-text"
                                        :class="{ empty: !item.data?.hw }"
                                    >
                                        <q-icon
                                            name="home_work"
                                            size="xs"
                                            color="amber"
                                        />
                                        <span v-if="item.data?.hw">{{
                                            item.data.hw
                                        }}</span>
                                        <span v-else class="text-grey-5"
                                            >{{ t('weeklySystem.myWeeklyPlans.noHomework') }}</span
                                        >
                                    </div>
                                </div>

                                <!-- Notes -->
                                <div class="plan-section">
                                    <div class="plan-label">{{ t('weeklySystem.myWeeklyPlans.notes') }}</div>
                                    <div
                                        class="plan-content-text"
                                        :class="{ empty: !item.data?.notes }"
                                    >
                                        <q-icon
                                            name="note"
                                            size="xs"
                                            color="secondary"
                                        />
                                        <span v-if="item.data?.notes">{{
                                            item.data.notes
                                        }}</span>
                                        <span v-else class="text-grey-5"
                                            >{{ t('weeklySystem.myWeeklyPlans.noNotes') }}</span
                                        >
                                    </div>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div
                                class="plan-actions row justify-end q-px-md q-py-sm"
                            >
                                <q-btn
                                    v-if="
                                        !editMode &&
                                        (!copiedData || copyingPlanId !== item.id)
                                    "
                                    icon="content_copy"
                                    size="sm"
                                    flat
                                    color="primary"
                                    @click="copyPlan(item, $event)"
                                    class="q-mr-sm"
                                >
                                    <q-tooltip>{{ t('weeklySystem.myWeeklyPlans.copied') }}</q-tooltip>
                                </q-btn>

                                <q-btn
                                    v-if="
                                        !editMode &&
                                        copiedData &&
                                        copyingPlanId !== item.id
                                    "
                                    icon="content_paste"
                                    size="sm"
                                    flat
                                    color="primary"
                                    @click="pastePlan(item, $event)"
                                    class="q-mr-sm"
                                >
                                    <q-tooltip>{{ t('weeklySystem.myWeeklyPlans.paste') }}</q-tooltip>
                                </q-btn>

                                <q-btn
                                    v-if="!editMode"
                                    icon="edit"
                                    size="sm"
                                    flat
                                    color="primary"
                                    @click="editPlan(item)"
                                >
                                    <q-tooltip>{{ t('weeklySystem.myWeeklyPlans.edit') }}</q-tooltip>
                                </q-btn>

                                <!-- Save button shown in edit mode -->
                                <q-btn
                                    v-if="editMode && item.isEditing"
                                    icon="save"
                                    size="sm"
                                    flat
                                    color="green"
                                    :loading="item.saving"
                                    @click="savePlanField(item, 'all')"
                                >
                                    <q-tooltip>{{ t('weeklySystem.myWeeklyPlans.save') }}</q-tooltip>
                                </q-btn>

                                <!-- Cancel button shown in edit mode -->
                                <q-btn
                                    v-if="editMode && item.isEditing"
                                    icon="cancel"
                                    size="sm"
                                    flat
                                    color="red"
                                    @click="revertPlan(item)"
                                >
                                    <q-tooltip>{{ t('weeklySystem.myWeeklyPlans.cancel') }}</q-tooltip>
                                </q-btn>
                            </div>
                        </q-card>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="!weeklyPlans.length" class="text-center q-pa-xl">
                <q-icon name="edit_note" size="64px" color="grey-5" />
                <p class="text-h6 text-grey-7 q-mt-md">{{ t('weeklySystem.myWeeklyPlans.noPlans') }}</p>
                <p class="text-grey-6">
                    {{ t('weeklySystem.myWeeklyPlans.selectWeek') }}
                </p>
            </div>
        </q-tab-panels>

        <!-- Edit Dialog -->
        <q-dialog v-model="showEditor" persistent>
            <WeeklyPlanEditor
                v-model="showEditor"
                :plan="selectedPlan"
                :saving="saving"
                @submit="handleSave"
                @close="showEditor = false"
            />
        </q-dialog>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from "vue";
import { useI18n } from 'vue-i18n';
import axios from "axios";
import WeekSelector from "@/Pages/my_table_mnger/weekly_system/components/weekly-plans/WeekSelector.vue";
import WeeklyPlanEditor from "@/Pages/my_table_mnger/weekly_system/teacher/WeeklyPlanEditor.vue";
import StatusBadge from "@/Pages/my_table_mnger/weekly_system/components/shared/StatusBadge.vue";
import WeeklyPlansTable from "@/Pages/my_table_mnger/weekly_system/teacher/WeeklyPlansTable.vue";

const { t } = useI18n();

// Define reactive properties
const editMode = ref(false);
const weekNumber = ref(1);
const semesterNumber = ref(1); // default semester
const maxWeeks = ref(18);
const currentWeek = ref(1);
const weeklyPlans = ref([]);
const copyingPlanId = ref(null);
const copiedData = ref(null);
const saving = ref(false);
const selectedPlan = ref(null);
const showEditor = ref(false);
const activeTab = ref('table');

// Function to get day name from translation
const getDayName = (dayNumber) => {
    const translationKey = `weeklyPlans.fullDays.${dayNumber}`;
    const translated = t(translationKey);
    // If translation not found, return default day name
    return translated !== translationKey ? translated : `Day ${dayNumber}`;
};

// Function to load weekly plans from the backend (real API)
async function loadWeeklyPlans() {
    try {
        const { data } = await axios.get(
            "/weekly-system/api/teacher/my-weekly-plans",
            {
                params: {
                    week_number: weekNumber.value,
                    semester_number: semesterNumber.value,
                },
            }
        );

        const flatPlans = Array.isArray(data?.data)
            ? data.data
            : Array.isArray(data)
            ? data
            : [];

        // Group by day number and sort by period
        const grouped = {};
        for (const p of flatPlans) {
            const dayNum = p?.schedule?.day ?? 0;
            if (!grouped[dayNum]) {
                grouped[dayNum] = {
                    dayNumber: dayNum,
                    dayName: getDayName(dayNum),
                    plans: [],
                };
            }
            grouped[dayNum].plans.push({
                id: p.id,
                data: p,
                isEditing: false,
                saving: false,
            });
        }

        // Sort plans within each day by period number
        Object.values(grouped).forEach((d) => {
            d.plans.sort(
                (a, b) =>
                    (a.data?.schedule?.period_number || 0) -
                    (b.data?.schedule?.period_number || 0)
            );
        });

        // Assign grouped and sorted array to weeklyPlans
        weeklyPlans.value = Object.values(grouped).sort(
            (a, b) => a.dayNumber - b.dayNumber
        );
    } catch (error) {
        console.error("Error loading weekly plans:", error);
    }
}

// Navigate to previous week
function prevWeek() {
    if (weekNumber.value > 1) {
        weekNumber.value--;
        loadWeeklyPlans();
    }
}

// Navigate to next week
function nextWeek() {
    if (weekNumber.value < maxWeeks.value) {
        weekNumber.value++;
        loadWeeklyPlans();
    }
}

// Watch for weekNumber changes and load plans when week is selected
watch(
    () => weekNumber.value,
    (newWeek) => {
        if (newWeek) {
            loadWeeklyPlans();
        }
    }
);

onMounted(() => {
    // Compute current week number (approximate ISO-like)
    const now = new Date();
    const startOfYear = new Date(now.getFullYear(), 0, 1);
    const daysSince = Math.floor((now - startOfYear) / (1000 * 60 * 60 * 24));
    const current = Math.ceil((daysSince + startOfYear.getDay() + 1) / 7);
    currentWeek.value = Math.min(current, maxWeeks.value);
    weekNumber.value = currentWeek.value;
    loadWeeklyPlans();
});

// Computed properties
const sortedPlansByDay = computed(() => {
    // This is a placeholder - implement actual sorting logic
    return weeklyPlans.value;
});

const completionPercentage = computed(() => {
    // Calculate based on actual data
    if (weeklyPlans.value.length === 0) return 0;

    const totalPlans = weeklyPlans.value.reduce(
        (total, day) => total + day.plans.length,
        0
    );
    if (totalPlans === 0) return 0;

    const completedPlans = weeklyPlans.value.reduce((count, day) => {
        const dayCompleted = day.plans.filter(
            (plan) => plan.data?.status === "completed"
        ).length;
        return count + dayCompleted;
    }, 0);

    return Math.round((completedPlans / totalPlans) * 100);
});

const progressColor = computed(() => {
    // Determine color based on completion percentage
    const percentage = completionPercentage.value;
    if (percentage < 30) return "negative";
    if (percentage < 70) return "warning";
    return "positive";
});

// Flatten plans for table view
const flatPlans = computed(() =>
    weeklyPlans.value.flatMap((d) => d.plans.map((p) => p.data))
);

// ============== Edit from Cards/Table (shared) ==============
const openEditor = (row) => {
    selectedPlan.value = row;
    if (selectedPlan.value) {
        showEditor.value = true;
    }
};

const editPlan = (item) => {
    // item from cards view contains { id, data }
    selectedPlan.value = item?.data ?? null;
    if (selectedPlan.value) {
        showEditor.value = true;
    }
};

const handleSave = async (payload) => {
    if (!selectedPlan.value?.id) return;
    try {
        saving.value = true;
        // 1) Update schedule.period_order if changed
        const newOrder = Number(payload?.schedule?.period_order);
        const oldOrder = Number(selectedPlan.value?.schedule?.period_order);
        const scheduleId = selectedPlan.value?.schedule?.id;
        if (scheduleId && Number.isFinite(newOrder) && newOrder > 0 && newOrder !== oldOrder) {
            await axios.put(`/weekly-system/api/schedules/${scheduleId}/period-order`, {
                period_order: newOrder
            });
        }
        await axios.put(`/weekly-system/api/weekly-plans/${selectedPlan.value.id}`, {
            cw: payload?.cw ?? '',
            hw: payload?.hw ?? '',
            notes: payload?.notes ?? ''
        });
        showEditor.value = false;
        await loadWeeklyPlans();
    } catch (e) {
        console.error('Failed to save weekly plan', e);
    } finally {
        saving.value = false;
    }
};

// Optional copy/paste helpers so buttons don't break
const copyPlan = (item) => {
    copyingPlanId.value = item?.id ?? null;
    copiedData.value = {
        cw: item?.data?.cw || '',
        hw: item?.data?.hw || '',
        notes: item?.data?.notes || ''
    };
};

const pastePlan = async (item) => {
    if (!copiedData.value) return;
    selectedPlan.value = item?.data ?? null;
    if (!selectedPlan.value?.id) return;
    await handleSave({
        cw: copiedData.value.cw,
        hw: copiedData.value.hw,
        notes: copiedData.value.notes
    });
};

const revertPlan = () => {
    // No-op placeholder for now
};

const savePlanField = async (item) => {
    // Save all fields for the given item
    if (!item?.data) return;
    selectedPlan.value = item.data;
    await handleSave(item.data);
};
</script>

<style scoped>
.weekly-plans-grid {
    max-width: 1200px;
}

.day-section {
    border-inline-start: 4px solid #1976d2;
    padding-inline-start: 16px;
}

.plan-card {
    transition: all 0.2s ease-in-out;
    min-width: 300px;
    max-width: 300px;
    height: 100%;
}

.plan-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.copied {
    border-inline-start: 4px solid #1976d2;
}

.plan-header {
    border-bottom: 1px solid #f1f1f1;
}

.plan-content {
    min-height: 150px;
}

.plan-section {
    min-height: 60px;
}

.plan-label {
    font-size: 0.8em;
    font-weight: 500;
    color: #666;
    margin-bottom: 4px;
}

.plan-content-text {
    font-size: 0.9em;
    line-height: 1.5;
}

.plan-content-text.empty {
    color: #999;
}

.plan-actions {
    border-top: 1px solid #f1f1f1;
}

/* Responsive adjustments */
@media (max-width: 1024px) {
    .plan-card {
        max-width: 100%;
    }
}

.editable-input {
    width: 100%;
    margin-top: 4px;
    margin-bottom: 4px;
}
</style>
