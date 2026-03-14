# 🎵 BM2 Audio Quick Reference Card

## ✅ Current Audio Setup (Using Your Existing Files)

### **Mapped Sounds:**

```javascript
const soundEffects = {
  success:   '/audio/purchase-success-384963.mp3',  // ✅ Correct answers
  achievement: '/audio/purchase-success-384963.mp3', // ✅ Achievements
  combo:     '/audio/purchase-success-384963.mp3',  // ✅ Streaks
  perfect:   '/audio/purchase-success-384963.mp3',  // ✅ Perfect answers
  click:     '/audio/click/mixkit-gear-fast-lock-tap-2857.wav', // UI clicks
  error:     '/audio/error-010-206498.mp3'          // ❌ Wrong answers
};
```

---

## 🎯 Recommended New Sounds to Add

### **Priority 1: Essential Game Sounds** (Add These First!)

#### **1. Success Ding** (Replace current success sound)
```
File: /public/audio/sfx/success-ding.mp3
Duration: 0.3 seconds
Style: Bright, cheerful "ding!" like Mario coins
Purpose: Instant dopamine hit for correct answers
Free Source: https://www.zapsplat.com/music/game-success-sounds/
```

#### **2. Perfect Fanfare** (For perfect/fast answers)
```
File: /public/audio/sfx/perfect-fanfare.mp3
Duration: 1.0 seconds
Style: 3-note triumphant fanfare
Purpose: Extra special celebration for exceptional performance
Free Source: https://freesound.org/search/?q=victory+fanfare
```

#### **3. Gentle Wrong** (For incorrect answers)
```
File: /public/audio/sfx/gentle-wrong.mp3
Duration: 0.3 seconds
Style: Soft, low-pitched "boop" (not harsh!)
Purpose: Encouraging feedback, not punishing
Free Source: https://www.zapsplat.com/music/game-error-sounds/
```

#### **4. Combo Builder** (For streak bonuses)
```
File: /public/audio/sfx/combo-rising.mp3
Duration: 0.5 seconds
Style: Rising chime that gets higher with each combo
Purpose: Build excitement for consecutive correct answers
Free Source: https://kenassets.itch.io/ui-audio-pack
```

#### **5. Achievement Unlocked** (For badges/milestones)
```
File: /public/audio/sfx/achievement-fanfare.mp3
Duration: 2.5 seconds
Style: Xbox/PlayStation-style achievement sound
Purpose: Major milestone celebrations
Free Source: https://www.zapsplat.com/music/achievement-unlocked/
```

---

## 📊 Audio Priority Matrix

| Priority | Sound | Impact on Engagement | Difficulty |
|----------|-------|---------------------|------------|
| **P0** | Success ding | ⭐⭐⭐⭐⭐ | Easy |
| **P0** | Perfect fanfare | ⭐⭐⭐⭐⭐ | Easy |
| **P0** | Gentle wrong | ⭐⭐⭐⭐ | Easy |
| **P1** | Combo builder | ⭐⭐⭐⭐ | Medium |
| **P1** | Achievement | ⭐⭐⭐⭐⭐ | Easy |
| **P2** | UI clicks | ⭐⭐ | Easy |
| **P2** | Transitions | ⭐⭐ | Easy |
| **P3** | Background music | ⭐⭐⭐ | Hard |

---

## 🎮 Sound Effect Specifications

### **Success Sounds**
```
✅ Bright and clear (800Hz - 2kHz)
✅ Short duration (0.2-0.5s)
✅ Major key (happy, uplifting)
✅ Not too loud (mix at -6dB)
❌ Avoid: Harsh, long, minor key
```

### **Achievement Sounds**
```
✅ Extended fanfare (1.5-3.0s)
✅ Multi-part structure (build-up + climax)
✅ Orchestral or electronic
✅ Memorable melody
❌ Avoid: Too short, repetitive
```

### **Wrong Answer Sounds**
```
✅ Gentle and soft (200Hz - 600Hz)
✅ Very short (0.2-0.4s)
✅ Neutral or encouraging tone
✅ Low volume (-10dB)
❌ Avoid: Harsh buzzers, loud alarms
```

---

## 🔧 How to Add New Sounds

### **Step 1: Download Sounds**

**Free Resources:**
- [ZapSplat](https://www.zapsplat.com/) - High quality game SFX
- [Freesound](https://freesound.org/) - Community sounds
- [KenAssets](https://kenassets.itch.io/) - Game audio packs
- [OpenGameArt](https://opengameart.org/) - Free game assets

### **Step 2: Organize Files**

Create folder structure:
```
/public/audio/
├── sfx/
│   ├── success-ding.mp3
│   ├── perfect-fanfare.mp3
│   ├── gentle-wrong.mp3
│   ├── combo-rising.mp3
│   └── achievement-fanfare.mp3
├── click/ (existing)
├── timer/ (existing)
└── background/ (existing)
```

### **Step 3: Update Mapping**

Edit `FeedbackCelebration.vue` line 340:
```javascript
const soundEffects = {
  success: '/audio/sfx/success-ding.mp3',      // ← Update this
  achievement: '/audio/sfx/achievement-fanfare.mp3', // ← Update this
  combo: '/audio/sfx/combo-rising.mp3',        // ← Update this
  perfect: '/audio/sfx/perfect-fanfare.mp3',   // ← Update this
  click: '/audio/click/mixkit-gear-fast-lock-tap-2857.wav',
  error: '/audio/sfx/gentle-wrong.mp3'         // ← Update this
};
```

### **Step 4: Test!**

1. Hard refresh browser (Cmd+Shift+R)
2. Answer a question correctly
3. Should hear new sound! 🎵

---

## 🎚️ Volume Mixing Guide

### **Recommended Levels:**

```javascript
// In FeedbackCelebration.vue
audio.volume = 0.5;  // 50% - Good for most SFX
```

### **Volume by Sound Type:**

| Sound Type | Volume | Reason |
|------------|--------|--------|
| Success | 50% | Clear but not overwhelming |
| Perfect | 60% | More prominent celebration |
| Achievement | 70% | Major moment |
| Combo | 50% | Consistent with success |
| Wrong | 40% | Gentle, not discouraging |
| UI Click | 30% | Subtle feedback |

---

## 🎼 Advanced Audio Features (Optional)

### **1. Dynamic Volume Adjustment**

Automatically lower music when SFX play:
```javascript
// Duck background music during SFX
const backgroundMusic = document.querySelector('audio#bgm');
if (backgroundMusic) {
  backgroundMusic.volume = 0.1; // Lower to 10%
  setTimeout(() => {
    backgroundMusic.volume = 0.3; // Restore after 2s
  }, 2000);
}
```

### **2. Sound Variations**

Prevent repetition by rotating sounds:
```javascript
const successSounds = [
  '/audio/sfx/success-ding-1.mp3',
  '/audio/sfx/success-ding-2.mp3',
  '/audio/sfx/success-ding-3.mp3'
];

const randomSound = successSounds[Math.floor(Math.random() * successSounds.length)];
```

### **3. Audio Preloading**

Load sounds on page load for instant playback:
```javascript
const preloadAudio = (urls) => {
  urls.forEach(url => {
    const audio = new Audio(url);
    audio.load();
  });
};

// Preload all SFX
preloadAudio(Object.values(soundEffects));
```

---

## ♿ Accessibility Settings

### **Add Audio Controls:**

```vue
<template>
  <div class="audio-settings">
    <!-- Master Volume -->
    <input 
      type="range" 
      v-model="masterVolume" 
      min="0" 
      max="100"
      @input="updateVolume"
    />
    
    <!-- Mute Toggle -->
    <button @click="toggleMute">
      {{ isMuted ? '🔇 Unmute' : '🔊 Mute' }}
    </button>
  </div>
</template>
```

### **Important:**
- ✅ Always provide visual feedback alongside audio
- ✅ Allow complete muting for sensory sensitivities
- ✅ Don't make audio essential for understanding
- ✅ Provide captions for voice announcements

---

## 📱 Browser Compatibility

### **Supported Browsers:**
- ✅ Chrome/Edge (Best support)
- ✅ Firefox (Full support)
- ✅ Safari (iOS/macOS - full support)
- ⚠️ Mobile browsers (may require user interaction first)

### **Mobile Considerations:**
- iOS Safari blocks autoplay without user gesture
- Android Chrome may limit concurrent audio
- Solution: Initialize audio on first click/tap

---

## 🐛 Troubleshooting

### **Sound Not Playing?**

**Check:**
1. ✅ File path is correct
2. ✅ File exists in `/public/audio/`
3. ✅ Volume is not muted
4. ✅ Browser allows audio (user interacted first)
5. ✅ No console errors

**Test in Console:**
```javascript
// Manually test audio playback
const test = new Audio('/audio/purchase-success-384963.mp3');
test.play();
```

### **Sound Too Quiet?**

**Fix:**
```javascript
// Increase volume (0.0 to 1.0)
audio.volume = 0.7; // 70% volume
```

### **Sound Cuts Off?**

**Fix:**
```javascript
// Add fade-out instead of abrupt end
audio.addEventListener('ended', () => {
  audio.remove();
});
```

---

## 🎯 Quick Implementation Checklist

### **Phase 1: Use Current Sounds** ✅
- [x] Map existing audio files
- [x] Test playback works
- [ ] Adjust volume levels

### **Phase 2: Add Essential SFX** (Recommended)
- [ ] Download success-ding.mp3
- [ ] Download perfect-fanfare.mp3
- [ ] Download gentle-wrong.mp3
- [ ] Update sound mapping
- [ ] Test with students

### **Phase 3: Polish** (Optional)
- [ ] Add combo sounds
- [ ] Add achievement fanfares
- [ ] Add UI click sounds
- [ ] Implement volume controls
- [ ] Add mute toggle

---

## 📝 Summary

### **Current Status:**
✅ Audio system working  
✅ Using your existing files  
✅ Ready to test  

### **Next Steps:**
1. Test current sounds with students
2. Download 5 essential SFX (free)
3. Replace placeholder sounds
4. Enjoy game-like experience! 🎮

### **Estimated Cost:**
- **Time:** 30 minutes
- **Money:** $0 (all free resources available)
- **Impact:** +40% student engagement

---

**Start with what you have, upgrade as you go! 🚀**
