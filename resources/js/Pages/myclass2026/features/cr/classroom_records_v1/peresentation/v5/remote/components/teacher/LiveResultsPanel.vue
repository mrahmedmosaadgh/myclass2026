<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import { useGameStore } from '../../../stores/gameStore';
import { useRealtimeChannel } from '@/composables/useRealtimeChannel';
import axios from 'axios';

const gameStore = useGameStore();
let statsInterval = null;
let lastFetchTime = 0;
const FETCH_THROTTLE_MS = 1500;

// Live response feed (recent submissions shown as cards)
const recentFeed = ref([]);
const MAX_FEED_ITEMS = 8;

const results = ref({
  totalResponses: 0,
  type: null,
  options: [],
  responses: [],
});

const totalParticipants = computed(() => gameStore.participants?.length || 0);
const responseRate = computed(() => {
  if (!totalParticipants.value) return 0;
  return Math.round((results.value.totalResponses / totalParticipants.value) * 100);
});

const isQuizActive = computed(() => gameStore.sessionStatus === 'active' && gameStore.sessionId);

const topOption = computed(() => {
  if (!results.value.options?.length) return -1;
  let max = -1, idx = -1;
  results.value.options.forEach((o, i) => { if (o.count > max) { max = o.count; idx = i; } });
  return idx;
});

const fetchStats = async (force = false) => {
  if (!isQuizActive.value) return;
  const now = Date.now();
  if (!force && now - lastFetchTime < FETCH_THROTTLE_MS) return;
  lastFetchTime = now;
  try {
    const { data } = await axios.get(`/api/cr/sessions/${gameStore.sessionId}/stats`);
    results.value.totalResponses = data.total;
    results.value.type = data.type;
    if (data.type === 'short_answer') {
      results.value.responses = data.responses || [];
    } else {
      results.value.options = (data.stats || []).map(s => ({
        text: s.text,
        count: s.count,
        percentage: data.total > 0 ? (s.count / data.total) * 100 : 0,
      }));
    }
  } catch (err) {
    console.error('Failed to fetch stats:', err);
  }
};

const markCorrect = async (resp) => {
  if (resp.is_correct) return;
  try {
    await axios.post(`/api/cr/sessions/${gameStore.sessionId}/mark-answer`, {
      nickname: resp.guest_nickname,
      student_id: resp.student_id,
      question_id: results.value.current_question_id,
    });
    resp.is_correct = true;
  } catch (err) {
    console.error('Failed to mark answer:', err);
  }
};

// Real-time listener — pushes new entries into live feed
const teacherChannel = computed(() =>
  gameStore.accessCode ? `quiz_${gameStore.accessCode}_teacher` : null
);

useRealtimeChannel(teacherChannel, (signal) => {
  if (signal.event === 'ANSWER_SUBMITTED') {
    // Add to live feed
    recentFeed.value.unshift({
      id: signal.trigger_id,
      name: signal.context?.student_name || 'Student',
      time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' }),
    });
    if (recentFeed.value.length > MAX_FEED_ITEMS) recentFeed.value.pop();

    // Refresh stats (throttled)
    fetchStats();
  }
});

watch(isQuizActive, (active) => {
  if (active) {
    statsInterval = setInterval(() => fetchStats(false), 8000);
    fetchStats(true);
    recentFeed.value = [];
  } else {
    clearInterval(statsInterval);
  }
}, { immediate: true });

onUnmounted(() => clearInterval(statsInterval));

// Avatar initials + color
const avatarColor = (name) => {
  const colors = ['#6366f1','#8b5cf6','#ec4899','#f59e0b','#10b981','#3b82f6','#ef4444'];
  let hash = 0;
  for (const c of (name || '?')) hash = c.charCodeAt(0) + hash * 31;
  return colors[Math.abs(hash) % colors.length];
};
const initials = (name) => (name || '?').slice(0, 2).toUpperCase();
const optionLabel = (idx) => String.fromCharCode(65 + idx);
</script>

<template>
  <div class="lrp">

    <!-- Empty state -->
    <div v-if="!isQuizActive" class="lrp-empty">
      <div class="lrp-empty-icon">📊</div>
      <p>Launch a quiz to see live responses here</p>
    </div>

    <template v-else>

      <!-- ── Top stat bar ── -->
      <div class="lrp-topbar">

        <!-- Response rate ring -->
        <div class="rate-ring-wrap">
          <svg class="rate-ring" viewBox="0 0 36 36">
            <circle class="ring-bg" cx="18" cy="18" r="15.9" />
            <circle
              class="ring-fill"
              cx="18" cy="18" r="15.9"
              :stroke-dasharray="`${responseRate} ${100 - responseRate}`"
              stroke-dashoffset="25"
            />
          </svg>
          <span class="rate-label">{{ responseRate }}%</span>
        </div>

        <div class="lrp-counts">
          <div class="stat-pill total">
            <span class="sp-val">{{ results.totalResponses }}</span>
            <span class="sp-lbl">Responded</span>
          </div>
          <div class="stat-pill waiting">
            <span class="sp-val">{{ Math.max(0, totalParticipants - results.totalResponses) }}</span>
            <span class="sp-lbl">Waiting</span>
          </div>
        </div>

        <div class="lrp-live-dot">
          <span class="dot-pulse"></span> LIVE
        </div>
      </div>

      <!-- ── MCQ Bar Chart ── -->
      <div v-if="results.type === 'multiple_choice'" class="lrp-bars">
        <div
          v-for="(opt, idx) in results.options"
          :key="idx"
          class="bar-row"
          :class="{ winner: idx === topOption && opt.count > 0 }"
        >
          <div class="bar-letter">{{ optionLabel(idx) }}</div>
          <div class="bar-track">
            <div
              class="bar-fill"
              :style="{ width: opt.percentage + '%' }"
              :class="{ 'bar-winner': idx === topOption && opt.count > 0 }"
            ></div>
          </div>
          <div class="bar-meta">
            <span class="bar-pct">{{ Math.round(opt.percentage) }}%</span>
            <span class="bar-cnt">{{ opt.count }}</span>
          </div>
        </div>
      </div>

      <!-- ── Short Answer List ── -->
      <div v-else-if="results.type === 'short_answer'" class="lrp-answers">
        <div v-if="!results.responses?.length" class="sa-empty">
          Waiting for student answers...
        </div>
        <div v-for="(resp, idx) in results.responses" :key="idx" class="sa-card" :class="{ correct: resp.is_correct }">
          <div class="sa-avatar" :style="{ background: avatarColor(resp.display_name) }">
            {{ initials(resp.display_name) }}
          </div>
          <div class="sa-body">
            <span class="sa-name">{{ resp.display_name }}</span>
            <p class="sa-text">"{{ resp.answer }}"</p>
          </div>
          <button
            class="sa-mark"
            :class="{ marked: resp.is_correct }"
            @click="markCorrect(resp)"
            :disabled="resp.is_correct"
          >
            {{ resp.is_correct ? '✅' : '👍' }}
          </button>
        </div>
      </div>

      <!-- ── Live Response Feed ── -->
      <div class="lrp-feed" v-if="recentFeed.length">
        <div class="feed-header">⚡ Live Feed</div>
        <TransitionGroup name="feed" tag="div" class="feed-list">
          <div v-for="item in recentFeed" :key="item.id" class="feed-item">
            <div class="feed-avatar" :style="{ background: avatarColor(item.name) }">
              {{ initials(item.name) }}
            </div>
            <span class="feed-name">{{ item.name }}</span>
            <span class="feed-time">{{ item.time }}</span>
          </div>
        </TransitionGroup>
      </div>

    </template>
  </div>
</template>

<style scoped>
/* ─── Container ─── */
.lrp {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
  padding: 1.25rem;
  background: linear-gradient(135deg, #f8fafc 0%, #f0f4ff 100%);
  border-radius: 1rem;
  border: 1px solid #e2e8f0;
  min-height: 200px;
}

/* ─── Empty ─── */
.lrp-empty {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: .75rem;
  padding: 2.5rem 0;
  color: #94a3b8;
  text-align: center;
}
.lrp-empty-icon { font-size: 2.5rem; }
.lrp-empty p { font-size: .9rem; margin: 0; }

/* ─── Top bar ─── */
.lrp-topbar {
  display: flex;
  align-items: center;
  gap: 1rem;
}

/* Rate ring */
.rate-ring-wrap {
  position: relative;
  width: 52px;
  height: 52px;
  flex-shrink: 0;
}
.rate-ring { transform: rotate(-90deg); width: 52px; height: 52px; }
.ring-bg { fill: none; stroke: #e2e8f0; stroke-width: 3.5; }
.ring-fill {
  fill: none;
  stroke: #6366f1;
  stroke-width: 3.5;
  stroke-linecap: round;
  transition: stroke-dasharray .6s ease;
}
.rate-label {
  position: absolute;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: .75rem;
  font-weight: 800;
  color: #4338ca;
}

/* Stat pills */
.lrp-counts { display: flex; gap: .5rem; flex: 1; }
.stat-pill {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: .4rem .6rem;
  border-radius: .6rem;
}
.stat-pill.total { background: #e0e7ff; }
.stat-pill.waiting { background: #fef9c3; }
.sp-val { font-size: 1.2rem; font-weight: 800; color: #1e293b; }
.sp-lbl { font-size: .65rem; font-weight: 700; color: #64748b; text-transform: uppercase; }

/* Live dot */
.lrp-live-dot {
  display: flex;
  align-items: center;
  gap: 5px;
  font-size: .65rem;
  font-weight: 800;
  color: #ef4444;
  white-space: nowrap;
}
.dot-pulse {
  width: 8px; height: 8px;
  background: #ef4444;
  border-radius: 50%;
  animation: pulse-anim 1.4s infinite;
}
@keyframes pulse-anim {
  0%, 100% { transform: scale(0.9); opacity: .7; }
  50%       { transform: scale(1.3); opacity: 1; }
}

/* ─── MCQ Bars ─── */
.lrp-bars { display: flex; flex-direction: column; gap: .7rem; }

.bar-row {
  display: flex;
  align-items: center;
  gap: .6rem;
  padding: .45rem .6rem;
  border-radius: .5rem;
  background: white;
  border: 1.5px solid #e2e8f0;
  transition: border-color .3s;
}
.bar-row.winner { border-color: #6366f1; background: #f5f3ff; }

.bar-letter {
  width: 22px; height: 22px;
  border-radius: 50%;
  background: #f1f5f9;
  display: flex; align-items: center; justify-content: center;
  font-size: .75rem; font-weight: 800; color: #475569;
  flex-shrink: 0;
}
.bar-row.winner .bar-letter { background: #6366f1; color: white; }

.bar-track {
  flex: 1;
  height: 12px;
  background: #f1f5f9;
  border-radius: 6px;
  overflow: hidden;
}
.bar-fill {
  height: 100%;
  background: #a5b4fc;
  border-radius: 6px;
  transition: width .6s cubic-bezier(.2, .8, .4, 1);
}
.bar-fill.bar-winner { background: #6366f1; }

.bar-meta { display: flex; flex-direction: column; align-items: flex-end; min-width: 34px; }
.bar-pct { font-size: .7rem; font-weight: 800; color: #4338ca; }
.bar-cnt { font-size: .65rem; color: #94a3b8; }

/* ─── Short Answer ─── */
.lrp-answers {
  display: flex;
  flex-direction: column;
  gap: .6rem;
  max-height: 320px;
  overflow-y: auto;
  padding-right: 2px;
}
.sa-empty { text-align: center; color: #94a3b8; font-size: .85rem; padding: 1.5rem 0; }
.sa-card {
  display: flex;
  align-items: center;
  gap: .75rem;
  background: white;
  border: 1.5px solid #e2e8f0;
  border-radius: .75rem;
  padding: .65rem .9rem;
  transition: border-color .3s;
}
.sa-card.correct { border-color: #10b981; background: #f0fdf4; }

.sa-avatar {
  width: 32px; height: 32px;
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-size: .7rem; font-weight: 800;
  color: white;
  flex-shrink: 0;
}
.sa-body { flex: 1; min-width: 0; }
.sa-name { font-size: .75rem; font-weight: 700; color: #64748b; display: block; }
.sa-text { margin: 2px 0 0; font-size: .88rem; color: #1e293b; font-style: italic; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

.sa-mark {
  background: none;
  border: 1.5px solid #e2e8f0;
  border-radius: 6px;
  padding: 3px 8px;
  font-size: 1rem;
  cursor: pointer;
  transition: all .2s;
  flex-shrink: 0;
}
.sa-mark:hover:not(:disabled) { border-color: #10b981; transform: scale(1.15); }
.sa-mark.marked { border-color: #10b981; }
.sa-mark:disabled { cursor: default; }

/* ─── Live Feed ─── */
.lrp-feed {
  border-top: 1px solid #e2e8f0;
  padding-top: .75rem;
}
.feed-header {
  font-size: .7rem;
  font-weight: 800;
  text-transform: uppercase;
  color: #6366f1;
  letter-spacing: .05em;
  margin-bottom: .5rem;
}
.feed-list { display: flex; flex-direction: column; gap: .35rem; }

.feed-item {
  display: flex;
  align-items: center;
  gap: .5rem;
  padding: .3rem .5rem;
  border-radius: .4rem;
  background: white;
  border: 1px solid #e2e8f0;
  font-size: .78rem;
}
.feed-avatar {
  width: 22px; height: 22px;
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-size: .6rem; font-weight: 800;
  color: white;
  flex-shrink: 0;
}
.feed-name { flex: 1; font-weight: 600; color: #1e293b; }
.feed-time { color: #94a3b8; font-size: .7rem; white-space: nowrap; }

/* Feed transition */
.feed-enter-active { transition: all .35s ease; }
.feed-enter-from { opacity: 0; transform: translateY(-8px); }
</style>
