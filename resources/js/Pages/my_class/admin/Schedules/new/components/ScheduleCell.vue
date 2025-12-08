<template>
    <div
        class="relative w-full h-full flex flex-col items-center justify-center cursor-pointer"
        @click="dialog = true"
    >
        <q-menu
            touch-position
            context-menu
            >
            <!-- transition-show="flip-right"
            transition-hide="flip-left" -->
            <q-list dense style="min-width: 100px">
                <q-item  >
                    <q-item-section>
                        <div


                            color="primary"
                            @click="confirmUnLinkPeriod"
                        >
                            <div class="p-2"></div>
                            <q-badge color="red">
                                {{ cellData[props.period_code]?.classroom }}
                            </q-badge>
                            : {{ cellData?.[period_code]?.subject }} :
                            {{ cellData?.teacher?.name }}
                        </div>
                    </q-item-section>
                </q-item>
                <q-item clickable v-close-popup>
                    <q-item-section>
                        <q-btn

                            v-if="cellData[props.period_code]?.classroom"
                            color="primary"
                            @click="confirmUnLinkPeriod"
                        >Unlink Period

                        </q-btn>
                    </q-item-section>
                </q-item>
                <q-separator />
                <q-item clickable>
                    <q-item-section>Link with</q-item-section>
                    <q-item-section side>
                        <q-icon name="keyboard_arrow_right" />
                    </q-item-section>

                    <q-menu anchor="top end" self="top start">

                            <q-item
                 v-for="emptyPeriod in cellData?.period_code_empty"
                            :key="emptyPeriod.id"
                            @click="emitLinkPeriod(emptyPeriod)"

                              clickable>
                                <q-item-section>


                                                <q-badge
                                                    class="text-sm relative"
                                                    color="primary"
                                                >
                                                    {{ emptyPeriod?.classroom }}

                                                    <q-badge
                                                        v-if="
                                                            getConflictCountForClassroom(
                                                                emptyPeriod?.classroom
                                                            )?.length > 0
                                                        "
                                                        color="negative"
                                                        rounded
                                                        class="text-xs absolute -top-4 right-4"
                                                        :label="
                                                            getConflictCountForClassroom(
                                                                emptyPeriod?.classroom
                                                            )?.length
                                                        "
                                                    />
                                                </q-badge>

                                                <span
                                                    class="text-sm text-gray-700"
                                                    >{{
                                                        emptyPeriod?.subject
                                                    }}</span
                                                >

                                                <!-- Tooltip with Conflict Details -->
                                                <q-tooltip
                                                    v-if="
                                                        getConflictCountForClassroom(
                                                            emptyPeriod?.classroom
                                                        )?.length > 0
                                                    "
                                                    anchor="top middle"
                                                    self="bottom middle"
                                                    :delay="100"
                                                >
                                                    <div class="q-pa-sm">
                                                        <div
                                                            class="text-sm font-medium mb-1"
                                                        >
                                                            Conflicts for this
                                                            classroom:
                                                        </div>
                                                        <q-table
                                                            :rows="
                                                                getConflictCountForClassroom(
                                                                    emptyPeriod?.classroom
                                                                )
                                                            "
                                                            :columns="[
                                                                {
                                                                    name: 'teacher',
                                                                    label: 'Teacher',
                                                                    field: 'teacher',
                                                                    align: 'left',
                                                                },
                                                                {
                                                                    name: 'classroom',
                                                                    label: 'Classroom',
                                                                    field: 'classroom',
                                                                    align: 'left',
                                                                },
                                                                {
                                                                    name: 'subject',
                                                                    label: 'Subject',
                                                                    field: 'subject',
                                                                    align: 'left',
                                                                },
                                                            ]"
                                                            row-key="teacher"

                                                        />
                                                    </div>
                                                </q-tooltip>


                                </q-item-section>
                                <q-item-section side>
                                    <q-icon name="keyboard_arrow_right" />
                                </q-item-section>
                            </q-item>

                    </q-menu>
                </q-item>
                <q-separator />
                <q-item clickable v-close-popup>
                    <q-item-section>Quit</q-item-section>
                </q-item>
            </q-list>
        </q-menu>

        <q-dialog v-model="dialog" position="bottom">
            <q-card class="relative">
                <!-- <q-linear-progress :value="0.6" color="pink" /> -->

                <!-- <q-card-section class="row items-center no-wrap"> -->

                <!-- <q-btn class="bg-blue w-12 h-4 m-auto" flat round icon="close" v-close-popup /> -->

                <!-- <q-space /> -->

                <!-- <q-scroll-area class="fit"> -->
                <!-- <q-card class="min-w-[650px]  "> -->
                <q-card-section
                    class="text-lg font-semibold text-center text-primary"
                >
                    Available Periods
                </q-card-section>

                <q-separator />

                <q-card-section class="q-pa-none">
                    <div class="flex p-1 flex-row justify-center gap-2">
                        <div
                            class="p-0"
                            v-for="emptyPeriod in cellData?.period_code_empty"
                            :key="emptyPeriod.id"
                        >
                            <q-btn-dropdown
                                v-if="
                                    getConflictCountForClassroom(
                                        emptyPeriod?.classroom
                                    )?.length > 0
                                "
                                class="min-w-fit"
                                :disable-dropdown="
                                    getConflictCountForClassroom(
                                        emptyPeriod?.classroom
                                    )?.length == 0
                                "
                                split
                                :color="
                                    getConflictCountForClassroom(
                                        emptyPeriod?.classroom
                                    )?.length > 0
                                        ? 'red'
                                        : 'blue'
                                "
                                push
                                no-caps
                                @click="emitLinkPeriod(emptyPeriod)"
                            >
                                <template v-slot:label>
                                    <div class="row items-center no-wrap">
                                        <!-- <q-icon left name="map" /> -->
                                        <div class="text-center">
                                            <div
                                                class="flex flex-col justify-start gap-2"
                                            >
                                                <q-badge
                                                    class="text-sm relative"
                                                    color="primary"
                                                >
                                                    {{ emptyPeriod?.classroom }}

                                                    <q-badge
                                                        v-if="
                                                            getConflictCountForClassroom(
                                                                emptyPeriod?.classroom
                                                            )?.length > 0
                                                        "
                                                        color="negative"
                                                        rounded
                                                        class="text-xs absolute -top-4 right-4"
                                                        :label="
                                                            getConflictCountForClassroom(
                                                                emptyPeriod?.classroom
                                                            )?.length
                                                        "
                                                    />
                                                </q-badge>

                                                <span
                                                    class="text-sm text-gray-700"
                                                    >{{
                                                        emptyPeriod?.subject
                                                    }}</span
                                                >

                                                <!-- Tooltip with Conflict Details -->
                                                <q-tooltip
                                                    v-if="
                                                        getConflictCountForClassroom(
                                                            emptyPeriod?.classroom
                                                        )?.length > 0
                                                    "
                                                    anchor="top middle"
                                                    self="bottom middle"
                                                    :delay="100"
                                                >
                                                    <div class="q-pa-sm">
                                                        <div
                                                            class="text-sm font-medium mb-1"
                                                        >
                                                            Conflicts for this
                                                            classroom:
                                                        </div>
                                                        <q-table
                                                            :rows="
                                                                getConflictCountForClassroom(
                                                                    emptyPeriod?.classroom
                                                                )
                                                            "
                                                            :columns="[
                                                                {
                                                                    name: 'teacher',
                                                                    label: 'Teacher',
                                                                    field: 'teacher',
                                                                    align: 'left',
                                                                },
                                                                {
                                                                    name: 'classroom',
                                                                    label: 'Classroom',
                                                                    field: 'classroom',
                                                                    align: 'left',
                                                                },
                                                                {
                                                                    name: 'subject',
                                                                    label: 'Subject',
                                                                    field: 'subject',
                                                                    align: 'left',
                                                                },
                                                            ]"
                                                            row-key="teacher"
                                                            hide-pagination
                                                            dense
                                                        />
                                                    </div>
                                                </q-tooltip>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                                <q-list>
                                    <q-item
                                        clickable
                                        v-close-popup
                                        @click="onItemClick"
                                    >
                                        <q-item-section>
                                            <q-item-label caption
                                                >Conflicts for this
                                                classroom:</q-item-label
                                            >
                                            <q-item-label
                                                ><q-table
                                                    :rows="
                                                        getConflictCountForClassroom(
                                                            emptyPeriod?.classroom
                                                        )
                                                    "
                                                    :columns="[
                                                        {
                                                            name: 'teacher',
                                                            label: 'Teacher',
                                                            field: 'teacher',
                                                            align: 'left',
                                                        },
                                                        {
                                                            name: 'classroom',
                                                            label: 'Classroom',
                                                            field: 'classroom',
                                                            align: 'left',
                                                        },
                                                        {
                                                            name: 'subject',
                                                            label: 'Subject',
                                                            field: 'subject',
                                                            align: 'left',
                                                        },
                                                    ]"
                                                    row-key="teacher"
                                                    hide-pagination
                                                    dense
                                            /></q-item-label>
                                        </q-item-section>
                                    </q-item>
                                </q-list>
                            </q-btn-dropdown>

                            <q-btn
                                v-else
                                class="min-w-fit text-yellow-200"
                                color="light-blue"
                                push
                                no-caps
                                @click="emitLinkPeriod(emptyPeriod)"
                            >
                                <div class="row items-center no-wrap">
                                    <!-- <q-icon left name="map" /> -->
                                    <div class="text-center">
                                        <div
                                            class="flex flex-col justify-start gap-2"
                                        >
                                            <div
                                                class="text-sm px-1"
                                                :style="`background-color:  ${emptyPeriod?.c_bg};color: ${emptyPeriod?.c_text};`"
                                            >
                                                <q-badge
                                                    class="text-sm relative"
                                                    color="primary"
                                                >
                                                    {{ emptyPeriod?.classroom }}
                                                </q-badge>

                                                {{ emptyPeriod?.subject }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </q-btn>
                        </div>

                        <!-- <q-btn
                v-for="emptyPeriod in cellData?.period_code_empty"
                :key="emptyPeriod.id"
                @click="emitLinkPeriod(emptyPeriod)"
                clickable
                v-close-popup
              >
                <q-item-section>
                  <div class="flex items-center gap-2">
                    <q-badge class="text-sm" color="primary" outline>
                      {{ emptyPeriod?.classroom }}
                    </q-badge>

                    <span class="text-sm text-gray-700">{{ emptyPeriod?.subject }}</span>

                    <q-badge
                      v-if="getConflictCountForClassroom(emptyPeriod?.classroom)?.length > 0"
                      color="negative"
                      rounded
                      class="text-xs"
                      :label="getConflictCountForClassroom(emptyPeriod?.classroom)?.length"
                    />

                    Tool tip with Conflict Details
                    <q-tooltip
                      v-if="getConflictCountForClassroom(emptyPeriod?.classroom)?.length > 0"
                      anchor="top middle"
                      self="bottom middle"
                      :delay="100"
                    >
                      <div class="q-pa-sm">
                        <div class="text-sm font-medium mb-1 text-red-700">
                          Conflicts for this classroom:
                        </div>
                        <q-table
                          :rows="getConflictCountForClassroom(emptyPeriod?.classroom)"
                          :columns="[
                            { name: 'teacher', label: 'Teacher', field: 'teacher', align: 'left' },
                            { name: 'classroom', label: 'Classroom', field: 'classroom', align: 'left' },
                            { name: 'subject', label: 'Subject', field: 'subject', align: 'left' }
                          ]"
                          row-key="teacher"
                          hide-pagination
                          dense
                        />
                      </div>
                    </q-tooltip>
                  </div>
                </q-item-section>
              </q-btn> -->
                    </div>
                </q-card-section>

                <q-separator />

                <q-card-actions align="right">
                    <q-btn
                        label="Unlink Period"
                        v-if="cellData[props.period_code]?.classroom"
                        color="primary"
                        @click="confirmUnLinkPeriod"
                    />
                </q-card-actions>
                <!-- </q-card> -->
                <!-- </q-scroll-area> -->
                <!-- <q-btn flat round icon="play_arrow" />
          <q-btn flat round icon="pause" /> -->
                <!-- </q-card-section> -->
            </q-card>
        </q-dialog>
        <!-- Action Button -->
        <!-- <q-btn
        v-if="teacher_data"
        icon="more_vert"
        size="sm"
        flat
        round
        dense
        class="absolute top-1 right-1 z-30"
        @click="dialog = true"
      /> -->

        <!-- Dialog -->
        <q-dialog transition-show="scale" transition-hide="scale">
            <q-card class="min-w-[650px]">
                <q-card-section
                    class="text-lg font-semibold text-center text-primary"
                >
                    Available Periods
                </q-card-section>

                <q-separator />

                <q-card-section class="q-pa-none">
                    <div class="flex flex-row justify-center gap-2">
                        <q-btn-dropdown
                            :disable-dropdown="
                                getConflictCountForClassroom(
                                    emptyPeriod?.classroom
                                )?.length == 0
                            "
                            split
                            :color="
                                getConflictCountForClassroom(
                                    emptyPeriod?.classroom
                                )?.length > 0
                                    ? 'red'
                                    : 'blue'
                            "
                            push
                            no-caps
                            v-for="emptyPeriod in cellData?.period_code_empty"
                            :key="emptyPeriod.id"
                            @click="emitLinkPeriod(emptyPeriod)"
                        >
                            <template v-slot:label>
                                <div class="row items-center no-wrap">
                                    <!-- <q-icon left name="map" /> -->
                                    <div class="text-center">
                                        <div
                                            class="flex flex-col justify-start gap-2"
                                        >
                                            <q-badge
                                                class="text-sm relative"
                                                color="primary"
                                            >
                                                {{ emptyPeriod?.classroom }}

                                                <q-badge
                                                    v-if="
                                                        getConflictCountForClassroom(
                                                            emptyPeriod?.classroom
                                                        )?.length > 0
                                                    "
                                                    color="negative"
                                                    rounded
                                                    class="text-xs absolute -top-4 right-4"
                                                    :label="
                                                        getConflictCountForClassroom(
                                                            emptyPeriod?.classroom
                                                        )?.length
                                                    "
                                                />
                                            </q-badge>

                                            <span
                                                class="text-sm text-gray-700"
                                                >{{
                                                    emptyPeriod?.subject
                                                }}</span
                                            >

                                            <!-- Tooltip with Conflict Details -->
                                            <q-tooltip
                                                v-if="
                                                    getConflictCountForClassroom(
                                                        emptyPeriod?.classroom
                                                    )?.length > 0
                                                "
                                                anchor="top middle"
                                                self="bottom middle"
                                                :delay="100"
                                            >
                                                <div class="q-pa-sm">
                                                    <div
                                                        class="text-sm font-medium mb-1"
                                                    >
                                                        Conflicts for this
                                                        classroom:
                                                    </div>
                                                    <q-table
                                                        :rows="
                                                            getConflictCountForClassroom(
                                                                emptyPeriod?.classroom
                                                            )
                                                        "
                                                        :columns="[
                                                            {
                                                                name: 'teacher',
                                                                label: 'Teacher',
                                                                field: 'teacher',
                                                                align: 'left',
                                                            },
                                                            {
                                                                name: 'classroom',
                                                                label: 'Classroom',
                                                                field: 'classroom',
                                                                align: 'left',
                                                            },
                                                            {
                                                                name: 'subject',
                                                                label: 'Subject',
                                                                field: 'subject',
                                                                align: 'left',
                                                            },
                                                        ]"
                                                        row-key="teacher"
                                                        hide-pagination
                                                        dense
                                                    />
                                                </div>
                                            </q-tooltip>
                                        </div>
                                    </div>
                                </div>
                            </template>
                            <q-list>
                                <q-item
                                    clickable
                                    v-close-popup
                                    @click="onItemClick"
                                >
                                    <q-item-section>
                                        <q-item-label caption
                                            >Conflicts for this
                                            classroom:</q-item-label
                                        >
                                        <q-item-label
                                            ><q-table
                                                :rows="
                                                    getConflictCountForClassroom(
                                                        emptyPeriod?.classroom
                                                    )
                                                "
                                                :columns="[
                                                    {
                                                        name: 'teacher',
                                                        label: 'Teacher',
                                                        field: 'teacher',
                                                        align: 'left',
                                                    },
                                                    {
                                                        name: 'classroom',
                                                        label: 'Classroom',
                                                        field: 'classroom',
                                                        align: 'left',
                                                    },
                                                    {
                                                        name: 'subject',
                                                        label: 'Subject',
                                                        field: 'subject',
                                                        align: 'left',
                                                    },
                                                ]"
                                                row-key="teacher"
                                                hide-pagination
                                                dense
                                        /></q-item-label>
                                    </q-item-section>
                                </q-item>
                            </q-list>
                        </q-btn-dropdown>

                        <!-- <q-btn
                v-for="emptyPeriod in cellData?.period_code_empty"
                :key="emptyPeriod.id"
                @click="emitLinkPeriod(emptyPeriod)"
                clickable
                v-close-popup
              >
                <q-item-section>
                  <div class="flex items-center gap-2">
                    <q-badge class="text-sm" color="primary" outline>
                      {{ emptyPeriod?.classroom }}
                    </q-badge>

                    <span class="text-sm text-gray-700">{{ emptyPeriod?.subject }}</span>

                    <q-badge
                      v-if="getConflictCountForClassroom(emptyPeriod?.classroom)?.length > 0"
                      color="negative"
                      rounded
                      class="text-xs"
                      :label="getConflictCountForClassroom(emptyPeriod?.classroom)?.length"
                    />

                    Tool tip with Conflict Details
                    <q-tooltip
                      v-if="getConflictCountForClassroom(emptyPeriod?.classroom)?.length > 0"
                      anchor="top middle"
                      self="bottom middle"
                      :delay="100"
                    >
                      <div class="q-pa-sm">
                        <div class="text-sm font-medium mb-1 text-red-700">
                          Conflicts for this classroom:
                        </div>
                        <q-table
                          :rows="getConflictCountForClassroom(emptyPeriod?.classroom)"
                          :columns="[
                            { name: 'teacher', label: 'Teacher', field: 'teacher', align: 'left' },
                            { name: 'classroom', label: 'Classroom', field: 'classroom', align: 'left' },
                            { name: 'subject', label: 'Subject', field: 'subject', align: 'left' }
                          ]"
                          row-key="teacher"
                          hide-pagination
                          dense
                        />
                      </div>
                    </q-tooltip>
                  </div>
                </q-item-section>
              </q-btn> -->
                    </div>
                </q-card-section>

                <q-separator />

                <q-card-actions align="right">
                    <q-btn
                        label="Unlink Period"
                        color="primary"
                        @click="confirmUnLinkPeriod"
                    />
                </q-card-actions>
            </q-card>
        </q-dialog>

        <!-- Selection Effects -->
        <div class="absolute inset-0 pointer-events-none cursor-pointer z-10 scale-150   ">
            <div
                :class="
                    isSelected
                        ? '  shadow-none rounded-none h-16    opacity-100'
                        : 'opacity-0'
                "
                class="absolute inset-0 rounded transition-opacity duration-200"
            >
                <div class="w-full h-full bg-gray-200 opacity-40"></div>
            </div>
            <div
                :class="isHovered ? 'bg-gray-400 opacity-20' : 'opacity-0'"
                class="absolute inset-0 rounded duration-200 pointer-events-none"
            ></div>
        </div>

        <!-- Main Content -->

        <div
            class="relative z-20 flex flex-row items-center w-full h-full text-xs text-center gap-1 px-2"
        >
            <div
                v-if="cellData[props.period_code]?.classroom"
                class="flex flex-row items-center"
                :class="cellData[props.period_code]?.classroom ? 'blue' : ' '"
            >
                <div class="rounded bg-black text-yellow-100">
                    <div class="p-0 flex flex-row items-center gap-1">
                        <div
                            class="text-xs scale-75 rounded bg-black text-yellow-100 px-1"
                        >
                            <div>
                                <q-badge
                                    v-if="
                                        cellData[props.period_code]
                                            ?.conflict_count > 0
                                    "
                                    color="red"
                                    text-color="white"
                                    >{{
                                        cellData[props.period_code]?.classroom
                                    }}
                                    <q-badge class="mt-4" color="red" floating
                                        >{{
                                            cellData[props.period_code]
                                                ?.conflict_count
                                        }}
                                    </q-badge>
                                </q-badge>

                                <div
                                    class="p-0"
                                    v-if="
                                        cellData[props.period_code]
                                            ?.conflict_count == 0
                                    "
                                >
                                    {{ cellData[props.period_code]?.classroom }}
                                </div>
                            </div>
                        </div>

                        <span class="relative px-1">
                            {{ cellData?.[period_code]?.subject_cute }}
                            <q-tooltip
                                v-if="cellData?.[period_code]?.subject"
                                anchor="top middle"
                                self="bottom middle"
                                :delay="300"
                            >
                                {{ cellData?.[period_code]?.subject }}
                            </q-tooltip>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, inject, ref } from "vue";
import { useQuasar } from "quasar";
import axios from "axios"; // Import axios
import GridControls from "./GridControls.vue"; // Adjust path if needed
import NProgress from "nprogress";
const props = defineProps({
    teacher_data: {
        // Renamed from teacher for clarity
        type: Object,
        required: true,
    },

    dayIndex: {
        type: Number,
        required: true,
    },
    period_code: {
        type: String,
        required: true,
    },
    periodIndex: {
        type: Number,
        required: true,
    },
    selectedPeriod: {
        type: Object,
        required: true,
    },
    hoverPeriod: {
        type: Object,
        required: true,
    },
    isLocked: {
        type: Boolean,
        default: false,
    },
    isDisabled: {
        type: Boolean,
        default: false,
    },
    is_confirm: {
        type: Boolean,
        default: false,
    },

    conflicts: {
        type: Array, // Conflicts should be an array
        required: false,
    },
});

const emit = defineEmits([
    "toggle-lock",
    "toggle-disable",
    "link-period",
    "update_grid",
]);
const $q = useQuasar(); // Initialize Quasar

// Inject days and periods count from parent if possible, otherwise use reasonable defaults or pass as props
// Assuming parent provides these for calculating empty periods
const days = inject("days", ["Sun", "Mon", "Tue", "Wed", "Thu"]); // Example injection
const periodsCount = inject("periodsCount", 8); // Example injection
const dialog = ref(false); // Example injection

// const currentCellCode = computed(() => `d${props.dayIndex + 1}p${props.periodIndex + 1}`);
const cellData = computed(() => props.teacher_data);

const isSelected = computed(
    () =>
        props.selectedPeriod.day === props.dayIndex &&
        props.selectedPeriod.period === props.periodIndex
);
const isHovered = computed(
    () =>
        props.hoverPeriod.day === props.dayIndex &&
        props.hoverPeriod.period === props.periodIndex
);

const getDayName = (index) => days[index] || `Day ${index + 1}`;
const getPeriodName = (index) => `P${index + 1}`;

// Calculate empty periods for the current teacher
const emptyPeriods = computed(() => {
    const empty = [];
    if (!props.teacher_data) return empty;

    for (let d = 0; d < days.length; d++) {
        for (let p = 0; p < periodsCount; p++) {
            const code = `d${d + 1}p${p + 1}`;
            // Check if the period exists and is considered empty (null, undefined, or no relevant data)
            if (!props.teacher_data[code] && code !== props.period_code) {
                // Exclude the current cell
                empty.push({
                    code: code,
                    label: `${getDayName(d)} ${getPeriodName(p)}`,
                    dayIndex: d,
                    periodIndex: p,
                });
            }
        }
    }
    return empty;
});

// Computed property to count conflicts for the *same classroom* as this cell
const sameClassroomConflictCount = computed(() => {
    const currentClassroom = cellData.value?.[props.period_code]?.classroom;
    if (!currentClassroom || !props.conflicts || props.conflicts.length === 0) {
        return 0; // No classroom in this cell or no conflicts passed
    }

    // Filter the conflicts array to find entries with the same classroom
    return props.conflicts.filter(
        (conflict) => conflict.classroom === currentClassroom
    ).length;
});

// Function to get conflict count for a specific classroom name (used in the dialog)
const getConflictCountForClassroom = (classroomName) => {
    if (!classroomName || !props.conflicts || props.conflicts.length === 0) {
        return 0; // No classroom name provided or no conflicts data
    }

    // Filter the conflicts array to find entries with the specified classroom
    return props.conflicts.filter(
        (conflict) => conflict.classroom === classroomName
    );
};

const emitLinkPeriod = (selectedEmptyPeriod) => {
    if (!props.is_confirm) {
        confirmLinkPeriod(selectedEmptyPeriod);
        return;
    }
    // Show confirmation dialog
    $q.dialog({
        title: "Confirm Period Link",
        message: `Assign "${selectedEmptyPeriod?.subject} / ${selectedEmptyPeriod?.classroom}" to period ${props.period_code}?`,
        cancel: true,
        persistent: true,
    })
        .onOk(() => {
            // User confirmed, proceed with update
            confirmLinkPeriod(selectedEmptyPeriod);
        })
        .onCancel(() => {
            // User cancelled
            gg("Period link cancelled");
        });
};

// Function to handle the actual database update after confirmation
const confirmLinkPeriod = (selectedEmptyPeriod) => {
    if (!selectedEmptyPeriod?.schedule_id) {
        $q.notify({
            type: "negative",
            message: "Error: Missing ID for the selected period.",
        });
        return;
    }

    const scheduleIdToUpdate = selectedEmptyPeriod.schedule_id; // Assuming this ID is the Schedule record ID
    const newPeriodCode = props.period_code;

    NProgress.start(); // Start the progress bar
    // $q.loading.show({ message: 'Updating schedule...' });

    // Define your backend endpoint URL
    const updateUrl = route("admin.schedules.update_period_code", {
        schedule: scheduleIdToUpdate,
    }); // Adjust route name and parameter as needed

    axios
        .patch(updateUrl, {
            // Using PATCH for partial update
            period_code: newPeriodCode,
        })
        .then((response) => {
            NProgress.done();
            dialog.value = false;
            $q.notify({
                type: "positive",
                message:
                    response.data.message || "Schedule updated successfully!",
            });
            // Optionally: Emit an event to the parent to refresh data or update UI locally
            // emit('schedule-updated');
            emit("update_grid");
        })
        .catch((error) => {
            NProgress.done();
            dialog.value = false;
            console.error("Error updating period code:", error);
            // Check for specific 422 conflict error
            if (error.response && error.response.status === 422) {
                $q.notify({
                    type: "warning",
                    message:
                        error.response.data.message ||
                        "Teacher conflict detected.",
                    icon: "warning",
                });
            } else {
                // Generic error message
                $q.notify({
                    type: "negative",
                    message:
                        error.response?.data?.message ||
                        "Failed to update schedule.",
                });
            }
        });
};

const confirmUnLinkPeriod = () => {
    const scheduleIdToUpdate =
        props.teacher_data?.[props.period_code]?.schedule_id; // Assuming this ID is the Schedule record ID
    const newPeriodCode = props.period_code;

    // $q.loading.show({ message: 'Updating schedule...' });
    NProgress.start();
    // Define your backend endpoint URL
    const updateUrl = route("admin.schedules.update_period_code", {
        schedule: scheduleIdToUpdate,
    }); // Adjust route name and parameter as needed

    axios
        .patch(updateUrl, {
            // Using PATCH for partial update
            period_code: null,
        })
        .then((response) => {
            NProgress.done();
            $q.loading.hide();
            dialog.value = false;
            $q.notify({
                type: "positive",
                message:
                    response.data.message || "Schedule updated successfully!",
            });
            // Optionally: Emit an event to the parent to refresh data or update UI locally
            // emit('schedule-updated');
            emit("update_grid");
        })
        .catch((error) => {
            NProgress.done();
            dialog.value = false;
            $q.loading.hide();
            console.error("Error updating period code:", error);
            // Check for specific 422 conflict error (though unlikely for unlink, good practice)
            if (error.response && error.response.status === 422) {
                $q.notify({
                    type: "warning",
                    message:
                        error.response.data.message ||
                        "Conflict detected during unlink.",
                    icon: "warning",
                });
            } else {
                // Generic error message
                $q.notify({
                    type: "negative",
                    message:
                        error.response?.data?.message ||
                        "Failed to update schedule.",
                });
            }
        });
};

const gg = (val, name = "log") => {
    console.log(name, val);
};
</script>

<style scoped>
/* Add any component-specific styles here if needed */
.truncate {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
</style>
