<script setup>
 defineProps({
    title: String,
});
</script>

<template>
    <div>
        <Head :title="title" />
             <main>
                <div class="py-6">
                    <div class="px-2 flex justify-center">
                        <div class="p-0">
                            <slot name="header" />
                        </div>
                    </div>

                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <slot />
                    </div>
                </div>
            </main>
    </div>
 
 
</template>
 
