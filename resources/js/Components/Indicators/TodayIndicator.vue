<template>
  <div
    class="today-indicator"
    :class="[
      position ? `position-${position}` : '',
      size ? `size-${size}` : '',
      customClass
    ]"
    :style="customStyle"
  >
    <div class="indicator-triangle" :style="triangleStyle"></div>
    {{ label }}
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  label: {
    type: String,
    default: 'Today'
  },
  position: {
    type: String,
    default: 'top-right',
    validator: (value) => ['top-right', 'top-left', 'bottom-right', 'bottom-left'].includes(value)
  },
  size: {
    type: String,
    default: 'small',
    validator: (value) => ['small', 'medium', 'large'].includes(value)
  },
  color: {
    type: String,
    default: null
  },
  textColor: {
    type: String,
    default: null
  },
  customClass: {
    type: String,
    default: ''
  }
});

const customStyle = computed(() => {
  const style = {};

  if (props.color) {
    style.backgroundColor = props.color;
  }

  if (props.textColor) {
    style.color = props.textColor;
  }

  return style;
});

const triangleStyle = computed(() => {
  if (props.color) {
    // Use the same color as the indicator for the triangle
    const style = {};

    // Always set the right border color
    style.borderRightColor = props.color;

    return style;
  }

  return {};
});
</script>

<style scoped>
.today-indicator {
  position: absolute;
  /* background: #ef4444; */
  color: white;
  font-size: 0.6rem;
  padding: 0.1rem 0.3rem;
  border-radius: 0.25rem;
  font-weight: 500;
  z-index: 10;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
  overflow: visible;
  display: flex;
  align-items: center;
  justify-content: center;
  translate:  7px 0 ;
}

/* Triangle indicator */
.indicator-triangle {
  position: absolute;

opacity: 0.7;
  width: 0;
  height: 0 ;
  border-style: solid;
  border-width:  5px  7px   0px  0px;
  transform: rotate(180deg);
  border-color: transparent #ef4444 transparent transparent;
}

/* Position variants with triangles */
.position-top-right {
  top: 0;
  right: 0;
  border-radius: 0 0 0 0.25rem;
}
.position-top-right .indicator-triangle {
  bottom: -5px;
  right: 0px;
}

.position-top-left {
  top: 0;
  left: 0;
  border-radius: 0 0 0.25rem 0;
}
.position-top-left .indicator-triangle {
  bottom: -4px;
  right: 0;
}

.position-bottom-right {
  bottom: 0;
  right: 0;
  border-radius: 0.25rem 0 0 0;
}
.position-bottom-right .indicator-triangle {
  top: -4px;
  right: 0;
  transform: rotate(270deg);
}

.position-bottom-left {
  bottom: 0;
  left: 0;
  border-radius: 0 0.25rem 0 0;
}
.position-bottom-left .indicator-triangle {
  top: -4px;
  right: 0;
  transform: rotate(270deg);
}

/* Size variants */
.size-small {
  font-size: 0.6rem;
  padding: 0.1rem 0.3rem;
}

.size-medium {
  font-size: 0.7rem;
  padding: 0.15rem 0.4rem;
}
.size-medium .indicator-triangle {
  border-width: 0 6px 5px 0;
}

.size-large {
  font-size: 0.8rem;
  padding: 0.2rem 0.5rem;
}
.size-large .indicator-triangle {
  border-width: 0 7px 6px 0;
}

/* Add a subtle hover effect */
.today-indicator:hover {
  transform: translateY(-1px);
  box-shadow: 0 3px 6px rgba(0, 0, 0, 0.25);
}
</style>


