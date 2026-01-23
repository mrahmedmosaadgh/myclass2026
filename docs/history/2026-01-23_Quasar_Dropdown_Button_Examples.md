# Quasar Dropdown Button Examples

**Date:** 2026-01-23  
**Purpose:** Reference examples for Quasar dropdown button patterns

## Example 1: Account Settings Dropdown

A rich dropdown with settings toggles and user profile section.

```vue
<template>
  <div class="q-pa-md">
    <q-btn-dropdown
      class="glossy"
      color="purple"
      label="Account Settings"
    >
      <div class="row no-wrap q-pa-md">
        <div class="column">
          <div class="text-h6 q-mb-md">Settings</div>
          <q-toggle v-model="mobileData" label="Use Mobile Data" />
          <q-toggle v-model="bluetooth" label="Bluetooth" />
        </div>

        <q-separator vertical inset class="q-mx-lg" />

        <div class="column items-center">
          <q-avatar size="72px">
            <img src="https://cdn.quasar.dev/img/boy-avatar.png">
          </q-avatar>

          <div class="text-subtitle1 q-mt-md q-mb-xs">John Doe</div>

          <q-btn
            color="primary"
            label="Logout"
            push
            size="sm"
            v-close-popup
          />
        </div>
      </div>
    </q-btn-dropdown>
  </div>
</template>

<script>
import { ref } from 'vue'

export default {
  setup () {
    return {
      mobileData: ref(false),
      bluetooth: ref(false)
    }
  }
}
</script>
```

**Features:**
- Custom content layout with multiple columns
- Toggles for settings
- Avatar display
- Logout button with `v-close-popup`
- Glossy styling

---

## Example 2: Simple List Dropdown

A standard dropdown with clickable list items.

```vue
<template>
  <div class="q-pa-md">
    <q-btn-dropdown color="primary" label="Dropdown Button">
      <q-list>
        <q-item clickable v-close-popup @click="onItemClick">
          <q-item-section>
            <q-item-label>Photos</q-item-label>
          </q-item-section>
        </q-item>

        <q-item clickable v-close-popup @click="onItemClick">
          <q-item-section>
            <q-item-label>Videos</q-item-label>
          </q-item-section>
        </q-item>

        <q-item clickable v-close-popup @click="onItemClick">
          <q-item-section>
            <q-item-label>Articles</q-item-label>
          </q-item-section>
        </q-item>
      </q-list>
    </q-btn-dropdown>
  </div>
</template>

<script>
export default {
  setup () {
    return {
      onItemClick () {
        // console.log('Clicked on an Item')
      }
    }
  }
}
</script>
```

**Features:**
- Standard `q-list` structure
- Clickable items with `v-close-popup`
- Simple event handling
- Clean, minimal design

---

## Usage Notes

### Key Directives
- **`v-close-popup`**: Automatically closes the dropdown when clicked
- **`clickable`**: Makes `q-item` interactive with hover effects

### Common Patterns
1. **Custom Content**: Use `<div>` with custom layout instead of `q-list`
2. **List Items**: Use `q-list` + `q-item` for menu-style dropdowns
3. **Auto-close**: Add `v-close-popup` to buttons/items that should close the dropdown

### Styling Options
- `glossy`: Adds glossy effect to button
- `color`: Sets button color (primary, secondary, purple, etc.)
- `push`: Adds push effect on click
- `flat`, `outline`, `unelevated`: Other button style variants

## Related Components
- `q-btn-dropdown`: Main dropdown button component
- `q-list`, `q-item`: For menu-style dropdowns
- `q-separator`: For dividing sections
- `q-avatar`: For user profile displays
- `q-toggle`: For settings switches
