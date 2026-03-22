import{_ as be,j as ke,r as u,a as k,a0 as le,Z as Pe,aC as xe,L as v,o as m,m as i,y as l,k as S,D as se,v as _,Q as N,V as r,A as c,w as p,aa as Ve,z as V,as as ae,aI as Ce,q as f,P as j,ba as qe,aO as De,aN as oe,aD as ne,S as F,U as X,X as Te,Y as Me,a9 as re,s as Z,aw as ze,ay as Se,a3 as Ne,ab as H}from"./feature-admin-core-BWNP0pMM.js";import{a as Ee}from"./app-Co2XdjjV.js";import{u as Ie}from"./useWeeklyPlansStore-B7EzUU4Z.js";import Qe from"./WeekSelector-BTRIfBYp.js";import Le from"./WeeklyPlanEditor-CWAa84RD.js";import $e from"./WeeklyPlanClassroomList-BCzBFpd6.js";import We from"./StatusBadge-CQEEy-5n.js";import je from"./WeeklyPlanMenu-CCb-kJPS.js";import"./vendor-xlsx-DkH2s96g.js";import"./vendor-firebase-BRt416St.js";import"./feature-teacher-portal-BTtWCv0a.js";import"./feature-bm-_53udHjM.js";import"./feature-focus-grid-D3HdvOqv.js";import"./schoolData-DzVVT79o.js";import"./WeeklySystemGuide-CRgmN_jy.js";import"./MainSchoolData-DcLHOogs.js";import"./SchoolSettingsDialog-BkHl3yse.js";const Fe={class:"q-pa-md"},He={class:"row items-center q-mb-lg"},Ue={class:"col"},Ae={class:"q-ma-none text-weight-bold"},Be={class:"text-grey-7 q-mb-none"},Oe={class:"row q-gutter-md items-center"},Re={class:"col-12 col-sm-6 col-md-4"},Ye={class:"col-auto q-ml-auto"},Ge={class:"absolute-full flex flex-center"},Xe={class:"text-caption text-weight-bold"},Ze={class:"row q-gutter-md items-center q-mt-sm"},Je={class:"col-12 col-sm-6 col-md-4"},Ke={class:"col-12 col-sm-6 col-md-4"},et={class:"col-auto"},tt={key:0,class:"col-12 col-sm-6 col-md-auto q-gutter-sm"},lt={key:0,class:"row justify-center q-pa-xl"},st={class:"text-h6 text-grey-7 q-mt-md"},at={class:"text-grey-6"},ot={class:"plans-grid"},nt={class:"day-header"},rt=["onClick"],it={class:"period-badge"},ct={class:"subject-name"},dt={class:"flex q-gutter-xs"},ut={class:"classroom text-caption text-grey-7"},mt={class:"plan-status"},yt={key:0,class:"plan-preview text-caption"},pt={key:0,class:"preview-item"},vt={key:1,class:"preview-item"},ht={class:"preview-container q-pa-md"},wt={id:"print-area",class:"a4-page"},_t={class:"print-header q-mb-lg"},ft={class:"q-ma-none"},gt={class:"q-ma-xs text-grey-7"},bt={class:"q-ma-xs text-grey-7"},kt={class:"q-ma-xs text-grey-7"},Pt={class:"print-table"},xt={class:"text-center"},Vt=["innerHTML"],Ct=["innerHTML"],qt=["innerHTML"],Dt={class:"print-footer q-mt-lg"},Tt={class:"text-caption text-grey-6"},Mt={__name:"MyWeeklyPlans",setup(zt){const{t:n,locale:ie}=Ee(),y=ke(),E=Ie(),P=u([]),C=u(null),g=u(1),ce=u(18),U=u(1),w=u([]),I=u([]),h=u(null),Q=u(null),q=u(!1),J=u(null),L=u(!1),$=u(!1),W=u("by-classroom"),A=u(!1),B=u({}),O=u(!1),K=k(()=>[{label:n("weeklyPlans.fullDays.1"),value:1},{label:n("weeklyPlans.fullDays.2"),value:2},{label:n("weeklyPlans.fullDays.3"),value:3},{label:n("weeklyPlans.fullDays.4"),value:4},{label:n("weeklyPlans.fullDays.5"),value:5}]),ee=k(()=>{const t={};return K.value.forEach(e=>{t[e.value]=e.label}),t}),de=k(()=>{const t=new Set;return P.value.forEach(e=>{var o,a;(a=(o=e.schedule)==null?void 0:o.cst)!=null&&a.classroom_name&&t.add(e.schedule.cst.classroom_name)}),Array.from(t).sort()}),R=k(()=>{var e,o;let t=P.value;return(e=w.value)!=null&&e.length&&(t=t.filter(a=>{var s,d;return w.value.includes((d=(s=a.schedule)==null?void 0:s.cst)==null?void 0:d.classroom_name)})),(o=I.value)!=null&&o.length&&(t=t.filter(a=>{var s;return I.value.includes((s=a.schedule)==null?void 0:s.day)})),t}),ue=k(()=>{const t={};return R.value.forEach(e=>{var a;const o=(a=e.schedule)==null?void 0:a.day;!o||o<1||o>7||(t[o]||(t[o]={dayNumber:o,dayName:n(`weeklyPlans.fullDays.${o}`)||`Day ${o}`,plans:[]}),t[o].plans.push(e))}),Object.values(t).forEach(e=>{e.plans.sort((o,a)=>{var s,d;return((s=o.schedule)==null?void 0:s.period_number)-((d=a.schedule)==null?void 0:d.period_number)})}),Object.values(t).sort((e,o)=>e.dayNumber-o.dayNumber)}),Y=k(()=>{const t=P.value.length;if(!t)return 0;const e=P.value.filter(o=>o.status==="completed").length;return Math.round(e/t*100)}),me=k(()=>{const t=Y.value;return t===100?"positive":t>=50?"warning":"negative"}),G=t=>{var a,s;const e=!!((a=t.cw)!=null&&a.trim()),o=!!((s=t.hw)!=null&&s.trim());return!e&&!o?"empty":e&&o?"completed":"partial"},ye=t=>{var e,o,a,s;return{backgroundColor:((o=(e=t.schedule)==null?void 0:e.cst)==null?void 0:o.c_bg)||"#e0e0e0",color:((s=(a=t.schedule)==null?void 0:a.cst)==null?void 0:s.c_text)||"#333"}},b=async()=>{O.value=!0;try{const t=await H.get("/weekly-system/api/teacher/my-weekly-plans",{params:{week_number:g.value,semester_number:E.semesterNumber}});P.value=(t.data.data||t.data||[]).map(e=>{var a,s,d;return{id:e.id,academic_year_id:e.academic_year_id,semester_number:e.semester_number,week_number:e.week_number,day_number:e.day_number,period_order:e.period_order,schedule_id:e.schedule_id,cw:e.cw||"",hw:e.hw||"",notes:e.notes||"",comments:e.comments,created_at:e.created_at,updated_at:e.updated_at,status:G(e),...e.schedule?{schedule:{id:e.schedule.id,cst_id:e.schedule.cst_id,day_number:e.schedule.day_number,period_order:e.schedule.period_order,cst:e.schedule.cst},classroom_id:(a=e.schedule.cst)==null?void 0:a.classroom_id,subject_id:(s=e.schedule.cst)==null?void 0:s.subject_id,teacher_id:(d=e.schedule.cst)==null?void 0:d.teacher_id}:{}}})}catch(t){console.error("Error fetching plans:",t),y.notify({type:"negative",message:"Failed to load weekly plans"})}finally{O.value=!1}},te=t=>{h.value||(J.value=t,q.value=!0)},pe=(t,e)=>{e.stopPropagation(),h.value={cw:t.cw,hw:t.hw,notes:t.notes},Q.value=t.id,y.notify({message:n("weeklyPlans.teacher.copy"),color:"info",icon:"content_copy",timeout:2e3})},ve=t=>{t.stopPropagation(),h.value=null,Q.value=null},he=async(t,e)=>{var o;if(console.log("Pasting plan:",t.id),console.log("Pasting plan:",t),e.stopPropagation(),!!h.value){if(!t.id||t.id<=0||t.id>1e4){y.notify({type:"negative",message:"Invalid plan ID"});return}try{await H.put(`/weekly-system/api/weekly-plans/${t.id}`,{...h.value}),t.cw=h.value.cw,t.hw=h.value.hw,t.notes=h.value.notes,t.status=G(t),y.notify({type:"positive",message:n("weeklyPlans.teacher.paste")})}catch(a){console.error("Error pasting plan:",a),((o=a.response)==null?void 0:o.status)===404?(y.notify({type:"warning",message:"Plan not found. Refreshing data...",timeout:2e3}),await b()):y.notify({type:"negative",message:"Failed to paste data"})}}},we=async t=>{var e;if(L.value=!0,!t.id||t.id<=0||t.id>1e4){y.notify({type:"negative",message:"Invalid plan ID"}),L.value=!1;return}try{await H.put(`/weekly-system/api/weekly-plans/${t.id}`,{cw:t.cw,hw:t.hw,notes:t.notes}),y.notify({type:"positive",message:"Weekly plan saved!"}),q.value=!1,await b()}catch(o){console.error("Error saving plan:",o),((e=o.response)==null?void 0:e.status)===404?(y.notify({type:"warning",message:"Plan not found. Refreshing data...",timeout:2e3}),await b(),q.value=!1):y.notify({type:"negative",message:"Failed to save plan"})}finally{L.value=!1}},_e=async t=>{var e;if(!(!t.cw&&!t.hw&&!t.notes)&&!B.value[t.id]){if(!t.id||t.id<=0||t.id>1e4){y.notify({type:"negative",message:"Invalid plan ID"});return}B.value[t.id]=!0;try{await H.put(`/weekly-system/api/weekly-plans/${t.id}`,{cw:t.cw,hw:t.hw,notes:t.notes}),t.status=G(t),y.notify({type:"positive",message:"Saved!",timeout:1e3,position:"top"})}catch(o){console.error("Error saving inline edit:",o),((e=o.response)==null?void 0:e.status)===404?(y.notify({type:"warning",message:"Plan not found. Refreshing data...",timeout:2e3}),await b()):y.notify({type:"negative",message:"Failed to save changes"})}finally{delete B.value[t.id]}}},fe=()=>{const t=document.getElementById("print-area");if(!t){y.notify({type:"negative",message:"Print area not found"});return}const e=window.open("","_blank");e.document.write(`
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
          line-height: 1.5;
        }
        
        .content-cell a {
          color: #1976d2;
          text-decoration: underline;
        }
        
        .content-cell p {
          margin: 4px 0;
        }
        
        .content-cell ul,
        .content-cell ol {
          margin: 4px 0;
          padding-left: 20px;
        }
        
        .content-cell li {
          margin: 2px 0;
        }
        
        .content-cell strong {
          font-weight: 600;
        }
        
        .content-cell em {
          font-style: italic;
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
  `),e.document.close(),e.onload=()=>{setTimeout(()=>{e.print(),e.close()},250)}},ge=()=>{C.value&&(C.value.openMobilePrint(),setTimeout(()=>{C.value.downloadPDF()},100))};return le(()=>g.value,()=>{b()}),le(()=>E.semesterNumber,()=>{b()}),Pe(()=>{const t=new Date,e=new Date(t.getFullYear(),0,1),o=Math.ceil(((t.getTime()-e.getTime())/864e5+e.getDay()+1)/7);U.value=Math.min(o,18),g.value=U.value,b()}),(t,e)=>{const o=xe("Head");return m(),v(F,null,[i(o,{title:"My Weekly Plans"}),l("div",Fe,[i(je),l("div",He,[l("div",Ue,[l("h4",Ae,[i(N,{name:"edit_note",class:"q-mr-sm",color:"primary"}),_(" "+r(c(n)("weeklyPlans.teacher.myWeeklyPlans")),1)]),l("p",Be,r(c(n)("weeklyPlans.teacher.fillInContent")),1)])]),i(j,{flat:"",bordered:"",class:"q-pa-md q-mb-lg"},{default:p(()=>{var a;return[l("div",Oe,[l("div",Re,[i(Qe,{modelValue:g.value,"onUpdate:modelValue":e[0]||(e[0]=s=>g.value=s),"max-weeks":ce.value,"current-week":U.value},null,8,["modelValue","max-weeks","current-week"])]),l("div",Ye,[i(Ve,{value:Y.value/100,size:"25px",color:me.value,"track-color":"grey-3",rounded:"",style:{width:"150px"}},{default:p(()=>[l("div",Ge,[l("span",Xe,r(Y.value)+"%",1)])]),_:1},8,["value","color"])])]),l("div",Ze,[l("div",Je,[i(ae,{modelValue:w.value,"onUpdate:modelValue":e[1]||(e[1]=s=>w.value=s),options:de.value,label:c(n)("weeklyPlans.teacher.selectToView"),outlined:"",dense:"",multiple:"","use-chips":"",clearable:""},null,8,["modelValue","options","label"])]),l("div",Ke,[i(ae,{modelValue:I.value,"onUpdate:modelValue":e[2]||(e[2]=s=>I.value=s),options:K.value,label:c(n)("weeklyPlans.filterByDay"),outlined:"",dense:"",multiple:"","use-chips":"",clearable:"","emit-value":"","map-options":""},null,8,["modelValue","options","label"])]),l("div",et,[i(Ce,{modelValue:A.value,"onUpdate:modelValue":e[3]||(e[3]=s=>A.value=s),label:c(n)("weeklyPlans.teacher.inlineEditMode"),color:"primary",icon:"edit"},null,8,["modelValue","label"])]),((a=w.value)==null?void 0:a.length)>0?(m(),v("div",tt,[i(f,{color:"primary",label:c(n)("weeklyPlans.teacher.previewAndPrint"),icon:"print",onClick:e[4]||(e[4]=s=>$.value=!0),outline:""},null,8,["label"]),i(f,{color:"secondary",label:c(n)("weeklyPlans.teacher.mobilePrint"),icon:"phone_iphone",onClick:e[5]||(e[5]=s=>{var d;return(d=C.value)==null?void 0:d.openMobilePrint()}),outline:""},null,8,["label"]),i(f,{color:"red-7",label:c(n)("weeklyPlans.teacher.downloadPdf"),icon:"picture_as_pdf",onClick:ge,outline:""},null,8,["label"])])):V("",!0)])]}),_:1}),O.value?(m(),v("div",lt,[i(qe,{size:"50px",color:"primary"})])):P.value.length?(m(),S(j,{key:2,flat:"",bordered:"",class:"q-mb-lg"},{default:p(()=>[i(De,{modelValue:W.value,"onUpdate:modelValue":e[6]||(e[6]=a=>W.value=a),dense:"",class:"text-grey-7","active-color":"primary","indicator-color":"primary",align:"left"},{default:p(()=>[i(oe,{name:"by-classroom",label:c(n)("weeklyPlans.teacher.byClassroom"),icon:"meeting_room",disable:!w.value||w.value.length===0},null,8,["label","disable"]),i(oe,{name:"by-day",label:c(n)("weeklyPlans.teacher.byDay"),icon:"calendar_today"},null,8,["label"])]),_:1},8,["modelValue"])]),_:1})):(m(),S(j,{key:1,flat:"",bordered:"",class:"text-center q-pa-xl"},{default:p(()=>[i(N,{name:"event_note",size:"64px",color:"grey-5"}),l("p",st,r(c(n)("weeklyPlans.teacher.noPlans")),1),l("p",at,r(c(n)("weeklyPlans.teacher.waitAdminGen")),1)]),_:1})),se(l("div",ot,[(m(!0),v(F,null,X(ue.value,a=>(m(),v("div",{key:a.dayNumber,class:"day-column"},[l("div",nt,r(a.dayName),1),(m(!0),v(F,null,X(a.plans,s=>{var d,D,T,M,z;return m(),v("div",{key:s.id,class:Te(["plan-card",{"is-completed":s.status==="completed","is-partial":s.status==="partial"}]),onClick:x=>te(s)},[l("div",{class:"plan-header",style:Me(ye(s))},[l("span",it,"P"+r((d=s.schedule)==null?void 0:d.period_number),1),l("span",ct,r((T=(D=s.schedule)==null?void 0:D.cst)==null?void 0:T.subject_name),1),i(re),l("div",dt,[Q.value===s.id?(m(),S(f,{key:0,icon:"close",size:"xs",flat:"",round:"",color:"white",onClick:e[7]||(e[7]=x=>ve(x))},{default:p(()=>[i(Z,null,{default:p(()=>[_(r(c(n)("weeklyPlans.teacher.cancelCopy")),1)]),_:1})]),_:1})):h.value?V("",!0):(m(),S(f,{key:1,icon:"content_copy",size:"xs",flat:"",round:"",color:"white",onClick:x=>pe(s,x)},{default:p(()=>[i(Z,null,{default:p(()=>[_(r(c(n)("weeklyPlans.teacher.copy")),1)]),_:1})]),_:2},1032,["onClick"])),h.value&&Q.value!==s.id?(m(),S(f,{key:2,icon:"content_paste",size:"xs",flat:"",round:"",color:"white",onClick:x=>he(s,x)},{default:p(()=>[i(Z,null,{default:p(()=>[_(r(c(n)("weeklyPlans.teacher.paste")),1)]),_:1})]),_:2},1032,["onClick"])):V("",!0)])],4),l("div",ut,[i(N,{name:"meeting_room",size:"xs"}),_(" "+r((z=(M=s.schedule)==null?void 0:M.cst)==null?void 0:z.classroom_name),1)]),l("div",mt,[i(We,{status:s.status},null,8,["status"])]),s.cw||s.hw?(m(),v("div",yt,[s.cw?(m(),v("div",pt,[i(N,{name:"school",size:"xs",color:"blue"}),_(" "+r(c(n)("weeklyPlans.teacher.cwFilled")),1)])):V("",!0),s.hw?(m(),v("div",vt,[i(N,{name:"home_work",size:"xs",color:"orange"}),_(" "+r(c(n)("weeklyPlans.teacher.hwFilled")),1)])):V("",!0)])):V("",!0)],10,rt)}),128))]))),128))],512),[[ne,W.value==="by-day"]]),se(l("div",null,[i($e,{ref_key:"classroomListRef",ref:C,plans:R.value,days:ee.value,"inline-edit-mode":A.value,"copied-data":h.value,"week-number":g.value,"semester-number":c(E).semesterNumber,onEdit:te,onSave:_e},null,8,["plans","days","inline-edit-mode","copied-data","week-number","semester-number"])],512),[[ne,W.value==="by-classroom"]]),i(Le,{modelValue:q.value,"onUpdate:modelValue":e[8]||(e[8]=a=>q.value=a),plan:J.value,saving:L.value,onSubmit:we},null,8,["modelValue","plan","saving"]),i(Ne,{modelValue:$.value,"onUpdate:modelValue":e[10]||(e[10]=a=>$.value=a),maximized:""},{default:p(()=>[i(j,{class:"full-height print-preview-card"},{default:p(()=>[i(ze,{class:"bg-primary text-white"},{default:p(()=>[i(Se,null,{default:p(()=>[_(r(c(n)("weeklyPlans.teacher.previewAndPrint")),1)]),_:1}),i(re),i(f,{icon:"print",label:c(n)("weeklyPlans.print"),color:"white",flat:"",onClick:fe},null,8,["label"]),i(f,{icon:"close",flat:"",round:"",dense:"",onClick:e[9]||(e[9]=a=>$.value=!1)})]),_:1}),l("div",ht,[l("div",wt,[l("div",_t,[l("h2",ft,r(c(n)("weeklyPlans.teacher.myWeeklyPlans")),1),l("p",gt,r(c(n)("weeklyPlans.week"))+" "+r(g.value)+" - "+r(c(n)("weeklyPlans.semester"))+" "+r(c(E).semesterNumber),1),l("p",bt,r(c(n)("weeklyPlans.classroom"))+": "+r(w.value.join(", ")),1),l("p",kt,r(new Date().toLocaleDateString(c(ie),{year:"numeric",month:"long",day:"numeric"})),1)]),l("table",Pt,[l("thead",null,[l("tr",null,[l("th",null,r(c(n)("weeklyPlans.day")),1),l("th",null,r(c(n)("weeklyPlans.period")),1),l("th",null,r(c(n)("weeklyPlans.subject")),1),l("th",null,r(c(n)("weeklyPlans.classroom")),1),l("th",null,r(c(n)("weeklyPlans.classwork")),1),l("th",null,r(c(n)("weeklyPlans.homework")),1),l("th",null,r(c(n)("weeklyPlans.notes")),1)])]),l("tbody",null,[(m(!0),v(F,null,X(R.value,a=>{var s,d,D,T,M,z;return m(),v("tr",{key:a.id},[l("td",null,r(ee.value[(s=a.schedule)==null?void 0:s.day]||"N/A"),1),l("td",xt,"P"+r((d=a.schedule)==null?void 0:d.period_number),1),l("td",null,r((T=(D=a.schedule)==null?void 0:D.cst)==null?void 0:T.subject_name),1),l("td",null,r((z=(M=a.schedule)==null?void 0:M.cst)==null?void 0:z.classroom_name),1),l("td",{class:"content-cell",innerHTML:a.cw||"-"},null,8,Vt),l("td",{class:"content-cell",innerHTML:a.hw||"-"},null,8,Ct),l("td",{class:"content-cell",innerHTML:a.notes||"-"},null,8,qt)])}),128))])]),l("div",Dt,[l("p",Tt,r(c(n)("weeklyPlans.teacher.generated"))+" "+r(new Date().toLocaleString()),1)])])])]),_:1})]),_:1},8,["modelValue"])])],64)}}},Gt=be(Mt,[["__scopeId","data-v-bb5bfa01"]]);export{Gt as default};
