<template>
  <div title="عرض ملف PDF من الجهاز">
    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-8">

          <!-- File Upload Area -->
          <div class="mb-8 text-center">
            <h2 class="text-2xl font-bold mb-6 text-gray-800 dark:text-white">
              ارفع ملف PDF لعرضه
            </h2>

            <label class="upload-box">
              <input 
                type="file" 
                accept=".pdf,application/pdf" 
                @change="handleFileUpload"
                class="hidden"
              />
              <div class="upload-content">
                <div class="upload-icon">Upload PDF</div>
                <p class="text-lg text-gray-600 dark:text-gray-300">
                  اسحب الملف هنا أو اضغط للرفع
                </p>
                <span class="text-sm text-gray-500">
                  (يدعم الملفات حتى 50 ميغابايت)
                </span>
              </div>
            </label>

            <!-- Show selected file name -->
            <div v-if="fileName" class="mt-4 text-green-600 font-semibold">
              تم اختيار: {{ fileName }}
            </div>
          </div>

          <!-- PDF Viewer -->
          <div class="pdf-viewer-container">
            <PDFViewer :pdf-url="localPdfUrl" />
          </div>

        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref,onUnmounted  } from 'vue'
// import div from '@/Layouts/div.vue'
import PDFViewer from './PDFViewer.vue'

// متغيرات مهمة
const localPdfUrl = ref('')    // رابط الملف المؤقت
const fileName = ref('')       // اسم الملف المرفوع

// دالة الرفع والعرض الفوري
const handleFileUpload = (event) => {
  const file = event.target.files[0]

  // تحقق من أن الملف PDF
  if (!file) {
    alert('لم يتم اختيار ملف')
    return
  }

  if (file.type !== 'application/pdf') {
    alert('يرجى رفع ملف بصيغة PDF فقط')
    return
  }

  if (file.size > 50 * 1024 * 1024) {
    alert('حجم الملف كبير جدًا (الحد الأقصى 50 ميغابايت)')
    return
  }

  // إنشاء رابط مؤقت لعرض الملف فورًا
  localPdfUrl.value = URL.createObjectURL(file)
  fileName.value = file.name

  console.log('تم رفع الملف بنجاح:', file.name)
}

// تنظيف الذاكرة عند إغلاق الصفحة (مهم جدًا!)
onUnmounted(() => {
  if (localPdfUrl.value) {
    URL.revokeObjectURL(localPdfUrl.value)
  }
})
</script>

<style scoped>
.upload-box {
  cursor: pointer;
  display: block;
  width: 100%;
  max-width: 500px;
  margin: 0 auto;
  border: 3px dashed #4a9eff;
  border-radius: 16px;
  padding: 40px 20px;
  transition: all 0.3s ease;
  background: #f8fbff;
  text-align: center;
}

.upload-box:hover {
  border-color: #0066ff;
  background: #f0f7ff;
  transform: translateY(-4px);
  box-shadow: 0 10px 30px rgba(0, 102, 255, 0.15);
}

.upload-icon {
  font-size: 4rem;
  margin-bottom: 16px;
}

.pdf-viewer-container {
  min-height: 70vh;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 10px 30px rgba(0,0,0,0.1);
  background: #2d2d2d;
}
</style>