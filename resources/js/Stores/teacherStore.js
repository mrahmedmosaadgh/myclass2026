import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import axios from 'axios';

export const useTeacherStore = defineStore('teacher', () => {
    const grades = ref([]);
    const classrooms = ref([]);
    const subjects = ref([]);
    const loading = ref(false);
    const error = ref(null);
    const loaded = ref(false);

    const fetchTeacherData = async (force = false) => {
        if (loaded.value && !force) return;

        loading.value = true;
        error.value = null;

        try {
            const response = await axios.get(route('lesson-presentation.teacher.grades'));

            // Handle new API format
            if (response.data.data) {
                // Extract grades from the new format
                grades.value = response.data.data.map(item => ({
                    ...item.grade,
                    classrooms: item.classrooms, // Preserve classrooms structure
                    subjects: item.classrooms.flatMap(c => c.subjects).filter((s, i, arr) =>
                        arr.findIndex(t => t.id === s.id) === i
                    )
                }));

                // Extract all classrooms and add grade info to them
                classrooms.value = response.data.data.flatMap(item =>
                    item.classrooms.map(c => ({
                        ...c,
                        grade: item.grade // Attach grade info
                    }))
                );

                // Extract unique subjects
                subjects.value = response.data.data.flatMap(item =>
                    item.classrooms.flatMap(c => c.subjects)
                ).filter((s, i, arr) => arr.findIndex(t => t.id === s.id) === i);
            } else {
                // Fallback to old format
                grades.value = response.data.grades || [];
                classrooms.value = response.data.classrooms || [];
            }

            loaded.value = true;
        } catch (err) {
            console.error('Failed to fetch teacher data:', err);
            error.value = 'Failed to load teacher data.';
        } finally {
            loading.value = false;
        }
    };

    const getGradeById = (id) => grades.value.find(g => g.id === id);

    return {
        grades,
        classrooms,
        subjects,
        loading,
        error,
        loaded,
        fetchTeacherData,
        getGradeById
    };
});
