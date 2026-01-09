import{a as qe,u as Ge,f as Q,g as D,C as Le,i as N,Q as x,q as Z,b as Ue,e as De}from"./app-C7rW1iCk.js";import{Q as ee}from"./QSpace-8l-BltlJ.js";import{Q as Ve}from"./QBtnToggle-Cs7TqA62.js";import{Q as P}from"./QSelect-33i7Va2A.js";import ke from"./TextToSpeechButton-Ccyl_yH1.js";import{f as M,b as $e,k as J,o as Fe,aa as He,I as K,J as y,K as b,c as d,Q as O,N as _,W as q,M as V}from"./vendor-BLQ8yBzx.js";import"./offline-D-M_H-zP.js";import"./i18n-D0hNWY36.js";import"./ui-04HvVE5c.js";import"./rtl-DFPa-2ov.js";var te;(function(e){e.STRING="string",e.NUMBER="number",e.INTEGER="integer",e.BOOLEAN="boolean",e.ARRAY="array",e.OBJECT="object"})(te||(te={}));/**
 * @license
 * Copyright 2024 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */var ne;(function(e){e.LANGUAGE_UNSPECIFIED="language_unspecified",e.PYTHON="python"})(ne||(ne={}));var oe;(function(e){e.OUTCOME_UNSPECIFIED="outcome_unspecified",e.OUTCOME_OK="outcome_ok",e.OUTCOME_FAILED="outcome_failed",e.OUTCOME_DEADLINE_EXCEEDED="outcome_deadline_exceeded"})(oe||(oe={}));/**
 * @license
 * Copyright 2024 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */const se=["user","model","function","system"];var ie;(function(e){e.HARM_CATEGORY_UNSPECIFIED="HARM_CATEGORY_UNSPECIFIED",e.HARM_CATEGORY_HATE_SPEECH="HARM_CATEGORY_HATE_SPEECH",e.HARM_CATEGORY_SEXUALLY_EXPLICIT="HARM_CATEGORY_SEXUALLY_EXPLICIT",e.HARM_CATEGORY_HARASSMENT="HARM_CATEGORY_HARASSMENT",e.HARM_CATEGORY_DANGEROUS_CONTENT="HARM_CATEGORY_DANGEROUS_CONTENT",e.HARM_CATEGORY_CIVIC_INTEGRITY="HARM_CATEGORY_CIVIC_INTEGRITY"})(ie||(ie={}));var ae;(function(e){e.HARM_BLOCK_THRESHOLD_UNSPECIFIED="HARM_BLOCK_THRESHOLD_UNSPECIFIED",e.BLOCK_LOW_AND_ABOVE="BLOCK_LOW_AND_ABOVE",e.BLOCK_MEDIUM_AND_ABOVE="BLOCK_MEDIUM_AND_ABOVE",e.BLOCK_ONLY_HIGH="BLOCK_ONLY_HIGH",e.BLOCK_NONE="BLOCK_NONE"})(ae||(ae={}));var le;(function(e){e.HARM_PROBABILITY_UNSPECIFIED="HARM_PROBABILITY_UNSPECIFIED",e.NEGLIGIBLE="NEGLIGIBLE",e.LOW="LOW",e.MEDIUM="MEDIUM",e.HIGH="HIGH"})(le||(le={}));var re;(function(e){e.BLOCKED_REASON_UNSPECIFIED="BLOCKED_REASON_UNSPECIFIED",e.SAFETY="SAFETY",e.OTHER="OTHER"})(re||(re={}));var k;(function(e){e.FINISH_REASON_UNSPECIFIED="FINISH_REASON_UNSPECIFIED",e.STOP="STOP",e.MAX_TOKENS="MAX_TOKENS",e.SAFETY="SAFETY",e.RECITATION="RECITATION",e.LANGUAGE="LANGUAGE",e.BLOCKLIST="BLOCKLIST",e.PROHIBITED_CONTENT="PROHIBITED_CONTENT",e.SPII="SPII",e.MALFORMED_FUNCTION_CALL="MALFORMED_FUNCTION_CALL",e.OTHER="OTHER"})(k||(k={}));var ue;(function(e){e.TASK_TYPE_UNSPECIFIED="TASK_TYPE_UNSPECIFIED",e.RETRIEVAL_QUERY="RETRIEVAL_QUERY",e.RETRIEVAL_DOCUMENT="RETRIEVAL_DOCUMENT",e.SEMANTIC_SIMILARITY="SEMANTIC_SIMILARITY",e.CLASSIFICATION="CLASSIFICATION",e.CLUSTERING="CLUSTERING"})(ue||(ue={}));var ce;(function(e){e.MODE_UNSPECIFIED="MODE_UNSPECIFIED",e.AUTO="AUTO",e.ANY="ANY",e.NONE="NONE"})(ce||(ce={}));var de;(function(e){e.MODE_UNSPECIFIED="MODE_UNSPECIFIED",e.MODE_DYNAMIC="MODE_DYNAMIC"})(de||(de={}));/**
 * @license
 * Copyright 2024 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */class p extends Error{constructor(t){super(`[GoogleGenerativeAI Error]: ${t}`)}}class L extends p{constructor(t,n){super(t),this.response=n}}class Ce extends p{constructor(t,n,o,s){super(t),this.status=n,this.statusText=o,this.errorDetails=s}}class A extends p{}class _e extends p{}/**
 * @license
 * Copyright 2024 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */const je="https://generativelanguage.googleapis.com",Be="v1beta",Pe="0.24.1",Ke="genai-js";var G;(function(e){e.GENERATE_CONTENT="generateContent",e.STREAM_GENERATE_CONTENT="streamGenerateContent",e.COUNT_TOKENS="countTokens",e.EMBED_CONTENT="embedContent",e.BATCH_EMBED_CONTENTS="batchEmbedContents"})(G||(G={}));class Ye{constructor(t,n,o,s,i){this.model=t,this.task=n,this.apiKey=o,this.stream=s,this.requestOptions=i}toString(){var t,n;const o=((t=this.requestOptions)===null||t===void 0?void 0:t.apiVersion)||Be;let i=`${((n=this.requestOptions)===null||n===void 0?void 0:n.baseUrl)||je}/${o}/${this.model}:${this.task}`;return this.stream&&(i+="?alt=sse"),i}}function Qe(e){const t=[];return e!=null&&e.apiClient&&t.push(e.apiClient),t.push(`${Ke}/${Pe}`),t.join(" ")}async function Je(e){var t;const n=new Headers;n.append("Content-Type","application/json"),n.append("x-goog-api-client",Qe(e.requestOptions)),n.append("x-goog-api-key",e.apiKey);let o=(t=e.requestOptions)===null||t===void 0?void 0:t.customHeaders;if(o){if(!(o instanceof Headers))try{o=new Headers(o)}catch(s){throw new A(`unable to convert customHeaders value ${JSON.stringify(o)} to Headers: ${s.message}`)}for(const[s,i]of o.entries()){if(s==="x-goog-api-key")throw new A(`Cannot set reserved header name ${s}`);if(s==="x-goog-api-client")throw new A(`Header name ${s} can only be set using the apiClient field`);n.append(s,i)}}return n}async function We(e,t,n,o,s,i){const a=new Ye(e,t,n,o,i);return{url:a.toString(),fetchOptions:Object.assign(Object.assign({},et(i)),{method:"POST",headers:await Je(a),body:s})}}async function H(e,t,n,o,s,i={},a=fetch){const{url:r,fetchOptions:c}=await We(e,t,n,o,s,i);return Xe(r,c,a)}async function Xe(e,t,n=fetch){let o;try{o=await n(e,t)}catch(s){ze(s,e)}return o.ok||await Ze(o,e),o}function ze(e,t){let n=e;throw n.name==="AbortError"?(n=new _e(`Request aborted when fetching ${t.toString()}: ${e.message}`),n.stack=e.stack):e instanceof Ce||e instanceof A||(n=new p(`Error fetching from ${t.toString()}: ${e.message}`),n.stack=e.stack),n}async function Ze(e,t){let n="",o;try{const s=await e.json();n=s.error.message,s.error.details&&(n+=` ${JSON.stringify(s.error.details)}`,o=s.error.details)}catch{}throw new Ce(`Error fetching from ${t.toString()}: [${e.status} ${e.statusText}] ${n}`,e.status,e.statusText,o)}function et(e){const t={};if((e==null?void 0:e.signal)!==void 0||(e==null?void 0:e.timeout)>=0){const n=new AbortController;(e==null?void 0:e.timeout)>=0&&setTimeout(()=>n.abort(),e.timeout),e!=null&&e.signal&&e.signal.addEventListener("abort",()=>{n.abort()}),t.signal=n.signal}return t}/**
 * @license
 * Copyright 2024 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */function W(e){return e.text=()=>{if(e.candidates&&e.candidates.length>0){if(e.candidates.length>1&&console.warn(`This response had ${e.candidates.length} candidates. Returning text from the first candidate only. Access response.candidates directly to use the other candidates.`),Y(e.candidates[0]))throw new L(`${R(e)}`,e);return tt(e)}else if(e.promptFeedback)throw new L(`Text not available. ${R(e)}`,e);return""},e.functionCall=()=>{if(e.candidates&&e.candidates.length>0){if(e.candidates.length>1&&console.warn(`This response had ${e.candidates.length} candidates. Returning function calls from the first candidate only. Access response.candidates directly to use the other candidates.`),Y(e.candidates[0]))throw new L(`${R(e)}`,e);return console.warn("response.functionCall() is deprecated. Use response.functionCalls() instead."),fe(e)[0]}else if(e.promptFeedback)throw new L(`Function call not available. ${R(e)}`,e)},e.functionCalls=()=>{if(e.candidates&&e.candidates.length>0){if(e.candidates.length>1&&console.warn(`This response had ${e.candidates.length} candidates. Returning function calls from the first candidate only. Access response.candidates directly to use the other candidates.`),Y(e.candidates[0]))throw new L(`${R(e)}`,e);return fe(e)}else if(e.promptFeedback)throw new L(`Function call not available. ${R(e)}`,e)},e}function tt(e){var t,n,o,s;const i=[];if(!((n=(t=e.candidates)===null||t===void 0?void 0:t[0].content)===null||n===void 0)&&n.parts)for(const a of(s=(o=e.candidates)===null||o===void 0?void 0:o[0].content)===null||s===void 0?void 0:s.parts)a.text&&i.push(a.text),a.executableCode&&i.push("\n```"+a.executableCode.language+`
`+a.executableCode.code+"\n```\n"),a.codeExecutionResult&&i.push("\n```\n"+a.codeExecutionResult.output+"\n```\n");return i.length>0?i.join(""):""}function fe(e){var t,n,o,s;const i=[];if(!((n=(t=e.candidates)===null||t===void 0?void 0:t[0].content)===null||n===void 0)&&n.parts)for(const a of(s=(o=e.candidates)===null||o===void 0?void 0:o[0].content)===null||s===void 0?void 0:s.parts)a.functionCall&&i.push(a.functionCall);if(i.length>0)return i}const nt=[k.RECITATION,k.SAFETY,k.LANGUAGE];function Y(e){return!!e.finishReason&&nt.includes(e.finishReason)}function R(e){var t,n,o;let s="";if((!e.candidates||e.candidates.length===0)&&e.promptFeedback)s+="Response was blocked",!((t=e.promptFeedback)===null||t===void 0)&&t.blockReason&&(s+=` due to ${e.promptFeedback.blockReason}`),!((n=e.promptFeedback)===null||n===void 0)&&n.blockReasonMessage&&(s+=`: ${e.promptFeedback.blockReasonMessage}`);else if(!((o=e.candidates)===null||o===void 0)&&o[0]){const i=e.candidates[0];Y(i)&&(s+=`Candidate was blocked due to ${i.finishReason}`,i.finishMessage&&(s+=`: ${i.finishMessage}`))}return s}function $(e){return this instanceof $?(this.v=e,this):new $(e)}function ot(e,t,n){if(!Symbol.asyncIterator)throw new TypeError("Symbol.asyncIterator is not defined.");var o=n.apply(e,t||[]),s,i=[];return s={},a("next"),a("throw"),a("return"),s[Symbol.asyncIterator]=function(){return this},s;function a(g){o[g]&&(s[g]=function(f){return new Promise(function(h,w){i.push([g,f,h,w])>1||r(g,f)})})}function r(g,f){try{c(o[g](f))}catch(h){v(i[0][3],h)}}function c(g){g.value instanceof $?Promise.resolve(g.value.v).then(C,m):v(i[0][2],g)}function C(g){r("next",g)}function m(g){r("throw",g)}function v(g,f){g(f),i.shift(),i.length&&r(i[0][0],i[0][1])}}/**
 * @license
 * Copyright 2024 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */const ge=/^data\: (.*)(?:\n\n|\r\r|\r\n\r\n)/;function st(e){const t=e.body.pipeThrough(new TextDecoderStream("utf8",{fatal:!0})),n=lt(t),[o,s]=n.tee();return{stream:at(o),response:it(s)}}async function it(e){const t=[],n=e.getReader();for(;;){const{done:o,value:s}=await n.read();if(o)return W(rt(t));t.push(s)}}function at(e){return ot(this,arguments,function*(){const n=e.getReader();for(;;){const{value:o,done:s}=yield $(n.read());if(s)break;yield yield $(W(o))}})}function lt(e){const t=e.getReader();return new ReadableStream({start(o){let s="";return i();function i(){return t.read().then(({value:a,done:r})=>{if(r){if(s.trim()){o.error(new p("Failed to parse stream"));return}o.close();return}s+=a;let c=s.match(ge),C;for(;c;){try{C=JSON.parse(c[1])}catch{o.error(new p(`Error parsing JSON response: "${c[1]}"`));return}o.enqueue(C),s=s.substring(c[0].length),c=s.match(ge)}return i()}).catch(a=>{let r=a;throw r.stack=a.stack,r.name==="AbortError"?r=new _e("Request aborted when reading from the stream"):r=new p("Error reading from the stream"),r})}}})}function rt(e){const t=e[e.length-1],n={promptFeedback:t==null?void 0:t.promptFeedback};for(const o of e){if(o.candidates){let s=0;for(const i of o.candidates)if(n.candidates||(n.candidates=[]),n.candidates[s]||(n.candidates[s]={index:s}),n.candidates[s].citationMetadata=i.citationMetadata,n.candidates[s].groundingMetadata=i.groundingMetadata,n.candidates[s].finishReason=i.finishReason,n.candidates[s].finishMessage=i.finishMessage,n.candidates[s].safetyRatings=i.safetyRatings,i.content&&i.content.parts){n.candidates[s].content||(n.candidates[s].content={role:i.content.role||"user",parts:[]});const a={};for(const r of i.content.parts)r.text&&(a.text=r.text),r.functionCall&&(a.functionCall=r.functionCall),r.executableCode&&(a.executableCode=r.executableCode),r.codeExecutionResult&&(a.codeExecutionResult=r.codeExecutionResult),Object.keys(a).length===0&&(a.text=""),n.candidates[s].content.parts.push(a)}s++}o.usageMetadata&&(n.usageMetadata=o.usageMetadata)}return n}/**
 * @license
 * Copyright 2024 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */async function ye(e,t,n,o){const s=await H(t,G.STREAM_GENERATE_CONTENT,e,!0,JSON.stringify(n),o);return st(s)}async function be(e,t,n,o){const i=await(await H(t,G.GENERATE_CONTENT,e,!1,JSON.stringify(n),o)).json();return{response:W(i)}}/**
 * @license
 * Copyright 2024 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */function we(e){if(e!=null){if(typeof e=="string")return{role:"system",parts:[{text:e}]};if(e.text)return{role:"system",parts:[e]};if(e.parts)return e.role?e:{role:"system",parts:e.parts}}}function F(e){let t=[];if(typeof e=="string")t=[{text:e}];else for(const n of e)typeof n=="string"?t.push({text:n}):t.push(n);return ut(t)}function ut(e){const t={role:"user",parts:[]},n={role:"function",parts:[]};let o=!1,s=!1;for(const i of e)"functionResponse"in i?(n.parts.push(i),s=!0):(t.parts.push(i),o=!0);if(o&&s)throw new p("Within a single message, FunctionResponse cannot be mixed with other type of part in the request for sending chat message.");if(!o&&!s)throw new p("No content is provided for sending chat message.");return o?t:n}function ct(e,t){var n;let o={model:t==null?void 0:t.model,generationConfig:t==null?void 0:t.generationConfig,safetySettings:t==null?void 0:t.safetySettings,tools:t==null?void 0:t.tools,toolConfig:t==null?void 0:t.toolConfig,systemInstruction:t==null?void 0:t.systemInstruction,cachedContent:(n=t==null?void 0:t.cachedContent)===null||n===void 0?void 0:n.name,contents:[]};const s=e.generateContentRequest!=null;if(e.contents){if(s)throw new A("CountTokensRequest must have one of contents or generateContentRequest, not both.");o.contents=e.contents}else if(s)o=Object.assign(Object.assign({},o),e.generateContentRequest);else{const i=F(e);o.contents=[i]}return{generateContentRequest:o}}function me(e){let t;return e.contents?t=e:t={contents:[F(e)]},e.systemInstruction&&(t.systemInstruction=we(e.systemInstruction)),t}function dt(e){return typeof e=="string"||Array.isArray(e)?{content:F(e)}:e}/**
 * @license
 * Copyright 2024 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */const he=["text","inlineData","functionCall","functionResponse","executableCode","codeExecutionResult"],ft={user:["text","inlineData"],function:["functionResponse"],model:["text","functionCall","executableCode","codeExecutionResult"],system:["text"]};function gt(e){let t=!1;for(const n of e){const{role:o,parts:s}=n;if(!t&&o!=="user")throw new p(`First content should be with role 'user', got ${o}`);if(!se.includes(o))throw new p(`Each item should include role field. Got ${o} but valid roles are: ${JSON.stringify(se)}`);if(!Array.isArray(s))throw new p("Content should have 'parts' property with an array of Parts");if(s.length===0)throw new p("Each Content should have at least one part");const i={text:0,inlineData:0,functionCall:0,functionResponse:0,fileData:0,executableCode:0,codeExecutionResult:0};for(const r of s)for(const c of he)c in r&&(i[c]+=1);const a=ft[o];for(const r of he)if(!a.includes(r)&&i[r]>0)throw new p(`Content with role '${o}' can't contain '${r}' part`);t=!0}}function pe(e){var t;if(e.candidates===void 0||e.candidates.length===0)return!1;const n=(t=e.candidates[0])===null||t===void 0?void 0:t.content;if(n===void 0||n.parts===void 0||n.parts.length===0)return!1;for(const o of n.parts)if(o===void 0||Object.keys(o).length===0||o.text!==void 0&&o.text==="")return!1;return!0}/**
 * @license
 * Copyright 2024 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */const ve="SILENT_ERROR";class mt{constructor(t,n,o,s={}){this.model=n,this.params=o,this._requestOptions=s,this._history=[],this._sendPromise=Promise.resolve(),this._apiKey=t,o!=null&&o.history&&(gt(o.history),this._history=o.history)}async getHistory(){return await this._sendPromise,this._history}async sendMessage(t,n={}){var o,s,i,a,r,c;await this._sendPromise;const C=F(t),m={safetySettings:(o=this.params)===null||o===void 0?void 0:o.safetySettings,generationConfig:(s=this.params)===null||s===void 0?void 0:s.generationConfig,tools:(i=this.params)===null||i===void 0?void 0:i.tools,toolConfig:(a=this.params)===null||a===void 0?void 0:a.toolConfig,systemInstruction:(r=this.params)===null||r===void 0?void 0:r.systemInstruction,cachedContent:(c=this.params)===null||c===void 0?void 0:c.cachedContent,contents:[...this._history,C]},v=Object.assign(Object.assign({},this._requestOptions),n);let g;return this._sendPromise=this._sendPromise.then(()=>be(this._apiKey,this.model,m,v)).then(f=>{var h;if(pe(f.response)){this._history.push(C);const w=Object.assign({parts:[],role:"model"},(h=f.response.candidates)===null||h===void 0?void 0:h[0].content);this._history.push(w)}else{const w=R(f.response);w&&console.warn(`sendMessage() was unsuccessful. ${w}. Inspect response object for details.`)}g=f}).catch(f=>{throw this._sendPromise=Promise.resolve(),f}),await this._sendPromise,g}async sendMessageStream(t,n={}){var o,s,i,a,r,c;await this._sendPromise;const C=F(t),m={safetySettings:(o=this.params)===null||o===void 0?void 0:o.safetySettings,generationConfig:(s=this.params)===null||s===void 0?void 0:s.generationConfig,tools:(i=this.params)===null||i===void 0?void 0:i.tools,toolConfig:(a=this.params)===null||a===void 0?void 0:a.toolConfig,systemInstruction:(r=this.params)===null||r===void 0?void 0:r.systemInstruction,cachedContent:(c=this.params)===null||c===void 0?void 0:c.cachedContent,contents:[...this._history,C]},v=Object.assign(Object.assign({},this._requestOptions),n),g=ye(this._apiKey,this.model,m,v);return this._sendPromise=this._sendPromise.then(()=>g).catch(f=>{throw new Error(ve)}).then(f=>f.response).then(f=>{if(pe(f)){this._history.push(C);const h=Object.assign({},f.candidates[0].content);h.role||(h.role="model"),this._history.push(h)}else{const h=R(f);h&&console.warn(`sendMessageStream() was unsuccessful. ${h}. Inspect response object for details.`)}}).catch(f=>{f.message!==ve&&console.error(f)}),g}}/**
 * @license
 * Copyright 2024 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */async function ht(e,t,n,o){return(await H(t,G.COUNT_TOKENS,e,!1,JSON.stringify(n),o)).json()}/**
 * @license
 * Copyright 2024 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */async function pt(e,t,n,o){return(await H(t,G.EMBED_CONTENT,e,!1,JSON.stringify(n),o)).json()}async function vt(e,t,n,o){const s=n.requests.map(a=>Object.assign(Object.assign({},a),{model:t}));return(await H(t,G.BATCH_EMBED_CONTENTS,e,!1,JSON.stringify({requests:s}),o)).json()}/**
 * @license
 * Copyright 2024 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */class Ee{constructor(t,n,o={}){this.apiKey=t,this._requestOptions=o,n.model.includes("/")?this.model=n.model:this.model=`models/${n.model}`,this.generationConfig=n.generationConfig||{},this.safetySettings=n.safetySettings||[],this.tools=n.tools,this.toolConfig=n.toolConfig,this.systemInstruction=we(n.systemInstruction),this.cachedContent=n.cachedContent}async generateContent(t,n={}){var o;const s=me(t),i=Object.assign(Object.assign({},this._requestOptions),n);return be(this.apiKey,this.model,Object.assign({generationConfig:this.generationConfig,safetySettings:this.safetySettings,tools:this.tools,toolConfig:this.toolConfig,systemInstruction:this.systemInstruction,cachedContent:(o=this.cachedContent)===null||o===void 0?void 0:o.name},s),i)}async generateContentStream(t,n={}){var o;const s=me(t),i=Object.assign(Object.assign({},this._requestOptions),n);return ye(this.apiKey,this.model,Object.assign({generationConfig:this.generationConfig,safetySettings:this.safetySettings,tools:this.tools,toolConfig:this.toolConfig,systemInstruction:this.systemInstruction,cachedContent:(o=this.cachedContent)===null||o===void 0?void 0:o.name},s),i)}startChat(t){var n;return new mt(this.apiKey,this.model,Object.assign({generationConfig:this.generationConfig,safetySettings:this.safetySettings,tools:this.tools,toolConfig:this.toolConfig,systemInstruction:this.systemInstruction,cachedContent:(n=this.cachedContent)===null||n===void 0?void 0:n.name},t),this._requestOptions)}async countTokens(t,n={}){const o=ct(t,{model:this.model,generationConfig:this.generationConfig,safetySettings:this.safetySettings,tools:this.tools,toolConfig:this.toolConfig,systemInstruction:this.systemInstruction,cachedContent:this.cachedContent}),s=Object.assign(Object.assign({},this._requestOptions),n);return ht(this.apiKey,this.model,o,s)}async embedContent(t,n={}){const o=dt(t),s=Object.assign(Object.assign({},this._requestOptions),n);return pt(this.apiKey,this.model,o,s)}async batchEmbedContents(t,n={}){const o=Object.assign(Object.assign({},this._requestOptions),n);return vt(this.apiKey,this.model,t,o)}}/**
 * @license
 * Copyright 2024 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */class Et{constructor(t){this.apiKey=t}getGenerativeModel(t,n){if(!t.model)throw new p("Must provide a model name. Example: genai.getGenerativeModel({ model: 'my-model-name' })");return new Ee(this.apiKey,t,n)}getGenerativeModelFromCachedContent(t,n,o){if(!t.name)throw new A("Cached content must contain a `name` field.");if(!t.model)throw new A("Cached content must contain a `model` field.");const s=["model","systemInstruction"];for(const a of s)if(n!=null&&n[a]&&t[a]&&(n==null?void 0:n[a])!==t[a]){if(a==="model"){const r=n.model.startsWith("models/")?n.model.replace("models/",""):n.model,c=t.model.startsWith("models/")?t.model.replace("models/",""):t.model;if(r===c)continue}throw new A(`Different value for "${a}" specified in modelParams (${n[a]}) and cachedContent (${t[a]})`)}const i=Object.assign(Object.assign({},n),{model:t.model,tools:t.tools,toolConfig:t.toolConfig,systemInstruction:t.systemInstruction,cachedContent:t});return new Ee(this.apiKey,i,o)}}const Ct={class:"row items-center q-gutter-sm"},_t={class:"row q-gutter-md q-mb-md"},yt={key:0,class:"q-mb-md"},bt={class:"text-subtitle2 q-mb-sm"},wt={class:"row q-gutter-md q-mb-md"},It={class:"row q-gutter-md q-mb-md"},Ot={key:1,class:"q-mb-md"},Rt={class:"text-subtitle2 q-mb-sm"},At={class:"row q-gutter-md q-mb-md"},St={key:2,class:"q-mb-md"},Tt={class:"text-subtitle2 q-mb-sm"},Nt={class:"row q-gutter-sm"},xt={class:"row items-center q-mb-sm"},Mt={class:"text-subtitle2"},qt={class:"ai-result"},Gt={key:0,class:"row q-gutter-sm q-mt-md"},Lt={key:1,class:"row q-gutter-sm q-mt-md"},Ut={__name:"GeminiPrompt",props:{modelValue:String,extra:String,autoRun:Boolean,questionId:{type:[String,Number],default:null}},emits:["update:modelValue","update:extra","response","use-as-question","use-as-answer"],setup(e,{emit:t}){const n=e,o=t,s=Ge(),i=M("questions"),a=M(n.modelValue||""),r=M(n.extra||""),c=M(""),C=M(!1),m=M({type:"behavioral",difficulty:"intermediate",topic:"",count:3}),v=M({question:"",style:"professional",length:"detailed"}),g=$e(()=>i.value==="questions"?m.value.topic.trim().length>0:i.value==="answers"?v.value.question.trim().length>0:a.value.trim().length>0),f=[{label:"Behavioral",value:"behavioral"},{label:"Technical",value:"technical"},{label:"Situational",value:"situational"},{label:"Knowledge-based",value:"knowledge"},{label:"Problem-solving",value:"problem-solving"}],h=[{label:"Beginner",value:"beginner"},{label:"Intermediate",value:"intermediate"},{label:"Advanced",value:"advanced"},{label:"Expert",value:"expert"}],w=[{label:"Professional",value:"professional"},{label:"Conversational",value:"conversational"},{label:"Technical",value:"technical"},{label:"Educational",value:"educational"},{label:"Creative",value:"creative"}],Ie=[{label:"Brief",value:"brief"},{label:"Detailed",value:"detailed"},{label:"Comprehensive",value:"comprehensive"}];J(()=>n.modelValue,E=>a.value=E),J(()=>n.extra,E=>r.value=E);const Oe=new Et(void 0),Re=()=>{switch(i.value){case"questions":return"Generate Questions";case"answers":return"Generate Answer";default:return"Ask Gemini"}},Ae=()=>{let E="";if(i.value==="questions"){const{type:l,difficulty:I,topic:u,count:U}=m.value;E=`Generate ${U} ${I} level ${l} interview questions about ${u}.`,r.value&&(E+=` Additional requirements: ${r.value}`),E+=" Format the response as a numbered list with clear, well-structured questions. Make them practical and relevant for job interviews."}else if(i.value==="answers"){const{question:l,style:I,length:u}=v.value;E=`Provide a ${u} ${I} answer to this question: "${l}"`,r.value&&(E+=` Additional context: ${r.value}`),E+=" Make the answer well-structured, informative, and suitable for a job interview context."}else E=[a.value,r.value].filter(Boolean).join(`

`);return E},X=async()=>{var l,I,u;const E=Ae();C.value=!0,c.value="";try{const S=await Oe.getGenerativeModel({model:"gemini-1.5-flash"}).generateContent(E);console.log("Full Gemini result:",S);let T="No response.";if(S!=null&&S.response){console.log("Response object:",S.response);try{T=S.response.text(),console.log("Text extracted via .text():",T)}catch(Me){console.log("Error calling .text(), trying manual extraction:",Me);const j=S.response.candidates;if(console.log("Candidates:",j),j&&j.length>0){const B=(I=(l=j[0])==null?void 0:l.content)==null?void 0:I.parts;console.log("Parts:",B),B&&B.length>0&&(T=((u=B[0])==null?void 0:u.text)||"No response.",console.log("Text extracted manually:",T))}}}console.log("Final AI Response:",T),c.value=T,o("response",T),s.notify({type:"positive",message:"AI content generated successfully!",position:"top",icon:"auto_awesome"})}catch(U){console.error("Gemini Error:",U),console.error("Full error details:",U),s.notify({type:"negative",message:"Failed to get a response from Gemini. Check console for details."})}C.value=!1},Se=()=>{navigator.clipboard.writeText(c.value).then(()=>{s.notify({type:"positive",message:"Content copied to clipboard!",position:"top"})})},Te=()=>{c.value=""},Ne=()=>{o("use-as-question",c.value),s.notify({type:"info",message:"Question content ready to use",position:"top"})},xe=()=>{o("use-as-answer",c.value),s.notify({type:"info",message:"Answer content ready to use",position:"top"})},z=()=>{s.notify({type:"info",message:"Edit functionality - copy the content and paste it in your form",position:"top"})};return J(i,()=>{c.value=""}),Fe(()=>{n.autoRun&&a.value&&X()}),(E,l)=>{const I=He("q-markdown");return y(),K(De,{class:"gemini-prompt-card"},{default:b(()=>[d(Q,{class:"bg-gradient-ai text-white"},{default:b(()=>[_("div",Ct,[d(D,{name:"auto_awesome",size:"md"}),l[17]||(l[17]=_("div",{class:"text-h6"},"AI Assistant",-1)),d(ee),d(Le,{icon:"psychology",color:"white","text-color":"primary",size:"sm"},{default:b(()=>l[16]||(l[16]=[q(" Powered by Gemini ")])),_:1})])]),_:1}),d(Q,null,{default:b(()=>[_("div",_t,[d(Ve,{modelValue:i.value,"onUpdate:modelValue":l[0]||(l[0]=u=>i.value=u),"toggle-color":"primary",options:[{label:"Generate Questions",value:"questions",icon:"quiz"},{label:"Generate Answers",value:"answers",icon:"forum"},{label:"Custom Prompt",value:"custom",icon:"edit"}],class:"full-width"},null,8,["modelValue"])]),i.value==="questions"?(y(),V("div",yt,[_("div",bt,[d(D,{name:"quiz",class:"q-mr-xs"}),l[18]||(l[18]=q(" Question Generation Settings "))]),_("div",wt,[d(P,{modelValue:m.value.type,"onUpdate:modelValue":l[1]||(l[1]=u=>m.value.type=u),options:f,label:"Question Type",class:"col",outlined:"",dense:""},null,8,["modelValue"]),d(P,{modelValue:m.value.difficulty,"onUpdate:modelValue":l[2]||(l[2]=u=>m.value.difficulty=u),options:h,label:"Difficulty Level",class:"col",outlined:"",dense:""},null,8,["modelValue"])]),_("div",It,[d(N,{modelValue:m.value.topic,"onUpdate:modelValue":l[3]||(l[3]=u=>m.value.topic=u),label:"Topic/Subject",placeholder:"e.g., JavaScript, Project Management, Leadership",class:"col",outlined:"",dense:""},null,8,["modelValue"]),d(N,{modelValue:m.value.count,"onUpdate:modelValue":l[4]||(l[4]=u=>m.value.count=u),label:"Number of Questions",type:"number",min:"1",max:"10",class:"col-3",outlined:"",dense:""},null,8,["modelValue"])]),d(N,{modelValue:r.value,"onUpdate:modelValue":[l[5]||(l[5]=u=>r.value=u),l[6]||(l[6]=u=>o("update:extra",u))],label:"Additional Instructions (Optional)",placeholder:"e.g., Focus on practical scenarios, Include code examples",type:"textarea",rows:"2",outlined:"",class:"q-mb-md"},null,8,["modelValue"])])):O("",!0),i.value==="answers"?(y(),V("div",Ot,[_("div",Rt,[d(D,{name:"forum",class:"q-mr-xs"}),l[19]||(l[19]=q(" Answer Generation Settings "))]),d(N,{modelValue:v.value.question,"onUpdate:modelValue":l[7]||(l[7]=u=>v.value.question=u),label:"Question to Answer",placeholder:"Enter the question you want to generate an answer for",type:"textarea",rows:"2",outlined:"",class:"q-mb-md",rules:[u=>!!u||"Question is required for answer generation"]},null,8,["modelValue","rules"]),_("div",At,[d(P,{modelValue:v.value.style,"onUpdate:modelValue":l[8]||(l[8]=u=>v.value.style=u),options:w,label:"Answer Style",class:"col",outlined:"",dense:""},null,8,["modelValue"]),d(P,{modelValue:v.value.length,"onUpdate:modelValue":l[9]||(l[9]=u=>v.value.length=u),options:Ie,label:"Answer Length",class:"col",outlined:"",dense:""},null,8,["modelValue"])]),d(N,{modelValue:r.value,"onUpdate:modelValue":[l[10]||(l[10]=u=>r.value=u),l[11]||(l[11]=u=>o("update:extra",u))],label:"Additional Context (Optional)",placeholder:"e.g., Target audience, specific requirements, examples to include",type:"textarea",rows:"2",outlined:"",class:"q-mb-md"},null,8,["modelValue"])])):O("",!0),i.value==="custom"?(y(),V("div",St,[_("div",Tt,[d(D,{name:"edit",class:"q-mr-xs"}),l[20]||(l[20]=q(" Custom Prompt "))]),d(N,{modelValue:a.value,"onUpdate:modelValue":[l[12]||(l[12]=u=>a.value=u),l[13]||(l[13]=u=>o("update:modelValue",u))],label:"Your Prompt",outlined:"",dense:"",autogrow:"",rules:[u=>!!u||"Prompt is required"],class:"q-mb-sm"},null,8,["modelValue","rules"]),d(N,{modelValue:r.value,"onUpdate:modelValue":[l[14]||(l[14]=u=>r.value=u),l[15]||(l[15]=u=>o("update:extra",u))],label:"Extra instructions (optional)",outlined:"",dense:"",autogrow:"",class:"q-mb-md"},null,8,["modelValue"])])):O("",!0),_("div",Nt,[d(x,{loading:C.value,disable:!g.value,color:"primary",icon:"auto_awesome",label:Re(),onClick:X,class:"col"},null,8,["loading","disable","label"]),c.value?(y(),K(x,{key:0,icon:"content_copy",flat:"",color:"primary",onClick:Se},{default:b(()=>[d(Z,null,{default:b(()=>l[21]||(l[21]=[q("Copy Result")])),_:1})]),_:1})):O("",!0),c.value?(y(),K(x,{key:1,icon:"clear",flat:"",color:"negative",onClick:Te},{default:b(()=>[d(Z,null,{default:b(()=>l[22]||(l[22]=[q("Clear Result")])),_:1})]),_:1})):O("",!0)])]),_:1}),c.value?(y(),K(Q,{key:0,class:"bg-grey-1"},{default:b(()=>[_("div",xt,[_("div",Mt,[d(D,{name:"auto_awesome",class:"q-mr-xs"}),l[23]||(l[23]=q(" AI Generated Content "))]),d(ee),d(ke,{text:c.value,size:"sm",round:"",flat:""},null,8,["text"])]),d(Ue,{class:"q-mb-md"}),_("div",qt,[d(I,{src:c.value},null,8,["src"])]),i.value==="questions"?(y(),V("div",Gt,[d(x,{icon:"add",label:"Use as New Question",color:"positive",flat:"",onClick:Ne}),d(x,{icon:"edit",label:"Edit & Use",color:"primary",flat:"",onClick:z})])):O("",!0),i.value==="answers"?(y(),V("div",Lt,[d(x,{icon:"add",label:"Use as Answer",color:"positive",flat:"",onClick:xe}),d(x,{icon:"edit",label:"Edit & Use",color:"primary",flat:"",onClick:z})])):O("",!0)]),_:1})):O("",!0)]),_:1})}}},Yt=qe(Ut,[["__scopeId","data-v-f5e650d1"]]);export{Yt as default};
