import{_ as k,r as f,a0 as y,Z as M,L as o,o as a,y as t,z as h,V as g,S as m,U as v,bQ as p,D as T,ah as A,m as D,A as Q,v as B,bR as C,a$ as q}from"./feature-admin-core-BWNP0pMM.js";import"./vendor-xlsx-DkH2s96g.js";const $={key:0,class:"mt-4 p-4 bg-white rounded-lg shadow"},W={key:0,class:"flex justify-center py-4"},H={key:1,class:"space-y-4"},E=["innerHTML"],L={__name:"MathQuestionDisplay",props:{questions:{type:String,default:`Sure! Here are five questions on adding fractions suitable for a 4th-grade level: 

1. **Question 1:** What is \\( \\frac{1}{4} + \\frac{2}{4} \\)? 

2. **Question 2:** What is \\( \\frac{3}{8} + \\frac{1}{8} \\)? 

3. **Question 3:** What is \\( \\frac{2}{5} + \\frac{1}{5} \\)? 

4. **Question 4:** What is \\( \\frac{1}{3} + \\frac{2}{3} \\)? 

5. **Question 5:** What is \\( \\frac{3}{10} + \\frac{4}{10} \\)? 

Feel free to ask if you need help with the answers or explanations!`}},setup(_){const u=_,n=f(!1),d=f(!1),i=f([]),s=()=>{d.value=!0;try{const e=/\d+\.\s+\*\*Question\s+\d+:\*\*\s+(.*?)(?=\n\n\d+\.|$)/gs,l=[...u.questions.matchAll(e)];if(l.length===0){const r=u.questions.split(/\n\n/);i.value=r.filter(c=>c.trim().match(/^\d+\./)).map(c=>p(c))}else i.value=l.map(r=>p(r[0]))}catch(e){console.error("Error processing questions:",e),i.value=[p(u.questions)]}finally{d.value=!1}};return y(()=>u.questions,()=>{n.value&&s()}),y(n,e=>{e&&i.value.length===0&&s()}),M(()=>{n.value&&s()}),(e,l)=>(a(),o("div",null,[t("button",{onClick:l[0]||(l[0]=r=>n.value=!n.value),class:"px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"},g(n.value?"Hide Questions":"Show Math Questions"),1),n.value?(a(),o("div",$,[d.value?(a(),o("div",W,l[1]||(l[1]=[t("div",{class:"animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"},null,-1)]))):(a(),o("div",H,[(a(!0),o(m,null,v(i.value,(r,c)=>(a(),o("div",{key:c,class:"p-3 border-b last:border-b-0"},[t("div",{innerHTML:r,class:"katex-preview prose max-w-none"},null,8,E)]))),128))]))])):h("",!0)]))}},S=k(L,[["__scopeId","data-v-592d17e9"]]),V={class:"p-6 bg-white rounded-lg shadow"},N={class:"mb-6"},R={class:"flex gap-4"},F={key:0,class:"mt-6"},P={class:"space-y-4"},U={class:"font-medium"},j=["innerHTML"],z={class:"mt-2"},I=["innerHTML"],K={key:0,class:"text-green-600 ml-2"},O={__name:"MathQuestionsExample",setup(_){const u=f(`Sure! Here are five questions on adding fractions suitable for a 4th-grade level: 

1. **Question 1:** What is \\( \\frac{1}{4} + \\frac{2}{4} \\)? 

**A)** \\( \\frac{3}{4} \\) 
**B)** \\( \\frac{3}{8} \\) 
**C)** \\( 1 \\) 
**D)** \\( \\frac{1}{2} \\) 

2. **Question 2:** What is \\( \\frac{3}{8} + \\frac{1}{8} \\)? 

**A)** \\( \\frac{4}{8} \\) 
**B)** \\( \\frac{4}{16} \\) 
**C)** \\( \\frac{1}{2} \\) 
**D)** \\( \\frac{3}{4} \\) 

3. **Question 3:** What is \\( \\frac{2}{5} + \\frac{1}{5} \\)? 

**A)** \\( \\frac{3}{10} \\) 
**B)** \\( \\frac{3}{5} \\) 
**C)** \\( \\frac{2}{5} \\) 
**D)** \\( 1 \\) 

4. **Question 4:** What is \\( \\frac{1}{3} + \\frac{2}{3} \\)? 

**A)** \\( \\frac{3}{6} \\) 
**B)** \\( \\frac{3}{3} \\) 
**C)** \\( 1 \\) 
**D)** \\( 3 \\) 

5. **Question 5:** What is \\( \\frac{3}{10} + \\frac{4}{10} \\)? 

**A)** \\( \\frac{7}{10} \\) 
**B)** \\( \\frac{7}{20} \\) 
**C)** \\( \\frac{7}{100} \\) 
**D)** \\( \\frac{3}{5} \\)`),n=f([]),d=()=>{try{const i=/(\d+)\.\s+\*\*Question\s+\d+:\*\*(.*?)(?=\n\n\d+\.|$)/gs,s=[...u.value.matchAll(i)];n.value=[];for(const e of s){const l=e[1],r=e[2],c=r.trim(),w=/\*\*([A-D])\)\*\*\s*(.*?)(?=\n\*\*[A-D]\)\*\*|$)/gs,b=[...r.matchAll(w)].map(x=>({option:x[2].trim(),feedback:"",isCorrect:x[1]==="A"}));b.length>0&&n.value.push({body:c,options:b})}if(n.value.length===0){const e=C(u.value);if(e.success)n.value=[e.data];else throw new Error(e.error)}q.success(`Successfully parsed ${n.value.length} questions`)}catch(i){console.error("Error parsing questions:",i),q.error("Failed to parse questions: "+i.message)}};return(i,s)=>(a(),o("div",V,[s[4]||(s[4]=t("h2",{class:"text-xl font-semibold mb-4"},"Math Questions Example",-1)),t("div",N,[s[1]||(s[1]=t("label",{class:"block text-sm font-medium text-gray-700 mb-2"}," Enter Math Questions Text ",-1)),T(t("textarea",{"onUpdate:modelValue":s[0]||(s[0]=e=>u.value=e),class:"w-full h-48 p-3 border rounded-lg focus:ring-indigo-500 focus:border-indigo-500 font-mono text-sm",placeholder:"Paste your formatted questions here..."},null,512),[[A,u.value]])]),t("div",R,[D(S,{questions:u.value},null,8,["questions"]),t("button",{onClick:d,class:"px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2"}," Parse to Question Bank ")]),n.value.length>0?(a(),o("div",F,[s[3]||(s[3]=t("h3",{class:"text-lg font-medium text-gray-900 mb-2"},"Parsed Questions:",-1)),t("div",P,[(a(!0),o(m,null,v(n.value,(e,l)=>(a(),o("div",{key:l,class:"p-4 bg-gray-50 rounded-lg"},[t("div",U,"Question "+g(l+1),1),t("div",{innerHTML:Q(p)(e.body),class:"mt-1"},null,8,j),t("div",z,[s[2]||(s[2]=t("div",{class:"font-medium"},"Options:",-1)),(a(!0),o(m,null,v(e.options,(r,c)=>(a(),o("div",{key:c,class:"ml-4 mt-1"},[B(g(["A","B","C","D"][c])+") ",1),t("span",{innerHTML:Q(p)(r.option)},null,8,I),r.isCorrect?(a(),o("span",K,"(Correct)")):h("",!0)]))),128))])]))),128))])])):h("",!0)]))}},J=k(O,[["__scopeId","data-v-1fa748e8"]]);export{J as default};
