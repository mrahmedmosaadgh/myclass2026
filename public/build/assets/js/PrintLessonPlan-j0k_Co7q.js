import{_ as ee,j as te,r as U,a as $,k as C,o as n,w as c,m as o,P as q,T as v,y as e,Q as L,a9 as P,q as b,b8 as B,L as a,S as h,U as x,aJ as E,O as F,Y as O,v as p,V as i,z as _,X as se,ar as oe,a3 as le}from"./feature-admin-core-BWNP0pMM.js";const ne={class:"row items-center"},ie={class:"row q-col-gutter-md"},ae={class:"col-12 col-md-5"},re={class:"row q-mb-md"},de={class:"row items-center q-mb-sm"},ce={class:"q-pl-lg"},ue={class:"col-12 col-md-7"},pe={class:"row items-center"},me={id:"printable-lesson-plan",class:"printable-content"},ge={class:"print-header"},fe={class:"school-logo"},he=["src"],ve={class:"lesson-title"},xe={class:"lesson-description"},be={class:"lesson-info"},_e={class:"info-row"},ye={class:"info-item"},ke={class:"info-item"},Se={class:"info-row"},we={class:"info-item"},Ce={class:"info-item"},qe={class:"info-row"},Le={class:"info-item"},Pe={class:"info-item"},ze={class:"section-icon"},Ne={class:"slide-number"},Qe={class:"slide-content"},Ve=["innerHTML"],Ae={key:1,class:"question-content"},Te={class:"question-text"},De={key:0,class:"question-options"},je={key:1,class:"question-answer"},Ue={key:2,class:"question-explanation"},$e={key:2,class:"slide-placeholder"},Be={class:"print-footer"},Ee={__name:"PrintLessonPlan",props:{presentation:{type:Object,required:!0},sections:{type:Array,required:!0},slides:{type:Array,required:!0},teacherName:{type:String,default:"Teacher Name"},subjectName:{type:String,default:"Subject"},gradeName:{type:String,default:"Grade"},schoolLogo:{type:String,default:""}},emits:["close"],setup(u,{expose:H,emit:M}){te();const y=u,G=M,k=U(!1),d=U([]),z=$(()=>new Date().toLocaleDateString("en-US",{year:"numeric",month:"long",day:"numeric"})),Y=$(()=>d.value.length*2),S=s=>y.slides.filter(t=>t.section===s),N=s=>d.value.filter(t=>t.section===s),Q=s=>{const t=S(s);return t.length===0?!1:t.every(l=>d.value.includes(l))},J=s=>{const t=S(s);Q(s)?d.value=d.value.filter(r=>r.section!==s):t.forEach(r=>{d.value.includes(r)||d.value.push(r)})},W=()=>{d.value=[...y.slides]},X=()=>{d.value=[]},K=s=>{var t,l,r;if(s.slide_type==="text"){const f=(((t=s.slide_content)==null?void 0:t.text)||"").replace(/<[^>]*>/g,"").trim();return f.substring(0,50)+(f.length>50?"...":"")}else if(s.slide_type==="question"){const m=((r=(l=s.slide_content)==null?void 0:l.questions)==null?void 0:r.length)||0;return`${m} question${m!==1?"s":""}`}return s.slide_type},R=s=>({text:"description",image:"image",video:"videocam",audio:"audiotrack",pdf:"picture_as_pdf",question:"quiz",drawing:"brush"})[s]||"article",V=(s,t)=>s.type==="single_choice"?s.correct_answer===t:s.type==="multiple_choice"?Array.isArray(s.correct_answer)&&s.correct_answer.includes(t):!1,Z=()=>{const s=document.getElementById("printable-lesson-plan").innerHTML,t=window.open("","_blank");t.document.write(`
    <!DOCTYPE html>
    <html>
    <head>
      <title>${y.presentation.name||"Lesson Plan"}</title>
      <style>
        @media print {
          @page { margin: 1cm; }
          body { margin: 0; }
        }
        
        body {
          font-family: 'Arial', sans-serif;
          line-height: 1.6;
          color: #333;
          max-width: 210mm;
          margin: 0 auto;
          padding: 20px;
        }
        
        .print-header {
          text-align: center;
          border-bottom: 3px solid #1976d2;
          padding-bottom: 20px;
          margin-bottom: 30px;
        }
        
        .print-header h1 {
          margin: 10px 0;
          color: #1976d2;
          font-size: 28px;
        }
        
        .lesson-description {
          color: #666;
          font-size: 14px;
        }
        
        .lesson-info {
          background: #f5f5f5;
          padding: 15px;
          border-radius: 8px;
          margin-bottom: 30px;
        }
        
        .info-row {
          display: flex;
          justify-content: space-between;
          margin-bottom: 8px;
        }
        
        .info-item {
          flex: 1;
        }
        
        .section-block {
          page-break-inside: avoid;
          margin-bottom: 30px;
        }
        
        .section-header {
          background: #1976d2;
          color: white;
          padding: 12px 20px;
          border-left: 5px solid;
          margin-bottom: 15px;
          display: flex;
          align-items: center;
          gap: 10px;
        }
        
        .section-header h2 {
          margin: 0;
          font-size: 20px;
        }
        
        .section-icon {
          font-size: 24px;
        }
        
        .slide-block {
          margin-bottom: 20px;
          padding: 15px;
          border: 1px solid #ddd;
          border-radius: 8px;
          background: white;
          page-break-inside: avoid;
        }
        
        .slide-number {
          font-weight: bold;
          color: #1976d2;
          margin-bottom: 10px;
          font-size: 14px;
        }
        
        .slide-content {
          font-size: 14px;
        }
        
        .question-item {
          margin-bottom: 15px;
          padding: 10px;
          background: #f9f9f9;
          border-radius: 4px;
        }
        
        .question-text {
          font-weight: bold;
          margin-bottom: 8px;
        }
        
        .question-options {
          margin-left: 20px;
        }
        
        .option-item {
          margin: 5px 0;
        }
        
        .correct-answer {
          color: #4caf50;
          font-weight: bold;
        }
        
        .question-explanation {
          margin-top: 8px;
          padding: 8px;
          background: #e3f2fd;
          border-left: 3px solid #2196f3;
          font-size: 13px;
        }
        
        .slide-placeholder {
          text-align: center;
          padding: 20px;
          color: #999;
        }
        
        .print-footer {
          margin-top: 40px;
          padding-top: 20px;
          border-top: 2px solid #ddd;
          text-align: center;
          font-size: 12px;
          color: #666;
        }
      </style>
    </head>
    <body>
      ${s}
    </body>
    </html>
  `),t.document.close(),t.focus(),setTimeout(()=>{t.print()},500)},I=()=>{k.value=!0,d.value=[...y.slides]},A=()=>{k.value=!1,G("close")};return H({open:I}),(s,t)=>(n(),C(le,{modelValue:k.value,"onUpdate:modelValue":t[1]||(t[1]=l=>k.value=l),persistent:"",maximized:""},{default:c(()=>[o(q,null,{default:c(()=>[o(v,{class:"bg-primary text-white"},{default:c(()=>[e("div",ne,[o(L,{name:"print",size:"32px",class:"q-mr-sm"}),t[2]||(t[2]=e("div",{class:"text-h5"},"Print Lesson Plan",-1)),o(P),o(b,{flat:"",round:"",dense:"",icon:"close",onClick:A})])]),_:1}),o(v,{class:"q-pa-md"},{default:c(()=>[e("div",ie,[e("div",ae,[o(q,{flat:"",bordered:""},{default:c(()=>[o(v,{class:"bg-grey-2"},{default:c(()=>t[3]||(t[3]=[e("div",{class:"text-h6"},"Select Slides to Print",-1),e("div",{class:"text-caption text-grey-7"},"Choose which slides to include in the lesson plan",-1)])),_:1}),o(v,null,{default:c(()=>[e("div",re,[o(b,{flat:"",dense:"",color:"primary",label:"Select All",onClick:W,class:"q-mr-sm"}),o(b,{flat:"",dense:"",color:"negative",label:"Deselect All",onClick:X})]),o(B,{style:{height:"calc(100vh - 350px)"}},{default:c(()=>[(n(!0),a(h,null,x(u.sections,l=>(n(),a("div",{key:l.id,class:"q-mb-md"},[e("div",de,[o(E,{"model-value":Q(l.id),"onUpdate:modelValue":r=>J(l.id),label:l.title,color:"primary",class:"text-weight-bold"},null,8,["model-value","onUpdate:modelValue","label"]),o(P),o(F,{dense:"",style:O({background:l.bg,color:l.textColor})},{default:c(()=>[p(i(S(l.id).length)+" slides ",1)]),_:2},1032,["style"])]),e("div",ce,[(n(!0),a(h,null,x(S(l.id),(r,m)=>(n(),C(E,{key:m,modelValue:d.value,"onUpdate:modelValue":t[0]||(t[0]=f=>d.value=f),val:r,label:`Slide ${m+1}: ${K(r)}`,color:"primary",dense:"",class:"q-mb-xs"},null,8,["modelValue","val","label"]))),128))])]))),128))]),_:1})]),_:1})]),_:1})]),e("div",ue,[o(q,{flat:"",bordered:""},{default:c(()=>[o(v,{class:"bg-grey-2"},{default:c(()=>[e("div",pe,[t[4]||(t[4]=e("div",{class:"text-h6"},"Preview",-1)),o(P),o(F,{color:"primary","text-color":"white"},{default:c(()=>[p(i(d.value.length)+" slides selected ",1)]),_:1})])]),_:1}),o(v,null,{default:c(()=>[o(B,{style:{height:"calc(100vh - 350px)"}},{default:c(()=>[e("div",me,[e("div",ge,[e("div",fe,[u.schoolLogo?(n(),a("img",{key:0,src:u.schoolLogo,alt:"School Logo",style:{"max-height":"60px"}},null,8,he)):_("",!0)]),e("div",ve,[e("h1",null,i(u.presentation.name||"Lesson Plan"),1),e("p",xe,i(u.presentation.description),1)])]),e("div",be,[e("div",_e,[e("div",ye,[t[5]||(t[5]=e("strong",null,"Teacher:",-1)),p(" "+i(u.teacherName),1)]),e("div",ke,[t[6]||(t[6]=e("strong",null,"Subject:",-1)),p(" "+i(u.subjectName),1)])]),e("div",Se,[e("div",we,[t[7]||(t[7]=e("strong",null,"Grade:",-1)),p(" "+i(u.gradeName),1)]),e("div",Ce,[t[8]||(t[8]=e("strong",null,"Date:",-1)),p(" "+i(z.value),1)])]),e("div",qe,[e("div",Le,[t[9]||(t[9]=e("strong",null,"Duration:",-1)),p(" "+i(Y.value)+" minutes ",1)]),e("div",Pe,[t[10]||(t[10]=e("strong",null,"Total Slides:",-1)),p(" "+i(d.value.length),1)])])]),(n(!0),a(h,null,x(u.sections,l=>(n(),a("div",{key:l.id,class:"section-block"},[N(l.id).length>0?(n(),a(h,{key:0},[e("div",{class:"section-header",style:O({borderLeftColor:l.borderColor})},[e("span",ze,i(l.icon),1),e("h2",null,i(l.title),1)],4),(n(!0),a(h,null,x(N(l.id),(r,m)=>{var f,T;return n(),a("div",{key:m,class:"slide-block"},[e("div",Ne,"Slide "+i(m+1),1),e("div",Qe,[r.slide_type==="text"?(n(),a("div",{key:0,innerHTML:((f=r.slide_content)==null?void 0:f.text)||"No content"},null,8,Ve)):r.slide_type==="question"?(n(),a("div",Ae,[(n(!0),a(h,null,x(((T=r.slide_content)==null?void 0:T.questions)||[],(g,D)=>(n(),a("div",{key:D,class:"question-item"},[e("div",Te,[e("strong",null,"Q"+i(D+1)+":",1),p(" "+i(g.text),1)]),g.options?(n(),a("div",De,[(n(!0),a(h,null,x(g.options,(w,j)=>(n(),a("div",{key:j,class:"option-item"},[e("span",{class:se({"correct-answer":V(g,w.id)})},[p(i(String.fromCharCode(65+j))+". "+i(w.text)+" ",1),V(g,w.id)?(n(),C(L,{key:0,name:"check_circle",color:"positive",size:"sm"})):_("",!0)],2)]))),128))])):g.type==="true_false"?(n(),a("div",je,[t[11]||(t[11]=e("strong",null,"Answer:",-1)),p(" "+i(g.correct_answer?"True":"False"),1)])):_("",!0),g.explanation?(n(),a("div",Ue,[e("em",null,"Explanation: "+i(g.explanation),1)])):_("",!0)]))),128))])):(n(),a("div",$e,[o(L,{name:R(r.slide_type),size:"md",color:"grey"},null,8,["name"]),e("div",null,i(r.slide_type.toUpperCase())+" Slide",1)]))])])}),128))],64)):_("",!0)]))),128)),e("div",Be,[e("div",null,"Prepared by "+i(u.teacherName),1),e("div",null,"Printed on "+i(z.value),1)])])]),_:1})]),_:1})]),_:1})])])]),_:1}),o(oe,{align:"right",class:"q-pa-md"},{default:c(()=>[o(b,{flat:"",label:"Cancel",onClick:A}),o(b,{unelevated:"",color:"primary",icon:"print",label:"Print",onClick:Z,disable:d.value.length===0},null,8,["disable"])]),_:1})]),_:1})]),_:1},8,["modelValue"]))}},Oe=ee(Ee,[["__scopeId","data-v-fe9e0d57"]]);export{Oe as P};
