<template>
    <div>
        <button @click="connectToGoogle">Connect Google Classroom</button>

        <div v-if="courses && courses.length">
            <h3>Your Google Classroom Courses</h3>
            <ul>
                <li v-for="course in courses" :key="course.id">{{ course.name }}</li>
            </ul>
        </div>
    </div>
</template>

<script>
export default {
    data() {
        return {
            courses: null
        };
    },
    methods: {
        connectToGoogle() {
            window.location.href = '/oauth/google';
        },
        async fetchCourses() {
            try {
                const response = await axios.get('/get-google-courses');
                this.courses = response.data.courses || [];
            } catch (error) {
                console.error("Error fetching courses:", error);
                this.courses = [];
            }
        }
    },
    mounted() {
        this.fetchCourses();
    }
}
</script>

