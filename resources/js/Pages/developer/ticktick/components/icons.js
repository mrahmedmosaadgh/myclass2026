// Import additional Quasar icon sets
import '@quasar/extras/material-icons/material-icons.css'
import '@quasar/extras/material-icons-outlined/material-icons-outlined.css'
import '@quasar/extras/material-icons-round/material-icons-round.css'
import '@quasar/extras/fontawesome-v6/fontawesome-v6.css'

// Export a function to map icon names to the appropriate format
export function mapIcon(iconName) {
  // Map specific icons that might need special handling
  const iconMap = {
    'break_time': 'coffee',
    'work': 'work',
    'event': 'event',
    'timer': 'timer',
    'check_circle': 'check_circle',
    'list': 'list',
    'today': 'today',
    'menu': 'menu',
    'search': 'search',
    'settings': 'settings',
    'label': 'label',
    'add': 'add',
    'edit': 'edit',
    'delete': 'delete',
    'play_arrow': 'play_arrow',
    'stop': 'stop',
    'pause': 'pause',
    'refresh': 'refresh',
    'fullscreen': 'fullscreen',
    'fullscreen_exit': 'fullscreen_exit',
    'check': 'check',
    'close': 'close',
    'water_drop': 'water_drop',
    'waves': 'waves',
    'forest': 'park'
  };

  return iconMap[iconName] || iconName;
}
