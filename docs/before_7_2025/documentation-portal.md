# 📚 Documentation Portal

A comprehensive documentation system for Laravel + Inertia + Vue 3 applications with role-based access control.

## 🎯 Overview

The Documentation Portal is a hybrid system that combines:
- **Database Documentation** - User-generated content with rich editing capabilities
- **File-based Documentation** - Markdown files automatically discovered from the `docs/` folder
- **Role-based Access** - Admin and developer only access
- **Beautiful UI** - Modern Quasar-based interface with dark mode support

## ✨ Features

### 🔍 **Smart Discovery**
- Automatically scans `docs/` folder for markdown files
- Extracts titles, categories, and excerpts
- Real-time file system monitoring

### 🎨 **Beautiful Interface**
- Card-based layout with hover effects
- Grid and list view modes
- Category-based navigation
- Advanced search and filtering

### 📱 **Responsive Design**
- Mobile-friendly interface
- Touch-optimized interactions
- Adaptive layouts

### 🌙 **Dark Mode Support**
- Automatic theme detection
- Consistent styling across all components
- User preference persistence

### 🔐 **Security**
- Role-based access control
- Admin/Developer only access
- Secure file access validation

## 🚀 Quick Start

### Access the Portal
1. Login as admin or superadmin
2. Navigate to **Documentation Portal** in the sidebar menu
3. Choose **View All Docs** to see the main portal

### Adding Documentation
- **Database Docs**: Use "Add New Doc" button or "Manage Database Docs"
- **File Docs**: Simply add `.md` files to the `docs/` folder

### Viewing Documentation
- Click any documentation card to open the viewer
- Switch between rendered and source views
- Use table of contents for navigation
- Print or export documents

## 🛠️ Technical Details

### Backend Components
- `DocumentationPortalController` - Main controller
- File scanning and content extraction
- Markdown parsing and rendering
- Search and filtering APIs

### Frontend Components
- `DocumentationPortal/Index.vue` - Main portal page
- `DocumentationCard.vue` - Card component
- `DocumentationListItem.vue` - List item component
- `DocumentViewer.vue` - Full-screen document viewer

### Features Implemented
- ✅ Auto-discovery of documentation files
- ✅ Hybrid database + file system
- ✅ Role-based access control
- ✅ Beautiful Quasar UI
- ✅ Search and filtering
- ✅ Category navigation
- ✅ Document viewer with TOC
- ✅ Dark mode support
- ✅ Mobile responsive
- ✅ Print functionality

## 📁 File Structure

```
docs/
├── documentation-portal.md     # This file
├── offline/                    # Offline system docs
│   ├── README.md
│   ├── concepts.md
│   └── ...
└── [other-docs]/              # Auto-discovered
```

## 🎨 Customization

### Adding New Categories
Categories are automatically detected from:
- Database doc types
- File folder structure
- Custom category mapping

### Styling
All components support:
- Quasar theming
- Custom CSS variables
- Dark mode variants
- Responsive breakpoints

## 🔧 Configuration

### Permissions
Access is controlled via:
- Laravel Spatie permissions
- Role-based middleware
- Menu visibility rules

### File Scanning
- Recursive directory scanning
- Markdown file detection
- Content extraction and parsing
- Caching for performance

## 📊 Statistics

The portal provides real-time statistics:
- Total documents count
- Database vs file documents
- Category distribution
- Recent additions (7 days)

## 🎯 Future Enhancements

- [ ] Advanced markdown editor
- [ ] Document versioning
- [ ] Collaborative editing
- [ ] Export to PDF/Word
- [ ] Full-text search indexing
- [ ] Document templates
- [ ] API documentation generator

## 🆘 Support

For issues or questions:
1. Check existing documentation
2. Review the implementation code
3. Test with sample documents
4. Verify permissions and access

---

**Created**: {{ date('Y-m-d') }}  
**Version**: 1.0.0  
**Author**: Documentation Portal System
