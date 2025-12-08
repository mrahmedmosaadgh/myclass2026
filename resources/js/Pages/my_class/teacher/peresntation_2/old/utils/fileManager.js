import JSZip from 'jszip';
import { saveAs } from 'file-saver';

// Create a zip with lesson JSON and optionally embedded small assets.
export async function exportLessonAsZip(lesson, assets = {}) {
  const zip = new JSZip();
  zip.file('lesson.json', JSON.stringify(lesson, null, 2));
  // assets is an object { 'path/in/zip': Blob|ArrayBuffer }
  for (const [path, data] of Object.entries(assets)) {
    zip.file(path, data);
  }
  const content = await zip.generateAsync({ type: 'blob' });
  saveAs(content, `${lesson.lessonId || 'lesson'}.zip`);
}

// Read a zip file and return parsed lesson + asset blobs map
export async function importLessonFromZip(file) {
  const zip = await JSZip.loadAsync(file);
  const result = { assets: {} };
  if (zip.files['lesson.json']) {
    const jsonText = await zip.files['lesson.json'].async('string');
    result.lesson = JSON.parse(jsonText);
  }
  // collect other files
  await Promise.all(Object.keys(zip.files).map(async (name) => {
    if (name === 'lesson.json') return;
    const blob = await zip.files[name].async('blob');
    result.assets[name] = blob;
  }));
  return result;
}

export default { exportLessonAsZip, importLessonFromZip };
