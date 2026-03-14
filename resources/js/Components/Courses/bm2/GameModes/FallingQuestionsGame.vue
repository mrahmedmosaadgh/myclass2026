<template>
  <div class="falling-questions-game" ref="gameContainer">
    <!-- Playful Background Elements (CSS) -->
    <div class="cloud cloud-1"></div>
    <div class="cloud cloud-2"></div>
    <div class="cloud cloud-3"></div>

    <canvas ref="gameCanvas" class="game-canvas"></canvas>
    
    <!-- Upgraded HUD -->
    <div class="game-hud">
      <div class="hud-item glass-panel">
        <div class="hud-icon">⭐</div>
        <div class="hud-text">
          <span class="hud-label">Score</span>
          <span class="hud-value">{{ score }}</span>
        </div>
      </div>
      
      <div class="hud-item glass-panel">
        <div class="hud-icon">❤️</div>
        <div class="hud-text">
          <span class="hud-label">Lives</span>
          <div class="lives-container">
            <span v-for="n in Math.max(0, lives)" :key="n" class="life-heart pulse">❤️</span>
            <span v-for="n in Math.max(0, 3 - lives)" :key="'empty'+n" class="life-heart empty">🤍</span>
          </div>
        </div>
      </div>
      
      <div class="hud-item glass-panel" :class="{ 'combo-active': combo > 1 }">
        <div class="hud-icon">🔥</div>
        <div class="hud-text">
          <span class="hud-label">Combo</span>
          <span class="hud-value combo-value" :class="{ 'shake': combo >= 3 }">{{ combo }}x</span>
        </div>
      </div>
      
      <div class="hud-item glass-panel" :class="{ 'time-warning': questionTimeLeft <= 3 }">
        <div class="hud-icon" :class="{ 'spin-fast': questionTimeLeft <= 3 }">⏱️</div>
        <div class="hud-text">
          <span class="hud-label">Time</span>
          <span class="hud-value">{{ questionTimeLeft }}s</span>
        </div>
      </div>
    </div>
    
    <!-- Start Screen -->
    <div class="start-screen bounce-in" v-if="!isPlaying && !gameOver">
      <div class="mascot">🎮</div>
      <h2 class="game-title">Falling Questions!</h2>
      <p class="game-instructions">
        Catch the falling balloons and tap the correct answer before time runs out!
      </p>
      <button class="btn-primary pushable" @click="startGame">
        <span class="front">Start Game 🚀</span>
      </button>
    </div>
    
    <!-- Game Over Screen -->
    <div class="game-over-screen slide-up" v-if="gameOver">
      <div class="mascot">{{ finalScore > 50 ? '🏆' : '😅' }}</div>
      <h2 class="game-title">{{ finalScore > 50 ? 'Awesome Job!' : 'Good Try!' }}</h2>
      
      <div class="final-stats-board">
        <div class="stat-box">
          <span class="stat-icon">⭐</span>
          <span class="stat-label">Final Score</span>
          <span class="stat-value text-gradient">{{ finalScore }}</span>
        </div>
        <div class="stat-box">
          <span class="stat-icon">🔥</span>
          <span class="stat-label">Best Combo</span>
          <span class="stat-value text-gradient">{{ maxCombo }}x</span>
        </div>
      </div>
      
      <button class="btn-primary pushable" @click="restartGame">
        <span class="front">Play Again 🔄</span>
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue';
import { useGameEngine } from '@/composables/useGameEngine';
import { useGameAudio } from '@/composables/useGameAudio';
import confetti from 'canvas-confetti';

const props = defineProps({
  questions: {
    type: Array,
    required: true,
  },
  settings: {
    type: Object,
    default: () => ({
      difficulty: 'medium',
      lives: 3,
      sound: true,
      music: true,
    }),
  },
});

const emit = defineEmits(['answer', 'complete']);

const gameContainer = ref(null);
const gameCanvas = ref(null);
const currentQuestion = ref(null);
const questionIndex = ref(0);
const gameOver = ref(false);
const finalScore = ref(0);
const isTransitioning = ref(false);

// Colors for balloons
const balloonColors = [
  { p1: '#FF9A9E', p2: '#FECFEF' }, // Pink
  { p1: '#a18cd1', p2: '#fbc2eb' }, // Purple
  { p1: '#84fab0', p2: '#8fd3f4' }, // Green/Blue
  { p1: '#fccb90', p2: '#d57eeb' }, // Orange/Pink
  { p1: '#e0c3fc', p2: '#8ec5fc' }, // Lavender
];

// Game engine
const {
  isPlaying,
  score,
  lives,
  combo,
  maxCombo,
  questionTimeLeft,
  startGame: initGame,
  pauseGame,
  resumeGame,
  handleCorrectAnswer,
  handleWrongAnswer,
} = useGameEngine();

// Audio
const {
  playCorrectSound,
  playWrongSound,
  startBackgroundMusic,
  stopBackgroundMusic,
} = useGameAudio();

// Game objects
const fallingQuestion = ref(null);
const floatingOptions = ref([]);
const particles = ref([]);
let animationFrameId = null;
let ctx = null;

onMounted(() => {
  const canvas = gameCanvas.value;
  const container = gameContainer.value;
  
  canvas.width = container.clientWidth;
  canvas.height = container.clientHeight;
  
  ctx = canvas.getContext('2d');
  
  window.addEventListener('resize', handleResize);
  window.addEventListener('click', handleClick);
});

onUnmounted(() => {
  stopBackgroundMusic();
  if (animationFrameId) {
    cancelAnimationFrame(animationFrameId);
  }
  window.removeEventListener('resize', handleResize);
  window.removeEventListener('click', handleClick);
});

const handleResize = () => {
  const canvas = gameCanvas.value;
  const container = gameContainer.value;
  
  canvas.width = container.clientWidth;
  canvas.height = container.clientHeight;
};

const startGame = () => {
  initGame({
    lives: props.settings.lives,
    difficulty: props.settings.difficulty,
  });
  
  gameOver.value = false;
  isTransitioning.value = false;
  questionIndex.value = 0;
  
  if (props.settings.music) {
    startBackgroundMusic('falling');
  }
  
  loadQuestion();
  gameLoop();
};

const loadQuestion = () => {
  isTransitioning.value = false;
  
  if (questionIndex.value >= props.questions.length) {
    endGame();
    return;
  }
  
  const questionData = props.questions[questionIndex.value];
  
  let options = questionData.options || questionData.answers || [];
  if (typeof options === 'string') {
    try { options = JSON.parse(options); } catch (e) { options = []; }
  }
  if (!Array.isArray(options)) options = [];
  
  if (options.length === 0) {
    if (questionData.correct_answer === 'True' || questionData.correct_answer === 'False' || questionData.type === 'true_false') {
      options = ['True', 'False'];
    } else if (questionData.correct_answer) {
      options = [questionData.correct_answer, "Other"];
      options.sort(() => Math.random() - 0.5);
    } else {
      options = ['A', 'B'];
    }
  }
  
  currentQuestion.value = {
    id: questionData.id || questionData.question_id,
    text: questionData.text || questionData.question_text || questionData.question || 'Question text not available',
    correct_answer: questionData.correct_answer || questionData.answer,
    options: options,
  };
  
  spawnFallingQuestion();
  spawnFloatingOptions();
};

const spawnFallingQuestion = () => {
  const canvas = gameCanvas.value;
  const difficulty = props.settings.difficulty;
  
  fallingQuestion.value = {
    x: Math.random() * (canvas.width - 600) + 300,
    y: -200,
    vx: (Math.random() - 0.5) * 1.5,
    vy: difficulty === 'easy' ? 0.7 : difficulty === 'hard' ? 2.0 : 1.2,
    width: 600,
    height: 180,
    bobbingOffset: Math.random() * Math.PI * 2,
  };
};

const spawnFloatingOptions = () => {
  const options = currentQuestion.value.options || [];
  const canvas = gameCanvas.value;
  
  floatingOptions.value = options.map((option, i) => {
    const rColors = balloonColors[i % balloonColors.length];
    return {
      id: i,
      text: option,
      x: Math.random() * (canvas.width - 200) + 100,
      y: Math.random() * (canvas.height - 300) + 200,
      vx: (Math.random() - 0.5) * 2.5,
      vy: (Math.random() - 0.5) * 2.5,
      radius: 90,
      targetRadius: 90,
      isCorrect: option === currentQuestion.value.correct_answer,
      colors: rColors,
      wobble: Math.random() * Math.PI * 2,
    };
  });
};

const createExplosion = (x, y, colors, isCorrect) => {
  const count = isCorrect ? 40 : 20;
  for (let i = 0; i < count; i++) {
    const angle = Math.random() * Math.PI * 2;
    const speed = Math.random() * (isCorrect ? 8 : 4);
    particles.value.push({
      x,
      y,
      vx: Math.cos(angle) * speed,
      vy: Math.sin(angle) * speed,
      life: 1.0,
      decay: Math.random() * 0.02 + 0.02,
      color: isCorrect ? '#ffd700' : '#ff4444',
      size: Math.random() * 5 + 3,
      shape: Math.random() > 0.5 ? 'circle' : 'star'
    });
  }
};

const gameLoop = () => {
  if (!isPlaying.value) return;
  
  updateGameObjects();
  render();
  
  animationFrameId = requestAnimationFrame(gameLoop);
};

const updateGameObjects = () => {
  // Update falling question
  if (fallingQuestion.value) {
    fallingQuestion.value.y += fallingQuestion.value.vy;
    fallingQuestion.value.x += fallingQuestion.value.vx;
    fallingQuestion.value.bobbingOffset += 0.05;
    
    // Bounce off walls
    if (fallingQuestion.value.x <= 0 || fallingQuestion.value.x >= gameCanvas.value.width - fallingQuestion.value.width) {
      fallingQuestion.value.vx *= -1;
    }
    
    // Check if fell off screen
    if (fallingQuestion.value.y > gameCanvas.value.height) {
      if (!isTransitioning.value) {
        handleWrongAnswer(1);
        playWrongSound();
        loadQuestion();
      }
    }
  }
  
  // Update floating options
  floatingOptions.value.forEach(option => {
    option.x += option.vx;
    option.y += option.vy;
    option.wobble += 0.05;
    
    // Return to normal target radius
    option.radius += (option.targetRadius - option.radius) * 0.1;
    
    // Bounce off walls
    if (option.x - option.radius <= 0 || option.x + option.radius >= gameCanvas.value.width) {
      option.vx *= -1;
      option.x = Math.max(option.radius, Math.min(gameCanvas.value.width - option.radius, option.x));
    }
    if (option.y - option.radius <= 0 || option.y + option.radius >= gameCanvas.value.height) {
      option.vy *= -1;
      option.y = Math.max(option.radius, Math.min(gameCanvas.value.height - option.radius, option.y));
    }
  });

  // Update particles
  for (let i = particles.value.length - 1; i >= 0; i--) {
    const p = particles.value[i];
    p.x += p.vx;
    p.y += p.vy;
    p.vy += 0.1; // gravity
    p.life -= p.decay;
    if (p.life <= 0) {
      particles.value.splice(i, 1);
    }
  }
};

const drawStar = (cx, cy, spikes, outerRadius, innerRadius, fill) => {
  let rot = Math.PI / 2 * 3;
  let x = cx;
  let y = cy;
  let step = Math.PI / spikes;

  ctx.beginPath();
  ctx.moveTo(cx, cy - outerRadius);
  for (let i = 0; i < spikes; i++) {
    x = cx + Math.cos(rot) * outerRadius;
    y = cy + Math.sin(rot) * outerRadius;
    ctx.lineTo(x, y);
    rot += step;

    x = cx + Math.cos(rot) * innerRadius;
    y = cy + Math.sin(rot) * innerRadius;
    ctx.lineTo(x, y);
    rot += step;
  }
  ctx.lineTo(cx, cy - outerRadius);
  ctx.closePath();
  ctx.fillStyle = fill;
  ctx.fill();
};

const render = () => {
  const canvas = gameCanvas.value;
  if (!ctx) return;
  
  // Clear canvas
  ctx.clearRect(0, 0, canvas.width, canvas.height);
  
  // Draw falling question as a nice card
  if (fallingQuestion.value) {
    const fq = fallingQuestion.value;
    const currentY = fq.y + Math.sin(fq.bobbingOffset) * 10;
    
    ctx.save();
    ctx.translate(fq.x + fq.width / 2, currentY + fq.height / 2);
    
    // Card Shadow
    ctx.shadowColor = 'rgba(0, 0, 0, 0.2)';
    ctx.shadowBlur = 20;
    ctx.shadowOffsetY = 10;
    
    // Card Body
    ctx.fillStyle = 'rgba(255, 255, 255, 0.95)';
    ctx.beginPath();
    ctx.roundRect(-fq.width / 2, -fq.height / 2, fq.width, fq.height, 20);
    ctx.fill();
    
    // Card Header Strip
    ctx.fillStyle = '#6366f1';
    ctx.beginPath();
    ctx.roundRect(-fq.width / 2, -fq.height / 2, fq.width, 15, {tl: 20, tr: 20, bl: 0, br: 0});
    ctx.fill();
    
    // Question Text
    const questionText = currentQuestion.value?.text || '';
    if (questionText) {
      ctx.fillStyle = '#1e293b';
      ctx.shadowBlur = 0;
      ctx.shadowOffsetY = 0;
      ctx.font = '900 36px "Nunito", "Segoe UI", Arial, sans-serif';
      ctx.textAlign = 'center';
      ctx.textBaseline = 'middle';
      
      const maxWidth = fq.width - 40;
      const lineHeight = 42;
      const words = questionText.split(' ');
      let lines = [];
      let currentLine = '';
      
      for (let n = 0; n < words.length; n++) {
        const testLine = currentLine + words[n] + ' ';
        const metrics = ctx.measureText(testLine);
        if (metrics.width > maxWidth && n > 0) {
          lines.push(currentLine);
          currentLine = words[n] + ' ';
        } else {
          currentLine = testLine;
        }
      }
      lines.push(currentLine);
      
      if (lines.length > 3) {
        lines = lines.slice(0, 3);
        lines[2] = lines[2].substring(0, lines[2].length - 3) + '...';
      }
      
      const actualLines = Math.min(lines.length, 3);
      let startY = -((actualLines - 1) * lineHeight) / 2 + 10; // offset for the header strip
      
      lines.forEach((textLine, i) => {
        if (textLine.trim()) {
          ctx.fillText(textLine.trim(), 0, startY + (i * lineHeight), maxWidth);
        }
      });
    }
    ctx.restore();
  }
  
  // Draw floating options (bubbles/balloons)
  floatingOptions.value.forEach(option => {
    ctx.save();
    
    // Add wobble to position
    const wx = option.x + Math.sin(option.wobble) * 5;
    const wy = option.y + Math.cos(option.wobble * 0.8) * 5;
    
    ctx.translate(wx, wy);
    
    // Balloon String
    ctx.beginPath();
    ctx.moveTo(0, option.radius);
    ctx.quadraticCurveTo(10, option.radius + 15, -5, option.radius + 30);
    ctx.strokeStyle = 'rgba(255,255,255,0.6)';
    ctx.lineWidth = 2;
    ctx.stroke();

    // Balloon Knot
    ctx.beginPath();
    ctx.moveTo(-5, option.radius + 2);
    ctx.lineTo(5, option.radius + 2);
    ctx.lineTo(0, option.radius - 2);
    ctx.fillStyle = option.colors.p2;
    ctx.fill();

    // Bubble Shadow
    ctx.shadowColor = 'rgba(0, 0, 0, 0.3)';
    ctx.shadowBlur = 15;
    ctx.shadowOffsetY = 8;
    
    // Bubble Body (Radial Gradient for 3D sphere look)
    const gradient = ctx.createRadialGradient(-option.radius/3, -option.radius/3, option.radius/10, 0, 0, option.radius);
    gradient.addColorStop(0, '#ffffff');
    gradient.addColorStop(0.2, option.colors.p1);
    gradient.addColorStop(1, option.colors.p2);
    
    ctx.fillStyle = gradient;
    ctx.beginPath();
    ctx.arc(0, 0, option.radius, 0, Math.PI * 2);
    ctx.fill();
    
    // Option text
    ctx.shadowBlur = 0;
    ctx.shadowOffsetY = 0;
    ctx.fillStyle = '#ffffff';
    // Text stroke for readability
    ctx.strokeStyle = 'rgba(0,0,0,0.4)';
    ctx.lineWidth = 5;
    ctx.font = '900 32px "Nunito", "Segoe UI", Arial, sans-serif';
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';
    
    // Line breaks for long options
    const words = option.text.toString().split(' ');
    if (words.length > 2) {
      ctx.strokeText(words.slice(0, Math.ceil(words.length/2)).join(' '), 0, -15);
      ctx.fillText(words.slice(0, Math.ceil(words.length/2)).join(' '), 0, -15);
      ctx.strokeText(words.slice(Math.ceil(words.length/2)).join(' '), 0, 15);
      ctx.fillText(words.slice(Math.ceil(words.length/2)).join(' '), 0, 15);
    } else {
      ctx.strokeText(option.text, 0, 0);
      ctx.fillText(option.text, 0, 0);
    }
    
    ctx.restore();
  });

  // Draw Particles
  particles.value.forEach(p => {
    ctx.save();
    ctx.globalAlpha = p.life;
    ctx.translate(p.x, p.y);
    if(p.shape === 'star') {
       drawStar(0, 0, 5, p.size * 2, p.size, p.color);
    } else {
       ctx.fillStyle = p.color;
       ctx.beginPath();
       ctx.arc(0, 0, p.size, 0, Math.PI * 2);
       ctx.fill();
    }
    ctx.restore();
  });
};

const selectAnswer = (option) => {
  if (isTransitioning.value) return;
  isTransitioning.value = true;
  
  // Animate balloon poppping
  option.targetRadius = 120; // Pop out
  
  createExplosion(option.x, option.y, option.colors, option.isCorrect);

  setTimeout(() => {
    if (option.isCorrect) {
      if(score.value > 0 && score.value % 50 === 0) {
        shootConfetti();
      }
      const points = handleCorrectAnswer(10);
      playCorrectSound();
      emit('answer', {
        questionId: currentQuestion.value.id,
        selectedAnswer: option.text,
        isCorrect: true,
        points,
      });
    } else {
      handleWrongAnswer(1);
      playWrongSound();
      emit('answer', {
        questionId: currentQuestion.value.id,
        selectedAnswer: option.text,
        isCorrect: false,
      });
    }
    
    questionIndex.value++;
    setTimeout(loadQuestion, 800);
  }, 100);
};

const shootConfetti = () => {
  confetti({
    particleCount: 100,
    spread: 70,
    origin: { y: 0.6 },
    colors: ['#26ccff', '#a25afd', '#ff5e7e', '#88ff5a', '#fcff42', '#ffa62d', '#ff36ff']
  });
}

const endGame = () => {
  gameOver.value = true;
  finalScore.value = score.value;
  stopBackgroundMusic();
  
  if(finalScore.value > 50) {
    shootConfetti();
    setTimeout(shootConfetti, 500);
  }

  emit('complete', {
    score: finalScore.value,
    maxCombo: maxCombo.value,
    questionsAnswered: questionIndex.value,
  });
};

const restartGame = () => {
  startGame();
};

const handleClick = (event) => {
  if (!isPlaying.value || gameOver.value) return;
  
  const rect = gameCanvas.value.getBoundingClientRect();
  const clickX = event.clientX - rect.left;
  const clickY = event.clientY - rect.top;
  
  // Check if clicked on any floating bubble
  for (let option of floatingOptions.value) {
    const dx = clickX - option.x;
    const dy = clickY - option.y;
    const distance = Math.sqrt(dx * dx + dy * dy);
    if (distance <= option.radius) {
      selectAnswer(option);
      break; 
    }
  }
};

defineExpose({ handleClick });
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Nunito:wght@600;800&display=swap');

.falling-questions-game {
  position: relative;
  width: 100%;
  height: calc(100vh - 250px);
  min-height: 800px;
  overflow: hidden;
  font-family: 'Nunito', sans-serif;
  background: linear-gradient(135deg, #a1c4fd 0%, #c2e9fb 100%);
  border-radius: 20px;
  box-shadow: inset 0 0 50px rgba(255,255,255,0.5);
}

/* Clouds Animation */
.cloud {
  position: absolute;
  background: white;
  border-radius: 50px;
  opacity: 0.8;
  animation: floatCloud linear infinite;
}
.cloud::before, .cloud::after {
  content: '';
  position: absolute;
  background: white;
  border-radius: 50%;
}
.cloud-1 {
  width: 100px; height: 30px;
  top: 15%; left: -100px;
  animation-duration: 25s;
}
.cloud-1::before { width: 50px; height: 50px; top: -25px; left: 10px; }
.cloud-1::after { width: 40px; height: 40px; top: -15px; right: 15px; }

.cloud-2 {
  width: 150px; height: 45px;
  top: 40%; left: -150px;
  animation-duration: 35s;
  animation-delay: 5s;
  opacity: 0.5;
  transform: scale(0.8);
}
.cloud-2::before { width: 70px; height: 70px; top: -35px; left: 15px; }
.cloud-2::after { width: 60px; height: 60px; top: -25px; right: 20px; }

.cloud-3 {
  width: 80px; height: 25px;
  top: 70%; left: -80px;
  animation-duration: 20s;
  animation-delay: 10s;
  opacity: 0.6;
}
.cloud-3::before { width: 40px; height: 40px; top: -20px; left: 10px; }
.cloud-3::after { width: 30px; height: 30px; top: -10px; right: 10px; }

@keyframes floatCloud {
  from { transform: translateX(-150px); }
  to { transform: translateX(1200px); }
}

.game-canvas {
  display: block;
  width: 100%;
  height: 100%;
  position: relative;
  z-index: 5;
}

.game-hud {
  position: absolute;
  top: 15px;
  left: 20px;
  right: 20px;
  display: flex;
  justify-content: space-between;
  z-index: 10;
  pointer-events: none;
}

.glass-panel {
  background: rgba(255, 255, 255, 0.25);
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.5);
  border-radius: 16px;
  padding: 8px 16px;
  display: flex;
  align-items: center;
  gap: 12px;
  box-shadow: 0 8px 32px rgba(31, 38, 135, 0.1);
  color: #1e293b;
}

.hud-icon {
  font-size: 1.8rem;
  filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));
}

.hud-text {
  display: flex;
  flex-direction: column;
}

.hud-label {
  font-size: 0.75rem;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  opacity: 0.7;
}

.hud-value {
  font-size: 1.5rem;
  font-weight: 800;
}

.lives-container {
  display: flex;
  gap: 2px;
  font-size: 1.2rem;
}

.life-heart.pulse { animation: heartPulse 1.5s infinite; }
.life-heart.empty { filter: grayscale(1) opacity(0.5); }

@keyframes heartPulse {
  0% { transform: scale(1); }
  50% { transform: scale(1.15); }
  100% { transform: scale(1); }
}

.combo-active {
  background: rgba(255, 215, 0, 0.3);
  border-color: rgba(255, 215, 0, 0.6);
}

.combo-value { color: #f59e0b; }
.shake { animation: shake 0.5s infinite; }

@keyframes shake {
  0% { transform: translate(1px, 1px) rotate(0deg); }
  10% { transform: translate(-1px, -2px) rotate(-1deg); }
  20% { transform: translate(-3px, 0px) rotate(1deg); }
  30% { transform: translate(3px, 2px) rotate(0deg); }
  40% { transform: translate(1px, -1px) rotate(1deg); }
  50% { transform: translate(-1px, 2px) rotate(-1deg); }
  60% { transform: translate(-3px, 1px) rotate(0deg); }
  70% { transform: translate(3px, 1px) rotate(-1deg); }
  80% { transform: translate(-1px, -1px) rotate(1deg); }
  90% { transform: translate(1px, 2px) rotate(0deg); }
  100% { transform: translate(1px, -2px) rotate(-1deg); }
}

.time-warning { background: rgba(239, 68, 68, 0.2); }
.time-warning .hud-value { color: #dc2626; animation: pulseRed 1s infinite; }
.spin-fast { animation: spin 1s linear infinite; }

@keyframes pulseRed {
  0%, 100% { opacity: 1; transform: scale(1); }
  50% { opacity: 0.8; transform: scale(1.1); }
}
@keyframes spin { 100% { transform: rotate(360deg); } }

/* Screens */
.start-screen,
.game-over-screen {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  background: rgba(255, 255, 255, 0.9);
  backdrop-filter: blur(10px);
  padding: 3rem;
  border-radius: 30px;
  text-align: center;
  box-shadow: 0 20px 50px rgba(0, 0, 0, 0.2);
  border: 4px solid white;
  z-index: 20;
  width: 90%;
  max-width: 500px;
}

.bounce-in { animation: bounceIn 0.8s cubic-bezier(0.175, 0.885, 0.32, 1.275) both; }
.slide-up { animation: slideUp 0.6s cubic-bezier(0.165, 0.84, 0.44, 1) both; }

@keyframes bounceIn {
  0% { opacity: 0; transform: translate(-50%, -50%) scale(0.3); }
  50% { opacity: 1; transform: translate(-50%, -50%) scale(1.05); }
  70% { transform: translate(-50%, -50%) scale(0.9); }
  100% { transform: translate(-50%, -50%) scale(1); }
}

@keyframes slideUp {
  0% { opacity: 0; transform: translate(-50%, 50%); }
  100% { opacity: 1; transform: translate(-50%, -50%); }
}

.mascot {
  font-size: 5rem;
  margin-bottom: -10px;
  animation: float 3s ease-in-out infinite;
}

@keyframes float {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-15px); }
}

.game-title {
  font-size: 2.5rem;
  font-weight: 800;
  margin-bottom: 1rem;
  background: linear-gradient(45deg, #4facfe 0%, #00f2fe 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  filter: drop-shadow(0 2px 2px rgba(0,0,0,0.1));
}

.game-instructions {
  font-size: 1.2rem;
  color: #64748b;
  margin-bottom: 2.5rem;
  font-weight: 600;
  line-height: 1.5;
}

.final-stats-board {
  display: flex;
  justify-content: space-around;
  background: #f8fafc;
  border-radius: 20px;
  padding: 1.5rem;
  margin-bottom: 2.5rem;
  border: 2px solid #e2e8f0;
}

.stat-box { display: flex; flex-direction: column; align-items: center; }
.stat-icon { font-size: 2rem; margin-bottom: 5px; }
.stat-label { font-size: 0.9rem; color: #64748b; font-weight: 800; text-transform: uppercase; }
.stat-value { font-size: 2.5rem; font-weight: 800; }

.text-gradient {
  background: linear-gradient(135deg, #f6d365 0%, #fda085 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.pushable {
  background: hsl(340deg 100% 32%);
  border-radius: 16px;
  border: none;
  padding: 0;
  cursor: pointer;
  outline-offset: 4px;
}
.front {
  display: block;
  padding: 16px 42px;
  border-radius: 16px;
  font-size: 1.4rem;
  font-weight: 800;
  font-family: 'Nunito', sans-serif;
  color: white;
  background: hsl(345deg 100% 47%);
  transform: translateY(-6px);
  will-change: transform;
  transition: transform 250ms;
}
.pushable:hover .front { transform: translateY(-8px); }
.pushable:active .front { transform: translateY(-2px); }

</style>
