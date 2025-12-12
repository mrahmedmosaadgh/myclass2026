/**
 * Tools Switcher - Control system features via localStorage
 * Helps make the system lighter by disabling unused features
 */

const TOOLS_CONFIG_KEY = 'toolsSwitcher';

const DEFAULT_CONFIG = {
  firebase: {
    enabled: true,
    auth: true,
    database: true,
    emulators: true,
    notifications: true
  },
  backgroundServices: {
    notifications: true,
    sync: true,
    realtime: true
  },
  lastUpdated: new Date().toISOString()
};

export class ToolsSwitcher {
  static getConfig() {
    try {
      const stored = localStorage.getItem(TOOLS_CONFIG_KEY);
      return stored ? { ...DEFAULT_CONFIG, ...JSON.parse(stored) } : DEFAULT_CONFIG;
    } catch (error) {
      console.warn('Failed to load tools config:', error);
      return DEFAULT_CONFIG;
    }
  }

  static saveConfig(config) {
    try {
      const updatedConfig = {
        ...config,
        lastUpdated: new Date().toISOString()
      };
      localStorage.setItem(TOOLS_CONFIG_KEY, JSON.stringify(updatedConfig));
      console.log('Tools config saved:', updatedConfig);
      return true;
    } catch (error) {
      console.error('Failed to save tools config:', error);
      return false;
    }
  }

  static isEnabled(category, feature = null) {
    const config = this.getConfig();
    if (feature) {
      return config[category]?.[feature] ?? false;
    }
    return config[category]?.enabled ?? false;
  }

  static toggle(category, feature = null) {
    const config = this.getConfig();
    if (feature) {
      if (!config[category]) config[category] = {};
      config[category][feature] = !config[category][feature];
    } else {
      if (!config[category]) config[category] = {};
      config[category].enabled = !config[category].enabled;
    }
    this.saveConfig(config);
    return config;
  }

  // Quick access methods
  static isFirebaseEnabled() {
    return this.isEnabled('firebase');
  }

  static isNotificationsEnabled() {
    return this.isEnabled('firebase', 'notifications') && this.isEnabled('backgroundServices', 'notifications');
  }

  static isEmulatorsEnabled() {
    return this.isEnabled('firebase', 'emulators');
  }
}

// Global helper for console
window.toolsSwitcher = ToolsSwitcher;