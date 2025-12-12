<template>
    <div>

hiiiiiii
    </div>
</template>
<script setup>
const props = defineProps({
    periods: {
        type: Array,
        required: true
    }
})

const emit = defineEmits([
    'update-periods'
])

const updatePeriods = () => {
    emit('update-periods', props.periods)
}
</script>