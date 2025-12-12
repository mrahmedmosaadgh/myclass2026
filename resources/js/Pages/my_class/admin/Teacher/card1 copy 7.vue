<style  >
.book {
  position: relative;
  border-radius: 15px;
  width: 220px;
  height: 300px;
  background: rgba(255, 255, 255, 0.7);
  backdrop-filter: blur(10px);
  box-shadow:
    0 10px 30px rgba(0, 0, 0, 0.1),
    0 1px 8px rgba(0, 0, 0, 0.05);
  transform-style: preserve-3d;
  perspective: 2000px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #2d3748;
  transition: all 0.5s ease;
}

.book::before {
  content: '';
  position: absolute;
  left: 0;
  top: 0;
  width: 30px;
  height: 100%;
  background: linear-gradient(90deg,
    rgba(255, 255, 255, 0.2),
    rgba(255, 255, 255, 0.6)
  );
  transform: translateX(-20px) rotateY(-90deg);
  transform-origin: right;
}

.content {
  position: relative;
  width: 100%;
  height: 100%;
  padding: 1.5rem;
  background: rgba(255, 255, 255, 0.9);
  backdrop-filter: blur(10px);
  border-radius: 15px;
  transform-origin: left;
  transition: all 0.5s ease;
  overflow: hidden;
  border: 1px solid rgba(255, 255, 255, 0.3);
}

.cover {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  border-radius: 15px;
  cursor: pointer;
  transform-origin: left;
  transition: transform 0.7s cubic-bezier(0.645, 0.045, 0.355, 1);
  background: linear-gradient(
    145deg,
    rgba(99, 102, 241, 0.9),
    rgba(79, 70, 229, 0.9)
  );
  backdrop-filter: blur(10px);
  box-shadow:
    inset 0 0 50px rgba(255, 255, 255, 0.15),
    0 5px 15px rgba(79, 70, 229, 0.3),
    0 10px 30px rgba(79, 70, 229, 0.2);
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  border: 1px solid rgba(255, 255, 255, 0.2);
}

/* Spine effect with glass */
.cover::before {
  content: '';
  position: absolute;
  left: 0;
  top: 0;
  width: 30px;
  height: 100%;
  background: linear-gradient(90deg,
    rgba(255, 255, 255, 0.3),
    transparent 70%
  );
}

/* Glass texture */
.cover::after {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background:
    linear-gradient(
      45deg,
      rgba(255, 255, 255, 0.2) 0%,
      transparent 40%,
      transparent 60%,
      rgba(255, 255, 255, 0.2) 100%
    );
  border-radius: 15px;
  backdrop-filter: blur(5px);
}

.book:hover .cover {
  transform: rotateY(-140deg);
}

.book:hover {
  transform: translateX(40px);
  box-shadow:
    -10px 10px 40px rgba(79, 70, 229, 0.2),
    0 15px 35px rgba(0, 0, 0, 0.1);
}

/* Text styles */
p {
  font-size: 1.25rem;
  font-weight: 600;
  color: #ffffff;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
  letter-spacing: 0.025em;
  position: relative;
  z-index: 1;
  text-align: center;
  padding: 0 20px;
}

/* Content animation */
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateX(-20px);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}

.book:hover .content > * {
  animation: fadeIn 0.5s ease forwards;
  animation-delay: 0.3s;
}

/* Inner pages effect with glass */
.inner-pages {
  position: absolute;
  width: 100%;
  height: 100%;
  top: 0;
  left: 0;
  background: rgba(255, 255, 255, 0.95);
  transform-origin: left;
  transform: rotateY(0);
  transition: transform 0.5s ease;
  border-radius: 0 15px 15px 0;
  box-shadow:
    inset -10px 0 20px rgba(0, 0, 0, 0.1),
    1px 0 2px rgba(0, 0, 0, 0.05);
  backdrop-filter: blur(5px);
}

.book:hover .inner-pages {
  transform: rotateY(-30deg);
}

/* Optional: Add shimmer effect */
@keyframes shimmer {
  0% { background-position: -100% 0; }
  100% { background-position: 100% 0; }
}

.cover::before {
  background: linear-gradient(
    90deg,
    transparent,
    rgba(255, 255, 255, 0.2),
    transparent
  );
  background-size: 200% 100%;
  animation: shimmer 3s infinite;
}

/* Neon Cover Title Styles */
.cover-title {
  font-size: 2rem;
  font-weight: 700;
  color: #fff;
  text-align: center;
  padding: 0.75rem 1.5rem;
  position: relative;
  z-index: 3;
  text-transform: uppercase;
  letter-spacing: 2px;
  animation: neonPulse 1.5s infinite alternate;
}

/* Main neon glow effect */
.cover-title {
  text-shadow:
    0 0 7px #fff,
    0 0 10px #fff,
    0 0 21px #fff,
    0 0 42px #5271ff,
    0 0 82px #5271ff,
    0 0 92px #5271ff,
    0 0 102px #5271ff,
    0 0 151px #5271ff;
}

/* Neon container */
.cover-content {
  position: relative;
  z-index: 2;
  padding: 1.5rem;
  background: rgba(0, 0, 0, 0.3);
  border-radius: 16px;
  box-shadow:
    0 0 10px rgba(82, 113, 255, 0.3),
    0 0 20px rgba(82, 113, 255, 0.2),
    0 0 30px rgba(82, 113, 255, 0.1),
    inset 0 0 30px rgba(82, 113, 255, 0.2);
  backdrop-filter: blur(8px);
  border: 2px solid rgba(255, 255, 255, 0.1);
  width: 90%;
  max-width: 320px;
}

/* Subtitle with neon */
.cover-subtitle {
  font-size: 1.1rem;
  color: #fff;
  text-align: center;
  margin-top: 0.75rem;
  font-weight: 500;
  letter-spacing: 1px;
  text-shadow:
    0 0 4px #fff,
    0 0 8px #fff,
    0 0 12px #5271ff,
    0 0 16px #5271ff;
  opacity: 0.9;
}

/* Neon pulse animation */
@keyframes neonPulse {
  0%, 100% {
    text-shadow:
      0 0 7px #fff,
      0 0 10px #fff,
      0 0 21px #fff,
      0 0 42px #5271ff,
      0 0 82px #5271ff,
      0 0 92px #5271ff,
      0 0 102px #5271ff,
      0 0 151px #5271ff;
  }
  50% {
    text-shadow:
      0 0 4px #fff,
      0 0 7px #fff,
      0 0 18px #fff,
      0 0 38px #5271ff,
      0 0 73px #5271ff,
      0 0 80px #5271ff,
      0 0 94px #5271ff,
      0 0 140px #5271ff;
  }
}

/* Neon border effect */
.cover-content::before {
  content: '';
  position: absolute;
  top: -2px;
  left: -2px;
  right: -2px;
  bottom: -2px;
  border-radius: 16px;
  background: linear-gradient(45deg,
    #5271ff, #fff, #5271ff,
    #fff, #5271ff, #fff);
  background-size: 400%;
  z-index: -1;
  animation: neonBorder 20s linear infinite;
  filter: blur(10px);
  opacity: 0.5;
}

@keyframes neonBorder {
  0% {
    background-position: 0 0;
  }
  50% {
    background-position: 400% 0;
  }
  100% {
    background-position: 0 0;
  }
}

/* Enhanced hover effects */
.book:hover .cover-title {
  animation: neonPulse 0.75s infinite alternate;
}

.book:hover .cover-content::before {
  opacity: 0.8;
  filter: blur(15px);
}

/* Flicker effect */
@keyframes neonFlicker {
  0%, 19%, 21%, 23%, 25%, 54%, 56%, 100% {
    text-shadow:
      0 0 7px #fff,
      0 0 10px #fff,
      0 0 21px #fff,
      0 0 42px #5271ff,
      0 0 82px #5271ff,
      0 0 92px #5271ff,
      0 0 102px #5271ff,
      0 0 151px #5271ff;
  }
  20%, 24%, 55% {
    text-shadow: none;
  }
}

/* Add occasional flicker effect */
.cover-title:hover {
  animation: neonFlicker 2s infinite alternate;
}

/* Additional clarity enhancement */
.cover::before {
  content: '';
  position: absolute;
  inset: 0;
  background: radial-gradient(
    circle at center,
    rgba(82, 113, 255, 0.2),
    transparent 70%
  );
  z-index: 1;
}
</style>

<script setup>
defineProps({
  coverText: {
    type: String,
    default: 'Hover Me'
  }
});
</script>

<template>
  <div class="book">
    <div class="inner-pages"></div>
    <div class="content">
      <slot></slot>
      <slot name="extra"></slot>
    </div>
    <div class="cover">
      <slot name="cover">
        <div class="cover-content">
          <h2 class="cover-title">{{ coverText }}</h2>
          <div class="cover-subtitle">
            <slot name="subtitle-text"></slot>
          </div>
        </div>
      </slot>
    </div>
  </div>
</template>




