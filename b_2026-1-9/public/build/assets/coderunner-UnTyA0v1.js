import{f as s,o as b,M as v,J as h,y as x,N as n,ab as y}from"./vendor-BLQ8yBzx.js";import{a as w}from"./app-C7rW1iCk.js";import"./offline-D-M_H-zP.js";import"./i18n-D0hNWY36.js";import"./ui-04HvVE5c.js";const p=`<template>
  <div>
    <h1>Hello from dynamic component!</h1>
    <button @click="count++">Clicked {{ count }} times</button>
  </div>
</template>

<script setup>
import { ref } from 'vue'
const count = ref(0)
<\/script>
`,g={__name:"coderunner",props:{initialCode:{type:String,default:""}},setup(l){const u=l,a=s(null),o=s(u.initialCode||p);b(()=>{c()});function i(){o.value=p,c()}function c(){const e=a.value,t=e.contentDocument||e.contentWindow.document,r=`
    <!DOCTYPE html>
    <html>
      <head>
        <script type="module">
          import { createApp, ref } from 'https://unpkg.com/vue@3/dist/vue.esm-browser.js'

          const App = {
            template: \`${m(o.value)}\`,
            setup() {
              ${d(o.value)}
              return { ${f(o.value)} }
            }
          }

          createApp(App).mount('#app')
        <\/script>
      </head>
      <body>
        <div id="app"></div>
      </body>
    </html>
  `;t.open(),t.write(r),t.close()}function m(e){const t=e.match(/<template>([\s\S]+?)<\/template>/);return t?t[1].trim():""}function d(e){const t=e.match(/<script setup>([\s\S]+?)<\/script>/);return t?t[1].trim():""}function f(e){const t=e.match(/const\s+(\w+)/g);return t?t.map(r=>r.replace("const ","")).join(", "):""}return(e,t)=>(h(),v("div",null,[x(n("textarea",{"onUpdate:modelValue":t[0]||(t[0]=r=>o.value=r),class:"w-full h-60 border p-2 mb-4 font-mono text-sm",placeholder:"Type your Vue 3 component with <template> and <script setup>..."},null,512),[[y,o.value]]),n("div",{class:"flex justify-between mb-2"},[n("button",{onClick:c,class:"px-4 py-1 bg-blue-600 text-white rounded hover:bg-blue-700"},"Run"),n("button",{onClick:i,class:"px-4 py-1 bg-gray-300 text-black rounded hover:bg-gray-400"},"Reset")]),n("iframe",{ref_key:"iframeRef",ref:a,class:"w-full h-[400px] border",sandbox:"allow-scripts"},null,512)]))}},R=w(g,[["__scopeId","data-v-7c499c6b"]]);export{R as default};
