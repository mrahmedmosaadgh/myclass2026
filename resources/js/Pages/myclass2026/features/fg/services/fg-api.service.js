import axios from 'axios'

const api = axios.create({
  baseURL: '/api/fg',
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
  }
})

export const fgApi = {
  vent(text) {
    return api.post('/ai/vent', { text })
  },
  
  // Future dedicated API implementations can go here
  // For now, Stores directly use axios
}
