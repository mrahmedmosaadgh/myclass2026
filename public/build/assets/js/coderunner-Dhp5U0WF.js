import{_ as h,r as s,Z as v,L as b,o as x,D as y,y as r,ah as w}from"./feature-admin-core-BWNP0pMM.js";import"./vendor-xlsx-DkH2s96g.js";const p=`<template>
  <div>
    <h1>Hello from dynamic component!</h1>
    <button @click="count++">Clicked {{ count }} times</button>
  </div>
</template>

<script setup>
import { ref } from 'vue'
const count = ref(0)
<\/script>
`,_={__name:"coderunner",props:{initialCode:{type:String,default:""}},setup(l){const u=l,a=s(null),o=s(u.initialCode||p);v(()=>{c()});function i(){o.value=p,c()}function c(){const e=a.value,t=e.contentDocument||e.contentWindow.document,n=`
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
  `;t.open(),t.write(n),t.close()}function m(e){const t=e.match(/<template>([\s\S]+?)<\/template>/);return t?t[1].trim():""}function d(e){const t=e.match(/<script setup>([\s\S]+?)<\/script>/);return t?t[1].trim():""}function f(e){const t=e.match(/const\s+(\w+)/g);return t?t.map(n=>n.replace("const ","")).join(", "):""}return(e,t)=>(x(),b("div",null,[y(r("textarea",{"onUpdate:modelValue":t[0]||(t[0]=n=>o.value=n),class:"w-full h-60 border p-2 mb-4 font-mono text-sm",placeholder:"Type your Vue 3 component with <template> and <script setup>..."},null,512),[[w,o.value]]),r("div",{class:"flex justify-between mb-2"},[r("button",{onClick:c,class:"px-4 py-1 bg-blue-600 text-white rounded hover:bg-blue-700"},"Run"),r("button",{onClick:i,class:"px-4 py-1 bg-gray-300 text-black rounded hover:bg-gray-400"},"Reset")]),r("iframe",{ref_key:"iframeRef",ref:a,class:"w-full h-[400px] border",sandbox:"allow-scripts"},null,512)]))}},C=h(_,[["__scopeId","data-v-d89ecc68"]]);export{C as default};
