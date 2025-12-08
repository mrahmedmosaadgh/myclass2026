<template>
  <div>
    <h1>Live MP3 Transcriber with Editing</h1>
    
    <div>
      <label for="language">Select Language:</label>
      <select id="language" v-model="selectedLanguage" @change="setupSpeechRecognition">
        <option value="en-US">English (US) - Default</option>
        <option value="es-ES">Spanish (Spain)</option>
        <option value="fr-FR">French (France)</option>
        <option value="de-DE">German (Germany)</option>
        </select>
    </div>
    D:\my_projects\2025\myclass9\myclass9\resources\js\Pages\quiz_system\add_students\add_students.vue
    
    <input type="file" accept=".mp3,audio/*" @change="loadAudioFile">
    <audio ref="audioPlayer" controls @play="startRecognition" @pause="stopRecognition" @ended="stopRecognition"></audio>
    
    <div>
      <button @click="clearTranscript" :disabled="isRecognizing">Clear Transcript</button>
      <button v-if="finalTranscript" @click="saveTranscript">Save as TXT</button>
    </div>

    <p class="status">Status: **{{ statusMessage }}**</p>
    
    <div class="transcript-container">
      <h2>Transcript (Editable):</h2>
      <textarea 
        v-model="finalTranscript" 
        placeholder="Start playing the audio to begin transcription. You can edit the text here as it appears."
        rows="15"
        cols="80"
      ></textarea>
      
      <p v-if="interimTranscript" class="interim-text">
        *Live correction: {{ interimTranscript }}
      </p>
    </div>
  </div>
</template>
<script>
// Define the Web Speech API interface for better type hinting
const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;

export default {
  data() {
    return {
      recognition: null,
      selectedLanguage: 'en-US', // **Set English as default**
      finalTranscript: '',
      interimTranscript: '',
      isRecognizing: false,
      statusMessage: 'Ready',
    };
  },
  
  mounted() {
    this.setupSpeechRecognition();
  },

  methods: {
    setupSpeechRecognition() {
      if (!SpeechRecognition) {
        this.statusMessage = 'ERROR: Speech Recognition is not supported in this browser.';
        return;
      }
      
      // Stop any previous recognition instance before setting up a new one
      if (this.recognition) {
        this.recognition.onend = null; // Prevent unwanted restart
        this.recognition.stop();
      }

      this.recognition = new SpeechRecognition();
      this.recognition.continuous = true; 
      this.recognition.interimResults = true; 
      this.recognition.lang = this.selectedLanguage; // Use the selected language

      this.recognition.onstart = () => {
        this.isRecognizing = true;
        this.statusMessage = 'Listening (Audio is Playing)...';
      };

      this.recognition.onerror = (event) => {
        console.error('Speech Recognition Error:', event);
        this.statusMessage = `Error: ${event.error}`;
      };

      this.recognition.onend = () => {
        this.isRecognizing = false;
        // Logic to automatically restart recognition if the audio is still playing
        if (this.$refs.audioPlayer && !this.$refs.audioPlayer.paused) {
          this.startRecognition();
        } else {
          this.statusMessage = 'Recognition stopped.';
        }
      };

      this.recognition.onresult = (event) => {
        let interim = '';
        let final = '';
        const currentFinalText = this.finalTranscript;

        for (let i = event.resultIndex; i < event.results.length; ++i) {
          if (event.results[i].isFinal) {
            final += event.results[i][0].transcript;
          } else {
            interim += event.results[i][0].transcript;
          }
        }
        
        // Append new final text, ensuring we don't overwrite user edits
        this.finalTranscript = currentFinalText + final;
        this.interimTranscript = interim; 
      };
    },

    loadAudioFile(event) {
      this.stopRecognition();
      const file = event.target.files[0];
      if (file) {
        const audioURL = URL.createObjectURL(file);
        this.$refs.audioPlayer.src = audioURL;
        this.clearTranscript();
        this.statusMessage = 'Audio loaded. Press play to transcribe.';
      }
    },

    startRecognition() {
      if (this.recognition && !this.isRecognizing) {
        try {
          // The browser will ask for microphone access here
          this.recognition.start();
        } catch (e) {
          if (e.name !== 'InvalidStateError') {
             console.error(e);
             this.statusMessage = 'Failed to start recognition.';
          }
        }
      }
    },

    stopRecognition() {
      if (this.recognition && this.isRecognizing) {
        this.recognition.stop();
        this.statusMessage = 'Recognition stopped.';
      }
    },

    clearTranscript() {
      this.finalTranscript = '';
      this.interimTranscript = '';
      this.statusMessage = 'Transcript cleared.';
    },
    
    saveTranscript() {
      if (!this.finalTranscript) return;

      const blob = new Blob([this.finalTranscript], { type: 'text/plain' });
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');
      
      a.href = url;
      a.download = 'transcription_' + new Date().toISOString().slice(0, 10) + '.txt';
      document.body.appendChild(a);
      a.click();
      document.body.removeChild(a);
      URL.revokeObjectURL(url);
      this.statusMessage = 'Transcript saved successfully!';
    },
  },
};
</script>
<style scoped>
.status {
  margin-top: 10px;
  font-size: 1.1em;
  font-weight: bold;
}
.transcript-container {
  margin-top: 20px;
}
textarea {
  width: 100%;
  max-width: 800px;
  padding: 10px;
  border: 1px solid #ccc;
  border-radius: 4px;
  font-size: 16px;
  box-sizing: border-box;
}
.interim-text {
  margin-top: 5px;
  color: #888;
  font-style: italic;
}
</style>