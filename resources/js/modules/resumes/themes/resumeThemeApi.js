// API module for resume themes
import axios from 'axios';

export async function getThemes() {
  const { data } = await axios.get('/api/resume-themes');
  return data;
}
// TODO: Add create, update, delete, apply, etc.
