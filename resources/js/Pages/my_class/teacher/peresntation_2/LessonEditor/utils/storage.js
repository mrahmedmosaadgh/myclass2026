// Dexie.js scaffolding for lesson drafts. If Dexie not available, falls back to localStorage.
// This file assumes Dexie will be installed in your project for IndexedDB support.
let useDexie = false
try{ useDexie = typeof Dexie !== 'undefined' }catch(e){ useDexie = false }

const DRAFT_KEY = 'lesson_draft_v1'

export function useLessonStore(){
  if(useDexie){
    // placeholder — app should initialize Dexie and create a store named 'lessons'
    const db = new Dexie('LessonEditorDB')
    db.version(1).stores({ drafts: 'id,data' })
    return {
      async saveDraft(lesson){ await db.drafts.put({ id: 'current', data: lesson }) },
      async loadDraft(){ const row = await db.drafts.get('current'); return row?.data || null },
      async clear(){ await db.drafts.delete('current') }
    }
  } else {
    return {
      saveDraft(lesson){ localStorage.setItem(DRAFT_KEY, JSON.stringify(lesson)) },
      loadDraft(){ try{ return JSON.parse(localStorage.getItem(DRAFT_KEY)) }catch(e){ return null } },
      clear(){ localStorage.removeItem(DRAFT_KEY) }
    }
  }
}
