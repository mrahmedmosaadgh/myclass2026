import{_ as ae,j as re,r as g,a as de,k as F,o as d,w as i,m as o,P as O,y as e,N as ce,Q as S,q as a,cH as ue,cI as L,L as u,S as C,U as I,A as pe,V as v,a5 as Q,v as $,T as R,a3 as B,s as me,ar as ve,D as fe,au as ge,z as D,O as be}from"./feature-admin-core-BWNP0pMM.js";function xe(p){const{lessonTitle:l,subject:c,grade:r,sections:f,customInstructions:b}=p,w=f.map(x=>`- ${x.title} (${x.id})`).join(`
`);return`You are an expert educational content designer specializing in ${c} for ${r} students.

Create a comprehensive lesson plan for: "${l}"

LESSON STRUCTURE:
The lesson has the following sections:
${w}

REQUIREMENTS:
1. Generate 3-5 slides for EACH section
2. Each slide should have educational content appropriate for ${r} level
3. Content should be engaging, clear, and pedagogically sound
4. Include variety: text slides, and question slides
5. For practice/assessment sections, include MORE question slides

${(x=>{const _=x.toLowerCase();return _.includes("math")||_.includes("رياضيات")?`For Mathematics:
- Use single_choice for concept understanding
- Use true_false for properties and theorems
- Include step-by-step problem solving in text slides
- Add practice problems with multiple choice answers`:_.includes("science")||_.includes("علوم")?`For Science:
- Use single_choice for facts and concepts
- Use true_false for scientific statements
- Include diagrams descriptions in text slides
- Add experiment-based questions`:_.includes("language")||_.includes("لغة")?`For Language Arts:
- Use single_choice for grammar and vocabulary
- Use true_false for comprehension checks
- Include reading passages in text slides
- Add questions about text analysis`:`General guidance:
- Use single_choice for concept testing
- Use true_false for fact checking
- Mix question types for engagement`})(c)}

${b?`CUSTOM INSTRUCTIONS:
${b}
`:""}

OUTPUT FORMAT:
Return a JSON object with this EXACT structure (no markdown, no code blocks):

{
  "sections": [
    {
      "sectionId": "objectives",
      "slides": [
        {
          "slide_type": "text",
          "slide_content": {
            "text": "<h2>Learning Objectives</h2><ul><li>Objective 1</li><li>Objective 2</li></ul>"
          }
        }
      ]
    },
    {
      "sectionId": "practice",
      "slides": [
        {
          "slide_type": "question",
          "slide_content": {
            "questions": [
              {
                "id": "q_abc123",
                "type": "single_choice",
                "text": "What is 2 + 2?",
                "options": [
                  {"id": "opt_1", "text": "3"},
                  {"id": "opt_2", "text": "4"},
                  {"id": "opt_3", "text": "5"}
                ],
                "correct_answer": "opt_2",
                "explanation": "2 + 2 equals 4",
                "timer": 30
              }
            ]
          }
        }
      ]
    }
  ]
}

SLIDE TYPES:
1. TEXT SLIDES ("slide_type": "text"):
   - Use for explanations, examples, and content delivery
   - Content should be HTML formatted (use <h2>, <h3>, <p>, <ul>, <li>, <strong>, <em>)
   - Keep content concise but informative

2. QUESTION SLIDES ("slide_type": "question"):
   - Use for practice, assessment, and engagement
   - Each question slide can have 1-3 questions in the "questions" array
   
   QUESTION TYPES:
   
   a) SINGLE CHOICE (most common):
   {
     "id": "q_unique_id",
     "type": "single_choice",
     "text": "Question text here",
     "options": [
       {"id": "opt_1", "text": "Option A"},
       {"id": "opt_2", "text": "Option B"},
       {"id": "opt_3", "text": "Option C"}
     ],
     "correct_answer": "opt_2",
     "explanation": "Why this is correct",
     "timer": 30
   }
   
   b) TRUE/FALSE:
   {
     "id": "q_unique_id",
     "type": "true_false",
     "text": "Statement to evaluate",
     "correct_answer": true,
     "explanation": "Explanation here",
     "timer": 20
   }
   
   c) MULTIPLE CHOICE (select all that apply):
   {
     "id": "q_unique_id",
     "type": "multiple_choice",
     "text": "Select all correct answers",
     "options": [
       {"id": "opt_1", "text": "Option A"},
       {"id": "opt_2", "text": "Option B"},
       {"id": "opt_3", "text": "Option C"}
     ],
     "correct_answer": ["opt_1", "opt_3"],
     "explanation": "Why these are correct",
     "timer": 45
   }

SECTION GUIDELINES:
- objectives: Use text slides to list learning goals
- learn/content: Mix text slides (70%) and question slides (30%) for engagement
- practice: Use MORE question slides (70%) and some text slides (30%)
- assessment/review: Primarily question slides (80-90%)

IMPORTANT RULES:
1. Return ONLY valid JSON
2. NO markdown code blocks (\`\`\`json)
3. NO extra text before or after the JSON
4. Each section MUST have at least 3 slides
5. HTML content must be properly escaped in JSON strings
6. Use double quotes for JSON properties and strings
7. Each question MUST have a unique "id" (use format: q_abc123, q_xyz789, etc.)
8. Each option MUST have a unique "id" within its question (opt_1, opt_2, etc.)
9. For single_choice: correct_answer is a STRING (the option id)
10. For multiple_choice: correct_answer is an ARRAY of strings (option ids)
11. For true_false: correct_answer is a BOOLEAN (true or false)
12. Timer is in seconds (20-60 seconds recommended)

Generate the complete lesson plan now with a good mix of text and question slides.`}function _e(p){const l=[];return!p.sections||!Array.isArray(p.sections)?(l.push('Response must have a "sections" array'),{valid:!1,errors:l}):p.sections.length===0?(l.push("Must have at least one section"),{valid:!1,errors:l}):(p.sections.forEach((c,r)=>{(!c.sectionId||typeof c.sectionId!="string")&&l.push(`Section ${r+1}: Missing or invalid "sectionId"`),!c.slides||!Array.isArray(c.slides)?l.push(`Section ${r+1}: Missing or invalid "slides" array`):c.slides.length<3?l.push(`Section ${r+1}: Must have at least 3 slides (has ${c.slides.length})`):c.slides.forEach((f,b)=>{f.slide_type||l.push(`Section ${r+1}, Slide ${b+1}: Missing "slide_type"`),f.slide_content?f.slide_type==="text"&&!f.slide_content.text?l.push(`Section ${r+1}, Slide ${b+1}: Text slide must have "text" content`):f.slide_type==="question"&&(!f.slide_content.questions||!Array.isArray(f.slide_content.questions)?l.push(`Section ${r+1}, Slide ${b+1}: Question slide must have "questions" array`):f.slide_content.questions.length===0&&l.push(`Section ${r+1}, Slide ${b+1}: Question slide must have at least 1 question`)):l.push(`Section ${r+1}, Slide ${b+1}: Missing "slide_content"`)})}),{valid:l.length===0,errors:l})}function he(p){try{let l=p.trim();l.startsWith("```json")?l=l.replace(/```json\n?/g,"").replace(/```\n?$/g,""):l.startsWith("```")&&(l=l.replace(/```\n?/g,""));const c=l.match(/\{[\s\S]*\}/);return c&&(l=c[0]),{success:!0,data:JSON.parse(l)}}catch(l){return{success:!1,error:`JSON Parse Error: ${l.message}. Please ensure the AI returned valid JSON.`}}}const ye=[{label:"Interactive Lesson",instructions:"Include interactive elements, questions for students, and hands-on activities in each section."},{label:"Visual Learning",instructions:"Focus on visual descriptions, diagrams explanations, and visual metaphors to explain concepts."},{label:"Step-by-Step",instructions:"Break down complex topics into simple, sequential steps. Use numbered lists and clear progression."},{label:"Real-World Examples",instructions:"Include real-world applications and practical examples that students can relate to."},{label:"Differentiated",instructions:"Provide content at multiple difficulty levels to accommodate different learning abilities."}],we={class:"z-10 w-full left-0 bg-grey-1"},Se={class:"q-pa-md",style:{background:"linear-gradient(135deg, #667eea 0%, #764ba2 100%)"}},Ce={class:"row items-center"},Ie={class:"max-w-3xl mx-auto p-6"},Te={class:"mb-6"},ke={class:"flex flex-wrap gap-2"},Ae={class:"bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4"},Ue={class:"grid grid-cols-2 gap-3 text-sm"},qe={class:"ml-2 text-blue-700"},Pe={class:"ml-2 text-blue-700"},Ee={class:"col-span-2"},Oe={class:"ml-2 text-blue-700"},Le={class:"mb-4"},$e={class:"grid grid-cols-2 gap-2"},Re={class:"text-xl"},Ne={class:"text-sm font-medium"},je={class:"flex justify-end gap-2"},Ve={class:"max-w-6xl mx-auto p-6"},Qe={class:"bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4"},Me={class:"flex items-start gap-2"},ze={class:"grid grid-cols-1 lg:grid-cols-2 gap-4"},Ge={class:"flex items-center justify-between mb-2"},Fe={class:"whitespace-pre-wrap text-xs font-mono"},Be={class:"mt-4"},De={class:"flex items-center justify-between mb-2"},He={class:"flex gap-2"},Je={class:"flex items-center justify-between mb-2"},We={class:"border rounded-lg overflow-hidden bg-white",style:{height:"400px"}},Ye=["src"],Xe={key:1,class:"flex items-center justify-center h-full text-gray-400"},Ke={class:"text-center"},Ze={class:"flex justify-between gap-2 mt-6"},et={class:"mt-4"},tt={class:"space-y-2"},st={class:"max-w-4xl mx-auto p-6"},ot={class:"bg-amber-50 border border-amber-200 rounded-lg p-4 mb-4"},lt={class:"flex items-start gap-2"},nt={class:"relative mb-4"},it={key:0,class:"bg-red-50 border border-red-200 rounded-lg p-4 mb-4"},at={class:"flex items-start gap-2"},rt={class:"flex-1"},dt={class:"list-disc list-inside text-sm text-red-700 space-y-1"},ct={class:"flex justify-between gap-2"},ut={class:"max-w-5xl mx-auto p-6"},pt={key:0,class:"space-y-4"},mt={class:"grid grid-cols-2 gap-4"},vt={class:"ml-2"},ft={class:"ml-2"},gt={class:"bg-gray-100 px-4 py-2 font-semibold flex items-center gap-2"},bt={class:"p-4 space-y-2"},xt={class:"flex-1"},_t={class:"text-xs text-gray-500 mb-1"},ht=["innerHTML"],yt={class:"flex justify-between gap-2 mt-6"},wt={class:"flex gap-2"},St={__name:"AILessonPlanGenerator",props:{lessonConfig:{type:Object,required:!0}},emits:["plan-accepted","close"],setup(p,{expose:l,emit:c}){const r=re(),f=c,b=p,w=g(!1),m=g(1),x=g({customInstructions:""}),_=ye,P=g(""),N=g(!1),h=g(""),U=g(""),E=g(!1),j=[{name:"Gemini",url:"https://gemini.google.com/"},{name:"ChatGPT",url:"https://chatgpt.com/"},{name:"Claude",url:"https://claude.ai/"},{name:"Perplexity",url:"https://www.perplexity.ai/"}],T=g(""),k=g([]),y=g(null),q=g(!1),H=de(()=>y.value?y.value.sections.reduce((n,t)=>n+t.slides.length,0):0),J=()=>{const n=localStorage.getItem("ai_tool_url");n?h.value=n:h.value=j[0].url},M=n=>{h.value=n,localStorage.setItem("ai_tool_url",n),r.notify({type:"positive",message:"AI tool updated",position:"top",timeout:1500})},W=()=>{U.value.trim()&&(M(U.value.trim()),E.value=!1)},Y=()=>{h.value&&window.open(h.value,"_blank")},X=()=>{w.value=!0,m.value=1,J(),K()},V=()=>{w.value=!1,f("close")},K=()=>{x.value={customInstructions:""},P.value="",T.value="",k.value=[],y.value=null},Z=n=>{x.value.customInstructions=n.instructions},ee=()=>{P.value=xe({...b.lessonConfig,customInstructions:x.value.customInstructions}),m.value=2},te=async()=>{N.value=!0;try{await navigator.clipboard.writeText(P.value),r.notify({type:"positive",message:"Prompt copied to clipboard!",position:"top",timeout:2e3})}catch{r.notify({type:"negative",message:"Failed to copy to clipboard",position:"top"})}finally{N.value=!1}},se=async()=>{try{const n=await navigator.clipboard.readText();T.value=n,r.notify({type:"positive",message:"Pasted from clipboard!",position:"top",timeout:1500})}catch{r.notify({type:"negative",message:"Failed to read from clipboard. Please paste manually.",position:"top"})}},oe=()=>{q.value=!0,k.value=[],setTimeout(()=>{const n=he(T.value);if(!n.success){k.value=[n.error],q.value=!1;return}const t=_e(n.data);if(!t.valid){k.value=t.errors,q.value=!1;return}y.value=n.data,m.value=4,q.value=!1},500)},le=n=>{const t=b.lessonConfig.sections.find(s=>s.id===n);return t?t.title:n},ne=n=>{var t;if(n.slide_type==="text"&&((t=n.slide_content)!=null&&t.text)){const s=n.slide_content.text.replace(/<[^>]*>/g," ").trim();return s.length>150?s.substring(0,150)+"...":s}return"Content preview not available"},ie=()=>{f("plan-accepted",y.value),w.value=!1};return l({open:X}),(n,t)=>(d(),F(B,{modelValue:w.value,"onUpdate:modelValue":t[10]||(t[10]=s=>w.value=s),persistent:"",maximized:"","transition-show":"slide-up","transition-hide":"slide-down"},{default:i(()=>[o(O,{class:"bg-grey-1"},{default:i(()=>[e("div",we,[e("div",Se,[e("div",Ce,[o(ce,{size:"36px",color:"white","text-color":"purple-8",class:"q-mr-md"},{default:i(()=>[o(S,{name:"auto_awesome",size:"24px"})]),_:1}),t[11]||(t[11]=e("div",{class:"col"},[e("div",{class:"text-h5 text-white font-bold"},"AI Lesson Plan Generator"),e("div",{class:"text-subtitle2 text-white",style:{opacity:"0.9"}},"Generate complete lesson content with intelligent AI assistance")],-1)),o(a,{flat:"",round:"",dense:"",icon:"close",color:"white",onClick:V,size:"md"})])])]),o(ue,{modelValue:m.value,"onUpdate:modelValue":t[9]||(t[9]=s=>m.value=s),ref:"stepper",color:"primary",animated:"",flat:"",class:"flex-1 bg-grey-1"},{default:i(()=>[o(L,{name:1,title:"Configure",icon:"settings",done:m.value>1},{default:i(()=>[e("div",Ie,[t[18]||(t[18]=e("h3",{class:"text-xl font-semibold mb-4"},"Lesson Configuration",-1)),e("div",Te,[t[12]||(t[12]=e("label",{class:"text-sm font-medium text-gray-700 mb-2 block"},"Quick Suggestions",-1)),e("div",ke,[(d(!0),u(C,null,I(pe(_),s=>(d(),F(a,{key:s.label,outline:"",color:"primary",label:s.label,onClick:A=>Z(s),size:"sm"},null,8,["label","onClick"]))),128))])]),e("div",Ae,[e("div",Ue,[e("div",null,[t[13]||(t[13]=e("span",{class:"font-semibold text-blue-900"},"Subject:",-1)),e("span",qe,v(p.lessonConfig.subject),1)]),e("div",null,[t[14]||(t[14]=e("span",{class:"font-semibold text-blue-900"},"Grade:",-1)),e("span",Pe,v(p.lessonConfig.grade),1)]),e("div",Ee,[t[15]||(t[15]=e("span",{class:"font-semibold text-blue-900"},"Lesson:",-1)),e("span",Oe,v(p.lessonConfig.lessonTitle),1)])])]),e("div",Le,[t[16]||(t[16]=e("label",{class:"text-sm font-medium text-gray-700 mb-2 block"},"Lesson Sections",-1)),e("div",$e,[(d(!0),u(C,null,I(p.lessonConfig.sections,s=>(d(),u("div",{key:s.id,class:"flex items-center gap-2 p-2 bg-gray-50 rounded border"},[e("span",Re,v(s.icon),1),e("span",Ne,v(s.title),1)]))),128))]),t[17]||(t[17]=e("p",{class:"text-xs text-gray-500 mt-2"}," AI will generate 3-5 slides for each section ",-1))]),o(Q,{modelValue:x.value.customInstructions,"onUpdate:modelValue":t[0]||(t[0]=s=>x.value.customInstructions=s),type:"textarea",label:"Additional Instructions (Optional)",outlined:"",rows:"4",hint:"Add specific requirements, topics to cover, or teaching style preferences",class:"mb-4"},null,8,["modelValue"]),e("div",je,[o(a,{flat:"",label:"Cancel",onClick:V}),o(a,{unelevated:"",color:"primary",label:"Generate Prompt","icon-right":"arrow_forward",onClick:ee})])])]),_:1},8,["done"]),o(L,{name:2,title:"Use AI Tool",icon:"smart_toy",done:m.value>2},{default:i(()=>[e("div",Ve,[t[24]||(t[24]=e("h3",{class:"text-xl font-semibold mb-4"},"Work with AI Tool",-1)),e("div",Qe,[e("div",Me,[o(S,{name:"info",color:"blue",size:"20px",class:"mt-1"}),t[19]||(t[19]=e("div",{class:"text-sm text-blue-800"},[e("strong",null,"Instructions:"),$(" Copy the prompt below and paste it into your AI tool. The AI will generate slides for all sections. ")],-1))])]),e("div",ze,[e("div",null,[e("div",Ge,[t[20]||(t[20]=e("label",{class:"text-sm font-medium text-gray-700"},"Generated Prompt",-1)),o(a,{dense:"",unelevated:"",color:"primary",icon:"content_copy",label:"Copy",onClick:te,size:"sm",loading:N.value},null,8,["loading"])]),o(O,{flat:"",bordered:"",class:"bg-gray-50",style:{"max-height":"400px","overflow-y":"auto"}},{default:i(()=>[o(R,null,{default:i(()=>[e("pre",Fe,v(P.value),1)]),_:1})]),_:1}),e("div",Be,[e("div",De,[t[21]||(t[21]=e("label",{class:"text-sm font-medium text-gray-700"},"AI Tool URL",-1)),o(a,{dense:"",flat:"",color:"primary",icon:"settings",label:"Change",onClick:t[1]||(t[1]=s=>E.value=!0),size:"sm"})]),e("div",He,[(d(),u(C,null,I(j,s=>o(a,{key:s.name,dense:"",outline:"",color:h.value===s.url?"primary":"grey",label:s.name,onClick:A=>M(s.url),size:"sm"},null,8,["color","label","onClick"])),64))])])]),e("div",null,[e("div",Je,[t[22]||(t[22]=e("label",{class:"text-sm font-medium text-gray-700"},"AI Tool (Embedded)",-1)),o(a,{dense:"",flat:"",color:"primary",icon:"open_in_new",label:"Open in New Tab",onClick:Y,size:"sm"})]),e("div",We,[h.value?(d(),u("iframe",{key:0,src:h.value,class:"w-full h-full",frameborder:"0",sandbox:"allow-same-origin allow-scripts allow-forms allow-popups allow-popups-to-escape-sandbox"},null,8,Ye)):(d(),u("div",Xe,[e("div",Ke,[o(S,{name:"smart_toy",size:"48px",class:"mb-2"}),t[23]||(t[23]=e("div",null,"Select an AI tool to get started",-1))])]))])])]),e("div",Ze,[o(a,{flat:"",label:"Back",icon:"arrow_back",onClick:t[2]||(t[2]=s=>m.value=1)}),o(a,{unelevated:"",color:"primary",label:"I Have the Response","icon-right":"arrow_forward",onClick:t[3]||(t[3]=s=>m.value=3)})])])]),_:1},8,["done"]),o(B,{modelValue:E.value,"onUpdate:modelValue":t[5]||(t[5]=s=>E.value=s)},{default:i(()=>[o(O,{style:{"min-width":"400px"}},{default:i(()=>[o(R,{class:"bg-primary text-white"},{default:i(()=>t[25]||(t[25]=[e("div",{class:"text-h6"},"Configure AI Tool URL",-1)])),_:1}),o(R,null,{default:i(()=>[o(Q,{modelValue:U.value,"onUpdate:modelValue":t[4]||(t[4]=s=>U.value=s),label:"Custom AI Tool URL",outlined:"",dense:"",placeholder:"https://chatgpt.com",hint:"Enter the URL of your preferred AI tool"},null,8,["modelValue"]),e("div",et,[t[26]||(t[26]=e("div",{class:"text-sm font-medium text-gray-700 mb-2"},"Quick Presets:",-1)),e("div",tt,[(d(),u(C,null,I(j,s=>o(a,{key:s.name,outline:"",color:"primary",label:s.name,onClick:A=>U.value=s.url,class:"w-full",align:"left"},{default:i(()=>[o(me,null,{default:i(()=>[$(v(s.url),1)]),_:2},1024)]),_:2},1032,["label","onClick"])),64))])])]),_:1}),o(ve,{align:"right"},{default:i(()=>[fe(o(a,{flat:"",label:"Cancel"},null,512),[[ge]]),o(a,{unelevated:"",color:"primary",label:"Save",onClick:W})]),_:1})]),_:1})]),_:1},8,["modelValue"]),o(L,{name:3,title:"Paste Response",icon:"content_paste",done:m.value>3},{default:i(()=>[e("div",st,[t[29]||(t[29]=e("h3",{class:"text-xl font-semibold mb-4"},"Paste AI Response",-1)),e("div",ot,[e("div",lt,[o(S,{name:"tips_and_updates",color:"amber",size:"20px",class:"mt-1"}),t[27]||(t[27]=e("div",{class:"text-sm text-amber-800"},[e("strong",null,"Tip:"),$(" Paste the complete JSON response from your AI tool. The system will validate and create slides for all sections. ")],-1))])]),e("div",nt,[o(Q,{modelValue:T.value,"onUpdate:modelValue":t[6]||(t[6]=s=>T.value=s),type:"textarea",label:"AI Response *",outlined:"",rows:"12",placeholder:"Paste the AI response here...",class:"font-mono"},null,8,["modelValue"]),o(a,{unelevated:"",color:"secondary",icon:"content_paste",label:"Paste from Clipboard",onClick:se,class:"absolute top-4 right-4",size:"sm"})]),k.value.length>0?(d(),u("div",it,[e("div",at,[o(S,{name:"error",color:"red",size:"20px",class:"mt-1"}),e("div",rt,[t[28]||(t[28]=e("div",{class:"font-semibold text-red-800 mb-2"},"Validation Errors:",-1)),e("ul",dt,[(d(!0),u(C,null,I(k.value,(s,A)=>(d(),u("li",{key:A},v(s),1))),128))])])])])):D("",!0),e("div",ct,[o(a,{flat:"",label:"Back",icon:"arrow_back",onClick:t[7]||(t[7]=s=>m.value=2)}),o(a,{unelevated:"",color:"primary",label:"Validate & Preview","icon-right":"arrow_forward",onClick:oe,disable:!T.value.trim(),loading:q.value},null,8,["disable","loading"])])])]),_:1},8,["done"]),o(L,{name:4,title:"Preview",icon:"visibility",done:m.value>4},{default:i(()=>[e("div",ut,[t[33]||(t[33]=e("h3",{class:"text-xl font-semibold mb-4"},"Preview Generated Lesson Plan",-1)),y.value?(d(),u("div",pt,[o(O,{flat:"",bordered:""},{default:i(()=>[o(R,null,{default:i(()=>[t[32]||(t[32]=e("div",{class:"text-sm text-gray-600 mb-2"},"Summary",-1)),e("div",mt,[e("div",null,[t[30]||(t[30]=e("span",{class:"font-semibold"},"Total Sections:",-1)),e("span",vt,v(y.value.sections.length),1)]),e("div",null,[t[31]||(t[31]=e("span",{class:"font-semibold"},"Total Slides:",-1)),e("span",ft,v(H.value),1)])])]),_:1})]),_:1}),(d(!0),u(C,null,I(y.value.sections,(s,A)=>(d(),u("div",{key:A,class:"border rounded-lg overflow-hidden"},[e("div",gt,[o(S,{name:"folder",size:"sm"}),e("span",null,v(le(s.sectionId)),1),o(be,{dense:"",size:"sm",color:"primary","text-color":"white"},{default:i(()=>[$(v(s.slides.length)+" slides ",1)]),_:2},1024)]),e("div",bt,[(d(!0),u(C,null,I(s.slides,(z,G)=>(d(),u("div",{key:G,class:"flex items-start gap-3 p-3 bg-gray-50 rounded border"},[o(S,{name:"description",size:"sm",color:"primary"}),e("div",xt,[e("div",_t,"Slide "+v(G+1)+" - "+v(z.slide_type),1),e("div",{class:"text-sm",innerHTML:ne(z)},null,8,ht)])]))),128))])]))),128))])):D("",!0),e("div",yt,[o(a,{flat:"",label:"Back",icon:"arrow_back",onClick:t[8]||(t[8]=s=>m.value=3)}),e("div",wt,[o(a,{flat:"",label:"Reject",color:"negative",onClick:V}),o(a,{unelevated:"",color:"positive",label:"Accept & Create Slides",icon:"check_circle",onClick:ie})])])])]),_:1},8,["done"])]),_:1},8,["modelValue"])]),_:1})]),_:1},8,["modelValue"]))}},It=ae(St,[["__scopeId","data-v-362dfff2"]]);export{It as A};
