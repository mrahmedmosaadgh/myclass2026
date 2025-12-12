<template>
    <div class="image-marker-container">
        <!-- Controls -->
        <div class="mb-4 flex gap-4 items-center">
            <div class="flex items-center gap-2">
                <label class="font-medium">Marking Mode:</label>
                <select 
                    v-model="currentMode" 
                    class="border rounded px-2 py-1"
                >
                    <option value="point">Point</option>
                    <option value="polygon">Polygon</option>
                </select>
            </div>
            
            <button 
                v-if="currentMode === 'polygon' && isDrawingPolygon"
                @click="completePolygon"
                class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600"
            >
                Complete Polygon
            </button>
        </div>

        <!-- Image Container -->
        <div class="relative border border-gray-300" ref="imageContainer">
            <img 
                :src="imageUrl" 
                @click="handleClick"
                :class="{
                    'cursor-crosshair': currentMode === 'point',
                    'cursor-pointer': currentMode === 'polygon'
                }"
                class="max-w-full"
                ref="image"
                @load="onImageLoad"
            >
            
            <!-- Points -->
            <div 
                v-for="(point, index) in points" 
                :key="`point-${index}`"
                class="absolute w-6 h-6 -ml-3 -mt-3 flex items-center justify-center"
                :style="{
                    left: point.x + 'px',
                    top: point.y + 'px'
                }"
            >
                <div class="relative group">
                    <div class="w-6 h-6 bg-blue-500 rounded-full flex items-center justify-center text-white text-sm">
                        {{ index + 1 }}
                    </div>
                    <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 bg-black text-white px-2 py-1 rounded text-sm whitespace-nowrap opacity-0 group-hover:opacity-100">
                        {{ point.label }}
                    </div>
                </div>
            </div>

            <!-- Polygons -->
            <div 
                v-for="(polygon, polyIndex) in polygons" 
                :key="`polygon-${polyIndex}`"
                class="absolute top-0 left-0 w-full h-full"
            >
                <!-- Polygon SVG -->
                <svg class="absolute top-0 left-0 w-full h-full pointer-events-none">
                    <polygon
                        :points="getPolygonPoints(polygon.points)"
                        :fill="polygon.color"
                        fill-opacity="0.2"
                        :stroke="polygon.color"
                        stroke-width="2"
                    />
                </svg>

                <!-- Polygon Vertices -->
                <div 
                    v-for="(point, pointIndex) in polygon.points" 
                    :key="`poly-point-${polyIndex}-${pointIndex}`"
                    class="absolute w-4 h-4 -ml-2 -mt-2"
                    :style="{
                        left: point.x + 'px',
                        top: point.y + 'px'
                    }"
                >
                    <div class="w-4 h-4 bg-green-500 rounded-full"></div>
                </div>
            </div>

            <!-- Active Polygon Drawing -->
            <svg 
                v-if="isDrawingPolygon" 
                class="absolute top-0 left-0 w-full h-full pointer-events-none"
            >
                <polygon
                    :points="getPolygonPoints(currentPolygonPoints)"
                    fill="rgba(0, 255, 0, 0.2)"
                    stroke="green"
                    stroke-width="2"
                />
            </svg>
        </div>

        <!-- Points List -->
        <div class="mt-4 space-y-4">
            <!-- Individual Points -->
            <div v-if="points.length > 0">
                <h3 class="font-medium mb-2">Points</h3>
                <div v-for="(point, index) in points" :key="`point-list-${index}`" class="flex items-center gap-4 mb-2">
                    <span class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center text-white">
                        {{ index + 1 }}
                    </span>
                    <input 
                        type="text" 
                        v-model="point.label"
                        class="border rounded px-2 py-1"
                        :placeholder="'Point ' + (index + 1)"
                    >
                    <button 
                        @click="removePoint(index)"
                        class="text-red-500 hover:text-red-700"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Polygons List -->
            <div v-if="polygons.length > 0">
                <h3 class="font-medium mb-2">Polygons</h3>
                <div v-for="(polygon, index) in polygons" :key="`polygon-list-${index}`" class="flex items-center gap-4 mb-2">
                    <span class="w-8 h-8 rounded-full flex items-center justify-center text-white"
                          :style="{ backgroundColor: polygon.color }">
                        {{ index + 1 }}
                    </span>
                    <input 
                        type="text" 
                        v-model="polygon.label"
                        class="border rounded px-2 py-1"
                        :placeholder="'Polygon ' + (index + 1)"
                    >
                    <input 
                        type="color" 
                        v-model="polygon.color"
                        class="w-8 h-8 rounded"
                    >
                    <button 
                        @click="removePolygon(index)"
                        class="text-red-500 hover:text-red-700"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Controls -->
        <div class="mt-4 space-x-2">
            <button 
                @click="clearAll"
                class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600"
            >
                Clear All
            </button>
            <button 
                @click="exportData"
                class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600"
            >
                Export Data
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
    imageUrl: {
        type: String,
        required: true
    },
    initialPoints: {
        type: Array,
        default: () => []
    },
    initialPolygons: {
        type: Array,
        default: () => []
    }
});

const emit = defineEmits(['update:data', 'export']);

const currentMode = ref('point');
const points = ref(props.initialPoints);
const polygons = ref(props.initialPolygons);
const imageContainer = ref(null);
const image = ref(null);

// Polygon drawing state
const isDrawingPolygon = ref(false);
const currentPolygonPoints = ref([]);

const handleClick = (event) => {
    const rect = imageContainer.value.getBoundingClientRect();
    const x = event.clientX - rect.left;
    const y = event.clientY - rect.top;

    if (currentMode.value === 'point') {
        addPoint(x, y);
    } else if (currentMode.value === 'polygon') {
        addPolygonPoint(x, y);
    }
};

const addPoint = (x, y) => {
    points.value.push({
        x,
        y,
        label: `Point ${points.value.length + 1}`
    });
    emitUpdate();
};

const addPolygonPoint = (x, y) => {
    if (!isDrawingPolygon.value) {
        isDrawingPolygon.value = true;
        currentPolygonPoints.value = [];
    }
    
    currentPolygonPoints.value.push({ x, y });
};

const completePolygon = () => {
    if (currentPolygonPoints.value.length >= 3) {
        polygons.value.push({
            points: [...currentPolygonPoints.value],
            label: `Polygon ${polygons.value.length + 1}`,
            color: getRandomColor()
        });
        emitUpdate();
    }
    
    isDrawingPolygon.value = false;
    currentPolygonPoints.value = [];
};

const getPolygonPoints = (polyPoints) => {
    return polyPoints.map(p => `${p.x},${p.y}`).join(' ');
};

const removePoint = (index) => {
    points.value.splice(index, 1);
    emitUpdate();
};

const removePolygon = (index) => {
    polygons.value.splice(index, 1);
    emitUpdate();
};

const clearAll = () => {
    points.value = [];
    polygons.value = [];
    isDrawingPolygon.value = false;
    currentPolygonPoints.value = [];
    emitUpdate();
};

const exportData = () => {
    const data = {
        points: points.value.map((point, index) => ({
            id: index + 1,
            label: point.label,
            coordinates: { x: point.x, y: point.y }
        })),
        polygons: polygons.value.map((polygon, index) => ({
            id: index + 1,
            label: polygon.label,
            color: polygon.color,
            vertices: polygon.points
        }))
    };
    
    emit('export', data);
};

const emitUpdate = () => {
    emit('update:data', {
        points: points.value,
        polygons: polygons.value
    });
};

const getRandomColor = () => {
    const letters = '0123456789ABCDEF';
    let color = '#';
    for (let i = 0; i < 6; i++) {
        color += letters[Math.floor(Math.random() * 16)];
    }
    return color;
};

const onImageLoad = () => {
    console.log('Image loaded');
};
</script>

<style scoped>
.image-marker-container {
    user-select: none;
}
</style>