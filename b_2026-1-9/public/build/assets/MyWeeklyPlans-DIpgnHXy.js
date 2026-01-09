import{a as he,u as ge,g,Q,e as W,c as we,q as Y,C as _e}from"./app-C7rW1iCk.js";import{Q as G}from"./QSelect-33i7Va2A.js";import{Q as xe}from"./QLinearProgress-D2mtvauf.js";import{Q as ke}from"./QSpinnerDots-BJF2sVeZ.js";import{Q as ee}from"./QTab-CxUm8rI2.js";import{Q as Pe}from"./QTabs-BSrAfvfE.js";import{Q as te}from"./QSpace-8l-BltlJ.js";import{Q as k}from"./QTd-CETECk2s.js";import{Q as Ce}from"./QBadge-DD1aImCE.js";import{Q as qe}from"./QTable-DjO3BO7Z.js";import{Q as Qe,a as Ve}from"./QToolbar-BnrHWu3X.js";import{f as m,b as V,k as Se,o as Ne,aa as ze,M as u,J as i,c as l,N as o,I as S,y as se,W as w,K as r,V as c,z as We,Q as P,Y as oe,F as D,R as U,v as R,U as De,S as ae}from"./vendor-BLQ8yBzx.js";import Te from"./WeekSelector-BV2aJ-pZ.js";import je from"./WeeklyPlanEditor-DFRQriEP.js";import le from"./StatusBadge-CH3gOUab.js";import $e from"./WeeklyPlanMenu-F9c4lK33.js";import"./offline-D-M_H-zP.js";import"./i18n-D0hNWY36.js";import"./ui-04HvVE5c.js";import"./rtl-DFPa-2ov.js";import"./QMarkupTable-m--WvMjF.js";import"./use-fullscreen-DCQ6cIEZ.js";import"./QEditor-Dv-7YwTC.js";import"./WeeklySystemGuide-CKnGojZL.js";import"./QTabPanels-Ck3Opblv.js";import"./use-panel-CAWGoS-F.js";import"./use-render-cache-DLxPkVnQ.js";import"./MainSchoolData-CwTfS8Gl.js";import"./SchoolSettingsDialog-D57vEzYO.js";import"./QForm-CbaVBd2o.js";const Ee={class:"q-pa-md"},Fe={class:"row items-center q-mb-lg"},Me={class:"col"},Ue={class:"q-ma-none text-weight-bold"},Be={class:"row q-gutter-md items-center"},He={class:"col-12 col-sm-6 col-md-4"},Oe={class:"col-12 col-sm-3 col-md-2"},Ae={class:"col-auto q-ml-auto"},Ie={class:"absolute-full flex flex-center"},Le={class:"text-caption text-weight-bold"},Ye={class:"row q-gutter-md items-center q-mt-sm"},Ge={class:"col-12 col-sm-6 col-md-4"},Re={class:"col-12 col-sm-6 col-md-4"},Je={class:"col-12 col-sm-6 col-md-auto q-gutter-sm"},Ke={key:0,class:"row justify-center q-pa-xl"},Xe={class:"plans-grid"},Ze={class:"day-header"},et=["onClick"],tt={class:"period-badge"},st={class:"subject-name"},ot={class:"flex q-gutter-xs"},at={class:"classroom text-caption text-grey-7"},lt={class:"plan-status"},nt={key:0,class:"plan-preview text-caption"},rt={key:0,class:"preview-item"},it={key:1,class:"preview-item"},dt={class:"classrooms-grid"},ct={class:"classroom-header bg-primary text-white q-pa-md"},ut={class:"q-ma-none flex items-center q-gutter-sm"},mt={key:0,class:"text-info"},pt={class:"q-ml-xs"},yt={key:1,class:"text-grey-5"},vt={key:0,class:"text-warning"},ft={class:"q-ml-xs"},bt={key:1,class:"text-grey-5"},ht={key:0,class:"text-info"},gt={key:1,class:"text-grey-5"},wt={key:0,class:"text-center q-pa-xl"},_t={class:"preview-container q-pa-md"},xt={id:"print-area",class:"a4-page"},kt={class:"print-header q-mb-lg"},Pt={class:"q-ma-xs text-grey-7"},Ct={class:"q-ma-xs text-grey-7"},qt={class:"q-ma-xs text-grey-7"},Qt={class:"print-table"},Vt={class:"text-center"},St={class:"content-cell"},Nt={class:"content-cell"},zt={class:"content-cell"},Wt={class:"print-footer q-mt-lg"},Dt={class:"text-caption text-grey-6"},Tt={__name:"MyWeeklyPlans",setup(jt){const x=ge(),C=m([]),q=m(1),N=m(1),J=m(18),T=m(1),f=m([]),j=m([]),b=m(null),$=m(null),E=m(!1),K=m(null),B=m(!1),F=m(!1),M=m("by-day"),H=m(!1),z={1:"Sunday",2:"Monday",3:"Tuesday",4:"Wednesday",5:"Thursday"},ne=V(()=>{const t=new Set;return C.value.forEach(e=>{var n,a;(a=(n=e.schedule)==null?void 0:n.cst)!=null&&a.classroom_name&&t.add(e.schedule.cst.classroom_name)}),Array.from(t).sort()}),re=Object.entries(z).map(([t,e])=>({label:e,value:parseInt(t)})),O=V(()=>{var e,n;let t=C.value;return(e=f.value)!=null&&e.length&&(t=t.filter(a=>{var s,d;return f.value.includes((d=(s=a.schedule)==null?void 0:s.cst)==null?void 0:d.classroom_name)})),(n=j.value)!=null&&n.length&&(t=t.filter(a=>{var s;return j.value.includes((s=a.schedule)==null?void 0:s.day)})),t}),ie=V(()=>{const t={};return O.value.forEach(e=>{var a;const n=(a=e.schedule)==null?void 0:a.day;t[n]||(t[n]={dayNumber:n,dayName:z[n]||`Day ${n}`,plans:[]}),t[n].plans.push(e)}),Object.values(t).forEach(e=>{e.plans.sort((n,a)=>{var s,d;return((s=n.schedule)==null?void 0:s.period_number)-((d=a.schedule)==null?void 0:d.period_number)})}),Object.values(t).sort((e,n)=>e.dayNumber-n.dayNumber)}),A=V(()=>{const t=C.value.length;if(!t)return 0;const e=C.value.filter(n=>n.status==="completed").length;return Math.round(e/t*100)}),de=V(()=>{const t=A.value;return t>=80?"green":t>=50?"amber":"red"}),ce=V(()=>{const t={};return O.value.forEach(e=>{var a,s;const n=(s=(a=e.schedule)==null?void 0:a.cst)==null?void 0:s.classroom_name;t[n]||(t[n]={name:n,plans:[]}),t[n].plans.push({id:e.id,data:e})}),Object.values(t).forEach(e=>{e.plans.sort((n,a)=>{var d,p,y,v;const s=(((d=n.data.schedule)==null?void 0:d.day)||0)-(((p=a.data.schedule)==null?void 0:p.day)||0);return s!==0?s:(((y=n.data.schedule)==null?void 0:y.period_number)||0)-(((v=a.data.schedule)==null?void 0:v.period_number)||0)})}),Object.values(t).sort((e,n)=>e.name.localeCompare(n.name))}),ue=[{name:"day",label:"Day",field:t=>{var e;return z[(e=t.data.schedule)==null?void 0:e.day]||"N/A"},align:"left"},{name:"period",label:"Period",field:t=>{var e;return(e=t.data.schedule)==null?void 0:e.period_number},align:"center"},{name:"subject",label:"Subject",field:t=>{var e,n;return(n=(e=t.data.schedule)==null?void 0:e.cst)==null?void 0:n.subject_name},align:"left"},{name:"status",label:"Status",field:t=>t.data.status,align:"left"},{name:"cw",label:"Classwork (CW)",field:t=>t.data.cw,align:"left"},{name:"hw",label:"Homework (HW)",field:t=>t.data.hw,align:"left"},{name:"notes",label:"Notes",field:t=>t.data.notes,align:"left"}],X=t=>{var a,s;const e=!!((a=t.cw)!=null&&a.trim()),n=!!((s=t.hw)!=null&&s.trim());return!e&&!n?"empty":e&&n?"completed":"partial"},I=(t,e)=>t?t.length>e?t.substring(0,e)+"...":t:"",me=t=>{var e,n,a,s;return{backgroundColor:((n=(e=t.schedule)==null?void 0:e.cst)==null?void 0:n.c_bg)||"#e0e0e0",color:((s=(a=t.schedule)==null?void 0:a.cst)==null?void 0:s.c_text)||"#333"}},L=async()=>{H.value=!0;try{const t=await R.get("/weekly-system/api/teacher/my-weekly-plans",{params:{week_number:q.value,semester_number:N.value}});C.value=(t.data.data||t.data||[]).map(e=>({...e,status:X(e)}))}catch(t){console.error("Error fetching plans:",t),x.notify({type:"negative",message:"Failed to load weekly plans"})}finally{H.value=!1}},Z=t=>{b.value||(K.value=t,E.value=!0)},pe=(t,e)=>{e.stopPropagation(),b.value={cw:t.cw,hw:t.hw,notes:t.notes},$.value=t.id,x.notify({message:'Data copied. Click "Paste" on other cards to apply.',color:"info",icon:"content_copy",timeout:2e3})},ye=t=>{t.stopPropagation(),b.value=null,$.value=null},ve=async(t,e)=>{if(e.stopPropagation(),!!b.value)try{await R.put(`/weekly-system/api/weekly-plans/${t.id}`,{...b.value}),t.cw=b.value.cw,t.hw=b.value.hw,t.notes=b.value.notes,t.status=X(t),x.notify({type:"positive",message:"Data pasted successfully!"})}catch(n){console.error("Error pasting plan:",n),x.notify({type:"negative",message:"Failed to paste data"})}},fe=async t=>{B.value=!0;try{await R.put(`/weekly-system/api/weekly-plans/${t.id}`,{cw:t.cw,hw:t.hw,notes:t.notes}),x.notify({type:"positive",message:"Weekly plan saved!"}),E.value=!1,await L()}catch(e){console.error("Error saving plan:",e),x.notify({type:"negative",message:"Failed to save plan"})}finally{B.value=!1}},be=()=>{const t=document.getElementById("print-area");if(!t){x.notify({type:"negative",message:"Print area not found"});return}const e=window.open("","_blank");e.document.write(`
    <!DOCTYPE html>
    <html lang="en">
    <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>Weekly Plans - Print</title>
      <style>
        * {
          margin: 0;
          padding: 0;
          box-sizing: border-box;
        }
        
        @page {
          size: A4;
          margin: 10mm;
        }
        
        body {
          font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
          background-color: white;
          color: #333;
          line-height: 1.4;
        }
        
        .print-header {
          margin-bottom: 24px;
          border-bottom: 2px solid #1976d2;
          padding-bottom: 12px;
        }
        
        .print-header h2 {
          font-size: 24px;
          color: #1976d2;
          margin-bottom: 8px;
        }
        
        .print-header p {
          font-size: 12px;
          color: #666;
          margin: 4px 0;
        }
        
        .print-table {
          width: 100%;
          border-collapse: collapse;
          margin-bottom: 20px;
          font-size: 11px;
        }
        
        .print-table thead {
          background-color: #f5f5f5;
          border-bottom: 2px solid #1976d2;
        }
        
        .print-table th {
          padding: 10px;
          text-align: left;
          font-weight: 600;
          color: #1976d2;
          border: 1px solid #ddd;
        }
        
        .print-table td {
          padding: 10px;
          border: 1px solid #ddd;
          word-wrap: break-word;
          overflow-wrap: break-word;
        }
        
        .print-table tbody tr:nth-child(even) {
          background-color: #f9f9f9;
        }
        
        .print-table tbody tr:hover {
          background-color: #f0f0f0;
        }
        
        .text-center {
          text-align: center;
        }
        
        .content-cell {
          max-width: 120px;
          white-space: pre-wrap;
        }
        
        .print-footer {
          margin-top: 20px;
          border-top: 1px solid #ddd;
          padding-top: 10px;
          text-align: right;
          font-size: 10px;
          color: #999;
        }
        
        @media print {
          body {
            background-color: white;
          }
          
          .print-table {
            page-break-inside: avoid;
          }
          
          .print-table tbody tr {
            page-break-inside: avoid;
          }
        }
      </style>
    </head>
    <body>
      ${t.innerHTML}
    </body>
    </html>
  `),e.document.close(),e.onload=()=>{setTimeout(()=>{e.print(),e.close()},250)}};return Se([q,N],()=>{L()}),Ne(()=>{const t=new Date,e=new Date(t.getFullYear(),0,1);T.value=Math.ceil(((t-e)/864e5+e.getDay()+1)/7),q.value=T.value>J.value?1:T.value,L()}),(t,e)=>{const n=ze("Head");return i(),u(D,null,[l(n,{title:"My Weekly Plans"}),o("div",Ee,[l($e),o("div",Fe,[o("div",Me,[o("h4",Ue,[l(g,{name:"edit_note",class:"q-mr-sm",color:"primary"}),e[11]||(e[11]=w(" My Weekly Plans "))]),e[12]||(e[12]=o("p",{class:"text-grey-7 q-mb-none"}," Fill in your classwork and homework for each class ",-1))])]),l(W,{flat:"",bordered:"",class:"q-pa-md q-mb-lg"},{default:r(()=>{var a;return[o("div",Be,[o("div",He,[l(Te,{modelValue:q.value,"onUpdate:modelValue":e[0]||(e[0]=s=>q.value=s),"max-weeks":J.value,"current-week":T.value},null,8,["modelValue","max-weeks","current-week"])]),o("div",Oe,[l(G,{modelValue:N.value,"onUpdate:modelValue":e[1]||(e[1]=s=>N.value=s),options:[{label:"Semester 1",value:1},{label:"Semester 2",value:2}],label:"Semester",outlined:"",dense:"","emit-value":"","map-options":""},null,8,["modelValue"])]),o("div",Ae,[l(xe,{value:A.value/100,size:"25px",color:de.value,"track-color":"grey-3",rounded:"",style:{width:"150px"}},{default:r(()=>[o("div",Ie,[o("span",Le,c(A.value)+"%",1)])]),_:1},8,["value","color"])])]),o("div",Ye,[o("div",Ge,[l(G,{modelValue:f.value,"onUpdate:modelValue":e[2]||(e[2]=s=>f.value=s),options:ne.value,label:"Filter by Classroom",outlined:"",dense:"",multiple:"","use-chips":"",clearable:""},null,8,["modelValue","options"])]),o("div",Re,[l(G,{modelValue:j.value,"onUpdate:modelValue":e[3]||(e[3]=s=>j.value=s),options:We(re),label:"Filter by Day",outlined:"",dense:"",multiple:"","use-chips":"",clearable:"","emit-value":"","map-options":""},null,8,["modelValue","options"])]),o("div",Je,[((a=f.value)==null?void 0:a.length)>0?(i(),S(Q,{key:0,color:"primary",label:"Preview & Print",icon:"print",onClick:e[4]||(e[4]=s=>F.value=!0),outline:""})):P("",!0)])])]}),_:1}),H.value?(i(),u("div",Ke,[l(ke,{size:"50px",color:"primary"})])):C.value.length?(i(),S(W,{key:2,flat:"",bordered:"",class:"q-mb-lg"},{default:r(()=>[l(Pe,{modelValue:M.value,"onUpdate:modelValue":e[5]||(e[5]=a=>M.value=a),dense:"",class:"text-grey-7","active-color":"primary","indicator-color":"primary",align:"left"},{default:r(()=>[l(ee,{name:"by-day",label:"By Day",icon:"calendar_today"}),l(ee,{name:"by-classroom",label:"By Classroom",icon:"meeting_room",disable:!f.value||f.value.length===0},null,8,["disable"])]),_:1},8,["modelValue"])]),_:1})):(i(),S(W,{key:1,flat:"",bordered:"",class:"text-center q-pa-xl"},{default:r(()=>[l(g,{name:"event_note",size:"64px",color:"grey-5"}),e[13]||(e[13]=o("p",{class:"text-h6 text-grey-7 q-mt-md"},"No plans for this week",-1)),e[14]||(e[14]=o("p",{class:"text-grey-6"},"Plans will appear once generated by admin",-1))]),_:1})),se(o("div",Xe,[(i(!0),u(D,null,U(ie.value,a=>(i(),u("div",{key:a.dayNumber,class:"day-column"},[o("div",Ze,c(a.dayName),1),(i(!0),u(D,null,U(a.plans,s=>{var d,p,y,v,_;return i(),u("div",{key:s.id,class:De(["plan-card",{"is-completed":s.status==="completed","is-partial":s.status==="partial"}]),onClick:h=>Z(s)},[o("div",{class:"plan-header",style:ae(me(s))},[o("span",tt,"P"+c((d=s.schedule)==null?void 0:d.period_number),1),o("span",st,c((y=(p=s.schedule)==null?void 0:p.cst)==null?void 0:y.subject_name),1),l(te),o("div",ot,[$.value===s.id?(i(),S(Q,{key:0,icon:"close",size:"xs",flat:"",round:"",color:"white",onClick:e[6]||(e[6]=h=>ye(h))},{default:r(()=>[l(Y,null,{default:r(()=>e[15]||(e[15]=[w("Cancel Copy")])),_:1})]),_:1})):b.value?P("",!0):(i(),S(Q,{key:1,icon:"content_copy",size:"xs",flat:"",round:"",color:"white",onClick:h=>pe(s,h)},{default:r(()=>[l(Y,null,{default:r(()=>e[16]||(e[16]=[w("Copy CW, HW & Notes")])),_:1})]),_:2},1032,["onClick"])),b.value&&$.value!==s.id?(i(),S(Q,{key:2,icon:"content_paste",size:"xs",flat:"",round:"",color:"white",onClick:h=>ve(s,h)},{default:r(()=>[l(Y,null,{default:r(()=>e[17]||(e[17]=[w("Paste Data")])),_:1})]),_:2},1032,["onClick"])):P("",!0)])],4),o("div",at,[l(g,{name:"meeting_room",size:"xs"}),w(" "+c((_=(v=s.schedule)==null?void 0:v.cst)==null?void 0:_.classroom_name),1)]),o("div",lt,[l(le,{status:s.status},null,8,["status"])]),s.cw||s.hw?(i(),u("div",nt,[s.cw?(i(),u("div",rt,[l(g,{name:"school",size:"xs",color:"blue"}),e[18]||(e[18]=w(" CW filled "))])):P("",!0),s.hw?(i(),u("div",it,[l(g,{name:"home_work",size:"xs",color:"orange"}),e[19]||(e[19]=w(" HW filled "))])):P("",!0)])):P("",!0)],10,et)}),128))]))),128))],512),[[oe,M.value==="by-day"]]),se(o("div",dt,[(i(!0),u(D,null,U(ce.value,a=>(i(),u("div",{key:a.name,class:"classroom-card q-mb-lg"},[l(W,{flat:"",bordered:""},{default:r(()=>[o("div",ct,[o("h5",ut,[l(g,{name:"meeting_room"}),w(" "+c(a.name)+" ",1),l(_e,{dense:"",label:`${a.plans.length} classes`,"text-color":"white"},null,8,["label"])])]),l(qe,{flat:"",bordered:"",rows:a.plans,columns:ue,"row-key":"id",dense:"",class:"classroom-table",onRowClick:e[7]||(e[7]=(s,d)=>Z(d.data))},{"body-cell-day":r(s=>[l(k,{props:s},{default:r(()=>{var d;return[o("strong",null,c(z[(d=s.row.data.schedule)==null?void 0:d.day]||"N/A"),1)]}),_:2},1032,["props"])]),"body-cell-period":r(s=>[l(k,{props:s,class:"text-center"},{default:r(()=>{var d;return[l(Ce,{label:`P${(d=s.row.data.schedule)==null?void 0:d.period_number}`},null,8,["label"])]}),_:2},1032,["props"])]),"body-cell-subject":r(s=>[l(k,{props:s},{default:r(()=>{var d,p,y,v,_,h;return[o("span",{class:"subject-badge",style:ae({backgroundColor:(p=(d=s.row.data.schedule)==null?void 0:d.cst)==null?void 0:p.c_bg,color:(v=(y=s.row.data.schedule)==null?void 0:y.cst)==null?void 0:v.c_text})},c((h=(_=s.row.data.schedule)==null?void 0:_.cst)==null?void 0:h.subject_name),5)]}),_:2},1032,["props"])]),"body-cell-status":r(s=>[l(k,{props:s},{default:r(()=>[l(le,{status:s.row.data.status},null,8,["status"])]),_:2},1032,["props"])]),"body-cell-cw":r(s=>[l(k,{props:s,class:"content-preview"},{default:r(()=>[s.row.data.cw?(i(),u("div",mt,[l(g,{name:"school",size:"xs"}),o("span",pt,c(I(s.row.data.cw,30)),1)])):(i(),u("span",yt,"-"))]),_:2},1032,["props"])]),"body-cell-hw":r(s=>[l(k,{props:s,class:"content-preview"},{default:r(()=>[s.row.data.hw?(i(),u("div",vt,[l(g,{name:"home_work",size:"xs"}),o("span",ft,c(I(s.row.data.hw,30)),1)])):(i(),u("span",bt,"-"))]),_:2},1032,["props"])]),"body-cell-notes":r(s=>[l(k,{props:s,class:"content-preview"},{default:r(()=>[s.row.data.notes?(i(),u("span",ht,c(I(s.row.data.notes,20)),1)):(i(),u("span",gt,"-"))]),_:2},1032,["props"])]),_:2},1032,["rows"])]),_:2},1024)]))),128)),!f.value||f.value.length===0?(i(),u("div",wt,[l(g,{name:"meeting_room",size:"64px",color:"grey-5"}),e[20]||(e[20]=o("p",{class:"text-h6 text-grey-7 q-mt-md"},"No classrooms selected",-1)),e[21]||(e[21]=o("p",{class:"text-grey-6"},"Select classrooms from the filter above to view by classroom",-1))])):P("",!0)],512),[[oe,M.value==="by-classroom"]]),l(je,{modelValue:E.value,"onUpdate:modelValue":e[8]||(e[8]=a=>E.value=a),plan:K.value,saving:B.value,onSubmit:fe},null,8,["modelValue","plan","saving"]),l(we,{modelValue:F.value,"onUpdate:modelValue":e[10]||(e[10]=a=>F.value=a),maximized:""},{default:r(()=>[l(W,{class:"full-height print-preview-card"},{default:r(()=>[l(Qe,{class:"bg-primary text-white"},{default:r(()=>[l(Ve,null,{default:r(()=>e[22]||(e[22]=[w("Print Preview - A4 Format")])),_:1}),l(te),l(Q,{icon:"print",label:"Print",color:"white",flat:"",onClick:be}),l(Q,{icon:"close",flat:"",round:"",dense:"",onClick:e[9]||(e[9]=a=>F.value=!1)})]),_:1}),o("div",_t,[o("div",xt,[o("div",kt,[e[23]||(e[23]=o("h2",{class:"q-ma-none"},"Weekly Plans",-1)),o("p",Pt," Week "+c(q.value)+" - Semester "+c(N.value),1),o("p",Ct," Classroom: "+c(f.value.join(", ")),1),o("p",qt,c(new Date().toLocaleDateString("en-US",{year:"numeric",month:"long",day:"numeric"})),1)]),o("table",Qt,[e[24]||(e[24]=o("thead",null,[o("tr",null,[o("th",null,"Day"),o("th",null,"Period"),o("th",null,"Subject"),o("th",null,"Classroom"),o("th",null,"Classwork (CW)"),o("th",null,"Homework (HW)"),o("th",null,"Notes")])],-1)),o("tbody",null,[(i(!0),u(D,null,U(O.value,a=>{var s,d,p,y,v,_;return i(),u("tr",{key:a.id},[o("td",null,c(z[(s=a.schedule)==null?void 0:s.day]||"N/A"),1),o("td",Vt,"P"+c((d=a.schedule)==null?void 0:d.period_number),1),o("td",null,c((y=(p=a.schedule)==null?void 0:p.cst)==null?void 0:y.subject_name),1),o("td",null,c((_=(v=a.schedule)==null?void 0:v.cst)==null?void 0:_.classroom_name),1),o("td",St,c(a.cw||"-"),1),o("td",Nt,c(a.hw||"-"),1),o("td",zt,c(a.notes||"-"),1)])}),128))])]),o("div",Wt,[o("p",Dt," Generated: "+c(new Date().toLocaleString()),1)])])])]),_:1})]),_:1},8,["modelValue"])])],64)}}},ms=he(Tt,[["__scopeId","data-v-8695e855"]]);export{ms as default};
