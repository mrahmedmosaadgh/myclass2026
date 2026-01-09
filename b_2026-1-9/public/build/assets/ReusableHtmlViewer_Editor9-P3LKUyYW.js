import{f as s,b as G,k as J,M as l,J as i,N as n,y,ab as C,F as w,R as x,U as f,Q as O,V as H,am as Q,W as M,S as z}from"./vendor-BLQ8yBzx.js";import p from"./purify.es-Ci5xwkH_.js";import{a as Y}from"./app-C7rW1iCk.js";import"./offline-D-M_H-zP.js";import"./i18n-D0hNWY36.js";import"./ui-04HvVE5c.js";const K={class:"relative bg-white"},X={class:"border-b"},Z={class:"p-4 flex flex-wrap items-center gap-3"},ee={class:"flex items-center gap-1 border rounded-md bg-white shadow-sm"},te=["onClick","title"],ne={key:0,class:"font-bold"},ae={key:1,class:"italic"},oe={key:2,class:"underline"},se={class:"flex items-center gap-2"},le={class:"flex items-center gap-1 border rounded-md bg-white shadow-sm"},ie=["onClick","title"],re={xmlns:"http://www.w3.org/2000/svg",class:"h-5 w-5",viewBox:"0 0 20 20",fill:"currentColor"},de=["d"],ce=["value"],ue={class:"p-6"},me=["innerHTML"],pe=["innerHTML"],ge={__name:"ReusableHtmlViewer_Editor9",props:{modelValue:{type:String,required:!0},title:{type:String,default:"Document"}},emits:["update:modelValue"],setup(V,{emit:T}){const g=V,B=T,h=s(g.title),L=s(null),r=s(null),d=s(1.5),c=s("left"),u=s("inline-block"),v=s(100),m=s([]),A=[5,10,15,20,25,33,50,75,100,110,120,150,200,300],I={left:"M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 5a1 1 0 011-1h6a1 1 0 110 2H4a1 1 0 01-1-1z",center:"M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3 5a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm-3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z",right:"M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm5 5a1 1 0 011-1h6a1 1 0 110 2H9a1 1 0 01-1-1zm-5 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z"},b=G(()=>p.sanitize(g.modelValue)),_=e=>{const a=e.target.innerHTML,t=p.sanitize(a);B("update:modelValue",t)},S=e=>{document.execCommand(e,!1,null)},D=e=>document.queryCommandState(e),E=()=>{d.value=Math.max(1,Math.min(3,d.value))},U=e=>{c.value=e},$=()=>{u.value=u.value==="inline-block"?"block":"inline-block",m.value.length&&m.value.forEach(e=>{e.style.display=u.value})},F=e=>{e.target.tagName==="IMG"&&(m.value=[e.target])},N=()=>{m.value.length&&m.value.forEach(e=>{e.style.width=`${v.value}%`})},P=()=>{const e=window.open("","_blank"),a=`
        <!DOCTYPE html>
        <html>
        <head>
            <title>${h.value}</title>
            <style>
                @page {
                    margin: 1cm;
                    size: A4;
                }
                body {
                    font-family: system-ui, -apple-system, sans-serif;
                    line-height: ${d.value};
                    margin: 0;
                    padding: 1cm;
                    text-align: ${c.value};
                }
                .prose {
                    max-width: none;
                }
                .prose img {
                    max-width: 100%;
                    height: auto;
                    page-break-inside: avoid;
                }
                @media print {
                    html, body {
                        width: 210mm;
                        height: 297mm;
                    }
                }
            </style>
        </head>
        <body>
            ${b.value}
        </body>
        </html>
    `;e.document.write(a),e.document.close(),e.onload=()=>{e.print(),e.onafterprint=()=>{e.close()}}},R=e=>new Promise((a,t)=>{const o=new FileReader;o.onload=()=>a(o.result),o.onerror=t,o.readAsDataURL(e)}),k=async()=>{if(!r.value)return;const e=r.value.getElementsByTagName("img"),a=Array.from(e).map(async t=>{if(!t.src.startsWith("data:"))try{const q=await(await fetch(t.src)).blob(),j=await R(q);t.src=j}catch(o){console.error("Failed to convert image to base64:",o)}});await Promise.all(a),_({target:r.value})};J(()=>g.modelValue,e=>{r.value&&r.value.innerHTML!==e&&(r.value.innerHTML=p.sanitize(e))});const W=async()=>{try{const e=await navigator.clipboard.readText();if(!e)return;const a=document.createElement("div");a.innerHTML=p.sanitize(e),k()}catch(e){console.error("Clipboard operation failed:",e)}};return(e,a)=>(i(),l("div",K,[n("div",X,[n("div",Z,[y(n("input",{type:"text","onUpdate:modelValue":a[0]||(a[0]=t=>h.value=t),class:"border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500",placeholder:"Document Title"},null,512),[[C,h.value]]),n("div",ee,[(i(),l(w,null,x(["bold","italic","underline"],t=>n("button",{key:t,onClick:o=>S(t),class:f(["p-2 hover:bg-gray-100 transition-colors",{"bg-blue-50 text-blue-600":D(t)}]),title:t.charAt(0).toUpperCase()+t.slice(1)},[t==="bold"?(i(),l("span",ne,"B")):t==="italic"?(i(),l("span",ae,"I")):t==="underline"?(i(),l("span",oe,"U")):O("",!0)],10,te)),64))]),n("div",se,[a[3]||(a[3]=n("label",{class:"text-sm text-gray-600"},"Line Spacing:",-1)),y(n("input",{type:"number","onUpdate:modelValue":a[1]||(a[1]=t=>d.value=t),onInput:E,min:"1",max:"3",step:"0.1",class:"w-20 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"},null,544),[[C,d.value]])]),n("div",le,[(i(),l(w,null,x(["left","center","right"],t=>n("button",{key:t,onClick:o=>U(t),class:f(["p-2 hover:bg-gray-100 transition-colors",{"bg-blue-50 text-blue-600":c.value===t}]),title:`Align ${t}`},[(i(),l("svg",re,[n("path",{"fill-rule":"evenodd",d:I[t],"clip-rule":"evenodd"},null,8,de)]))],10,ie)),64))]),n("button",{onClick:$,class:f(["px-2 py-1 rounded text-sm",u.value==="inline-block"?"bg-blue-500 text-white":"bg-gray-200 text-gray-700"])},H(u.value==="inline-block"?"Inline":"Block"),3),y(n("select",{"onUpdate:modelValue":a[2]||(a[2]=t=>v.value=t),onChange:N,class:"w-24 rounded-md border-gray-300 shadow-sm"},[(i(),l(w,null,x(A,t=>n("option",{key:t,value:t},H(t)+"% ",9,ce)),64))],544),[[Q,v.value]]),n("button",{onClick:k,class:"px-2 py-1 rounded text-sm bg-gray-200 text-gray-700 hover:bg-gray-300"}," Convert Images to Base64 "),n("button",{onClick:W,class:"px-2 py-1 rounded text-sm bg-blue-500 text-white hover:bg-blue-600 flex items-center"},a[4]||(a[4]=[n("svg",{class:"w-4 h-4 mr-1",viewBox:"0 0 24 24",fill:"none",stroke:"currentColor"},[n("path",{"stroke-linecap":"round","stroke-linejoin":"round","stroke-width":"2",d:"M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"})],-1),M(" Paste with Base64 ")])),n("div",{class:"flex items-center gap-2 ml-auto"},[n("button",{onClick:P,class:"px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition flex items-center gap-2"},a[5]||(a[5]=[n("svg",{xmlns:"http://www.w3.org/2000/svg",class:"h-5 w-5",viewBox:"0 0 20 20",fill:"currentColor"},[n("path",{"fill-rule":"evenodd",d:"M5 4v3H4a2 2 0 00-2 2v3a2 2 0 002 2h1v2a2 2 0 002 2h6a2 2 0 002-2v-2h1a2 2 0 002-2V9a2 2 0 00-2-2h-1V4a2 2 0 00-2-2H7a2 2 0 00-2 2zm8 0H7v3h6V4zm0 8H7v4h6v-4z","clip-rule":"evenodd"})],-1),M(" Print ")]))])])]),n("div",ue,[n("div",{class:"prose max-w-none",innerHTML:b.value,contenteditable:"true",ref_key:"editableContent",ref:r,onInput:_,onClick:F,style:z({lineHeight:d.value,textAlign:c.value})},null,44,me)]),n("div",{ref_key:"printContainer",ref:L,class:"hidden"},[n("div",{class:"print-content prose max-w-none",innerHTML:b.value,style:z({lineHeight:d.value,textAlign:c.value})},null,12,pe)],512)]))}},fe=Y(ge,[["__scopeId","data-v-996691a4"]]);export{fe as default};
