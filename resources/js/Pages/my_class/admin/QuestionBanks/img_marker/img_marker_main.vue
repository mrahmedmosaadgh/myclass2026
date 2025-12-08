<template>
    <div class="p-4">
        <AdvancedImageMarker
            :image-url="'https://www.mathstips.com/wp-content/uploads/2014/01/angle.png'"
            :initial-points="savedPoints"
            :initial-polygons="savedPolygons"
            @update:data="handleUpdate"
            @export="handleExport"
        />
    </div>
</template>

<script setup>
import AdvancedImageMarker from './AdvancedImageMarker.vue';
import { ref } from 'vue';

const savedPoints = ref([
    { x: 100, y: 100, label: 'Vertex A' },
    { x: 200, y: 150, label: 'Point B' }
]);

const savedPolygons = ref([
    {
        label: 'Triangle ABC',
        color: '#FF0000',
        points: [
            { x: 100, y: 100 },
            { x: 200, y: 100 },
            { x: 150, y: 200 }
        ]
    }
]);

const handleUpdate = (data) => {
    console.log('Updated data:', data);
    // Handle the update
};

const handleExport = (exportedData) => {
    console.log('Exported data:', exportedData);
    // Handle the exported data
};
</script>
