<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import CustomModal from '@/Components/CustomModal.vue';
import LoginForm from '@/Components/LoginForm.vue';

// Modal states
const activeModal = ref(null);
const modalConfig = ref({
  title: '',
  userType: '',
});

const openModal = (type) => {
  modalConfig.value = {
    title: `${type} Login`,
    userType: type.toLowerCase(),
  };
  activeModal.value = type;
};

const closeModal = () => {
  activeModal.value = null;
};

const testimonials = ref([
  {
    text: "MyClass LMS made it easy to manage my entire school remotely. The parent feedback has been amazing.",
    author: "Principal, Future Vision Academy"
  },
  {
    text: "Finally, I can track my child's progress daily without needing to ask.",
    author: "Parent, Grade 6 Student"
  },
  {
    text: "Using quizzes and the scoreboard got my class more excited than ever!",
    author: "Teacher, Middle School"
  }
]);

const features = ref([
  { icon: "✅", text: "Interactive Lessons – Schedule and deliver dynamic lessons using rich media" },
  { icon: "✅", text: "Smart Quizzes & Exams – Instant grading, feedback, and reports" },
  { icon: "✅", text: "Group Projects & Collaboration – Encourage teamwork and creativity" },
  { icon: "✅", text: "Real-Time Notifications – Stay informed with instant updates" },
  { icon: "✅", text: "Attendance & Behavior Tracking – Monitor students with ease" },
  { icon: "✅", text: "Scoreboards & Rankings – Motivate students through gamified progress" },
  { icon: "✅", text: "Parent Portal – Keep parents engaged and updated" },
  { icon: "✅", text: "Weekly Plans & Reports – Structured planning for better performance" },
  { icon: "✅", text: "Multi-grade Support – Tailored learning paths for each class" },
  { icon: "✅", text: "Role-Based Dashboards – Custom views for admins, teachers, students, and parents" }
]);

const activities = ref([
  { icon: "🧪", text: "Science Fairs" },
  { icon: "🎭", text: "Talent Shows" },
  { icon: "🏆", text: "Competitions & Quizzes" },
  { icon: "🎨", text: "Art & Creativity Challenges" },
  { icon: "🌍", text: "Community Service Initiatives" },
  { icon: "🎉", text: "School Celebrations & National Days" },
  { icon: "⚽", text: "Sports Days & PE Highlights" }
]);
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <!-- <AIChat /> -->
    <!-- Hero Section -->
   
     <!-- page:{{ $page }} -->

    <header class="bg-gradient-to-r from-blue-600 to-indigo-700 text-white py-20">
      <div class="container mx-auto px-6 text-center">
        <h1 class="text-5xl font-bold mb-4">🌐 MyClass LMS</h1>
        <p class="text-2xl mb-8">Learn. Connect. Grow.</p>
        <p class="text-xl mb-12">Empowering Education Through Innovation</p>

        <div  v-if="!$page.props?.auth?.user" class="grid md:grid-cols-3 gap-4 max-w-3xl mx-auto">
          <button @click="openModal('Teacher')"
                  class="bg-white text-blue-600 px-6 py-3 rounded-lg shadow-lg hover:bg-blue-50 transition">
            👩‍🏫 Teacher Login
          </button>

          <button @click="openModal('Student')"
                  class="bg-white text-blue-600 px-6 py-3 rounded-lg shadow-lg hover:bg-blue-50 transition">
            👨‍🎓 Student Login
          </button>

          <button @click="openModal('Parent')"
                  class="bg-white text-blue-600 px-6 py-3 rounded-lg shadow-lg hover:bg-blue-50 transition">
            👨‍👩‍👧 Parent Login
          </button>

          <!-- Login Modal -->
          <CustomModal :show="activeModal !== null"
                      @close="closeModal"
                      max-width="md">
            <LoginForm v-if="activeModal"
                      :title="modalConfig.title"
                      :user-type="modalConfig.userType"
                      @success="closeModal" />
          </CustomModal>

          <!-- <Link href="/student-login" class="bg-white text-blue-600 px-6 py-3 rounded-lg shadow-lg hover:bg-blue-50 transition">
            👨‍🎓 Student Login
          </Link>
          <Link href="/parent-login" class="bg-white text-blue-600 px-6 py-3 rounded-lg shadow-lg hover:bg-blue-50 transition">
            👨‍👩‍👧 Parent Login
          </Link> -->
        </div>
      </div>
    </header>

    <!-- Features Section -->
    <section class="py-20 bg-white">
      <div class="container mx-auto px-6">
        <h2 class="text-3xl font-bold text-center mb-12">📚 Key Features</h2>
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
          <div v-for="feature in features" :key="feature.text" class="p-6 border rounded-lg hover:shadow-lg transition">
            <p class="text-lg">{{ feature.icon }} {{ feature.text }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Activities Section -->
    <section class="py-20 bg-gray-50">
      <div class="container mx-auto px-6">
        <h2 class="text-3xl font-bold text-center mb-12">🎨 School Activities</h2>
        <div class="grid md:grid-cols-3 lg:grid-cols-4 gap-6">
          <div v-for="activity in activities" :key="activity.text"
               class="p-6 bg-white rounded-lg shadow-sm hover:shadow-md transition">
            <p class="text-xl mb-2">{{ activity.icon }}</p>
            <p class="text-gray-700">{{ activity.text }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Testimonials Section -->
    <section class="py-20 bg-blue-600 text-white">
      <div class="container mx-auto px-6">
        <h2 class="text-3xl font-bold text-center mb-12">💬 Testimonials</h2>
        <div class="grid md:grid-cols-3 gap-8">
          <div v-for="testimonial in testimonials" :key="testimonial.author"
               class="p-6 bg-blue-700 rounded-lg">
            <p class="text-lg mb-4">"{{ testimonial.text }}"</p>
            <p class="font-semibold">— {{ testimonial.author }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Contact Section -->
    <section class="py-20 bg-white">
      <div class="container mx-auto px-6 text-center">
        <h2 class="text-3xl font-bold mb-8">📞 Contact Us</h2>
        <div class="max-w-2xl mx-auto">
          <p class="mb-4">📧 Email: support@myclasslms.com</p>
          <p class="mb-4">☎️ Phone: +1 (123) 456-7890</p>
          <p class="mb-8">🕒 Hours: Mon-Fri, 8:00 AM - 5:00 PM</p>
          <div class="flex justify-center gap-4">
            <Link href="/contact" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition">
              Contact Us
            </Link>
            <Link href="/demo" class="bg-indigo-600 text-white px-6 py-3 rounded-lg hover:bg-indigo-700 transition">
              Request a Demo
            </Link>
          </div>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12">
      <div class="container mx-auto px-6 text-center">
        <p class="mb-4">🌍 Join Our Learning Community</p>
        <p class="mb-8">Empower your classroom, simplify school operations, and inspire students to thrive.</p>
        <p>MyClass LMS – where learning meets purpose.</p>
      </div>
    </footer>
  </div>
</template>

<style scoped>
.container {
  max-width: 1200px;
}
</style>
