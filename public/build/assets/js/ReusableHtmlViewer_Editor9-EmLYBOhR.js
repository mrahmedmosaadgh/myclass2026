import{_ as j,r as s,a as G,b2 as p,a0 as O,L as l,o as i,y as a,D as y,ah as C,S as w,U as x,X as f,z as X,V as H,ac as J,v as z,Y as M}from"./feature-admin-core-BWNP0pMM.js";import"./vendor-xlsx-DkH2s96g.js";const K={class:"relative bg-white"},Q={class:"border-b"},Z={class:"p-4 flex flex-wrap items-center gap-3"},ee={class:"flex items-center gap-1 border rounded-md bg-white shadow-sm"},te=["onClick","title"],ae={key:0,class:"font-bold"},ne={key:1,class:"italic"},oe={key:2,class:"underline"},se={class:"flex items-center gap-2"},le={class:"flex items-center gap-1 border rounded-md bg-white shadow-sm"},ie=["onClick","title"],re={xmlns:"http://www.w3.org/2000/svg",class:"h-5 w-5",viewBox:"0 0 20 20",fill:"currentColor"},de=["d"],ce=["value"],ue={class:"p-6"},me=["innerHTML"],pe=["innerHTML"],he={__name:"ReusableHtmlViewer_Editor9",props:{modelValue:{type:String,required:!0},title:{type:String,default:"Document"}},emits:["update:modelValue"],setup(V,{emit:T}){const h=V,L=T,g=s(h.title),B=s(null),r=s(null),d=s(1.5),c=s("left"),u=s("inline-block"),v=s(100),m=s([]),A=[5,10,15,20,25,33,50,75,100,110,120,150,200,300],I={left:"M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 5a1 1 0 011-1h6a1 1 0 110 2H4a1 1 0 01-1-1z",center:"M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3 5a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm-3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z",right:"M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm5 5a1 1 0 011-1h6a1 1 0 110 2H9a1 1 0 01-1-1zm-5 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z"},b=G(()=>p.sanitize(h.modelValue)),_=e=>{const n=e.target.innerHTML,t=p.sanitize(n);L("update:modelValue",t)},S=e=>{document.execCommand(e,!1,null)},D=e=>document.queryCommandState(e),E=()=>{d.value=Math.max(1,Math.min(3,d.value))},U=e=>{c.value=e},$=()=>{u.value=u.value==="inline-block"?"block":"inline-block",m.value.length&&m.value.forEach(e=>{e.style.display=u.value})},P=e=>{e.target.tagName==="IMG"&&(m.value=[e.target])},F=()=>{m.value.length&&m.value.forEach(e=>{e.style.width=`${v.value}%`})},N=()=>{const e=window.open("","_blank"),n=`
        <!DOCTYPE html>
        <html>
        <head>
            <title>${g.value}</title>
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
    `;e.document.write(n),e.document.close(),e.onload=()=>{e.print(),e.onafterprint=()=>{e.close()}}},R=e=>new Promise((n,t)=>{const o=new FileReader;o.onload=()=>n(o.result),o.onerror=t,o.readAsDataURL(e)}),k=async()=>{if(!r.value)return;const e=r.value.getElementsByTagName("img"),n=Array.from(e).map(async t=>{if(!t.src.startsWith("data:"))try{const q=await(await fetch(t.src)).blob(),Y=await R(q);t.src=Y}catch(o){console.error("Failed to convert image to base64:",o)}});await Promise.all(n),_({target:r.value})};O(()=>h.modelValue,e=>{r.value&&r.value.innerHTML!==e&&(r.value.innerHTML=p.sanitize(e))});const W=async()=>{try{const e=await navigator.clipboard.readText();if(!e)return;const n=document.createElement("div");n.innerHTML=p.sanitize(e),k()}catch(e){console.error("Clipboard operation failed:",e)}};return(e,n)=>(i(),l("div",K,[a("div",Q,[a("div",Z,[y(a("input",{type:"text","onUpdate:modelValue":n[0]||(n[0]=t=>g.value=t),class:"border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500",placeholder:"Document Title"},null,512),[[C,g.value]]),a("div",ee,[(i(),l(w,null,x(["bold","italic","underline"],t=>a("button",{key:t,onClick:o=>S(t),class:f(["p-2 hover:bg-gray-100 transition-colors",{"bg-blue-50 text-blue-600":D(t)}]),title:t.charAt(0).toUpperCase()+t.slice(1)},[t==="bold"?(i(),l("span",ae,"B")):t==="italic"?(i(),l("span",ne,"I")):t==="underline"?(i(),l("span",oe,"U")):X("",!0)],10,te)),64))]),a("div",se,[n[3]||(n[3]=a("label",{class:"text-sm text-gray-600"},"Line Spacing:",-1)),y(a("input",{type:"number","onUpdate:modelValue":n[1]||(n[1]=t=>d.value=t),onInput:E,min:"1",max:"3",step:"0.1",class:"w-20 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"},null,544),[[C,d.value]])]),a("div",le,[(i(),l(w,null,x(["left","center","right"],t=>a("button",{key:t,onClick:o=>U(t),class:f(["p-2 hover:bg-gray-100 transition-colors",{"bg-blue-50 text-blue-600":c.value===t}]),title:`Align ${t}`},[(i(),l("svg",re,[a("path",{"fill-rule":"evenodd",d:I[t],"clip-rule":"evenodd"},null,8,de)]))],10,ie)),64))]),a("button",{onClick:$,class:f(["px-2 py-1 rounded text-sm",u.value==="inline-block"?"bg-blue-500 text-white":"bg-gray-200 text-gray-700"])},H(u.value==="inline-block"?"Inline":"Block"),3),y(a("select",{"onUpdate:modelValue":n[2]||(n[2]=t=>v.value=t),onChange:F,class:"w-24 rounded-md border-gray-300 shadow-sm"},[(i(),l(w,null,x(A,t=>a("option",{key:t,value:t},H(t)+"% ",9,ce)),64))],544),[[J,v.value]]),a("button",{onClick:k,class:"px-2 py-1 rounded text-sm bg-gray-200 text-gray-700 hover:bg-gray-300"}," Convert Images to Base64 "),a("button",{onClick:W,class:"px-2 py-1 rounded text-sm bg-blue-500 text-white hover:bg-blue-600 flex items-center"},n[4]||(n[4]=[a("svg",{class:"w-4 h-4 mr-1",viewBox:"0 0 24 24",fill:"none",stroke:"currentColor"},[a("path",{"stroke-linecap":"round","stroke-linejoin":"round","stroke-width":"2",d:"M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"})],-1),z(" Paste with Base64 ")])),a("div",{class:"flex items-center gap-2 ml-auto"},[a("button",{onClick:N,class:"px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition flex items-center gap-2"},n[5]||(n[5]=[a("svg",{xmlns:"http://www.w3.org/2000/svg",class:"h-5 w-5",viewBox:"0 0 20 20",fill:"currentColor"},[a("path",{"fill-rule":"evenodd",d:"M5 4v3H4a2 2 0 00-2 2v3a2 2 0 002 2h1v2a2 2 0 002 2h6a2 2 0 002-2v-2h1a2 2 0 002-2V9a2 2 0 00-2-2h-1V4a2 2 0 00-2-2H7a2 2 0 00-2 2zm8 0H7v3h6V4zm0 8H7v4h6v-4z","clip-rule":"evenodd"})],-1),z(" Print ")]))])])]),a("div",ue,[a("div",{class:"prose max-w-none",innerHTML:b.value,contenteditable:"true",ref_key:"editableContent",ref:r,onInput:_,onClick:P,style:M({lineHeight:d.value,textAlign:c.value})},null,44,me)]),a("div",{ref_key:"printContainer",ref:B,class:"hidden"},[a("div",{class:"print-content prose max-w-none",innerHTML:b.value,style:M({lineHeight:d.value,textAlign:c.value})},null,12,pe)],512)]))}},be=j(he,[["__scopeId","data-v-996691a4"]]);export{be as default};
