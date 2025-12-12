/**
 * Dexie.js Database Configuration for Offline-First Education Management System
 *
 * This file sets up the IndexedDB database using Dexie.js for storing education data offline.
 * It supports dynamic resource schemas and provides a flexible foundation for any resource type.
 *
 * @author Education Management System
 * @version 1.0.0
 */

import Dexie from 'dexie';

/**
 * Resource schemas configuration
 * Add new resource types here with their field definitions
 */
export const resourceSchemas = {
  // Core education resources
  lessons: 'id, title, content, course_id, teacher_id, updated_at, synced_at, is_dirty',
  students: 'id, name, email, class_id, grade_id, updated_at, synced_at, is_dirty',
  courses: 'id, name, description, teacher_id, grade_id, updated_at, synced_at, is_dirty',
  teachers: 'id, name, email, subject_id, updated_at, synced_at, is_dirty',

  // Assessment and grading
  quiz_answers: 'id, quiz_id, student_id, answers, score, submitted_at, updated_at, synced_at, is_dirty',
  assignments: 'id, title, description, course_id, due_date, updated_at, synced_at, is_dirty',
  grades: 'id, student_id, assignment_id, score, graded_at, updated_at, synced_at, is_dirty',

  // Attendance and scheduling
  attendance: 'id, student_id, date, status, recorded_at, updated_at, synced_at, is_dirty',
  schedules: 'id, class_id, subject_id, teacher_id, day_of_week, start_time, end_time, updated_at, synced_at, is_dirty',

  // Communication
  messages: 'id, sender_id, recipient_id, content, sent_at, read_at, updated_at, synced_at, is_dirty',
  announcements: 'id, title, content, author_id, published_at, updated_at, synced_at, is_dirty',

  // User Context Tables for Offline-First Caching (7-day expiry)
  user_context_profile: 'user_id, id, name, email, user_role, cached_at, expires_at, updated_at, synced_at, is_dirty',
  user_context_permissions: 'user_id, roles, cached_at, expires_at, updated_at, synced_at, is_dirty',
  user_context_school: 'user_id, school, schools, cached_at, expires_at, updated_at, synced_at, is_dirty',
  user_context_classroom: 'user_id, teacher, classroom, cached_at, expires_at, updated_at, synced_at, is_dirty',
  user_context_schedule: 'user_id, schedule, cached_at, expires_at, updated_at, synced_at, is_dirty',

  // System tables - DO NOT MODIFY
  sync_queue: 'id, method, url, payload, timestamp, retry_count, status, priority, resource_type',
  offline_metadata: 'key, value, updated_at'
};

/**
 * Education Management Database Class
 * Extends Dexie to provide offline-first capabilities for education data
 */
class EducationDB extends Dexie {
  constructor() {
    super('EducationManagementDB');

    // Define database version and schema
    // Version 2: Added user context tables for offline-first caching
    this.version(2).stores(resourceSchemas);

    // Add hooks for automatic timestamp management
    this.setupHooks();
  }

  /**
   * Set up database hooks for automatic data management
   */
  setupHooks() {
    // Auto-add timestamps and dirty flags on create
    Object.keys(resourceSchemas).forEach(tableName => {
      if (tableName === 'sync_queue' || tableName === 'offline_metadata') {
        return; // Skip system tables
      }

      this[tableName].hook('creating', (primKey, obj, trans) => {
        const now = new Date().toISOString();
        obj.updated_at = now;
        obj.synced_at = null;
        obj.is_dirty = true;

        // For user context tables, add cache timestamps
        if (tableName.startsWith('user_context_')) {
          obj.cached_at = now;
          obj.expires_at = new Date(Date.now() + 7 * 24 * 60 * 60 * 1000).toISOString(); // 7 days
        }
      });

      this[tableName].hook('updating', (modifications, primKey, obj, trans) => {
        modifications.updated_at = new Date().toISOString();
        modifications.is_dirty = true;

        // For user context tables, update cache timestamps
        if (tableName.startsWith('user_context_')) {
          modifications.cached_at = new Date().toISOString();
          modifications.expires_at = new Date(Date.now() + 7 * 24 * 60 * 60 * 1000).toISOString(); // 7 days
        }
      });
    });
  }

  /**
   * Get all dirty (unsynced) records for a resource
   * @param {string} resourceName - Name of the resource table
   * @returns {Promise<Array>} Array of dirty records
   */
  getDirtyRecords(resourceName) {
    if (!this[resourceName]) {
      throw new Error(`Resource '${resourceName}' not found in database schema`);
    }

    return this[resourceName]
      .where('is_dirty')
      .equals(true)
      .toArray();
  }

  /**
   * Mark records as synced
   * @param {string} resourceName - Name of the resource table
   * @param {Array} ids - Array of record IDs to mark as synced
   * @returns {Promise<number>} Number of updated records
   */
  markAsSynced(resourceName, ids) {
    if (!this[resourceName]) {
      throw new Error(`Resource '${resourceName}' not found in database schema`);
    }

    const now = new Date().toISOString();
    return this[resourceName]
      .where('id')
      .anyOf(ids)
      .modify({
        is_dirty: false,
        synced_at: now
      });
  }

  /**
   * Get sync statistics for a resource
   * @param {string} resourceName - Name of the resource table
   * @returns {Promise<Object>} Sync statistics
   */
  getSyncStats(resourceName) {
    if (!this[resourceName]) {
      throw new Error(`Resource '${resourceName}' not found in database schema`);
    }

    return Promise.all([
      this[resourceName].count(),
      this[resourceName].where('is_dirty').equals(true).count(),
      this[resourceName].where('is_dirty').equals(false).count()
    ]).then(([total, dirty, synced]) => ({
      total,
      dirty,
      synced,
      syncPercentage: total > 0 ? Math.round((synced / total) * 100) : 100
    }));
  }

  /**
   * Clear all data for a resource (useful for testing or reset)
   * @param {string} resourceName - Name of the resource table
   * @returns {Promise<void>}
   */
  clearResource(resourceName) {
    if (!this[resourceName]) {
      throw new Error(`Resource '${resourceName}' not found in database schema`);
    }

    return this[resourceName].clear();
  }

  /**
   * Get database size information
   * @returns {Promise<Object>} Database size info
   */
  async getDatabaseInfo() {
    const info = {
      name: this.name,
      version: this.verno,
      tables: {},
      totalRecords: 0
    };

    for (const tableName of Object.keys(resourceSchemas)) {
      const count = await this[tableName].count();
      info.tables[tableName] = count;
      info.totalRecords += count;
    }

    return info;
  }

  /**
   * Export data for backup or migration
   * @param {Array<string>} resourceNames - Resources to export (default: all)
   * @returns {Promise<Object>} Exported data
   */
  async exportData(resourceNames = null) {
    const resourcesToExport = resourceNames || Object.keys(resourceSchemas);
    const exportData = {
      timestamp: new Date().toISOString(),
      version: this.verno,
      data: {}
    };

    for (const resourceName of resourcesToExport) {
      if (this[resourceName]) {
        exportData.data[resourceName] = await this[resourceName].toArray();
      }
    }

    return exportData;
  }

  /**
   * Import data from backup
   * @param {Object} importData - Data to import
   * @param {boolean} clearFirst - Whether to clear existing data first
   * @returns {Promise<Object>} Import results
   */
  async importData(importData, clearFirst = false) {
    const results = {
      imported: {},
      errors: []
    };

    try {
      await this.transaction('rw', Object.keys(resourceSchemas), async () => {
        for (const [resourceName, data] of Object.entries(importData.data)) {
          if (!this[resourceName]) {
            results.errors.push(`Resource '${resourceName}' not found`);
            continue;
          }

          try {
            if (clearFirst) {
              await this[resourceName].clear();
            }

            const count = await this[resourceName].bulkAdd(data);
            results.imported[resourceName] = count;
          } catch (error) {
            results.errors.push(`Error importing ${resourceName}: ${error.message}`);
          }
        }
      });
    } catch (error) {
      results.errors.push(`Transaction failed: ${error.message}`);
    }

    return results;
  }

  /**
   * User Context Cache Management Methods
   */

  /**
   * Store user context segment in cache
   * @param {number} userId - User ID
   * @param {string} segment - Context segment (profile, permissions, school, classroom, schedule)
   * @param {Object} data - Context data to store
   * @returns {Promise<void>}
   */
  async storeUserContext(userId, segment, data) {
    const tableName = `user_context_${segment}`;
    if (!this[tableName]) {
      throw new Error(`User context segment '${segment}' not found`);
    }

    const now = new Date().toISOString();
    const expiresAt = new Date(Date.now() + 7 * 24 * 60 * 60 * 1000).toISOString(); // 7 days

    const contextData = {
      user_id: userId,
      ...data,
      cached_at: now,
      expires_at: expiresAt,
      updated_at: now,
      synced_at: now,
      is_dirty: false
    };

    // Use put to update if exists, create if not
    return this[tableName].put(contextData);
  }

  /**
   * Get user context segment from cache
   * @param {number} userId - User ID
   * @param {string} segment - Context segment
   * @returns {Promise<Object|null>} Cached context data or null if not found/expired
   */
  async getUserContext(userId, segment) {
    const tableName = `user_context_${segment}`;
    if (!this[tableName]) {
      throw new Error(`User context segment '${segment}' not found`);
    }

    const cached = await this[tableName].where('user_id').equals(userId).first();

    if (!cached) {
      return null;
    }

    // Check if cache has expired
    const now = new Date();
    const expiresAt = new Date(cached.expires_at);

    if (now > expiresAt) {
      // Cache expired, remove it
      await this[tableName].where('user_id').equals(userId).delete();
      return null;
    }

    return cached;
  }

  /**
   * Check if user context segment is cached and valid
   * @param {number} userId - User ID
   * @param {string} segment - Context segment
   * @returns {Promise<boolean>} True if cached and valid
   */
  async isUserContextCached(userId, segment) {
    const cached = await this.getUserContext(userId, segment);
    return cached !== null;
  }

  /**
   * Clear expired user context cache entries
   * @returns {Promise<number>} Number of expired entries removed
   */
  async clearExpiredUserContext() {
    const now = new Date().toISOString();
    let totalCleared = 0;

    const contextTables = ['user_context_profile', 'user_context_permissions',
                          'user_context_school', 'user_context_classroom', 'user_context_schedule'];

    for (const tableName of contextTables) {
      const cleared = await this[tableName]
        .where('expires_at')
        .below(now)
        .delete();
      totalCleared += cleared;
    }

    return totalCleared;
  }

  /**
   * Clear all user context cache for a specific user
   * @param {number} userId - User ID
   * @returns {Promise<number>} Number of entries removed
   */
  async clearUserContextCache(userId) {
    let totalCleared = 0;

    const contextTables = ['user_context_profile', 'user_context_permissions',
                          'user_context_school', 'user_context_classroom', 'user_context_schedule'];

    for (const tableName of contextTables) {
      const cleared = await this[tableName]
        .where('user_id')
        .equals(userId)
        .delete();
      totalCleared += cleared;
    }

    return totalCleared;
  }

  /**
   * Get user context cache statistics
   * @param {number} userId - User ID (optional, for specific user stats)
   * @returns {Promise<Object>} Cache statistics
   */
  async getUserContextStats(userId = null) {
    const stats = {
      total: 0,
      expired: 0,
      valid: 0,
      segments: {}
    };

    const now = new Date().toISOString();
    const contextTables = ['user_context_profile', 'user_context_permissions',
                          'user_context_school', 'user_context_classroom', 'user_context_schedule'];

    for (const tableName of contextTables) {
      const segment = tableName.replace('user_context_', '');
      let query = this[tableName];

      if (userId) {
        query = query.where('user_id').equals(userId);
      }

      const total = await query.count();
      const expired = await query.where('expires_at').below(now).count();
      const valid = total - expired;

      stats.segments[segment] = { total, expired, valid };
      stats.total += total;
      stats.expired += expired;
      stats.valid += valid;
    }

    return stats;
  }
}

// Create and export database instance
export const db = new EducationDB();

// Export database class for advanced usage
export { EducationDB };

/**
 * Utility functions for database operations
 */
export const dbUtils = {
  /**
   * Check if a resource exists in the schema
   * @param {string} resourceName - Resource name to check
   * @returns {boolean} True if resource exists
   */
  resourceExists(resourceName) {
    return resourceName in resourceSchemas;
  },

  /**
   * Get all available resource names
   * @returns {Array<string>} Array of resource names
   */
  getResourceNames() {
    return Object.keys(resourceSchemas).filter(
      name => name !== 'sync_queue' && name !== 'offline_metadata' && !name.startsWith('user_context_')
    );
  },

  /**
   * Get user context table names
   * @returns {Array<string>} Array of user context table names
   */
  getUserContextTableNames() {
    return Object.keys(resourceSchemas).filter(
      name => name.startsWith('user_context_')
    );
  },

  /**
   * Validate resource data against basic requirements
   * @param {string} resourceName - Resource name
   * @param {Object} data - Data to validate
   * @returns {Object} Validation result
   */
  validateResourceData(resourceName, data) {
    const result = { valid: true, errors: [] };

    if (!this.resourceExists(resourceName)) {
      result.valid = false;
      result.errors.push(`Resource '${resourceName}' does not exist`);
      return result;
    }

    // Basic validation - can be extended
    if (!data || typeof data !== 'object') {
      result.valid = false;
      result.errors.push('Data must be an object');
    }

    return result;
  }
};

// Initialize database connection
db.open().catch(error => {
  console.error('Failed to open database:', error);
});

export default db;
