import { ref } from 'vue';

const DB_NAME = 'ScheduleAppDB';
const DB_VERSION = 1;
const STORES = {
  PERSONAL_SCHEDULE: 'personalSchedule',
  SCHOOL_TIMETABLE: 'schoolTimetable',
  TIMINGS: 'timings',
  APP_SETTINGS: 'appSettings',
  SYNC_QUEUE: 'syncQueue'
};

let db = null;

export function useOfflineDB() {
  const isReady = ref(false);
  const error = ref(null);

  const initDB = () => {
    return new Promise((resolve, reject) => {
      const request = indexedDB.open(DB_NAME, DB_VERSION);

      request.onerror = () => {
        error.value = 'Failed to open database';
        reject(request.error);
      };

      request.onsuccess = () => {
        db = request.result;
        isReady.value = true;
        console.log('[OfflineDB] Database initialized');
        resolve(db);
      };

      request.onupgradeneeded = (event) => {
        const database = event.target.result;

        // Personal Schedule Store
        if (!database.objectStoreNames.contains(STORES.PERSONAL_SCHEDULE)) {
          const personalStore = database.createObjectStore(STORES.PERSONAL_SCHEDULE, {
            keyPath: 'id',
            autoIncrement: true
          });
          personalStore.createIndex('stage', 'stage', { unique: false });
          personalStore.createIndex('day', 'day', { unique: false });
          personalStore.createIndex('period', 'period', { unique: false });
        }

        // School Timetable Store
        if (!database.objectStoreNames.contains(STORES.SCHOOL_TIMETABLE)) {
          const schoolStore = database.createObjectStore(STORES.SCHOOL_TIMETABLE, {
            keyPath: 'id',
            autoIncrement: true
          });
          schoolStore.createIndex('stage', 'stage', { unique: false });
          schoolStore.createIndex('day', 'day', { unique: false });
          schoolStore.createIndex('teacher', 'teacher', { unique: false });
          schoolStore.createIndex('class', 'class', { unique: false });
        }

        // Timings Store
        if (!database.objectStoreNames.contains(STORES.TIMINGS)) {
          const timingsStore = database.createObjectStore(STORES.TIMINGS, {
            keyPath: 'id'
          });
          timingsStore.createIndex('stage', 'stage', { unique: false });
          timingsStore.createIndex('day', 'day', { unique: false });
        }

        // App Settings Store
        if (!database.objectStoreNames.contains(STORES.APP_SETTINGS)) {
          database.createObjectStore(STORES.APP_SETTINGS, {
            keyPath: 'key'
          });
        }

        // Sync Queue Store
        if (!database.objectStoreNames.contains(STORES.SYNC_QUEUE)) {
          const syncStore = database.createObjectStore(STORES.SYNC_QUEUE, {
            keyPath: 'id',
            autoIncrement: true
          });
          syncStore.createIndex('timestamp', 'timestamp', { unique: false });
          syncStore.createIndex('synced', 'synced', { unique: false });
        }

        console.log('[OfflineDB] Database schema created');
      };
    });
  };

  const saveData = async (storeName, data) => {
    if (!db) await initDB();

    return new Promise((resolve, reject) => {
      const transaction = db.transaction([storeName], 'readwrite');
      const store = transaction.objectStore(storeName);
      const request = store.put(data);

      request.onsuccess = () => {
        console.log(`[OfflineDB] Data saved to ${storeName}:`, data);
        resolve(request.result);
      };

      request.onerror = () => {
        error.value = `Failed to save data to ${storeName}`;
        reject(request.error);
      };
    });
  };

  const getData = async (storeName, key) => {
    if (!db) await initDB();

    return new Promise((resolve, reject) => {
      const transaction = db.transaction([storeName], 'readonly');
      const store = transaction.objectStore(storeName);
      const request = store.get(key);

      request.onsuccess = () => {
        resolve(request.result);
      };

      request.onerror = () => {
        error.value = `Failed to get data from ${storeName}`;
        reject(request.error);
      };
    });
  };

  const getAllData = async (storeName) => {
    if (!db) await initDB();

    return new Promise((resolve, reject) => {
      const transaction = db.transaction([storeName], 'readonly');
      const store = transaction.objectStore(storeName);
      const request = store.getAll();

      request.onsuccess = () => {
        resolve(request.result);
      };

      request.onerror = () => {
        error.value = `Failed to get all data from ${storeName}`;
        reject(request.error);
      };
    });
  };

  const deleteData = async (storeName, key) => {
    if (!db) await initDB();

    return new Promise((resolve, reject) => {
      const transaction = db.transaction([storeName], 'readwrite');
      const store = transaction.objectStore(storeName);
      const request = store.delete(key);

      request.onsuccess = () => {
        console.log(`[OfflineDB] Data deleted from ${storeName}:`, key);
        resolve();
      };

      request.onerror = () => {
        error.value = `Failed to delete data from ${storeName}`;
        reject(request.error);
      };
    });
  };

  const clearStore = async (storeName) => {
    if (!db) await initDB();

    return new Promise((resolve, reject) => {
      const transaction = db.transaction([storeName], 'readwrite');
      const store = transaction.objectStore(storeName);
      const request = store.clear();

      request.onsuccess = () => {
        console.log(`[OfflineDB] Store cleared: ${storeName}`);
        resolve();
      };

      request.onerror = () => {
        error.value = `Failed to clear store ${storeName}`;
        reject(request.error);
      };
    });
  };

  const queryByIndex = async (storeName, indexName, value) => {
    if (!db) await initDB();

    return new Promise((resolve, reject) => {
      const transaction = db.transaction([storeName], 'readonly');
      const store = transaction.objectStore(storeName);
      const index = store.index(indexName);
      const request = index.getAll(value);

      request.onsuccess = () => {
        resolve(request.result);
      };

      request.onerror = () => {
        error.value = `Failed to query ${storeName} by ${indexName}`;
        reject(request.error);
      };
    });
  };

  const addToSyncQueue = async (action, data) => {
    const queueItem = {
      action,
      data,
      timestamp: Date.now(),
      synced: false
    };

    return saveData(STORES.SYNC_QUEUE, queueItem);
  };

  const getSyncQueue = async () => {
    const allItems = await getAllData(STORES.SYNC_QUEUE);
    return allItems.filter(item => !item.synced);
  };

  const markSynced = async (id) => {
    const item = await getData(STORES.SYNC_QUEUE, id);
    if (item) {
      item.synced = true;
      item.syncedAt = Date.now();
      await saveData(STORES.SYNC_QUEUE, item);
    }
  };

  const exportDatabase = async () => {
    if (!db) await initDB();

    const exportData = {};

    for (const storeName of Object.values(STORES)) {
      exportData[storeName] = await getAllData(storeName);
    }

    return {
      version: DB_VERSION,
      exportedAt: new Date().toISOString(),
      data: exportData
    };
  };

  const importDatabase = async (importData) => {
    if (!db) await initDB();

    for (const [storeName, items] of Object.entries(importData.data)) {
      if (Object.values(STORES).includes(storeName)) {
        await clearStore(storeName);
        for (const item of items) {
          await saveData(storeName, item);
        }
      }
    }

    console.log('[OfflineDB] Database imported successfully');
  };

  const getStorageInfo = async () => {
    if (!db) await initDB();

    const info = {
      dbName: DB_NAME,
      version: DB_VERSION,
      stores: {}
    };

    for (const storeName of Object.values(STORES)) {
      const data = await getAllData(storeName);
      info.stores[storeName] = {
        count: data.length,
        size: new Blob([JSON.stringify(data)]).size
      };
    }

    return info;
  };

  return {
    isReady,
    error,
    STORES,
    initDB,
    saveData,
    getData,
    getAllData,
    deleteData,
    clearStore,
    queryByIndex,
    addToSyncQueue,
    getSyncQueue,
    markSynced,
    exportDatabase,
    importDatabase,
    getStorageInfo
  };
}
