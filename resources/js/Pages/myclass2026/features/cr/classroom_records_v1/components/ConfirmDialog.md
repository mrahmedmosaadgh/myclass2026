# ConfirmDialog Component

A reusable confirmation dialog component for the Classroom Records system. Provides a consistent, user-friendly way to confirm destructive actions.

## Features

- ✅ Reusable across the entire application
- ✅ Customizable title, message, and button labels
- ✅ Configurable colors and styling
- ✅ Click-outside-to-close functionality
- ✅ Clean, modern UI with rounded corners
- ✅ Icon support in labels
- ✅ HTML message support

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `title` | String | `'Confirm Action'` | Dialog title |
| `message` | String | `'Are you sure you want to continue?'` | Dialog message (supports HTML) |
| `okLabel` | String | `'OK'` | Primary button label |
| `cancelLabel` | String | `'Cancel'` | Cancel button label |
| `okColor` | String | `'primary'` | Primary button color (Quasar color) |
| `cancelColor` | String | `'grey-7'` | Cancel button color (Quasar color) |
| `persistent` | Boolean | `false` | If true, user must click a button to close |

## Events

| Event | Description | Payload |
|-------|-------------|---------|
| `@ok` | Emitted when user clicks OK button | - |
| `@cancel` | Emitted when user clicks Cancel or clicks outside | - |

## Usage Examples

### Basic Usage

```vue
<script setup>
import ConfirmDialog from './ConfirmDialog.vue'

const confirmDialog = ref(null)

const handleDelete = () => {
  confirmDialog.value.show()
}
</script>

<template>
  <ConfirmDialog 
    ref="confirmDialog"
    title="Delete Item?"
    message="This action cannot be undone."
    ok-label="🗑️ Delete"
    cancel-label="❌ Cancel"
    ok-color="negative"
    @ok="handleDeleteConfirmed"
    @cancel="handleDeleteCancelled"
  />
</template>
```

### Classroom Records - Reset Points

```vue
<script setup>
import ConfirmDialog from './ConfirmDialog.vue'

const confirmDialog = ref(null)
const student = ref({ period: { total_score: 15 } })

const showResetConfirmation = () => {
  const points = student.value.period.total_score
  
  // Create dialog configuration
  const dialogConfig = {
    title: '⚠️ Reset Points?',
    message: `Student has ${points} points\nMark absent will reset to 0`,
    okLabel: '🗑️ Reset to 0',
    cancelLabel: '❌ Cancel',
    okColor: 'negative',
    cancelColor: 'grey-7',
    persistent: false // Allow click outside to cancel
  }
  
  // Use Quasar dialog directly with the configuration
  $q.dialog({
    title: dialogConfig.title,
    message: dialogConfig.message,
    html: true,
    ok: {
      label: dialogConfig.okLabel,
      color: dialogConfig.okColor
    },
    cancel: {
      label: dialogConfig.cancelLabel,
      color: dialogConfig.cancelColor
    },
    persistent: dialogConfig.persistent,
    style: 'border-radius: 12px',
    'class': 'text-center'
  }).onOk(() => {
    // Handle confirmation
    markStudentAbsent()
  })
}
</script>
```

### Bulk Actions

```vue
<script setup>
const showBulkDeleteConfirmation = () => {
  $q.dialog({
    title: '🗑️ Delete All Items?',
    message: `This will delete <strong>${selectedItems.length}</strong> items.<br>Continue?`,
    html: true,
    ok: {
      label: '🗑️ Delete All',
      color: 'negative'
    },
    cancel: {
      label: '❌ Cancel',
      color: 'grey-7'
    },
    persistent: false,
    style: 'border-radius: 12px',
    'class': 'text-center'
  }).onOk(() => {
    deleteAllSelectedItems()
  })
}
</script>
```

## Styling

The dialog uses Quasar's built-in styling with custom enhancements:

- **Rounded corners**: `border-radius: 12px`
- **Centered text**: `text-center` class
- **Modern spacing**: Default Quasar dialog padding
- **Responsive**: Works on all screen sizes

## Best Practices

1. **Use clear, concise titles** with relevant emojis
2. **Keep messages short** but informative
3. **Use descriptive button labels** with icons
4. **Set persistent to false** for non-critical actions
5. **Use appropriate colors**:
   - `negative` for destructive actions
   - `positive` for safe actions
   - `warning` for cautionary actions

## Color Palette

| Action | Color | Use Case |
|--------|-------|----------|
| `negative` | Red | Delete, reset, remove |
| `positive` | Green | Confirm, save, approve |
| `warning` | Orange/Yellow | Cautionary actions |
| `primary` | Blue | Default actions |
| `grey-7` | Grey | Cancel, dismiss |

## Integration

The component integrates seamlessly with:
- ✅ Quasar Dialog system
- ✅ Vue 3 Composition API
- ✅ Classroom Records store
- ✅ Pinia state management
- ✅ Inertia.js routing

## Accessibility

- ✅ Keyboard navigation support
- ✅ Screen reader friendly
- ✅ Focus management
- ✅ ARIA labels via Quasar
