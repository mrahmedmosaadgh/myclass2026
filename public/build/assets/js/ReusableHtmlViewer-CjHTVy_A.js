import{_ as D,r as i,a as b,b2 as c,a0 as E,L as F,o as L,m as p}from"./feature-admin-core-BWNP0pMM.js";import M from"./EditorToolbar-UhY1CoVL.js";import R from"./EditorContent-DHuEeDEK.js";import B from"./PrintLayout-CLY7HawN.js";import"./vendor-xlsx-DkH2s96g.js";import"./TitleInput-DgsMN-oR.js";import"./TextFormatControls-2HfiTHSN.js";import"./LineSpacingControl-BfSmsYHL.js";import"./AlignmentControls-CBMza7GL.js";import"./ImageControls-DptEUby7.js";import"./PrintButton-D0GEKW8l.js";const H={class:"relative bg-white"},N={__name:"ReusableHtmlViewer",props:{modelValue:{type:String,required:!0},title:{type:String,default:"Document"}},emits:["update:modelValue"],setup(g,{emit:h}){const r=g,v=h,m=i(r.title),C=i(null),o=i(null),l=i(1.5),s=i("left"),f=i("inline-block"),d=i(100),u=i([]),V=[5,10,15,20,25,33,50,75,100,110,120,150,200,300],y=b(()=>c.sanitize(r.modelValue)),z=b(()=>["bold","italic","underline"].filter(e=>document.queryCommandState(e))),k=e=>{const t=e.target.innerHTML,a=c.sanitize(t);v("update:modelValue",a)},I=e=>{document.execCommand(e,!1,null)},_=e=>{e.target.tagName==="IMG"&&(u.value=[e.target])},$=()=>{u.value.length&&u.value.forEach(e=>{e.style.width=`${d.value}%`})},U=()=>{const e=window.open("","_blank"),t=`
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
                    text-align: ${s.value};
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
    `;e.document.write(t),e.document.close(),e.onload=()=>{e.print(),e.onafterprint=()=>{e.close()}}},x=e=>new Promise((t,a)=>{const n=new FileReader;n.onload=()=>t(n.result),n.onerror=a,n.readAsDataURL(e)}),S=async()=>{if(!o.value)return;const e=o.value.$el||o.value,t=e.getElementsByTagName("img"),a=Array.from(t).map(async n=>{if(!n.src.startsWith("data:"))try{const T=await(await fetch(n.src)).blob(),A=await x(T);n.src=A}catch(w){console.error("Failed to convert image to base64:",w)}});await Promise.all(a),k({target:e})};return E(()=>r.modelValue,e=>{o.value&&o.value.innerHTML!==e&&(o.value.innerHTML=c.sanitize(e))}),(e,t)=>(L(),F("div",H,[p(M,{title:m.value,"onUpdate:title":t[0]||(t[0]=a=>m.value=a),"line-spacing":l.value,"onUpdate:lineSpacing":t[1]||(t[1]=a=>l.value=a),alignment:s.value,"onUpdate:alignment":t[2]||(t[2]=a=>s.value=a),"image-display-mode":f.value,"onUpdate:imageDisplayMode":t[3]||(t[3]=a=>f.value=a),"image-width":d.value,"onUpdate:imageWidth":t[4]||(t[4]=a=>d.value=a),"active-formats":z.value,"image-sizes":V,onFormat:I,onConvertImagesToBase64:S,onResizeImages:$,onPrint:U},null,8,["title","line-spacing","alignment","image-display-mode","image-width","active-formats"]),p(R,{"model-value":g.modelValue,"line-spacing":l.value,alignment:s.value,"onUpdate:modelValue":t[5]||(t[5]=a=>v("update:modelValue",a)),onImageClick:_,ref_key:"editableContent",ref:o},null,8,["model-value","line-spacing","alignment"]),p(B,{ref_key:"printContainer",ref:C,content:y.value,"line-spacing":l.value,alignment:s.value},null,8,["content","line-spacing","alignment"])]))}},Z=D(N,[["__scopeId","data-v-98ba914f"]]);export{Z as default};
