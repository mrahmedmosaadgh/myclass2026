import{f as L,b as R,o as U,M as l,J as i,N as e,Q as c,V as r,W as g,U as u,F as M,R as j,$ as Y,v as G}from"./vendor-BLQ8yBzx.js";const K={class:"text-2xl font-bold text-gray-900"},X={class:"text-4xl font-bold text-gray-900 mb-4"},Z={class:"text-xl text-gray-600"},q={class:"mt-4 text-sm text-gray-500"},ee=["onClick"],te={class:"text-sm font-medium text-gray-500 uppercase tracking-wider"},se={class:"text-xs text-gray-400 uppercase"},le={class:"slide-content"},ie=["innerHTML"],re={key:1,class:"flex flex-col items-center"},ne={key:0,class:"text-lg font-medium mb-4"},oe=["src"],de={key:2,class:"text-center p-8 bg-gray-50 rounded-lg w-full print:border print:border-gray-300"},ae={class:"text-sm text-gray-600"},ce={key:3,class:"text-center p-8 bg-gray-50 rounded-lg w-full print:border print:border-gray-300"},ue={class:"text-sm text-gray-600"},pe={key:4,class:"text-center p-8 bg-gray-50 rounded-lg w-full print:border print:border-gray-300"},ve={class:"text-sm text-gray-600"},me={key:5,class:"mt-2 text-sm text-gray-500 italic"},xe={key:2,class:"space-y-6"},ye=["innerHTML"],fe={class:"ml-4 space-y-2"},ge={key:0,class:"flex gap-4"},be={key:1,class:"space-y-2"},_e=["innerHTML"],he={class:"bg-white rounded-xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-hidden flex flex-col"},we={class:"flex items-center justify-between p-6 border-b border-gray-200"},ke={class:"text-sm text-gray-500 mt-1"},Pe={class:"flex items-center justify-end gap-3 p-6 border-t border-gray-200 bg-gray-50"},$e={class:"flex-1 overflow-y-auto p-8 bg-gray-50"},Te={class:"bg-white rounded-lg shadow-sm p-8 max-w-3xl mx-auto"},Le={key:0},Me=["innerHTML"],je={key:1,class:"flex flex-col items-center"},Ce={key:0,class:"text-lg font-medium mb-4"},He=["src"],De={key:2,class:"text-center p-8 bg-gray-50 rounded-lg w-full border border-gray-300"},Se={class:"text-sm text-gray-600"},Fe={key:3,class:"text-center p-8 bg-gray-50 rounded-lg w-full border border-gray-300"},Ve={class:"text-sm text-gray-600"},Ne={key:4,class:"text-center p-8 bg-gray-50 rounded-lg w-full border border-gray-300"},Ae={class:"text-sm text-gray-600"},Ee={key:5,class:"mt-2 text-sm text-gray-500 italic"},Ie={key:2,class:"space-y-6"},ze=["innerHTML"],Be={class:"ml-4 space-y-2"},We={key:0,class:"flex gap-4"},Je={key:1,class:"space-y-2"},Oe=["innerHTML"],Re={__name:"LessonPrintView copy",props:{presentationId:{type:[Number,String],required:!0}},setup(I){const z=I,p=L({}),x=L([]),C=L(0),B=L(!1),W=async()=>{try{const o=await G.get(route("lesson-presentation.show",{id:z.presentationId}));p.value=o.data,x.value=o.data.slides||[]}catch(o){console.error("Failed to load lesson:",o)}},J=()=>{v.value=!1,window.print()},v=L(!1),n=R(()=>x.value[C.value]),O=o=>{var b,_,h,w,k,P,$,T;C.value=o;const t=x.value[o];if(!t)return;const y=window.open("","_blank","width=800,height=600");if(!y){alert("Please allow popups to preview the slide");return}let a="";if(t.slide_type==="text")a=`<div class="prose max-w-none">${((b=t.slide_content)==null?void 0:b.text)||""}</div>`;else if(["image","video","audio","pdf"].includes(t.slide_type)){const s=(_=t.slide_content)!=null&&_.title?`<h3 class="text-lg font-medium mb-4">${t.slide_content.title}</h3>`:"",d=(h=t.slide_content)!=null&&h.caption?`<p class="mt-2 text-sm text-gray-500 italic">${t.slide_content.caption}</p>`:"";t.slide_type==="image"&&((w=t.slide_content)!=null&&w.url)?a=`
        <div class="flex flex-col items-center">
          ${s}
          <img src="${t.slide_content.url}" class="max-w-full h-auto rounded-lg shadow-sm" />
          ${d}
        </div>
      `:t.slide_type==="video"?a=`
        <div class="flex flex-col items-center">
          ${s}
          <div class="text-center p-8 bg-gray-50 rounded-lg w-full border border-gray-300">
            <i class="fas fa-video text-4xl text-gray-400 mb-2"></i>
            <p class="text-sm text-gray-600">Video: ${((k=t.slide_content)==null?void 0:k.url)||""}</p>
          </div>
          ${d}
        </div>
      `:t.slide_type==="audio"?a=`
        <div class="flex flex-col items-center">
          ${s}
          <div class="text-center p-8 bg-gray-50 rounded-lg w-full border border-gray-300">
            <i class="fas fa-volume-up text-4xl text-gray-400 mb-2"></i>
            <p class="text-sm text-gray-600">Audio: ${((P=t.slide_content)==null?void 0:P.url)||""}</p>
          </div>
          ${d}
        </div>
      `:t.slide_type==="pdf"&&(a=`
        <div class="flex flex-col items-center">
          ${s}
          <div class="text-center p-8 bg-gray-50 rounded-lg w-full border border-gray-300">
            <i class="fas fa-file-pdf text-4xl text-gray-400 mb-2"></i>
            <p class="text-sm text-gray-600">PDF Document: ${(($=t.slide_content)==null?void 0:$.url)||""}</p>
          </div>
          ${d}
        </div>
      `)}else if(t.slide_type==="question"){const s=((T=t.slide_content)==null?void 0:T.questions)||[];a='<div class="space-y-6">',s.forEach((d,f)=>{a+=`
        <div class="pl-4 border-l-4 border-blue-100">
          <div class="font-medium text-gray-900 mb-3">${d.text||""}</div>
          <div class="ml-4 space-y-2">
      `,d.type==="true_false"?a+=`
          <div class="flex gap-4">
            <div class="flex items-center">
              <div class="w-4 h-4 border border-gray-300 rounded-full mr-2"></div> True
            </div>
            <div class="flex items-center">
              <div class="w-4 h-4 border border-gray-300 rounded-full mr-2"></div> False
            </div>
          </div>
        `:(d.type==="multiple_choice"||d.type==="single_choice")&&(a+='<div class="space-y-2">',(d.options||[]).forEach(H=>{const D=d.type==="single_choice"?"rounded-full":"rounded";a+=`
            <div class="flex items-center">
              <div class="w-4 h-4 border border-gray-300 mr-2 ${D}"></div>
              <span>${H.text||""}</span>
            </div>
          `}),a+="</div>"),a+=`
          </div>
        </div>
      `}),a+="</div>"}y.document.write(`
    <!DOCTYPE html>
    <html>
    <head>
      <title>Print Preview - Slide ${o+1}</title>
      <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
      <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
      <style>
        @media print {
          body {
            margin: 0;
            padding: 20px;
          }
          .no-print {
            display: none !important;
          }
        }
        .prose {
          max-width: none;
        }
      </style>
    </head>
    <body class="bg-white p-8">
      <div class="max-w-4xl mx-auto">
        <div class="no-print mb-6 flex justify-between items-center border-b pb-4">
          <div>
            <h1 class="text-2xl font-bold text-gray-900">Print Preview</h1>
            <p class="text-sm text-gray-500">Slide ${o+1} of ${x.value.length} - ${p.value.name||""}</p>
          </div>
          <button 
            onclick="window.print()" 
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center gap-2"
          >
            <i class="fas fa-print"></i>
            Print
          </button>
        </div>
        
        <div class="slide-content">
          ${a}
        </div>
      </div>
    </body>
    </html>
  `),y.document.close()};return U(()=>{W()}),(o,t)=>{var y,a,b,_,h,w,k,P,$,T;return i(),l("div",{class:u([v.value?"":"min-h-screen p-8 print:p-0","bg-white"])},[e("div",{class:u([v.value?"no-print":"print:hidden","max-w-4xl mx-auto mb-8 flex justify-between items-center"])},[e("h1",K,r(p.value.name),1),e("button",{onClick:J,class:"no-print inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"},t[4]||(t[4]=[e("i",{class:"fas fa-print mr-2"},null,-1),g(" Print Lesson ")]))],2),e("div",{class:u(v.value?"":"max-w-4xl mx-auto space-y-8 print:w-full print:max-w-none print:space-y-8")},[e("div",{class:u([v.value?"no-print":"","text-center border-b-2 border-gray-200 pb-8 mb-12"])},[e("h1",X,r(p.value.name),1),e("p",Z,r(p.value.description),1),e("div",q,[e("p",null,"Teacher: "+r(((y=p.value.teacher)==null?void 0:y.name)||"N/A"),1),e("p",null,"Subject: "+r(((a=p.value.subject)==null?void 0:a.name)||"N/A"),1),e("p",null,"Date: "+r(new Date().toLocaleDateString()),1)])],2),(i(!0),l(M,null,j(x.value,(s,d)=>{var f,H,D,S,F,V,N,A;return i(),l("div",{class:u([v.value&&d!=C.value?"no-print":"","slide-container relative break-inside-avoid border border-gray-200 rounded-lg p-8 print:border-0 print:p-0 print:rounded-none print:mb-0 group"]),key:s.id||d},[e("button",{onClick:m=>O(d),class:"absolute no-print top-4 right-4 opacity-0 group-hover:opacity-100 transition-opacity duration-200 print:hidden bg-blue-600 hover:bg-blue-700 text-white rounded-lg px-3 py-2 shadow-lg flex items-center gap-2 text-sm font-medium z-10",title:"Print this slide"},t[5]||(t[5]=[e("i",{class:"fas fa-print"},null,-1),e("span",null,"Print Slide",-1)]),8,ee),e("div",{class:u([v.value?"no-print":"print:hidden","flex items-center justify-between mb-6 border-b border-gray-100 pb-2"])},[e("span",te,"Slide "+r(d+1),1),e("span",se,r(s.slide_type),1)],2),e("div",le,[s.slide_type==="text"?(i(),l("div",{key:0,class:"prose max-w-none",innerHTML:(f=s.slide_content)==null?void 0:f.text},null,8,ie)):["image","video","audio","pdf"].includes(s.slide_type)?(i(),l("div",re,[(H=s.slide_content)!=null&&H.title?(i(),l("h3",ne,r(s.slide_content.title),1)):c("",!0),s.slide_type==="image"&&((D=s.slide_content)!=null&&D.url)?(i(),l("img",{key:1,src:s.slide_content.url,class:"max-w-full h-auto rounded-lg shadow-sm print:shadow-none"},null,8,oe)):s.slide_type==="video"?(i(),l("div",de,[t[6]||(t[6]=e("i",{class:"fas fa-video text-4xl text-gray-400 mb-2"},null,-1)),e("p",ae,"Video: "+r((S=s.slide_content)==null?void 0:S.url),1)])):s.slide_type==="audio"?(i(),l("div",ce,[t[7]||(t[7]=e("i",{class:"fas fa-volume-up text-4xl text-gray-400 mb-2"},null,-1)),e("p",ue,"Audio: "+r((F=s.slide_content)==null?void 0:F.url),1)])):s.slide_type==="pdf"?(i(),l("div",pe,[t[8]||(t[8]=e("i",{class:"fas fa-file-pdf text-4xl text-gray-400 mb-2"},null,-1)),e("p",ve,"PDF Document: "+r((V=s.slide_content)==null?void 0:V.url),1)])):c("",!0),(N=s.slide_content)!=null&&N.caption?(i(),l("p",me,r(s.slide_content.caption),1)):c("",!0)])):s.slide_type==="question"?(i(),l("div",xe,[(i(!0),l(M,null,j(((A=s.slide_content)==null?void 0:A.questions)||[],(m,Q)=>(i(),l("div",{key:Q,class:"pl-4 border-l-4 border-blue-100 print:border-gray-300"},[e("div",{class:"font-medium text-gray-900 mb-3",innerHTML:m.text},null,8,ye),e("div",fe,[m.type==="true_false"?(i(),l("div",ge,t[9]||(t[9]=[e("div",{class:"flex items-center"},[e("div",{class:"w-4 h-4 border border-gray-300 rounded-full mr-2"}),g(" True ")],-1),e("div",{class:"flex items-center"},[e("div",{class:"w-4 h-4 border border-gray-300 rounded-full mr-2"}),g(" False ")],-1)]))):m.type==="multiple_choice"||m.type==="single_choice"?(i(),l("div",be,[(i(!0),l(M,null,j(m.options,E=>(i(),l("div",{key:E.id,class:"flex items-center"},[e("div",{class:u(["w-4 h-4 border border-gray-300 mr-2",m.type==="single_choice"?"rounded-full":"rounded"])},null,2),e("span",{innerHTML:E.text},null,8,_e)]))),128))])):c("",!0)])]))),128))])):c("",!0)])],2)}),128))],2),B.value?(i(),l("div",{key:0,class:"fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4 no-print",onClick:t[3]||(t[3]=Y((...s)=>o.closePreview&&o.closePreview(...s),["self"]))},[e("div",he,[e("div",we,[e("div",null,[t[10]||(t[10]=e("h2",{class:"text-2xl font-bold text-gray-900"},"Print Preview",-1)),e("p",ke,"Slide "+r(C.value+1)+" of "+r(x.value.length),1)]),e("button",{onClick:t[0]||(t[0]=(...s)=>o.closePreview&&o.closePreview(...s)),class:"text-gray-400 hover:text-gray-600 text-2xl w-8 h-8 flex items-center justify-center"}," × ")]),e("div",Pe,[e("button",{onClick:t[1]||(t[1]=(...s)=>o.closePreview&&o.closePreview(...s)),class:"px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors font-medium"}," Cancel "),e("button",{onClick:t[2]||(t[2]=(...s)=>o.confirmPrint&&o.confirmPrint(...s)),class:"px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium flex items-center gap-2"},t[11]||(t[11]=[e("i",{class:"fas fa-print"},null,-1),g(" Print This Slide ")]))]),e("div",$e,[e("div",Te,[n.value?(i(),l("div",Le,[n.value.slide_type==="text"?(i(),l("div",{key:0,class:"prose max-w-none",innerHTML:(b=n.value.slide_content)==null?void 0:b.text},null,8,Me)):["image","video","audio","pdf"].includes(n.value.slide_type)?(i(),l("div",je,[(_=n.value.slide_content)!=null&&_.title?(i(),l("h3",Ce,r(n.value.slide_content.title),1)):c("",!0),n.value.slide_type==="image"&&((h=n.value.slide_content)!=null&&h.url)?(i(),l("img",{key:1,src:n.value.slide_content.url,class:"max-w-full h-auto rounded-lg shadow-sm"},null,8,He)):n.value.slide_type==="video"?(i(),l("div",De,[t[12]||(t[12]=e("i",{class:"fas fa-video text-4xl text-gray-400 mb-2"},null,-1)),e("p",Se,"Video: "+r((w=n.value.slide_content)==null?void 0:w.url),1)])):n.value.slide_type==="audio"?(i(),l("div",Fe,[t[13]||(t[13]=e("i",{class:"fas fa-volume-up text-4xl text-gray-400 mb-2"},null,-1)),e("p",Ve,"Audio: "+r((k=n.value.slide_content)==null?void 0:k.url),1)])):n.value.slide_type==="pdf"?(i(),l("div",Ne,[t[14]||(t[14]=e("i",{class:"fas fa-file-pdf text-4xl text-gray-400 mb-2"},null,-1)),e("p",Ae,"PDF Document: "+r((P=n.value.slide_content)==null?void 0:P.url),1)])):c("",!0),($=n.value.slide_content)!=null&&$.caption?(i(),l("p",Ee,r(n.value.slide_content.caption),1)):c("",!0)])):n.value.slide_type==="question"?(i(),l("div",Ie,[(i(!0),l(M,null,j(((T=n.value.slide_content)==null?void 0:T.questions)||[],(s,d)=>(i(),l("div",{key:d,class:"pl-4 border-l-4 border-blue-100"},[e("div",{class:"font-medium text-gray-900 mb-3",innerHTML:s.text},null,8,ze),e("div",Be,[s.type==="true_false"?(i(),l("div",We,t[15]||(t[15]=[e("div",{class:"flex items-center"},[e("div",{class:"w-4 h-4 border border-gray-300 rounded-full mr-2"}),g(" True ")],-1),e("div",{class:"flex items-center"},[e("div",{class:"w-4 h-4 border border-gray-300 rounded-full mr-2"}),g(" False ")],-1)]))):s.type==="multiple_choice"||s.type==="single_choice"?(i(),l("div",Je,[(i(!0),l(M,null,j(s.options,f=>(i(),l("div",{key:f.id,class:"flex items-center"},[e("div",{class:u(["w-4 h-4 border border-gray-300 mr-2",s.type==="single_choice"?"rounded-full":"rounded"])},null,2),e("span",{innerHTML:f.text},null,8,Oe)]))),128))])):c("",!0)])]))),128))])):c("",!0)])):c("",!0)])])])])):c("",!0)],2)}}};export{Re as default};
