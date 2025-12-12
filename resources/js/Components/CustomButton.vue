<template>
    <div class="flex items-center justify-center border-x border-b border-[#1A1A1A] px-2 py-10 text-primary-text md:px-0">
      <a
        class="group flex items-center"
        href="/blog/windsurf-wave-5"
      >
        <div
          class="relative flex w-full items-center justify-between gap-2 rounded-full border border-brand-dark/30 px-4 py-1.5 text-sm tracking-tight transition-all duration-300 ease-in-out group-hover:border-primary-600 group-hover:shadow-[0_0_30px_rgba(52,211,153,0.6)] md:text-base"
        >
          Wave 5: Windsurf Tab
          <svg
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
            stroke-width="1.5"
            stroke="currentColor"
            aria-hidden="true"
            data-slot="icon"
            class="hidden h-8 w-8 text-primary-text transition-transform duration-200 group-hover:translate-x-1 md:block md:h-4 md:w-4"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"
            />
          </svg>
          <div class="pointer-events-none absolute inset-0 z-10 rounded-full">
            <div
              class="mask-with-browser-support absolute -inset-[1px] rounded-full border border-transparent bg-origin-border"
              style="background-image: conic-gradient(from 0.502turn, rgba(9, 182, 162, 0) 75%, rgb(9, 182, 162) 100%);"
            ></div>
          </div>
        </div>
      </a>
    </div>
  </template>

  <script>
  export default {
    name: "StyledButton",
  };
  </script>

  <style scoped>
  /* You can add component-specific styles here if needed */
  </style>
