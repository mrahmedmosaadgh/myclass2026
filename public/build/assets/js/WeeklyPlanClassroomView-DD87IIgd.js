import{_ as be,j as ye,r as _,a as I,a0 as K,Z as ge,L as m,o as u,m as i,k as fe,w as d,y as c,as as X,A as y,F as we,G as ve,E as xe,v as V,V as b,P as F,ba as ke,Q as ee,S as te,U as ae,O as _e,a9 as $e,q as se,af as le,s as oe,a8 as Pe,a7 as E,Y as je,av as De,ab as ne}from"./feature-admin-core-BWNP0pMM.js";import{a as Ne}from"./app-Co2XdjjV.js";import{u as Ce}from"./useWeeklyPlansStore-B7EzUU4Z.js";import"./vendor-xlsx-DkH2s96g.js";import"./vendor-firebase-BRt416St.js";import"./feature-teacher-portal-BTtWCv0a.js";import"./feature-bm-_53udHjM.js";import"./feature-focus-grid-D3HdvOqv.js";import"./schoolData-DzVVT79o.js";const Se={class:"q-pa-md"},ze={class:"row q-gutter-md items-end"},Te={class:"col-12 col-sm-6"},Qe={class:"col-12 col-sm-4"},qe={key:0,class:"row justify-center q-pa-xl"},Ie={class:"text-h6 text-grey-7 q-mt-md"},Ee={class:"text-grey-6"},Le={key:2,class:"classrooms-grid"},Ve={class:"classroom-header bg-primary text-white q-pa-md"},Be={class:"q-ma-none flex items-center q-gutter-sm"},Me={class:"plans-by-day"},We={class:"day-header-row"},Ae={class:"day-info"},He={class:"day-name"},Fe={class:"day-meta"},Oe={class:"subject-teacher"},Ue={class:"teacher-name"},Ye=["innerHTML"],Re={key:1,class:"text-grey-5"},Ze=["innerHTML"],Ge={key:1,class:"text-grey-5"},Je=["innerHTML"],Ke={key:1,class:"text-grey-5"},Xe={__name:"WeeklyPlanClassroomView",setup(et){const{t:l,locale:B}=Ne(),p=Ce(),P=ye(),$=_([]),f=_([]),L=_([]),M=_([]),W=_(!1),A=_(!1),ie=_({rowsPerPage:0}),re=I(()=>[{label:l("weeklyPlans.shortDays.1"),value:1},{label:l("weeklyPlans.shortDays.2"),value:2},{label:l("weeklyPlans.shortDays.3"),value:3},{label:l("weeklyPlans.shortDays.4"),value:4},{label:l("weeklyPlans.shortDays.5"),value:5}]),de=I(()=>[{name:"period",label:l("weeklyPlans.period"),field:"schedule.period_number",align:"center"},{name:"subject",label:l("weeklyPlans.subject"),field:"schedule.cst.subject_name",align:"left"},{name:"cw",label:l("weeklyPlans.classwork"),field:"cw",align:"left"},{name:"hw",label:l("weeklyPlans.homework"),field:"hw",align:"left"},{name:"notes",label:l("weeklyPlans.notes"),field:"notes",align:"left"}]),O=I(()=>$.value.length>0&&f.value.length===$.value.length),ce=I(()=>{var o;let s=M.value;return(o=L.value)!=null&&o.length&&(s=s.filter(e=>{var t;return L.value.includes((t=e.schedule)==null?void 0:t.day)})),s}),U=I(()=>{const s={};return ce.value.forEach(o=>{const e=o.classroom_id;if(!s[e]){const t=$.value.find(a=>a.id===e);s[e]={id:e,name:(t==null?void 0:t.name)||"Unknown",plans:[]}}s[e].plans.push(o)}),Object.values(s).forEach(o=>{o.plans.sort((e,t)=>{var r,n,w,g;const a=(((r=e.schedule)==null?void 0:r.day)||0)-(((n=t.schedule)==null?void 0:n.day)||0);return a!==0?a:(((w=e.schedule)==null?void 0:w.period_number)||0)-(((g=t.schedule)==null?void 0:g.period_number)||0)})}),Object.values(s).sort((o,e)=>o.name.localeCompare(e.name))}),pe=()=>{O.value?f.value=[]:f.value=$.value.map(s=>s.id)},Y=s=>{const o=new Date().getFullYear();let e=new Date(o,0,1),t=e.getDay()===0?0:7-e.getDay(),a=new Date(o,0,1+t);const r=new Date(a);r.setDate(a.getDate()+(p.weekNumber-1)*7);const n=new Date(r);return n.setDate(r.getDate()+(s-1)),n.toLocaleDateString("en-US",{month:"short",day:"numeric",year:"numeric"})},H=s=>{const o={};return s.forEach(e=>{var a;const t=(a=e.schedule)==null?void 0:a.day;o[t]||(o[t]={dayNumber:t,dayName:l(`weeklyPlans.shortDays.${t}`)||`Day ${t}`,plans:[]}),o[t].plans.push(e)}),Object.values(o).forEach(e=>{e.plans.sort((t,a)=>{var r,n;return((r=t.schedule)==null?void 0:r.period_number)-((n=a.schedule)==null?void 0:n.period_number)})}),Object.values(o).sort((e,t)=>e.dayNumber-t.dayNumber)},R=async()=>{if(p.selectedSchoolId){A.value=!0;try{const s=await ne.get(`/api/classrooms?school_id=${p.selectedSchoolId}`);$.value=s.data.data||s.data||[]}catch(s){console.error("Error fetching classrooms:",s)}finally{A.value=!1}}},ue=async()=>{if(!f.value.length){M.value=[];return}W.value=!0;try{const s=f.value.map(e=>ne.get("/weekly-system/api/weekly-plans",{params:{classroom_id:e,week_number:p.weekNumber,semester_number:p.semesterNumber,academic_year_id:p.selectedAcademicYearId}}).then(t=>({classroom_id:e,plans:t.data.data||t.data||[]}))),o=await Promise.all(s);M.value=o.flatMap(e=>e.plans.map(t=>({...t,classroom_id:e.classroom_id})))}catch(s){console.error("Error fetching plans:",s),P.notify({type:"negative",message:"Failed to load weekly plans"})}finally{W.value=!1}},me=s=>{if(!s||!s.plans||!s.plans.length){P.notify({type:"warning",message:"No plans to print for this classroom"});return}const o=H(s.plans),e=`
    @import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap');
    @import url('https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap');
    body { font-family: 'Nunito', 'Tajawal', 'Segoe UI', sans-serif; color: #374151; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .page { max-width: 210mm; margin: 0 auto; padding: 15mm; }
    
    .header-box { 
      background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%); 
      color: white; 
      padding: 20px; 
      border-radius: 12px;
      margin-bottom: 25px;
      box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }
    .header-title { font-size: 24px; font-weight: 800; margin: 0; letter-spacing: -0.5px; }
    .header-meta { margin-top: 8px; font-size: 14px; opacity: 0.9; font-weight: 500; }
    
    .day-container { margin-bottom: 25px; page-break-inside: avoid; }
    .day-header { 
      display: flex; 
      align-items: center; 
      margin-bottom: 1px; /* connected to table */
    }
    .day-pill {
      background: #4f46e5;
      color: white;
      padding: 8px 16px;
      border-radius: 12px 12px 0 0;
      font-weight: 700;
      font-size: 15px;
      display: inline-block;
    }
    .date-label {
      color: #6b7280;
      font-size: 13px;
      margin-left: 12px;
      font-weight: 600;
    }

    table { width: 100%; border-collapse: collapse; border-radius: 0 8px 8px 8px; overflow: hidden; border: 1px solid #e5e7eb; }
    th { 
      background: #f3f4f6; 
      color: #374151; 
      font-weight: 700; 
      padding: 10px 12px; 
      text-align: left; 
      font-size: 13px;
      border-bottom: 2px solid #e5e7eb;
    }
    td { 
      padding: 10px 12px; 
      border-bottom: 1px solid #e5e7eb; 
      font-size: 13px; 
      vertical-align: top;
      line-height: 1.5;
    }
    tr:last-child td { border-bottom: none; }
    tr:nth-child(even) { background-color: #f9fafb; }
    
    .period-col { width: 60px; text-align: center; color: #6b7280; font-weight: 700; }
    .subject-col { width: 180px; }
    .cw-col { width: 35%; }
    .hw-col { width: 35%; }
    
    .subject-badge { 
      display: inline-block; 
      padding: 4px 10px; 
      border-radius: 6px; 
      font-weight: 700; 
      font-size: 12px;
      margin-bottom: 4px;
    }
    .teacher-name { color: #6b7280; font-size: 12px; font-style: italic; display: flex; align-items: center; gap: 4px; }
    
    .label-tag {
      display: inline-block;
      font-size: 10px;
      text-transform: uppercase;
      font-weight: 700;
      letter-spacing: 0.5px;
      margin-right: 6px;
      padding: 2px 6px;
      border-radius: 4px;
    }
    .cw-tag { background: #dbeafe; color: #1e40af; }
    .hw-tag { background: #fef3c7; color: #92400e; }
    
    @media print { 
      @page { margin: 10mm; } 
      body { -webkit-print-color-adjust: exact; }
    }
  `;let t=`
    <div class="page" dir="${B.value==="ar"?"rtl":"ltr"}">
      <div class="header-box">
        <div class="header-title">${l("weeklyPlans.weeklyLearningPlan")} - ${s.name}</div>
        <div class="header-meta">${l("weeklyPlans.week")} ${p.weekNumber} • ${l("weeklyPlans.semester")} ${p.semesterNumber} • ${new Date().toLocaleDateString(B.value,{weekday:"long",year:"numeric",month:"long",day:"numeric"})}</div>
      </div>
  `;o.forEach(r=>{t+=`
      <div class="day-container">
        <div class="day-header">
          <div class="day-pill">${r.dayName}</div>
          <div class="date-label">${Y(r.dayNumber)}</div>
        </div>
        <table>
          <thead>
            <tr>
              <th class="period-col">${l("weeklyPlans.period")}</th>
              <th class="subject-col">${l("weeklyPlans.subject")}</th>
              <th class="cw-col">${l("weeklyPlans.classwork")}</th>
              <th class="hw-col">${l("weeklyPlans.homework")}</th>
            </tr>
          </thead>
          <tbody>
    `,r.plans.forEach(n=>{var j,D,N,C,S,z,T,Q,q;const w=((D=(j=n.schedule)==null?void 0:j.cst)==null?void 0:D.subject_name)||"-",g=((C=(N=n.schedule)==null?void 0:N.cst)==null?void 0:C.c_bg)||"#e0e0e0",h=((z=(S=n.schedule)==null?void 0:S.cst)==null?void 0:z.c_text)||"#111",v=((Q=(T=n.schedule)==null?void 0:T.cst)==null?void 0:Q.teacher_name)||"",x=n.cw?n.cw:"-",k=n.hw?n.hw:"-";t+=`
        <tr>
          <td class="period-col">${((q=n.schedule)==null?void 0:q.period_number)||""}</td>
          <td class="subject-col">
            <div class="subject-badge" style="background:${g}; color:${h}">${w}</div>
            ${v?`<div class="teacher-name">👤 ${v}</div>`:""}
          </td>
          <td>${x!=="-"?'<span class="label-tag cw-tag">CW</span>'+x:'<span style="color:#9ca3af">-</span>'}</td>
          <td>${k!=="-"?'<span class="label-tag hw-tag">HW</span>'+k:'<span style="color:#9ca3af">-</span>'}</td>
        </tr>
      `}),t+="</tbody></table></div>"}),t+="</div>";const a=window.open("","_blank");if(!a){P.notify({type:"negative",message:"Popup blocked: allow popups to print"});return}a.document.write(`<!doctype html><html><head><meta charset="utf-8"><title>Plan - ${s.name}</title><style>${e}</style></head><body>${t}</body></html>`),a.document.close(),a.onload=()=>{setTimeout(()=>{a.print(),a.close()},500)}},he=s=>{if(!s||!s.plans||!s.plans.length){P.notify({type:"warning",message:"No plans to save for this classroom"});return}const o=H(s.plans),e=`
    @import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap');
    @import url('https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap');
    body { font-family: 'Nunito', 'Tajawal', 'Segoe UI', sans-serif; color: #374151; }
    .page { width: 100%; padding: 20px; box-sizing: border-box; }
    
    .header-box { 
      background: #4f46e5; 
      color: white; 
      padding: 20px; 
      border-radius: 12px;
      margin-bottom: 25px;
    }
    .header-title { font-size: 24px; font-weight: 800; margin: 0; }
    .header-meta { margin-top: 8px; font-size: 14px; opacity: 0.9; }
    
    .day-container { margin-bottom: 20px; page-break-inside: avoid; }
    .day-header { 
      display: flex; 
      align-items: center; 
      margin-bottom: 0px;
    }
    .day-pill {
      background: #4f46e5;
      color: white;
      padding: 6px 14px;
      border-radius: 12px 12px 0 0;
      font-weight: 700;
      font-size: 14px;
      display: inline-block;
    }

    table { width: 100%; border-collapse: collapse; border: 1px solid #e5e7eb; border-radius: 0 8px 8px 8px; overflow: hidden; }
    th { 
      background: #f3f4f6; 
      color: #374151; 
      font-weight: 700; 
      padding: 8px 10px; 
      text-align: left; 
      font-size: 12px;
      border-bottom: 2px solid #e5e7eb;
    }
    td { 
      padding: 8px 10px; 
      border-bottom: 1px solid #e5e7eb; 
      font-size: 12px; 
      vertical-align: top;
    }
    tr:nth-child(even) { background-color: #f9fafb; }
    
    .period-col { width: 50px; text-align: center; color: #6b7280; font-weight: 700; }
    .subject-col { width: 160px; }
    
    .subject-badge { 
      display: inline-block; 
      padding: 3px 8px; 
      border-radius: 4px; 
      font-weight: 700; 
      font-size: 11px;
    }
    .teacher-name { color: #6b7280; font-size: 11px; font-style: italic; margin-top: 2px; }
    
    .label-tag {
      font-size: 9px;
      font-weight: 700;
      padding: 1px 4px;
      border-radius: 3px;
      margin-right: 4px;
    }
    .cw-tag { background: #dbeafe; color: #1e40af; }
    .hw-tag { background: #fef3c7; color: #92400e; }
  `;let t=`
    <div class="page" dir="${B.value==="ar"?"rtl":"ltr"}">
      <div class="header-box">
        <div class="header-title">${l("weeklyPlans.weeklyLearningPlan")}</div>
        <div class="header-meta">${s.name} • ${l("weeklyPlans.week")} ${p.weekNumber} • ${l("weeklyPlans.semester")} ${p.semesterNumber}</div>
      </div>
  `;o.forEach(g=>{t+=`
      <div class="day-container">
        <div class="day-header">
          <div class="day-pill">${g.dayName}</div>
        </div>
        <table>
          <thead><tr><th class="period-col">${l("weeklyPlans.period")}</th><th class="subject-col">${l("weeklyPlans.subject")}</th><th>${l("weeklyPlans.classwork")}</th><th>${l("weeklyPlans.homework")}</th></tr></thead>
          <tbody>
    `,g.plans.forEach(h=>{var C,S,z,T,Q,q,Z,G,J;const v=((S=(C=h.schedule)==null?void 0:C.cst)==null?void 0:S.subject_name)||"-",x=((T=(z=h.schedule)==null?void 0:z.cst)==null?void 0:T.c_bg)||"#e0e0e0",k=((q=(Q=h.schedule)==null?void 0:Q.cst)==null?void 0:q.c_text)||"#111",j=((G=(Z=h.schedule)==null?void 0:Z.cst)==null?void 0:G.teacher_name)||"",D=h.cw||"-",N=h.hw||"-";t+=`
        <tr>
          <td class="period-col">${((J=h.schedule)==null?void 0:J.period_number)||""}</td>
          <td class="subject-col">
            <div class="subject-badge" style="background:${x}; color:${k}">${v}</div>
            ${j?`<div class="teacher-name">${j}</div>`:""}
          </td>
          <td>${D!=="-"?'<span class="label-tag cw-tag">CW</span>'+D:"-"}</td>
          <td>${N!=="-"?'<span class="label-tag hw-tag">HW</span>'+N:"-"}</td>
        </tr>
      `}),t+="</tbody></table></div>"}),t+="</div>";const a="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.3/html2pdf.bundle.min.js",r=`WeeklyPlans_${String(s.name).replace(/\s+/g,"_")}_Week${p.weekNumber}.pdf`,n=window.open("","_blank");if(!n){P.notify({type:"negative",message:"Popup blocked: allow popups to save PDF"});return}const w=`
    var opt = { margin:0.3, filename: '${r}', image:{type:'jpeg', quality:0.98}, html2canvas:{scale:2, useCORS:true}, jsPDF:{unit:'in', format:'a4', orientation:'portrait'} };
    function doSave(){ html2pdf().set(opt).from(document.body).save().then(function(){ setTimeout(function(){ window.close(); }, 1000); }); }
    if (typeof html2pdf === 'undefined'){
      var s = document.createElement('script'); s.src = '${a}'; s.onload = doSave; document.head.appendChild(s);
    } else { doSave(); }
  `;n.document.write(`<!doctype html><html><head><meta charset="utf-8"><title>PDF</title><style>${e}</style></head><body>${t}<script>${w} <\/script></body></html>`),n.document.close()};return K(()=>p.selectedSchoolId,()=>{R(),f.value=[]}),K(()=>[f.value,p.weekNumber,p.semesterNumber,p.selectedAcademicYearId],()=>{ue()},{deep:!0}),ge(async()=>{p.selectedSchoolId&&R()}),(s,o)=>(u(),m("div",Se,[i(F,{flat:"",bordered:"",class:"q-pa-md q-mb-lg"},{default:d(()=>[c("div",ze,[c("div",Te,[i(X,{modelValue:f.value,"onUpdate:modelValue":o[0]||(o[0]=e=>f.value=e),options:$.value,"option-value":"id","option-label":"name",label:y(l)("weeklyPlans.filterClassrooms"),outlined:"",dense:"",multiple:"","use-chips":"","emit-value":"","map-options":"",loading:A.value},{"before-options":d(()=>[i(we,{clickable:"",onClick:pe},{default:d(()=>[i(ve,null,{default:d(()=>[i(xe,null,{default:d(()=>[V(b(O.value?y(l)("weeklyPlans.deselectAll"):y(l)("weeklyPlans.selectAll")),1)]),_:1})]),_:1})]),_:1})]),_:1},8,["modelValue","options","label","loading"])]),c("div",Qe,[i(X,{modelValue:L.value,"onUpdate:modelValue":o[1]||(o[1]=e=>L.value=e),options:re.value,label:y(l)("weeklyPlans.filterByDay"),outlined:"",dense:"",multiple:"","use-chips":"",clearable:"","emit-value":"","map-options":""},null,8,["modelValue","options","label"])])])]),_:1}),W.value?(u(),m("div",qe,[i(ke,{size:"50px",color:"primary"})])):U.value.length?(u(),m("div",Le,[(u(!0),m(te,null,ae(U.value,e=>(u(),m("div",{key:e.id,class:"classroom-card q-mb-lg"},[i(F,{flat:"",bordered:""},{default:d(()=>[c("div",Ve,[c("h5",Be,[i(ee,{name:"meeting_room"}),V(" "+b(e.name)+" ",1),i(_e,{dense:"",label:`${e.plans.length} ${y(l)("weeklyPlans.classes")}`,"text-color":"white"},null,8,["label"]),i($e),i(se,{flat:"",dense:"",round:"",icon:"picture_as_pdf",color:"white",onClick:le(t=>he(e),["stop"]),disable:!e.plans||!e.plans.length},{default:d(()=>[i(oe,null,{default:d(()=>[V(b(y(l)("weeklyPlans.savePdf")),1)]),_:1})]),_:2},1032,["onClick","disable"]),i(se,{flat:"",dense:"",round:"",icon:"print",color:"white",onClick:le(t=>me(e),["stop"]),disable:!e.plans||!e.plans.length},{default:d(()=>[i(oe,null,{default:d(()=>[V(b(y(l)("weeklyPlans.print")),1)]),_:1})]),_:2},1032,["onClick","disable"])])]),c("div",Me,[(u(!0),m(te,null,ae(H(e.plans),t=>(u(),m("div",{key:t.dayNumber,class:"day-group"},[c("div",We,[c("div",Ae,[c("div",He,b(t.dayName),1),c("div",Fe,[c("span",null,b(y(l)("weeklyPlans.week"))+" "+b(y(p).weekNumber),1),o[2]||(o[2]=c("span",{class:"separator"},"•",-1)),c("span",null,b(Y(t.dayNumber)),1)])])]),i(Pe,{flat:"",bordered:"",rows:t.plans,columns:de.value,"row-key":"id",dense:"",class:"classroom-table day-table","hide-header":"","hide-bottom":"",pagination:ie.value,"rows-per-page-options":[]},{"body-cell-period":d(a=>[i(E,{props:a,class:"text-center period-cell"},{default:d(()=>{var r;return[i(De,{label:`P${(r=a.row.schedule)==null?void 0:r.period_number}`,color:"primary"},null,8,["label"])]}),_:2},1032,["props"])]),"body-cell-subject":d(a=>[i(E,{props:a,class:"subject-teacher-cell"},{default:d(()=>{var r,n,w,g,h,v,x,k;return[c("div",Oe,[c("div",{class:"subject-name",style:je({backgroundColor:(n=(r=a.row.schedule)==null?void 0:r.cst)==null?void 0:n.c_bg,color:(g=(w=a.row.schedule)==null?void 0:w.cst)==null?void 0:g.c_text})},b((v=(h=a.row.schedule)==null?void 0:h.cst)==null?void 0:v.subject_name),5),c("div",Ue,b(((k=(x=a.row.schedule)==null?void 0:x.cst)==null?void 0:k.teacher_name)||"N/A"),1)])]}),_:2},1032,["props"])]),"body-cell-cw":d(a=>[i(E,{props:a,class:"content-preview"},{default:d(()=>[a.row.cw?(u(),m("div",{key:0,class:"text-info ellipsis-2-lines",innerHTML:a.row.cw},null,8,Ye)):(u(),m("span",Re,"-"))]),_:2},1032,["props"])]),"body-cell-hw":d(a=>[i(E,{props:a,class:"content-preview"},{default:d(()=>[a.row.hw?(u(),m("div",{key:0,class:"text-warning ellipsis-2-lines",innerHTML:a.row.hw},null,8,Ze)):(u(),m("span",Ge,"-"))]),_:2},1032,["props"])]),"body-cell-notes":d(a=>[i(E,{props:a,class:"content-preview"},{default:d(()=>[a.row.notes?(u(),m("div",{key:0,class:"text-info ellipsis-2-lines",innerHTML:a.row.notes},null,8,Je)):(u(),m("span",Ke,"-"))]),_:2},1032,["props"])]),_:2},1032,["rows","columns","pagination"])]))),128))])]),_:2},1024)]))),128))])):(u(),fe(F,{key:1,flat:"",bordered:"",class:"text-center q-pa-xl"},{default:d(()=>[i(ee,{name:"meeting_room",size:"64px",color:"grey-5"}),c("p",Ie,b(y(l)("weeklyPlans.noDataAvailable")),1),c("p",Ee,b(y(l)("weeklyPlans.selectClassroomsToView")),1)]),_:1}))]))}},ct=be(Xe,[["__scopeId","data-v-09455642"]]);export{ct as default};
