export async function exportAsJSON(obj, filename = 'lesson.json'){
  const blob = new Blob([JSON.stringify(obj, null, 2)], { type: 'application/json' })
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a'); a.href = url; a.download = filename; a.click(); URL.revokeObjectURL(url)
}

export async function parseJSONFile(file){ const text = await file.text(); return JSON.parse(text) }
