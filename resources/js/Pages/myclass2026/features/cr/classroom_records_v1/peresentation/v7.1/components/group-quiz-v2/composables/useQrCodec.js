import { useQuasar } from 'quasar';
import { useGameStore } from '../../../stores/gameStore';

/**
 * Composable for QR code encode/decode and scanner handling.
 */
export function useQrCodec() {
  const gameStore = useGameStore();
  const $q = useQuasar();

  function getQrPayload(groupId, optId) {
    const g = String(groupId);
    const groupToken = g.toLowerCase().startsWith('g') ? g : `g${g}`;
    return `${groupToken}_${String(optId).toLowerCase()}`;
  }

  function parseQrPayload(rawText) {
    const raw = String(rawText || '').trim();
    if (!raw) return { type: 'unknown', raw };

    const normalized = raw.toLowerCase().replace(/\s+/g, '');
    const match = normalized.match(/^g[a-z]?(\d+)[\-_]([a-z])$/i);

    if (match) {
      const groupToken = match[1];
      const choiceToken = match[2];
      return {
        type: 'answer',
        groupToken,
        choiceToken: String(choiceToken).toUpperCase(),
        raw
      };
    }

    // Try to match group by name or id
    const targetGroup = gameStore.groups.find(
      (g) => String(g.id) === raw || String(g.name || '').toLowerCase() === raw.toLowerCase()
    );

    if (targetGroup) {
      return { type: 'select-group', group: targetGroup, raw };
    }

    return { type: 'unknown', raw };
  }

  function validateAnswerPayload(parsed, element) {
    const targetGroup = gameStore.groups.find((g) => {
      const idStr = String(g.id);
      return idStr === String(parsed.groupToken) || idStr.toLowerCase() === `g${String(parsed.groupToken)}`;
    });

    const optionExists = !!element?.questionData?.options?.some(
      (o) => String(o.id).toUpperCase() === parsed.choiceToken
    );

    if (!targetGroup) {
      return { ok: false, message: `Unknown group in QR: g${parsed.groupToken}` };
    }
    if (!optionExists) {
      return { ok: false, message: `Unknown choice in QR: ${parsed.choiceToken}` };
    }

    return { ok: true, group: targetGroup, choiceId: parsed.choiceToken };
  }

  function isTypingTarget(el) {
    if (!el) return false;
    const tag = (el.tagName || '').toLowerCase();
    if (tag === 'input' || tag === 'textarea' || tag === 'select') return true;
    if (el.isContentEditable) return true;
    return false;
  }

  return {
    getQrPayload,
    parseQrPayload,
    validateAnswerPayload,
    isTypingTarget
  };
}
