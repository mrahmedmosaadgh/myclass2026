import { ref, reactive, onMounted, onUnmounted } from 'vue';

export function useGameEngine() {
  // Game State
  const isPlaying = ref(false);
  const isPaused = ref(false);
  const score = ref(0);
  const lives = ref(3);
  const combo = ref(0);
  const maxCombo = ref(0);
  const level = ref(1);
  const questionTimeLeft = ref(30);
  
  // Power-ups
  const activePowerUps = ref({
    doublePoints: false,
    timeFreeze: false,
    slowMotion: false,
    extraTime: false,
  });
  
  // Game objects
  const gameObjects = reactive([]);
  const particles = reactive([]);
  const powerUps = reactive([]);
  
  // Configuration
  const config = ref({
    baseTimePerQuestion: 30,
    pointsPerCorrectAnswer: 10,
    pointsPerTimeBonus: 5,
    livesLostPerWrongAnswer: 1,
    powerUpSpawnRate: 0.3,
    difficulty: 'medium', // easy, medium, hard
  });
  
  // Physics constants
  const GRAVITY = 0.3;
  const FRICTION = 0.98;
  const ELASTICITY = 0.7;
  
  // Timer
  let gameTimer = null;
  let animationFrameId = null;
  
  const startGame = (initialConfig = {}) => {
    config.value = { ...config.value, ...initialConfig };
    isPlaying.value = true;
    isPaused.value = false;
    score.value = 0;
    lives.value = initialConfig.lives || 3;
    combo.value = 0;
    maxCombo.value = 0;
    level.value = 1;
    
    startTimer();
    gameLoop();
  };
  
  const pauseGame = () => {
    isPaused.value = true;
    if (gameTimer) clearInterval(gameTimer);
    if (animationFrameId) cancelAnimationFrame(animationFrameId);
  };
  
  const resumeGame = () => {
    isPaused.value = false;
    startTimer();
    gameLoop();
  };
  
  const stopGame = () => {
    isPlaying.value = false;
    isPaused.value = false;
    if (gameTimer) clearInterval(gameTimer);
    if (animationFrameId) cancelAnimationFrame(animationFrameId);
  };
  
  const startTimer = () => {
    if (gameTimer) clearInterval(gameTimer);
    
    gameTimer = setInterval(() => {
      if (!isPaused.value && isPlaying.value) {
        if (!activePowerUps.value.timeFreeze) {
          questionTimeLeft.value -= 1;
          
          if (questionTimeLeft.value <= 0) {
            handleTimeOut();
          }
        }
      }
    }, 1000);
  };
  
  const handleTimeOut = () => {
    lives.value -= config.value.livesLostPerWrongAnswer;
    combo.value = 0;
    
    if (lives.value <= 0) {
      endGame();
    } else {
      nextQuestion();
    }
  };
  
  const handleCorrectAnswer = (basePoints = 10) => {
    const earnedPoints = addPoints(basePoints);
    
    // Time bonus
    if (questionTimeLeft.value > config.value.baseTimePerQuestion * 0.5) {
      addPoints(5);
    }
    
    // Combo bonus
    if (combo.value >= 3) {
      addPoints(combo.value * 2);
    }
    
    // Spawn power-up chance
    if (Math.random() < config.value.powerUpSpawnRate) {
      spawnPowerUp();
    }
    
    nextQuestion(); // Reset timer for next question
    return earnedPoints;
  };
  
  const handleWrongAnswer = (livesLost = 1) => {
    lives.value -= livesLost;
    combo.value = 0;
    
    if (lives.value <= 0) {
      endGame();
    }
  };
  
  const addPoints = (points) => {
    let earnedPoints = points;
    
    if (activePowerUps.value.doublePoints) {
      earnedPoints *= 2;
    }
    
    score.value += earnedPoints;
    return earnedPoints;
  };
  
  const spawnPowerUp = () => {
    const types = ['doublePoints', 'timeFreeze', 'slowMotion', 'extraTime'];
    const randomType = types[Math.floor(Math.random() * types.length)];
    
    const powerUp = {
      id: Date.now(),
      type: randomType,
      x: Math.random() * 800,
      y: -50,
      width: 40,
      height: 40,
      vy: 2,
    };
    
    powerUps.push(powerUp);
  };
  
  const collectPowerUp = (powerUp) => {
    const index = powerUps.findIndex(p => p.id === powerUp.id);
    if (index !== -1) {
      powerUps.splice(index, 1);
    }
    
    activatePowerUp(powerUp.type);
  };
  
  const activatePowerUp = (type) => {
    activePowerUps.value[type] = true;
    
    const duration = type === 'doublePoints' ? 10000 :
                     type === 'timeFreeze' ? 5000 :
                     type === 'slowMotion' ? 8000 : 3000;
    
    setTimeout(() => {
      activePowerUps.value[type] = false;
    }, duration);
  };
  
  const updatePhysics = (obj) => {
    // Apply gravity
    if (obj.vy !== undefined) {
      obj.vy += GRAVITY;
    }
    
    // Apply friction
    if (obj.vx !== undefined) {
      obj.vx *= FRICTION;
    }
    
    // Update position
    if (obj.x !== undefined && obj.vx !== undefined) {
      obj.x += obj.vx;
    }
    if (obj.y !== undefined && obj.vy !== undefined) {
      obj.y += obj.vy;
    }
    
    // Boundary collision
    if (obj.x < 0) {
      obj.x = 0;
      obj.vx = -obj.vx * ELASTICITY;
    }
    if (obj.x > 800) {
      obj.x = 800;
      obj.vx = -obj.vx * ELASTICITY;
    }
    if (obj.y < 0) {
      obj.y = 0;
      obj.vy = -obj.vy * ELASTICITY;
    }
    if (obj.y > 600) {
      obj.y = 600;
      obj.vy = -obj.vy * ELASTICITY;
    }
    
    return obj;
  };
  
  const checkCollision = (obj1, obj2, radius1 = 50, radius2 = 50) => {
    const dx = obj1.x - obj2.x;
    const dy = obj1.y - obj2.y;
    const distance = Math.sqrt(dx * dx + dy * dy);
    
    return distance < (radius1 + radius2);
  };
  
  const createParticles = (x, y, count = 10, color = '#fff') => {
    for (let i = 0; i < count; i++) {
      particles.push({
        id: Date.now() + i,
        x,
        y,
        vx: (Math.random() - 0.5) * 10,
        vy: (Math.random() - 0.5) * 10,
        life: 1,
        decay: 0.02 + Math.random() * 0.03,
        color,
        size: 2 + Math.random() * 4,
      });
    }
  };
  
  const updateParticles = () => {
    for (let i = particles.length - 1; i >= 0; i--) {
      const p = particles[i];
      p.x += p.vx;
      p.y += p.vy;
      p.vy += 0.1; // gravity
      p.life -= p.decay;
      
      if (p.life <= 0) {
        particles.splice(i, 1);
      }
    }
  };
  
  const gameLoop = () => {
    if (!isPlaying.value || isPaused.value) return;
    
    // Update game objects
    updatePhysicsObjects();
    updateParticles();
    
    // Continue loop
    animationFrameId = requestAnimationFrame(gameLoop);
  };
  
  const updatePhysicsObjects = () => {
    // Update power-ups
    for (let i = powerUps.length - 1; i >= 0; i--) {
      const p = powerUps[i];
      p.y += p.vy;
      
      if (p.y > 600) {
        powerUps.splice(i, 1);
      }
    }
  };
  
  const nextQuestion = () => {
    questionTimeLeft.value = config.value.baseTimePerQuestion;
  };
  
  const endGame = () => {
    stopGame();
    // Emit game over event
    window.dispatchEvent(new CustomEvent('game-over', {
      detail: {
        score: score.value,
        maxCombo: maxCombo.value,
        level: level.value,
      }
    }));
  };
  
  const getGameState = () => ({
    isPlaying: isPlaying.value,
    isPaused: isPaused.value,
    score: score.value,
    lives: lives.value,
    combo: combo.value,
    maxCombo: maxCombo.value,
    level: level.value,
    questionTimeLeft: questionTimeLeft.value,
    activePowerUps: activePowerUps.value,
  });
  
  onUnmounted(() => {
    stopGame();
  });
  
  return {
    // State
    isPlaying,
    isPaused,
    score,
    lives,
    combo,
    maxCombo,
    level,
    questionTimeLeft,
    activePowerUps,
    gameObjects,
    particles,
    powerUps,
    config,
    
    // Methods
    startGame,
    pauseGame,
    resumeGame,
    stopGame,
    handleCorrectAnswer,
    handleWrongAnswer,
    addPoints,
    spawnPowerUp,
    collectPowerUp,
    checkCollision,
    createParticles,
    getGameState,
    nextQuestion,
  };
}
