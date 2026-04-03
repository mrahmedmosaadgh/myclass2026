# Schedule App V4 - Offline Auto-Save

## Overview
Schedule App V4 is an advanced schedule management application with offline auto-save capabilities, user folder persistence, and enhanced PWA features.

## Key Features

### 🔄 Offline Auto-Save
- **Real-time auto-save**: All changes are automatically saved to the cloud
- **User folder storage**: Each user gets their own dedicated storage folder
- **Local backup**: Data is also saved to localStorage as backup
- **Queue system**: Changes made offline are queued and synced when back online
- **Visual indicators**: Auto-save status is shown in real-time

### 📁 User Folder Management
- **Unique user IDs**: Each user gets a unique identifier for data isolation
- **Automatic backups**: Creates timestamped backups automatically
- **Backup rotation**: Keeps only the last 10 backups to manage storage
- **Export/Import**: Full data export and import functionality

### 🌐 Enhanced PWA Features
- **Service Worker**: Advanced caching with offline support
- **Background Sync**: Syncs data when connection is restored
- **Push Notifications**: Notifications for sync status and reminders
- **Installable**: Can be installed as a standalone app

### 📱 Mobile Optimized
- **Responsive Design**: Works perfectly on all screen sizes
- **Touch Friendly**: Optimized for touch interactions
- **Haptic Feedback**: Provides tactile feedback on mobile devices
- **Safe Area Support**: Proper handling of device notches and safe areas

## Routes

### Main Application
- `GET /my-fly-schedule-app/v4` - Main application
- `GET /my-fly-schedule-app/v4/manifest.webmanifest` - PWA manifest
- `GET /my-fly-schedule-app/v4/icon.svg` - App icon
- `GET /my-fly-schedule-app/v4/sw.js` - Service worker

### API Endpoints
- `POST /api/v4/save-data` - Save user data to server
- `GET /api/v4/load-data` - Load user data from server
- `GET /api/v4/backups` - List user backups
- `GET /api/v4/download-backup/{filename}` - Download specific backup
- `GET /api/v4/health` - Health check endpoint

## Data Storage

### Server Storage
```
storage/app/public/schedule-app-v4/users/{userId}/
├── schedule-data.json          # Main data file
├── backup-2024-01-01-12-00-00.json  # Timestamped backups
├── backup-2024-01-01-13-00-00.json
└── ...
```

### Local Storage
- `schedule-v4-user-id` - Unique user identifier
- `schedule-v4-data-backup` - Local backup of all data
- `schedule-v4-test-time` - Test time configuration
- `schedule-v4-admin-selection` - Admin panel selections
- `school-timings-v4` - Custom timing configurations

## Auto-Save Behavior

### Triggers
- View mode changes
- Stage/day selection changes
- Timing configuration updates
- Any data modifications

### Status Indicators
- 💾 **Saving**: Currently saving to server
- ✅ **Saved**: Successfully saved to cloud
- ❌ **Error**: Save failed, saved locally
- 📴 **Offline**: Offline mode, saved locally
- ⏳ **Idle**: Ready for next save

### Retry Logic
- Failed saves are queued for retry
- Automatic retry when connection is restored
- Maximum 10 failed operations in queue
- Local storage as fallback

## PWA Features

### Installation
- Install prompt appears on supported browsers
- Can be installed from browser menu
- Standalone mode with custom icons
- Full-screen experience

### Offline Support
- Complete offline functionality
- Cached app shell for instant loading
- Offline data access
- Sync when online

### Background Sync
- Automatic data synchronization
- Push notifications for sync status
- Queue management for failed operations

## Development

### File Structure
```
v4/
├── StandaloneScheduleAppV4.vue    # Main app container
├── MyTableScheduleV4.vue          # Core schedule component
├── components/                    # Vue components
├── composables/                   # Vue composables
├── data/                         # Static data files
├── README_V4.md                  # This documentation
└── service worker files          # PWA assets
```

### Key Components
- **StandaloneScheduleAppV4**: Main app wrapper with PWA features
- **MyTableScheduleV4**: Core schedule functionality with auto-save
- **DataManager**: Data import/export management
- **ViewModeSwitcher**: Switch between different view modes

### API Integration
- RESTful API for data persistence
- User-specific data isolation
- Automatic backup management
- Health monitoring

## Browser Support

### Modern Browsers
- Chrome 80+
- Firefox 75+
- Safari 13+
- Edge 80+

### PWA Features
- Service Workers
- Background Sync
- Push Notifications
- Install Prompt

## Security

### Data Isolation
- User-specific folders
- Unique user IDs
- No data sharing between users
- Server-side validation

### Secure Storage
- Server-side file storage
- Local encryption options
- Backup verification
- Access logging

## Performance

### Optimization
- Lazy loading of components
- Efficient caching strategies
- Minimal bundle size
- Fast data synchronization

### Monitoring
- Auto-save performance tracking
- Error reporting
- Usage analytics
- Health checks

## Troubleshooting

### Common Issues
1. **Auto-save not working**: Check network connection and browser console
2. **Data not loading**: Verify user ID and server connectivity
3. **PWA not installing**: Check browser compatibility and HTTPS
4. **Offline mode**: Clear cache and reload app

### Debug Information
- Console logs for all operations
- Network tab for API calls
- Application tab for storage
- Service Worker status in DevTools

## Future Enhancements

### Planned Features
- Real-time collaboration
- Advanced analytics
- Calendar integration
- Multi-language support

### Technical Improvements
- WebSocket integration
- Advanced caching
- Better error handling
- Performance optimization

---

**Version**: 4.0.0  
**Build**: Offline Auto-Save  
**Platform**: Progressive Web App  
**URL**: https://qudratpro.com/my-schedule-app/v4
