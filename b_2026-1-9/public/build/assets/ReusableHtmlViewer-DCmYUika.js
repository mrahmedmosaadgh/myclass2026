import p from"./purify.es-Ci5xwkH_.js";import A from"./EditorToolbar-C36CKtFy.js";import D from"./EditorContent-DB-UlgM4.js";import E from"./PrintLayout-nzb8aruO.js";import{a as F}from"./app-C7rW1iCk.js";import{f as i,b,k as R,M as B,J as H,c}from"./vendor-BLQ8yBzx.js";import"./TitleInput-C5PT00vV.js";import"./TextFormatControls-Co7yWY7q.js";import"./underline-CGTlERih.js";import"./createLucideIcon-CzKeZgGE.js";import"./LineSpacingControl-ByZnQa1i.js";import"./AlignmentControls-DYuMHk5o.js";import"./align-right-DEP5Z8AX.js";import"./ImageControls-CjcGWRos.js";import"./image-Dl4gWOD4.js";import"./PrintButton-AnFlqEt_.js";import"./printer-B2cbZ4At.js";import"./offline-D-M_H-zP.js";import"./i18n-D0hNWY36.js";import"./ui-04HvVE5c.js";const L={class:"relative bg-white"},N={__name:"ReusableHtmlViewer",props:{modelValue:{type:String,required:!0},title:{type:String,default:"Document"}},emits:["update:modelValue"],setup(g,{emit:h}){const s=g,v=h,m=i(s.title),C=i(null),o=i(null),l=i(1.5),r=i("left"),f=i("inline-block"),d=i(100),u=i([]),V=[5,10,15,20,25,33,50,75,100,110,120,150,200,300],y=b(()=>p.sanitize(s.modelValue)),k=b(()=>["bold","italic","underline"].filter(e=>document.queryCommandState(e))),z=e=>{const t=e.target.innerHTML,n=p.sanitize(t);v("update:modelValue",n)},I=e=>{document.execCommand(e,!1,null)},$=e=>{e.target.tagName==="IMG"&&(u.value=[e.target])},U=()=>{u.value.length&&u.value.forEach(e=>{e.style.width=`${d.value}%`})},_=()=>{const e=window.open("","_blank"),t=`
        <!DOCTYPE html>
        <html>
        <head>
            <title>${m.value}</title>
            <style>
                @page {
                    margin: 1cm;
                    size: A4;
                }
                body {
                    font-family: system-ui, -apple-system, sans-serif;
                    line-height: ${l.value};
                    margin: 0;
                    padding: 1cm;
                    text-align: ${r.value};
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
            ${y.value}
        </body>
        </html>
    `;e.document.write(t),e.document.close(),e.onload=()=>{e.print(),e.onafterprint=()=>{e.close()}}},x=e=>new Promise((t,n)=>{const a=new FileReader;a.onload=()=>t(a.result),a.onerror=n,a.readAsDataURL(e)}),M=async()=>{if(!o.value)return;const e=o.value.$el||o.value,t=e.getElementsByTagName("img"),n=Array.from(t).map(async a=>{if(!a.src.startsWith("data:"))try{const S=await(await fetch(a.src)).blob(),T=await x(S);a.src=T}catch(w){console.error("Failed to convert image to base64:",w)}});await Promise.all(n),z({target:e})};return R(()=>s.modelValue,e=>{o.value&&o.value.innerHTML!==e&&(o.value.innerHTML=p.sanitize(e))}),(e,t)=>(H(),B("div",L,[c(A,{title:m.value,"onUpdate:title":t[0]||(t[0]=n=>m.value=n),"line-spacing":l.value,"onUpdate:lineSpacing":t[1]||(t[1]=n=>l.value=n),alignment:r.value,"onUpdate:alignment":t[2]||(t[2]=n=>r.value=n),"image-display-mode":f.value,"onUpdate:imageDisplayMode":t[3]||(t[3]=n=>f.value=n),"image-width":d.value,"onUpdate:imageWidth":t[4]||(t[4]=n=>d.value=n),"active-formats":k.value,"image-sizes":V,onFormat:I,onConvertImagesToBase64:M,onResizeImages:U,onPrint:_},null,8,["title","line-spacing","alignment","image-display-mode","image-width","active-formats"]),c(D,{"model-value":g.modelValue,"line-spacing":l.value,alignment:r.value,"onUpdate:modelValue":t[5]||(t[5]=n=>v("update:modelValue",n)),onImageClick:$,ref_key:"editableContent",ref:o},null,8,["model-value","line-spacing","alignment"]),c(E,{ref_key:"printContainer",ref:C,content:y.value,"line-spacing":l.value,alignment:r.value},null,8,["content","line-spacing","alignment"])]))}},se=F(N,[["__scopeId","data-v-98ba914f"]]);export{se as default};
