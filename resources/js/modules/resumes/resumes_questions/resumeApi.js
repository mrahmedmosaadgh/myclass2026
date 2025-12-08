import axios from 'axios';

export default {
  async getQuestions() {
    const { data } = await axios.get('/api/resume-questions');
    return data;
  },
  async createQuestion(payload) {
    const { data } = await axios.post('/api/resume-questions', payload);
    return data;
  },
  async updateQuestion(id, payload) {
    const { data } = await axios.put(`/api/resume-questions/${id}`, payload);
    return data;
  },
  async deleteQuestion(id) {
    return axios.delete(`/api/resume-questions/${id}`);
  },
  async getAnswers(questionId) {
    const { data } = await axios.get(`/api/resume-questions/${questionId}/answers`);
    return data;
  },
  async createAnswer(questionId, payload) {
    const { data } = await axios.post(`/api/resume-questions/${questionId}/answers`, payload);
    return data;
  },
  async updateAnswer(id, payload) {
    const { data } = await axios.put(`/api/resume-answers/${id}`, payload);
    return data;
  },
  async deleteAnswer(id) {
    return axios.delete(`/api/resume-answers/${id}`);
  }
};
