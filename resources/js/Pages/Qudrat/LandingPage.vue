<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
});
</script>

<template>
    <Head>
        <title>Qudrat Pro - قدرات برو | تأسيس كمي | Master Quantitative Reasoning</title>
        <meta name="description" content="أفضل دورة تأسيس كمي لاختبار القدرات. Master Qudrat Quantitative reasoning in 8 Weeks. Comprehensive online course for the General Aptitude Test (GAT). تأسيس شامل للقسم الكمي في اختبار القدرات مع منصة قدرات برو." />
        <meta name="keywords" content="Qudrat Pro, Qudrat, GAT, General Aptitude Test, Quantitative Reasoning, Saudi Arabia, Education, Online Course, قدرات, قدرات برو, اختبار القدرات, القسم الكمي, قياس, دورة قدرات, تعليم, تأسيس, كمي, تأسيس قدرات, تأسيس كمي, أساسيات القدرات" />
        
        <!-- Open Graph / Facebook -->
        <meta property="og:type" content="website" />
        <meta property="og:url" content="https://qudratpro.com/" />
        <meta property="og:title" content="Qudrat Pro - قدرات برو | تأسيس كمي | Master Quantitative Reasoning" />
        <meta property="og:description" content="The ultimate platform to master the General Aptitude Test (Qudrat). Complete foundation for Quantitative section. تأسيس شامل ومتقن للقدرات (القسم الكمي). حقق +90 في القدرات." />
        <meta property="og:site_name" content="Qudrat Pro - قدرات برو" />
        <meta property="og:locale" content="en_US" />
        <meta property="og:locale:alternate" content="ar_SA" />
        
        <!-- Twitter -->
        <meta name="twitter:card" content="summary_large_image" />
        <meta name="twitter:title" content="Qudrat Pro - قدرات برو" />
        <meta name="twitter:description" content="Master Qudrat Quantitative reasoning with expert teachers. أتقن القدرات الكمية مع نخبة من المعلمين." />

        <!-- Structured Data (JSON-LD) -->
        <component is="script" type="application/ld+json">
            {
                "@context": "https://schema.org",
                "@type": "WebSite",
                "name": "Qudrat Pro",
                "alternateName": ["قدرات برو", "QudratPro", "منصة قدرات برو"],
                "url": "https://qudratpro.com/",
                "description": "Comprehensive online platform for mastering the General Aptitude Test (Qudrat). منصة تعليمية متخصصة في التأسيس لاختبار القدرات العامة.",
                "potentialAction": {
                    "@type": "SearchAction",
                    "target": "https://qudratpro.com/search?q={search_term_string}",
                    "query-input": "required name=search_term_string"
                },
                "inLanguage": ["en", "ar"]
            }
        </component>
        <component is="script" type="application/ld+json">
            {
                "@context": "https://schema.org",
                "@type": "EducationalOrganization",
                "name": "Qudrat Pro",
                "alternateName": "قدرات برو",
                "url": "https://qudratpro.com",
                "logo": "https://qudratpro.com/images/logo.png",
                "sameAs": [
                    "https://twitter.com/qudratpro",
                    "https://instagram.com/qudratpro"
                ],
                "description": "Educational platform specialized in General Aptitude Test (Qudrat) preparation. منصة رائدة في التدريب لاختبار القدرات."
            }
        </component>
    </Head>

    <div class="min-h-screen bg-white font-sans text-gray-900 antialiased selection:bg-indigo-500 selection:text-white">
        <!-- Hero Section -->
        <div class="relative overflow-hidden bg-slate-50">
            <div class="hidden sm:absolute sm:inset-y-0 sm:block sm:h-full sm:w-full" aria-hidden="true">
                <div class="relative h-full mx-auto max-w-7xl">
                    <svg class="absolute right-full translate-y-1/4 translate-x-1/4 transform lg:translate-x-1/2" width="404" height="784" fill="none" viewBox="0 0 404 784">
                        <defs>
                            <pattern id="f210dbf6-a58d-4871-961e-36d5016a0f49" x="0" y="0" width="20" height="20" patternUnits="userSpaceOnUse">
                                <rect x="0" y="0" width="4" height="4" class="text-slate-200" fill="currentColor" />
                            </pattern>
                        </defs>
                        <rect width="404" height="784" fill="url(#f210dbf6-a58d-4871-961e-36d5016a0f49)" />
                    </svg>
                    <svg class="absolute left-full -translate-y-3/4 -translate-x-1/4 transform md:-translate-y-1/2 lg:-translate-x-1/2" width="404" height="784" fill="none" viewBox="0 0 404 784">
                        <defs>
                            <pattern id="5d0dd344-b041-4d26-bec4-8d33ea57ec9b" x="0" y="0" width="20" height="20" patternUnits="userSpaceOnUse">
                                <rect x="0" y="0" width="4" height="4" class="text-slate-200" fill="currentColor" />
                            </pattern>
                        </defs>
                        <rect width="404" height="784" fill="url(#5d0dd344-b041-4d26-bec4-8d33ea57ec9b)" />
                    </svg>
                </div>
            </div>

            <div class="relative pt-6 pb-16 sm:pb-24">
                <!-- Navigation -->
                <div class="mx-auto max-w-7xl px-4 sm:px-6">
                    <nav class="relative flex items-center justify-between sm:h-10 md:justify-center" aria-label="Global">
                        <div class="flex flex-1 items-center md:absolute md:inset-y-0 md:left-0">
                            <div class="flex w-full items-center justify-between md:w-auto">
                                    <Link href="/" class="flex items-center gap-2" aria-label="Qudrat Pro Home">
                                        <span class="sr-only">Qudrat Pro - قدرات برو</span>
                                        <span class="text-2xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent" lang="en">Qudrat Pro</span>
                                    </Link>
                            </div>
                        </div>
                        <div class="hidden md:flex md:space-x-10">
                            <a href="#curriculum" class="font-medium text-gray-500 hover:text-gray-900">Curriculum</a>
                            <a href="#testimonials" class="font-medium text-gray-500 hover:text-gray-900">Success Stories</a>
                            <a href="#pricing" class="font-medium text-gray-500 hover:text-gray-900">Pricing</a>
                        </div>
                        <div class="hidden md:absolute md:inset-y-0 md:right-0 md:flex md:items-center md:justify-end gap-4">
                            <Link :href="route('login')" class="text-base font-medium text-gray-500 hover:text-gray-900">Log in</Link>
                            <Link :href="route('register')" class="inline-flex items-center justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-base font-medium text-white shadow-sm hover:bg-indigo-700">Start Free Trial</Link>
                        </div>
                    </nav>
                </div>

                <!-- Main Content -->
                <main class="mx-auto mt-16 max-w-7xl px-4 sm:mt-24 sm:px-6">
                    <div class="text-center">
                        <h1 class="text-4xl font-bold tracking-tight text-gray-900 sm:text-5xl md:text-6xl flex flex-col gap-2">
                            <span class="block text-indigo-600 xl:inline">Master Qudrat Quantitative</span>
                            <span class="block text-gray-900 text-3xl sm:text-4xl mt-2 font-arabic" lang="ar">أتقن اختبار القدرات (الكمي)</span>
                            <span class="block text-indigo-500 text-2xl sm:text-3xl mt-1">in 8 Weeks | في 8 أسابيع</span>
                        </h1>
                        <p class="mx-auto mt-5 max-w-md text-base text-gray-500 sm:text-lg md:mt-8 md:max-w-3xl md:text-xl">
                            The comprehensive online course designed by teachers to help you score 90+ in your Qudrat test.
                        </p>
                        <p class="mx-auto mt-2 max-w-md text-base text-gray-500 sm:text-lg md:max-w-3xl md:text-xl font-arabic" lang="ar">
                            الدورة الشاملة التي صممها معلمون خبراء لمساعدتك في تحقيق +90 في اختبار القدرات.
                        </p>
                        <div class="mx-auto mt-5 max-w-md sm:flex sm:justify-center md:mt-8">
                            <div class="rounded-md shadow">
                                <Link :href="route('register')" class="flex w-full items-center justify-center rounded-md border border-transparent bg-indigo-600 px-8 py-3 text-base font-medium text-white hover:bg-indigo-700 md:py-4 md:px-10 md:text-lg">
                                    Get Started
                                </Link>
                            </div>
                            <div class="mt-3 rounded-md shadow sm:mt-0 sm:ml-3">
                                <a href="#curriculum" class="flex w-full items-center justify-center rounded-md border border-transparent bg-white px-8 py-3 text-base font-medium text-indigo-600 hover:bg-gray-50 md:py-4 md:px-10 md:text-lg">
                                    View Full Curriculum
                                </a>
                            </div>
                        </div>
                    </div>
                </main>
            </div>
        </div>

        <!-- Course Curriculum & Features -->
        <div id="curriculum" class="bg-gray-50 py-12">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <h2 class="text-base font-semibold uppercase tracking-wide text-indigo-600">What You'll Learn | ماذا ستتعلم</h2>
                    <p class="mt-2 text-3xl font-bold leading-8 tracking-tight text-gray-900 sm:text-4xl">Everything You Need to Ace The Exam</p>
                    <p class="mt-2 text-xl font-medium leading-8 tracking-tight text-gray-600 font-arabic">كل ما تحتاجه للتفوق في الاختبار</p>
                </div>

                <div class="mt-10">
                    <div class="grid grid-cols-1 gap-10 sm:grid-cols-2 lg:grid-cols-3">
                        <div class="bg-white overflow-hidden rounded-lg shadow hover:shadow-md transition-shadow">
                            <div class="p-6">
                                <h3 class="text-lg font-medium text-gray-900">Algebra & Equations <span class="block text-indigo-600 text-base font-arabic mt-1">الجبر والمعادلات</span></h3>
                                <p class="mt-2 text-base text-gray-500">Master solving for X, linear equations, and functional relationships.</p>
                            </div>
                        </div>
                        <div class="bg-white overflow-hidden rounded-lg shadow hover:shadow-md transition-shadow">
                            <div class="p-6">
                                <h3 class="text-lg font-medium text-gray-900">Geometry <span class="block text-indigo-600 text-base font-arabic mt-1">الهندسة</span></h3>
                                <p class="mt-2 text-base text-gray-500">Understand shapes, angles, areas, and volumes with visual guides.</p>
                            </div>
                        </div>
                        <div class="bg-white overflow-hidden rounded-lg shadow hover:shadow-md transition-shadow">
                            <div class="p-6">
                                <h3 class="text-lg font-medium text-gray-900">Statistics & Probability <span class="block text-indigo-600 text-base font-arabic mt-1">الإحصاء والاحتمالات</span></h3>
                                <p class="mt-2 text-base text-gray-500">Data analysis, probability theory, and statistical reasoning.</p>
                            </div>
                        </div>
                        <div class="bg-white overflow-hidden rounded-lg shadow hover:shadow-md transition-shadow">
                            <div class="p-6">
                                <h3 class="text-lg font-medium text-gray-900">Logic & Word Problems <span class="block text-indigo-600 text-base font-arabic mt-1">المسائل اللفظية والمنطق</span></h3>
                                <p class="mt-2 text-base text-gray-500">Strategies to deconstruct complex word problems quickly.</p>
                            </div>
                        </div>
                        <div class="bg-white overflow-hidden rounded-lg shadow hover:shadow-md transition-shadow">
                            <div class="p-6">
                                <h3 class="text-lg font-medium text-gray-900">3 Full Mock Exams <span class="block text-indigo-600 text-base font-arabic mt-1">3 اختبارات تجريبية كاملة</span></h3>
                                <p class="mt-2 text-base text-gray-500">Simulate the real test environment with timed practice exams.</p>
                            </div>
                        </div>
                        <div class="bg-white overflow-hidden rounded-lg shadow hover:shadow-md transition-shadow">
                            <div class="p-6">
                                <h3 class="text-lg font-medium text-gray-900">Progress Tracking <span class="block text-indigo-600 text-base font-arabic mt-1">متابعة المستوى</span></h3>
                                <p class="mt-2 text-base text-gray-500">Detailed analytics to identify and improve your weak areas.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="bg-white border-t border-gray-200">
            <div class="mx-auto max-w-7xl py-12 px-4 sm:px-6 md:flex md:items-center md:justify-between lg:px-8">
                <div class="flex justify-center space-x-6 md:order-2">
                    <a href="#" class="text-gray-400 hover:text-gray-500">
                        <span class="sr-only">Instagram</span>
                        <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path fill-rule="evenodd" d="M12.315 2c2.43 0 2.784.013 3.808.06 1.064.049 1.791.218 2.427.465a4.902 4.902 0 011.772 1.153 4.902 4.902 0 011.153 1.772c.247.636.416 1.363.465 2.427.048 1.067.06 1.407.06 4.123v.08c0 2.643-.012 2.987-.06 4.043-.049 1.064-.218 1.791-.465 2.427a4.902 4.902 0 01-1.153 1.772 4.902 4.902 0 01-1.772 1.153c-.636.247-1.363.416-2.427.465-1.067.048-1.407.06-4.123.06h-.08c-2.643 0-2.987-.012-4.043-.06-1.064-.049-1.791-.218-2.427-.465a4.902 4.902 0 01-1.772-1.153 4.902 4.902 0 01-1.153-1.772c-.247-.636-.416-1.363-.465-2.427-.047-1.024-.06-1.379-.06-3.808v-.63c0-2.43.013-2.784.06-3.808.049-1.064.218-1.791.465-2.427a4.902 4.902 0 011.153-1.772 4.902 4.902 0 011.772-1.153c.636-.247 1.363-.416 2.427-.465C9.673 2.013 9.92 2 12.315 2zm-1.2 1.9c-2.67.114-3.537.408-4.168.981-.626.57-1.05 1.436-1.259 2.112-.209.676-.23 1.543-.23 3.664v3.088c0 2.121.021 2.988.23 3.664.209.676.633 1.543 1.259 2.112.631.573 1.498.867 4.168.981 1.767.075 4.545.075 6.312 0 2.67-.114 3.537-.408 4.168-.981.626-.57 1.05-1.436 1.259-2.112.209-.676.23-1.543.23-3.664V9.657c0-2.121-.021-2.988-.23-3.664-.209-.676-.633-1.543-1.259-2.112-.631-.573-1.498-.867-4.168-.981-1.767-.075-4.545-.075-6.312 0zm7.144 2.179a1.071 1.071 0 110 2.142 1.071 1.071 0 010-2.142zm-5.944 1.74a5.051 5.051 0 110 10.102 5.051 5.051 0 010-10.102zm0 1.9a3.151 3.151 0 100 6.302 3.151 3.151 0 000-6.302z" clip-rule="evenodd" />
                        </svg>
                    </a>
                </div>
                <div class="mt-8 md:order-1 md:mt-0">
                    <p class="text-center text-base text-gray-400">&copy; 2026 QudratPro. All rights reserved.</p>
                </div>
            </div>
        </footer>
    </div>
</template>
