import Dexie from 'dexie';

const db = new Dexie('LessonEditorDB');
db.version(1).stores({ lessons: 'lessonId, updatedAt' });

export async function saveLessonToIndexedDB(lesson) {
  const payload = { ...lesson, updatedAt: Date.now() };
  await db.lessons.put(payload);
  return payload;
}

export async function loadLessonFromIndexedDB(lessonId) {
  return await db.lessons.get(lessonId);
}

export async function loadLastLesson() {
  const all = await db.lessons.orderBy('updatedAt').reverse().toArray();
  return all.length ? all[0] : null;
}

export async function clearLessons() {
  await db.table('lessons').clear();
}

export default { saveLessonToIndexedDB, loadLessonFromIndexedDB, loadLastLesson, clearLessons };
