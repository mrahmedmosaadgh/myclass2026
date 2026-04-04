import { ref } from 'vue';

const DB_NAME = 'ScheduleAppV5';
const DB_VERSION = 1;

export const STORES = {
  TIMINGS: 'timings',
  PERSONAL_SCHEDULE: 'personalSchedule',
  SCHOOL_TIMETABLE: 'schoolTimetable',
  APP_SETTINGS: 'appSettings',
  SYNC_QUEUE: 'syncQueue'
};

let dbInstance = null;
let dbPromise = null;

function openDB() {
  if (dbInstance) return Promise.resolve(dbInstance);
  if (dbPromise) return dbPromise;

  dbPromise = new Promise((resolve, reject) => {
    const request = indexedDB.open(DB_NAME, DB_VERSION);

    request.onerror = () => {
      dbPromise = null;
      reject(request.error);
    };

    request.onsuccess = () => {
      dbInstance = request.result;
      dbInstance.onclose = () => { dbInstance = null; dbPromise = null; };
      resolve(dbInstance);
    };

    request.onupgradeneeded = (event) => {
      const db = event.target.result;

      if (!db.objectStoreNames.contains(STORES.TIMINGS)) {
        db.createObjectStore(STORES.TIMINGS, { keyPath: 'id' });
      }

      if (!db.objectStoreNames.contains(STORES.PERSONAL_SCHEDULE)) {
        db.createObjectStore(STORES.PERSONAL_SCHEDULE, { keyPath: 'id' });
      }

      if (!db.objectStoreNames.contains(STORES.SCHOOL_TIMETABLE)) {
        db.createObjectStore(STORES.SCHOOL_TIMETABLE, { keyPath: 'id' });
      }

      if (!db.objectStoreNames.contains(STORES.APP_SETTINGS)) {
        db.createObjectStore(STORES.APP_SETTINGS, { keyPath: 'key' });
      }

      if (!db.objectStoreNames.contains(STORES.SYNC_QUEUE)) {
        const syncStore = db.createObjectStore(STORES.SYNC_QUEUE, { keyPath: 'id', autoIncrement: true });
        syncStore.createIndex('synced', 'synced', { unique: false });
      }
    };
  });

  return dbPromise;
}

function tx(storeName, mode = 'readonly') {
  return openDB().then(db => {
    const transaction = db.transaction([storeName], mode);
    return transaction.objectStore(storeName);
  });
}

function reqToPromise(request) {
  return new Promise((resolve, reject) => {
    request.onsuccess = () => resolve(request.result);
    request.onerror = () => reject(request.error);
  });
}

export function useOfflineDB() {
  const isReady = ref(false);
  const error = ref(null);

  const init = async () => {
    try {
      await openDB();
      isReady.value = true;
    } catch (e) {
      error.value = e.message;
    }
  };

  // ── Generic CRUD ──

  const put = async (storeName, data) => {
    const store = await tx(storeName, 'readwrite');
    return reqToPromise(store.put(data));
  };

  const get = async (storeName, key) => {
    const store = await tx(storeName, 'readonly');
    return reqToPromise(store.get(key));
  };

  const getAll = async (storeName) => {
    const store = await tx(storeName, 'readonly');
    return reqToPromise(store.getAll());
  };

  const remove = async (storeName, key) => {
    const store = await tx(storeName, 'readwrite');
    return reqToPromise(store.delete(key));
  };

  const clear = async (storeName) => {
    const store = await tx(storeName, 'readwrite');
    return reqToPromise(store.clear());
  };

  // ── Timing Config ──

  const getTimingConfig = async () => {
    return get(STORES.TIMINGS, 'config');
  };

  const saveTimingConfig = async (config) => {
    return put(STORES.TIMINGS, {
      id: 'config',
      default: config.default || [],
      overrides: config.overrides || {},
      lastModified: Date.now()
    });
  };

  // ── Personal Schedule ──

  const getPersonalSchedule = async () => {
    return get(STORES.PERSONAL_SCHEDULE, 'main');
  };

  const savePersonalSchedule = async (data) => {
    return put(STORES.PERSONAL_SCHEDULE, {
      id: 'main',
      schedule: data.schedule || [],
      timings: data.timings || [],
      preferences: data.preferences || {},
      lastModified: Date.now()
    });
  };

  // ── School Timetable ──

  const getSchoolTimetable = async () => {
    return get(STORES.SCHOOL_TIMETABLE, 'main');
  };

  const saveSchoolTimetable = async (data) => {
    return put(STORES.SCHOOL_TIMETABLE, {
      id: 'main',
      stages: data.stages || {},
      defaultTimings: data.defaultTimings || [],
      customTimings: data.customTimings || null,
      lastModified: Date.now()
    });
  };

  // ── App Settings ──

  const getSetting = async (key, fallback = null) => {
    const row = await get(STORES.APP_SETTINGS, key);
    return row ? row.value : fallback;
  };

  const saveSetting = async (key, value) => {
    return put(STORES.APP_SETTINGS, { key, value });
  };

  const getAllSettings = async () => {
    const rows = await getAll(STORES.APP_SETTINGS);
    const map = {};
    rows.forEach(r => { map[r.key] = r.value; });
    return map;
  };

  // ── Sync Queue ──

  const addToSyncQueue = async (action, data) => {
    return put(STORES.SYNC_QUEUE, {
      action,
      data,
      timestamp: Date.now(),
      synced: false
    });
  };

  const getPendingSyncItems = async () => {
    const all = await getAll(STORES.SYNC_QUEUE);
    return all.filter(item => !item.synced);
  };

  const markSynced = async (id) => {
    const item = await get(STORES.SYNC_QUEUE, id);
    if (item) {
      item.synced = true;
      item.syncedAt = Date.now();
      await put(STORES.SYNC_QUEUE, item);
    }
  };

  const clearSyncedItems = async () => {
    const all = await getAll(STORES.SYNC_QUEUE);
    const store = await tx(STORES.SYNC_QUEUE, 'readwrite');
    for (const item of all) {
      if (item.synced) {
        store.delete(item.id);
      }
    }
  };

  // ── Export / Import entire DB ──

  const exportAll = async () => {
    const data = {};
    for (const storeName of Object.values(STORES)) {
      data[storeName] = await getAll(storeName);
    }
    return {
      version: DB_VERSION,
      appVersion: '5.0',
      exportedAt: new Date().toISOString(),
      data
    };
  };

  const importAll = async (payload) => {
    for (const [storeName, items] of Object.entries(payload.data || {})) {
      if (Object.values(STORES).includes(storeName)) {
        await clear(storeName);
        for (const item of items) {
          await put(storeName, item);
        }
      }
    }
  };

  // ── Storage Info ──

  const getStorageInfo = async () => {
    const info = { dbName: DB_NAME, version: DB_VERSION, stores: {} };
    for (const storeName of Object.values(STORES)) {
      const items = await getAll(storeName);
      info.stores[storeName] = {
        count: items.length,
        sizeBytes: new Blob([JSON.stringify(items)]).size
      };
    }
    return info;
  };

  return {
    isReady,
    error,
    STORES,
    init,
    put,
    get,
    getAll,
    remove,
    clear,
    getTimingConfig,
    saveTimingConfig,
    getPersonalSchedule,
    savePersonalSchedule,
    getSchoolTimetable,
    saveSchoolTimetable,
    getSetting,
    saveSetting,
    getAllSettings,
    addToSyncQueue,
    getPendingSyncItems,
    markSynced,
    clearSyncedItems,
    exportAll,
    importAll,
    getStorageInfo
  };
}
