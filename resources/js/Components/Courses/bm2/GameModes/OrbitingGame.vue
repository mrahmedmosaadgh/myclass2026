<template>
  <div class="orbiting-game" ref="gameContainer">
    <!-- Playful Background Elements (CSS) -->
    <div class="star-bg"></div>
    <div class="twinkling"></div>
    
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
          <div class="time-ring-container">
            <svg width="40" height="40" viewBox="0 0 60 60" style="overflow: visible;">
              <circle cx="30" cy="30" r="28" stroke="rgba(255,255,255,0.2)" stroke-width="4" fill="none" />
              <circle
                cx="30" cy="30" r="28" stroke="#4facfe" stroke-width="6" stroke-linecap="round" fill="none"
                :stroke-dasharray="`${timeCircumference}`"
                :stroke-dashoffset="`${timeOffset}`"
                transform="rotate(-90 30 30)"
                class="time-ring"
                :class="{ 'critical-ring': questionTimeLeft <= 3 }"
              />
            </svg>
            <span class="time-value" :class="{ 'critical-text': questionTimeLeft <= 3 }">{{ questionTimeLeft }}</span>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Start Screen -->
    <div class="start-screen bounce-in" v-if="!isPlaying && !gameOver">
      <div class="mascot">🌟</div>
      <h2 class="game-title">Solar System!</h2>
      <p class="game-instructions">
        Find the correct planet orbiting the sun and tap it before time runs out!
      </p>
      <button class="btn-primary pushable" @click="startGame">
        <span class="front">Start Mission 🚀</span>
      </button>
    </div>
    
    <!-- Game Over Screen -->
    <div class="game-over-screen slide-up" v-if="gameOver">
      <div class="mascot">{{ finalScore > 50 ? '🏆' : '😅' }}</div>
      <h2 class="game-title">{{ finalScore > 50 ? 'Galactic Explorer!' : 'Mission Over!' }}</h2>
      
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
        <span class="front">Launch Again 🔄</span>
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
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

// Colors for planets
const planetStyles = [
  { body: '#f39c12', ring: '#e67e22', shadow: '#d35400' }, // Orange/Mars-like
  { body: '#3498db', ring: '#2980b9', shadow: '#1f618d' }, // Blue/Earth-like
  { body: '#9b59b6', ring: '#8e44ad', shadow: '#6c3483' }, // Purple
  { body: '#2ecc71', ring: '#27ae60', shadow: '#1e8449' }, // Green
  { body: '#e74c3c', ring: '#c0392b', shadow: '#922b21' }, // Red
  { body: '#1abc9c', ring: '#16a085', shadow: '#117864' }, // Teal
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

// Game state
const orbitingOptions = ref([]);
const orbitAngle = ref(0);
const orbitRadius = ref(200); // Increased from 150 for more space
const orbitSpeed = ref(0.012); // Slightly slower for readability
const particles = ref([]);
const bgStars = ref([]);
let animationFrameId = null;
let ctx = null;

const timeCircumference = computed(() => 2 * Math.PI * 28);
const timeOffset = computed(() => {
  const totalTime = 30; // base time per question
  const fraction = Math.max(0, questionTimeLeft.value) / totalTime;
  return timeCircumference.value * (1 - fraction);
});

onMounted(() => {
  const canvas = gameCanvas.value;
  const container = gameContainer.value;
  
  canvas.width = container.clientWidth;
  canvas.height = container.clientHeight;
  
  ctx = canvas.getContext('2d');
  
  // Initialize background stars
  for(let i=0; i<80; i++) {
    bgStars.value.push({
      x: Math.random() * canvas.width,
      y: Math.random() * canvas.height,
      radius: Math.random() * 2,
      o: Math.random(),
      s: 0.01 + Math.random() * 0.02
    });
  }
  
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
  
  // Adjust orbit radius based on screen size, keep it responsive
  orbitRadius.value = Math.min(canvas.width, canvas.height) * 0.35;
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
    startBackgroundMusic('orbiting');
  }
  
  handleResize();
  loadQuestion();
  gameLoop();
};

const loadQuestion = () => {
  isTransitioning.value = false;
  
  if (questionIndex.value >= props.questions.length) {
    endGame();
    return;
  }
  
  const qData = props.questions[questionIndex.value];
  
  let options = qData.options || qData.answers || [];
  if (typeof options === 'string') {
    try { options = JSON.parse(options); } catch (e) { options = []; }
  }
  if (!Array.isArray(options)) options = [];
  
  if (options.length === 0) {
    if (qData.correct_answer === 'True' || qData.correct_answer === 'False' || qData.type === 'true_false') {
      options = ['True', 'False'];
    } else if (qData.correct_answer) {
      options = [qData.correct_answer, "Other"];
      options.sort(() => Math.random() - 0.5);
    } else {
      options = ['A', 'B'];
    }
  }

  currentQuestion.value = {
    id: qData.id || qData.question_id,
    text: qData.text || qData.question_text || qData.question || 'Question text not available',
    correct_answer: qData.correct_answer || qData.answer,
    options: options,
  };
  
  spawnOrbitingOptions();
};

const spawnOrbitingOptions = () => {
  const options = currentQuestion.value.options || [];
  const angleStep = (Math.PI * 2) / options.length;
  
  orbitingOptions.value = options.map((option, i) => {
    const style = planetStyles[i % planetStyles.length];
    return {
      id: i,
      text: option,
      angle: i * angleStep,
      isCorrect: option === currentQuestion.value.correct_answer,
      x: 0,
      y: 0,
      radius: 90, // Target size
      currentRadius: 0, // Animates in
      style: style,
      rotation: Math.random() * Math.PI * 2,
      wobble: Math.random() * Math.PI * 2,
    }
  });
  
  orbitAngle.value = 0;
};

const createExplosion = (x, y, isCorrect) => {
  const count = isCorrect ? 50 : 25;
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
      color: isCorrect ? '#4ade80' : '#ef4444',
      size: Math.random() * 6 + 2,
    });
  }
};

const updateOrbits = () => {
  const difficulty = props.settings.difficulty;
  const speedMultiplier = difficulty === 'easy' ? 0.7 : difficulty === 'hard' ? 1.5 : 1;
  
  orbitAngle.value += orbitSpeed.value * speedMultiplier;
  
  const centerX = gameCanvas.value.width / 2;
  const centerY = gameCanvas.value.height / 2;
  
  orbitingOptions.value.forEach(option => {
    // Pop-in animation
    if(option.currentRadius < option.radius) {
      option.currentRadius += (option.radius - option.currentRadius) * 0.1;
    }
    
    // Add varying orbit distances slightly to make it organic
    const wobbleRadius = orbitRadius.value + Math.sin(option.wobble) * 15;
    option.wobble += 0.02;
    
    option.x = centerX + Math.cos(option.angle + orbitAngle.value) * wobbleRadius;
    option.y = centerY + Math.sin(option.angle + orbitAngle.value) * wobbleRadius;
    option.rotation += 0.02; // Planet spins on its axis
  });
  
  // Update particles
  for (let i = particles.value.length - 1; i >= 0; i--) {
    const p = particles.value[i];
    p.x += p.vx;
    p.y += p.vy;
    p.life -= p.decay;
    if (p.life <= 0) particles.value.splice(i, 1);
  }
  
  // Update background stars
  bgStars.value.forEach(star => {
    star.o += star.s;
    if(star.o > 1 || star.o < 0) star.s = -star.s;
  });
};

const drawPlanet = (ctx, x, y, r, style, rotation) => {
    ctx.save();
    ctx.translate(x, y);
    ctx.rotate(rotation);
    
    // Planet Body
    const gradient = ctx.createRadialGradient(-r/3, -r/3, r/10, 0, 0, r);
    gradient.addColorStop(0, '#ffffff');
    gradient.addColorStop(0.2, style.body);
    gradient.addColorStop(1, style.shadow);
    
    ctx.shadowColor = style.shadow;
    ctx.shadowBlur = 15;
    
    ctx.fillStyle = gradient;
    ctx.beginPath();
    ctx.arc(0, 0, r, 0, Math.PI * 2);
    ctx.fill();
    
    // Planet stripes/craters (simplified decorative pattern)
    ctx.fillStyle = 'rgba(0,0,0,0.1)';
    ctx.beginPath();
    ctx.ellipse(r/3, 0, r/2, r/4, Math.PI/6, 0, Math.PI*2);
    ctx.fill();
    ctx.beginPath();
    ctx.ellipse(-r/4, r/2, r/3, r/5, -Math.PI/4, 0, Math.PI*2);
    ctx.fill();

    // Planet atmosphere glow ring
    ctx.strokeStyle = style.ring;
    ctx.lineWidth = 4;
    ctx.beginPath();
    ctx.arc(0, 0, r + 4, 0, Math.PI * 2);
    ctx.stroke();

    ctx.restore();
};

const render = () => {
  const canvas = gameCanvas.value;
  if (!ctx) return;
  
  ctx.clearRect(0, 0, canvas.width, canvas.height);
  
  // Background handled mostly by CSS now, but draw the stars
  ctx.fillStyle = '#0f172a'; // Deep space color
  ctx.fillRect(0, 0, canvas.width, canvas.height);
  
  bgStars.value.forEach(star => {
    ctx.fillStyle = `rgba(255, 255, 255, ${Math.max(0, star.o)})`;
    ctx.beginPath();
    ctx.arc(star.x, star.y, star.radius, 0, Math.PI * 2);
    ctx.fill();
  });
  
  const centerX = canvas.width / 2;
  const centerY = canvas.height / 2;
  
  // Draw subtle orbit paths
  ctx.strokeStyle = 'rgba(255, 255, 255, 0.1)';
  ctx.lineWidth = 1;
  ctx.setLineDash([5, 10]);
  ctx.beginPath();
  ctx.arc(centerX, centerY, orbitRadius.value, 0, Math.PI * 2);
  ctx.stroke();
  ctx.beginPath();
  ctx.arc(centerX, centerY, orbitRadius.value + 15, 0, Math.PI * 2);
  ctx.stroke();
  ctx.beginPath();
  ctx.arc(centerX, centerY, orbitRadius.value - 15, 0, Math.PI * 2);
  ctx.stroke();
  ctx.setLineDash([]);
  
  // Draw The SUN (Center Question)
  const pulse = 1 + Math.sin(Date.now() / 300) * 0.05;
  const sunRadius = 150 * pulse;
  
  ctx.save();
  ctx.translate(centerX, centerY);
  
  // Sun Glow
  const sunGlow = ctx.createRadialGradient(0, 0, sunRadius * 0.5, 0, 0, sunRadius * 1.5);
  sunGlow.addColorStop(0, 'rgba(250, 204, 21, 0.8)');
  sunGlow.addColorStop(1, 'rgba(250, 204, 21, 0)');
  ctx.fillStyle = sunGlow;
  ctx.beginPath();
  ctx.arc(0, 0, sunRadius * 1.5, 0, Math.PI * 2);
  ctx.fill();
  
  // Sun Body
  const sunGradient = ctx.createRadialGradient(-20, -20, 10, 0, 0, sunRadius);
  sunGradient.addColorStop(0, '#fef08a');
  sunGradient.addColorStop(0.3, '#fde047');
  sunGradient.addColorStop(1, '#eab308');
  
  ctx.fillStyle = sunGradient;
  ctx.shadowColor = '#facc15';
  ctx.shadowBlur = 30;
  ctx.beginPath();
  ctx.arc(0, 0, sunRadius, 0, Math.PI * 2);
  ctx.fill();

  // Draw center question text
  ctx.shadowBlur = 0;
  ctx.fillStyle = '#713f12'; // Dark brown/yellow for contrast on sun
  ctx.font = '900 36px "Nunito", Arial, sans-serif';
  ctx.textAlign = 'center';
  ctx.textBaseline = 'middle';
  
  const questionText = currentQuestion.value?.text || '';
  const maxWidth = sunRadius * 1.8;
  const lineHeight = 40;
  const words = questionText.split(' ');
  let line = '';
  let lines = [];
  
  for (let n = 0; n < words.length; n++) {
    const testLine = line + words[n] + ' ';
    const metrics = ctx.measureText(testLine);
    if (metrics.width > maxWidth && n > 0) {
      lines.push(line);
      line = words[n] + ' ';
    } else {
      line = testLine;
    }
  }
  lines.push(line);
  
  if (lines.length > 3) {
    lines = lines.slice(0, 3);
    lines[2] = lines[2].substring(0, lines[2].length - 3) + '...';
  }
  
  const totalHeight = lines.length * lineHeight;
  const startY = -(totalHeight / 2) + (lineHeight / 2);
  
  lines.forEach((textLine, i) => {
    ctx.fillText(textLine.trim(), 0, startY + (i * lineHeight));
  });
  ctx.restore();
  
  // Draw orbiting options (Planets)
  orbitingOptions.value.forEach(option => {
    // Draw the planet
    drawPlanet(ctx, option.x, option.y, option.currentRadius, option.style, option.rotation);
    
    // Draw Option text ON TOP of planet
    ctx.shadowBlur = 0;
    ctx.fillStyle = 'white';
    ctx.strokeStyle = 'rgba(0,0,0,0.8)';
    ctx.lineWidth = 6;
    ctx.font = '900 28px "Nunito", Arial, sans-serif';
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';
    
    // Line breaks for long options
    const words = option.text.toString().split(' ');
    if (words.length > 2) {
      ctx.strokeText(words.slice(0, Math.ceil(words.length/2)).join(' '), option.x, option.y - 15);
      ctx.fillText(words.slice(0, Math.ceil(words.length/2)).join(' '), option.x, option.y - 15);
      ctx.strokeText(words.slice(Math.ceil(words.length/2)).join(' '), option.x, option.y + 15);
      ctx.fillText(words.slice(Math.ceil(words.length/2)).join(' '), option.x, option.y + 15);
    } else {
      ctx.strokeText(option.text, option.x, option.y);
      ctx.fillText(option.text, option.x, option.y);
    }
  });
  
  // Draw particles
  particles.value.forEach(p => {
    ctx.globalAlpha = p.life;
    ctx.fillStyle = p.color;
    ctx.beginPath();
    ctx.arc(p.x, p.y, p.size, 0, Math.PI * 2);
    ctx.fill();
    ctx.globalAlpha = 1.0;
  });
};

const gameLoop = () => {
  if (!isPlaying.value) return;
  
  updateOrbits();
  render();
  
  animationFrameId = requestAnimationFrame(gameLoop);
};

const selectOption = (clickedX, clickedY) => {
  if (!isPlaying.value || gameOver.value || isTransitioning.value) return;
  
  let hitOption = null;
  orbitingOptions.value.forEach(option => {
    const dx = clickedX - option.x;
    const dy = clickedY - option.y;
    const distance = Math.sqrt(dx * dx + dy * dy);
    
    if (distance <= option.radius) {
      hitOption = option;
    }
  });

  if (hitOption) {
    isTransitioning.value = true;
    hitOption.currentRadius = hitOption.radius * 1.5; // Expand animation
    createExplosion(hitOption.x, hitOption.y, hitOption.isCorrect);

    setTimeout(() => {
      if (hitOption.isCorrect) {
        if(score.value > 0 && score.value % 50 === 0) {shootConfetti();}
        const points = handleCorrectAnswer(10);
        playCorrectSound();
        emit('answer', {
          questionId: currentQuestion.value.id,
          selectedAnswer: hitOption.text,
          isCorrect: true,
          points,
        });
      } else {
        handleWrongAnswer(1);
        playWrongSound();
        emit('answer', {
          questionId: currentQuestion.value.id,
          selectedAnswer: hitOption.text,
          isCorrect: false,
        });
      }
      questionIndex.value++;
      setTimeout(loadQuestion, 800);
    }, 150);
  }
};

const shootConfetti = () => {
  confetti({
    particleCount: 150,
    spread: 80,
    origin: { y: 0.6 },
    colors: ['#3498db', '#f1c40f', '#e74c3c', '#9b59b6', '#2ecc71']
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
  const rect = gameCanvas.value.getBoundingClientRect();
  const clickX = event.clientX - rect.left;
  const clickY = event.clientY - rect.top;
  selectOption(clickX, clickY);
};

defineExpose({ handleClick });
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Nunito:wght@600;800&display=swap');

.orbiting-game {
  position: relative;
  width: 100%;
  height: calc(100vh - 250px);
  min-height: 800px;
  overflow: hidden;
  font-family: 'Nunito', sans-serif;
  border-radius: 20px;
  background-color: #0f172a;
  box-shadow: inset 0 0 50px rgba(0,0,0,0.8);
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
  background: rgba(30, 41, 59, 0.6);
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 16px;
  padding: 8px 16px;
  display: flex;
  align-items: center;
  gap: 12px;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
  color: #f8fafc;
}

.hud-icon {
  font-size: 1.8rem;
  filter: drop-shadow(0 2px 4px rgba(0,0,0,0.5));
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
  color: #cbd5e1;
}

.hud-value {
  font-size: 1.5rem;
  font-weight: 800;
  color: white;
}

.time-ring-container {
  position: relative;
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.time-ring {
  transition: stroke-dashoffset 0.1s linear, stroke 0.3s;
}

.critical-ring {
  stroke: #ef4444;
  animation: pulseStroke 1s infinite;
}

.time-value {
  position: absolute;
  font-size: 1.1rem;
  font-weight: 800;
  color: white;
}
.critical-text {
  color: #ef4444;
  animation: pulseScale 1s infinite;
}

@keyframes pulseStroke { 0%, 100% { stroke-width: 6; } 50% { stroke-width: 8; stroke: #f87171;} }
@keyframes pulseScale { 0%, 100% { transform: scale(1); } 50% { transform: scale(1.2); } }

.lives-container {
  display: flex;
  gap: 2px;
  font-size: 1.2rem;
}
.life-heart.pulse { animation: heartPulse 1.5s infinite; }
.life-heart.empty { filter: grayscale(1) opacity(0.3); }

@keyframes heartPulse { 0% { transform: scale(1); } 50% { transform: scale(1.15); } 100% { transform: scale(1); } }

.combo-active { background: rgba(234, 179, 8, 0.2); border-color: rgba(234, 179, 8, 0.5); }
.combo-value { color: #facc15; }
.shake { animation: shake 0.5s infinite; }
@keyframes shake {
  0%, 100% { transform: translateX(0); }
  25% { transform: translateX(-2px) rotate(-2deg); }
  75% { transform: translateX(2px) rotate(2deg); }
}

.time-warning { background: rgba(239, 68, 68, 0.2); border-color: rgba(239, 68, 68, 0.5); }
.spin-fast { animation: spin 1s linear infinite; }
@keyframes spin { 100% { transform: rotate(360deg); } }

/* Screens */
.start-screen,
.game-over-screen {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  background: rgba(15, 23, 42, 0.85); /* Dark space theme */
  backdrop-filter: blur(12px);
  padding: 3rem;
  border-radius: 30px;
  text-align: center;
  box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5), 0 0 0 2px rgba(255,255,255,0.1) inset;
  border: 4px solid #3b82f6; /* Blue border for space */
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

@keyframes float { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-15px); } }

.game-title {
  font-size: 2.5rem;
  font-weight: 800;
  margin-bottom: 1rem;
  background: linear-gradient(to right, #4facfe 0%, #00f2fe 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  filter: drop-shadow(0 2px 4px rgba(0,0,0,0.5));
}

.game-instructions {
  font-size: 1.2rem;
  color: #94a3b8;
  margin-bottom: 2.5rem;
  font-weight: 600;
  line-height: 1.5;
}

.final-stats-board {
  display: flex;
  justify-content: space-around;
  background: rgba(30, 41, 59, 0.6);
  border-radius: 20px;
  padding: 1.5rem;
  margin-bottom: 2.5rem;
  border: 1px solid rgba(255,255,255,0.1);
}

.stat-box { display: flex; flex-direction: column; align-items: center; }
.stat-icon { font-size: 2rem; margin-bottom: 5px; }
.stat-label { font-size: 0.9rem; color: #94a3b8; font-weight: 800; text-transform: uppercase; }
.stat-value { font-size: 2.5rem; font-weight: 800; }

.text-gradient {
  background: linear-gradient(to right, #4ade80 0%, #3b82f6 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.pushable {
  background: #1e3a8a; /* Darker blue */
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
  background: #3b82f6; /* Bright blue */
  transform: translateY(-6px);
  will-change: transform;
  transition: transform 250ms;
}
.pushable:hover .front { transform: translateY(-8px); }
.pushable:active .front { transform: translateY(-2px); }

</style>

