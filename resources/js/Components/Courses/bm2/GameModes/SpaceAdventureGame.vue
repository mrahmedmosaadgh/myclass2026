<template>
  <div class="space-adventure-game" ref="gameContainer" @mousemove="handleMouseMove" @touchmove.prevent="handleTouchMove" @click="handleClick">
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
          <span class="hud-label">Shields</span>
          <div class="lives-container">
            <span v-for="n in Math.max(0, lives)" :key="n" class="life-heart pulse">🛡️</span>
            <span v-for="n in Math.max(0, 3 - lives)" :key="'empty'+n" class="life-heart empty">⬛</span>
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
    
    <!-- Active Question Banner -->
    <div class="question-banner-container" v-if="isPlaying && currentQuestion && !gameOver">
      <div class="question-banner glass-panel fade-in">
        <div class="question-title">Target Computer:</div>
        <div class="question-text">{{ currentQuestion.text }}</div>
      </div>
    </div>
    
    <!-- Start Screen -->
    <div class="start-screen bounce-in" v-if="!isPlaying && !gameOver">
      <div class="mascot">🚀</div>
      <h2 class="game-title">Space Defender!</h2>
      <p class="game-instructions">
        Pilot your ship! Shoot the asteroid with the correct answer before it hits you!
      </p>
      <button class="btn-primary pushable" @click="startGame">
        <span class="front">Launch Ship 🚀</span>
      </button>
    </div>
    
    <!-- Game Over Screen -->
    <div class="game-over-screen slide-up" v-if="gameOver">
      <div class="mascot">{{ finalScore > 50 ? '🏅' : '💥' }}</div>
      <h2 class="game-title">{{ finalScore > 50 ? 'Ace Pilot!' : 'Ship Destroyed!' }}</h2>
      
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
        <span class="front">Fly Again 🔄</span>
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

// Game entities
const player = ref({ x: 0, y: 0, targetX: 0, targetY: 0, width: 60, height: 60, velocity: {x:0, y:0} });
const targets = ref([]);
const lasers = ref([]);
const particles = ref([]);
const stars = ref([]);

let animationFrameId = null;
let ctx = null;
let lastTime = 0;

const timeCircumference = computed(() => 2 * Math.PI * 28);
const timeOffset = computed(() => {
  const totalTime = 30;
  const fraction = Math.max(0, questionTimeLeft.value) / totalTime;
  return timeCircumference.value * (1 - fraction);
});

onMounted(() => {
  const canvas = gameCanvas.value;
  const container = gameContainer.value;
  
  canvas.width = container.clientWidth;
  canvas.height = container.clientHeight;
  
  ctx = canvas.getContext('2d');
  
  player.value.x = canvas.width / 2;
  player.value.y = canvas.height - 80;
  player.value.targetX = canvas.width / 2;
  player.value.targetY = canvas.height - 80;
  
  // Initialize multiple layers of stars for parallax
  for (let i = 0; i < 150; i++) {
    stars.value.push({
      x: Math.random() * canvas.width,
      y: Math.random() * canvas.height,
      size: Math.random() * 2.5,
      speed: Math.random() * 2 + 0.5,
      color: `hsla(${Math.random() * 60 + 200}, 100%, ${Math.random() * 40 + 60}%, ${Math.random()})`
    });
  }
  
  window.addEventListener('resize', handleResize);
});

onUnmounted(() => {
  stopBackgroundMusic();
  if (animationFrameId) {
    cancelAnimationFrame(animationFrameId);
  }
  window.removeEventListener('resize', handleResize);
});

const handleResize = () => {
  const canvas = gameCanvas.value;
  const container = gameContainer.value;
  
  canvas.width = container.clientWidth;
  canvas.height = container.clientHeight;
  
  // Keep player in bounds on resize
  if (player.value.x > canvas.width) player.value.x = canvas.width / 2;
  if (player.value.y > canvas.height) player.value.y = canvas.height - 80;
};

const startGame = () => {
  initGame({
    lives: props.settings.lives,
    difficulty: props.settings.difficulty,
  });
  
  gameOver.value = false;
  isTransitioning.value = false;
  questionIndex.value = 0;
  player.value.x = gameCanvas.value.width / 2;
  player.value.y = gameCanvas.value.height - 80;
  player.value.targetX = player.value.x;
  player.value.targetY = player.value.y;
  targets.value = [];
  lasers.value = [];
  particles.value = [];
  
  if (props.settings.music) {
    startBackgroundMusic('action');
  }
  
  lastTime = performance.now();
  loadQuestion();
  gameLoop(lastTime);
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
  
  spawnTargets();
};

const spawnTargets = () => {
  const canvas = gameCanvas.value;
  const options = currentQuestion.value.options || [];
  
  const difficulty = props.settings.difficulty;
  const baseSpeed = difficulty === 'easy' ? 0.6 : difficulty === 'hard' ? 1.5 : 1.0;
  
  targets.value = options.map((option, i) => {
    // Distribute evenly across top
    let initialX = (canvas.width / (options.length + 1)) * (i + 1);
    
    // Slight random offset
    initialX += (Math.random() - 0.5) * 40;
    
    // Determine target type based on correctness for visuals
    const isCorrect = option === currentQuestion.value.correct_answer;
    
    return {
      id: i,
      text: option,
      x: initialX,
      y: -100 - (Math.random() * 150), // Stagger spawns vertically
      radius: 75,
      speed: baseSpeed + (Math.random() * 0.5),
      isCorrect: isCorrect,
      active: true,
      rotation: Math.random() * Math.PI * 2,
      rotationSpeed: (Math.random() - 0.5) * 0.05,
      hitRadius: 65,
      scale: 0, // Pop in animation
      type: i % 3 // 0, 1, or 2 for different asteroid sprites
    };
  });
};

const handleMouseMove = (event) => {
  if (!isPlaying.value || gameOver.value) return;
  const rect = gameCanvas.value.getBoundingClientRect();
  player.value.targetX = event.clientX - rect.left;
  // Keep Y somewhat constrained to bottom half
  player.value.targetY = Math.max(gameCanvas.value.height / 2, event.clientY - rect.top);
};

const handleTouchMove = (event) => {
  if (!isPlaying.value || gameOver.value) return;
  const rect = gameCanvas.value.getBoundingClientRect();
  const touch = event.touches[0];
  player.value.targetX = touch.clientX - rect.left;
  player.value.targetY = Math.max(gameCanvas.value.height / 2, touch.clientY - rect.top);
};

const handleClick = () => {
  if (!isPlaying.value || gameOver.value) return;
  shootLaser();
};

// Also support spacebar to shoot
window.addEventListener('keydown', (e) => {
  if(e.code === 'Space' && isPlaying.value && !gameOver.value) {
    shootLaser();
  }
});

const shootLaser = () => {
  lasers.value.push({
    x: player.value.x,
    y: player.value.y - player.value.height / 2,
    speed: 15,
    width: 6,
    height: 25,
    color: combo.value >= 3 ? '#fbbf24' : '#4facfe', // Yellow lasers on combo
    active: true
  });
  
  // Recoil effect
  player.value.y += 5;
};

const spawnParticles = (x, y, count, color, isExplosion = false) => {
  for (let i = 0; i < count; i++) {
    const angle = Math.random() * Math.PI * 2;
    const speed = Math.random() * (isExplosion ? 8 : 4);
    particles.value.push({
      x,
      y,
      vx: Math.cos(angle) * speed,
      vy: Math.sin(angle) * speed,
      life: 1.0,
      decay: Math.random() * 0.02 + 0.02,
      color: color || '#fff',
      size: Math.random() * 4 + 1
    });
  }
};

const checkCollisions = () => {
  // Laser -> Target
  lasers.value.forEach(laser => {
    if (!laser.active) return;
    
    targets.value.forEach(target => {
      if (!target.active) return;
      
      const dx = laser.x - target.x;
      const dy = laser.y - target.y;
      const distance = Math.sqrt(dx * dx + dy * dy);
      
      if (distance < target.hitRadius) {
        laser.active = false;
        target.active = false;
        handleTargetHit(target);
      }
    });
  });
  
  // Player -> Target (Crash)
  targets.value.forEach(target => {
    if (!target.active) return;
      
    const dx = player.value.x - target.x;
    const dy = player.value.y - target.y;
    const distance = Math.sqrt(dx * dx + dy * dy);
    
    // Relaxed hit detection for player body
    if (distance < target.hitRadius + 20) {
      target.active = false;
      handlePlayerCrash(target);
    }
  });
};

const handleTargetHit = (target) => {
  if (isTransitioning.value) return;
  
  spawnParticles(target.x, target.y, 40, target.isCorrect ? '#4ade80' : '#ef4444', true);
  
  // Set all to inactive immediately so bullets/player don't hit them in this frame
  targets.value.forEach(t => t.active = false);

  if (target.isCorrect) {
    isTransitioning.value = true;
    if(score.value > 0 && score.value % 50 === 0) {shootConfetti();}
    const points = handleCorrectAnswer(15);
    playCorrectSound();
    emit('answer', {
      questionId: currentQuestion.value.id,
      selectedAnswer: target.text,
      isCorrect: true,
      points,
    });
    questionIndex.value++;
    setTimeout(loadQuestion, 500);
  } else {
    handleWrongAnswer(1);
    playWrongSound();
    emit('answer', {
      questionId: currentQuestion.value.id,
      selectedAnswer: target.text,
      isCorrect: false,
    });
  }
};

const handlePlayerCrash = (target) => {
  if (isTransitioning.value) return;

  spawnParticles(player.value.x, player.value.y, 30, '#fbbf24', true);
  
  // Immediately deactivate all targets to prevent cascading hits
  targets.value.forEach(t => t.active = false);
  
  handleWrongAnswer(1);
  playWrongSound();
  
  // Screen shake effect via CSS class later
  document.querySelector('.space-adventure-game') && document.querySelector('.space-adventure-game').classList.add('shake');
  setTimeout(()=> document.querySelector('.space-adventure-game') && document.querySelector('.space-adventure-game').classList.remove('shake'), 500);

  if (target.isCorrect) {
    isTransitioning.value = true;
    emit('answer', {
      questionId: currentQuestion.value.id,
      selectedAnswer: "CRASHED INTO CORRECT ANSWER",
      isCorrect: false,
    });
    questionIndex.value++;
    setTimeout(loadQuestion, 500);
  } else {
     emit('answer', {
      questionId: currentQuestion.value.id,
      selectedAnswer: target.text,
      isCorrect: false,
    });
  }
};

const checkTargetsOffscreen = (canvasHeight) => {
  targets.value.forEach(target => {
    if(target.active && target.y > canvasHeight + 100) {
      target.active = false;
      // If the correct target falls off screen, it's a miss
      if(target.isCorrect && !isTransitioning.value) {
        isTransitioning.value = true;
        handleWrongAnswer(1);
        playWrongSound();
        targets.value.forEach(t => t.active = false);
        questionIndex.value++;
        setTimeout(loadQuestion, 500);
      }
    }
  });
}

const update = (dt) => {
  const canvas = gameCanvas.value;
  if (!canvas) return;

  // Player smooth movement (Lerp)
  player.value.x += (player.value.targetX - player.value.x) * 0.15;
  player.value.y += (player.value.targetY - player.value.y) * 0.15;

  // Boundary checks
  player.value.x = Math.max(player.value.width/2, Math.min(canvas.width - player.value.width/2, player.value.x));
  player.value.y = Math.max(player.value.height/2, Math.min(canvas.height - player.value.height/2, player.value.y));
  
  // Ship banking visual calculations
  player.value.velocity.x = (player.value.targetX - player.value.x);
  
  // Engine thrust particles
  if (Math.random() > 0.3) {
    particles.value.push({
      x: player.value.x + (Math.random() - 0.5) * 10,
      y: player.value.y + player.value.height/2 - 10,
      vx: (Math.random() - 0.5) * 2,
      vy: Math.random() * 5 + 2,
      life: 1.0,
      decay: 0.05,
      color: combo.value >= 3 ? '#fcd34d' : '#818cf8',
      size: Math.random() * 4 + 2
    });
  }

  // Update Lasers
  for (let i = lasers.value.length - 1; i >= 0; i--) {
    const laser = lasers.value[i];
    laser.y -= laser.speed * (dt / 16);
    if (laser.y < -50 || !laser.active) lasers.value.splice(i, 1);
  }

  // Update Targets
  targets.value.forEach(target => {
    if (!target.active) return;
    target.y += target.speed * (dt / 16);
    target.rotation += target.rotationSpeed;
    if(target.scale < 1) target.scale += 0.05;
  });

  // Update Particles
  for (let i = particles.value.length - 1; i >= 0; i--) {
    const p = particles.value[i];
    p.x += p.vx;
    p.y += p.vy;
    p.life -= p.decay;
    if (p.life <= 0) particles.value.splice(i, 1);
  }

  // Update Stars (Parallax)
  stars.value.forEach(star => {
    star.y += star.speed * (dt / 16);
    if(star.y > canvas.height) {
      star.y = 0;
      star.x = Math.random() * canvas.width;
    }
  });

  checkCollisions();
  checkTargetsOffscreen(canvas.height);
};

const drawPlayer = (ctx) => {
  ctx.save();
  ctx.translate(player.value.x, player.value.y);
  
  // Ship banking effect
  const bankAngle = Math.max(-0.5, Math.min(0.5, player.value.velocity.x * 0.01));
  ctx.rotate(bankAngle);

  // Ship Body
  ctx.fillStyle = '#cbd5e1';
  ctx.beginPath();
  ctx.moveTo(0, -30); // Nose
  ctx.lineTo(20, 20); // Right wing
  ctx.lineTo(15, 30); // Right engine
  ctx.lineTo(-15, 30); // Left engine
  ctx.lineTo(-20, 20); // Left wing
  ctx.closePath();
  ctx.fill();
  
  // Ship Wings Highlights
  ctx.fillStyle = '#3b82f6';
  ctx.beginPath();
  ctx.moveTo(0, -10);
  ctx.lineTo(20, 20);
  ctx.lineTo(0, 15);
  ctx.fill();
  
  ctx.fillStyle = '#2563eb';
  ctx.beginPath();
  ctx.moveTo(0, -10);
  ctx.lineTo(-20, 20);
  ctx.lineTo(0, 15);
  ctx.fill();

  // Cockpit
  ctx.fillStyle = '#38bdf8';
  ctx.beginPath();
  ctx.ellipse(0, 0, 8, 15, 0, 0, Math.PI * 2);
  ctx.fill();
  
  // Cockpit shine
  ctx.fillStyle = 'white';
  ctx.beginPath();
  ctx.ellipse(-2, -5, 3, 6, Math.PI/4, 0, Math.PI * 2);
  ctx.fill();

  ctx.restore();
};

const drawTarget = (ctx, target) => {
  if (!target.active) return;
  
  ctx.save();
  ctx.translate(target.x, target.y);
  ctx.scale(target.scale, target.scale);
  ctx.rotate(target.rotation);

  // Draw Asteroid Base depending on type
  const gradient = ctx.createRadialGradient(-10, -10, 5, 0, 0, target.radius);
  if(target.type === 0) {
    gradient.addColorStop(0, '#9ca3af');
    gradient.addColorStop(1, '#4b5563');
  } else if (target.type === 1) {
     gradient.addColorStop(0, '#a78bfa');
     gradient.addColorStop(1, '#5b21b6');
  } else {
     gradient.addColorStop(0, '#fbbf24');
     gradient.addColorStop(1, '#b45309');
  }
  
  ctx.fillStyle = gradient;
  ctx.shadowColor = 'rgba(0,0,0,0.5)';
  ctx.shadowBlur = 10;
  
  // Wavy asteroid shape
  ctx.beginPath();
  for (let i = 0; i < Math.PI * 2; i += Math.PI / 4) {
    const r = target.radius - (Math.random() * 8); // slight irregularity
    const x = Math.cos(i) * r;
    const y = Math.sin(i) * r;
    if(i === 0) ctx.moveTo(x,y);
    else ctx.lineTo(x,y);
  }
  ctx.closePath();
  ctx.fill();
  
  // Craters
  ctx.fillStyle = 'rgba(0,0,0,0.3)';
  ctx.beginPath(); ctx.arc(-10, -10, 8, 0, Math.PI*2); ctx.fill();
  ctx.beginPath(); ctx.arc(15, 5, 12, 0, Math.PI*2); ctx.fill();
  ctx.beginPath(); ctx.arc(-5, 15, 6, 0, Math.PI*2); ctx.fill();
  
  ctx.restore();
  
  // Draw Text (Not rotated so it's readable)
  ctx.save();
  ctx.translate(target.x, target.y);
  ctx.scale(target.scale, target.scale);
  
  // Background pill for text
  ctx.font = '900 24px "Nunito", Arial, sans-serif';
  const textWidth = ctx.measureText(target.text).width;
  ctx.fillStyle = 'rgba(0,0,0,0.8)';
  ctx.beginPath();
  ctx.roundRect(-textWidth/2 - 15, -20, textWidth + 30, 40, 20);
  ctx.fill();
  
  ctx.fillStyle = 'white';
  ctx.textAlign = 'center';
  ctx.textBaseline = 'middle';
  ctx.fillText(target.text, 0, 0);
  ctx.restore();
};

const render = () => {
  const canvas = gameCanvas.value;
  if (!ctx || !canvas) return;
  
  // Clear with purely CSS background showing through
  ctx.clearRect(0, 0, canvas.width, canvas.height);
  
  // Render Parallax Stars
  stars.value.forEach(star => {
    ctx.fillStyle = star.color;
    ctx.beginPath();
    ctx.arc(star.x, star.y, star.size, 0, Math.PI * 2);
    ctx.fill();
  });

  // Render Lasers
  lasers.value.forEach(laser => {
    ctx.fillStyle = laser.color;
    ctx.shadowColor = laser.color;
    ctx.shadowBlur = 10;
    ctx.beginPath();
    ctx.roundRect(laser.x - laser.width/2, laser.y, laser.width, laser.height, 5);
    ctx.fill();
    ctx.shadowBlur = 0;
  });

  // Render Targets
  targets.value.forEach(target => drawTarget(ctx, target));

  // Render Player
  drawPlayer(ctx);

  // Render Particles
  particles.value.forEach(p => {
    ctx.globalAlpha = p.life;
    ctx.fillStyle = p.color;
    ctx.shadowColor = p.color;
    ctx.shadowBlur = 5;
    ctx.beginPath();
    ctx.arc(p.x, p.y, p.size, 0, Math.PI * 2);
    ctx.fill();
    ctx.shadowBlur = 0;
  });
  ctx.globalAlpha = 1.0;
};

const gameLoop = (timestamp) => {
  if (!isPlaying.value) return;
  
  const dt = timestamp - lastTime;
  lastTime = timestamp;
  
  update(dt);
  render();
  
  animationFrameId = requestAnimationFrame(gameLoop);
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
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Nunito:wght@600;800&display=swap');

.space-adventure-game {
  position: relative;
  width: 100%;
  height: calc(100vh - 250px);
  min-height: 800px;
  overflow: hidden;
  font-family: 'Nunito', sans-serif;
  border-radius: 20px;
  background-color: #020617;
  box-shadow: inset 0 0 50px rgba(0,0,0,0.8);
  cursor: crosshair;
}

.star-bg {
  position: absolute;
  top: 0; left: 0; right: 0; bottom: 0;
  background-image: 
    radial-gradient(2px 2px at 20px 30px, #eee, rgba(0,0,0,0)),
    radial-gradient(2px 2px at 40px 70px, #fff, rgba(0,0,0,0)),
    radial-gradient(2px 2px at 50px 160px, #ddd, rgba(0,0,0,0));
  background-size: 200px 200px;
  animation: scrollBg 20s linear infinite;
  opacity: 0.3;
}

@keyframes scrollBg {
  from { background-position: 0 0; }
  to { background-position: 0 200px; }
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
.life-heart.pulse { animation: heartPulse 1.5s infinite; filter: drop-shadow(0 0 5px #3b82f6);}
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

.question-banner-container {
  position: absolute;
  top: 80px;
  left: 0;
  right: 0;
  display: flex;
  justify-content: center;
  z-index: 10;
  pointer-events: none;
}

.question-banner {
  flex-direction: column;
  align-items: center;
  padding: 12px 30px;
  border-bottom: 3px solid #3b82f6;
  text-align: center;
}

.question-title {
  font-size: 0.8rem;
  color: #94a3b8;
  text-transform: uppercase;
  letter-spacing: 0.1em;
  font-weight: 800;
  margin-bottom: 4px;
}

.question-text {
  font-size: 2.5rem;
  font-weight: 900;
  color: #fff;
  text-shadow: 0 4px 8px rgba(0,0,0,0.9), 0 0 15px rgba(59,130,246,0.8);
  padding: 10px 0;
}

.fade-in { animation: fadeIn 0.5s ease-out forwards; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }

/* Screens */
.start-screen,
.game-over-screen {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  background: rgba(15, 23, 42, 0.9);
  backdrop-filter: blur(12px);
  padding: 3rem;
  border-radius: 30px;
  text-align: center;
  box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5), 0 0 0 2px rgba(255,255,255,0.1) inset;
  border: 4px solid #8b5cf6; /* Purple border */
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
  background: linear-gradient(to right, #c084fc 0%, #db2777 100%);
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
  background: #5b21b6;
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
  background: #8b5cf6;
  transform: translateY(-6px);
  will-change: transform;
  transition: transform 250ms;
}
.pushable:hover .front { transform: translateY(-8px); }
.pushable:active .front { transform: translateY(-2px); }

</style>
