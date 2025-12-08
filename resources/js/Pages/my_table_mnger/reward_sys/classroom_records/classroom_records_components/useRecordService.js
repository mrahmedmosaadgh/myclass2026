import axios from 'axios';

export async function fetchClassroomRecords(params){
  const resp = await axios.get('/api/classroom-records', { params });
  return resp.data;
}

export async function updateClassroomRecord(id, payload){
  const resp = await axios.patch(`/api/classroom-records/${id}`, payload);
  return resp.data;
}
