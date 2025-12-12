<template>
    <!-- position ? `position-${position}` : '', -->
    <!-- size ? `size-${size}` : '', -->
  <div
    class="indicator-red  p-0 m-0 "
    :class="[label_class 
    ,direction=='left'?'indicator-l    '+'triangle-color-'+color:'indicator-r '+'triangle-color-'+color,
      customClass
    ]"
    :style="customStyle"
  >
 
    <!-- <div class="indicator-triangle indicator-triangle-left" :style="triangleStyle"></div> -->
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
  direction: {
    type: String,
    default: 'left'
  },
  label_class: {
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
.indicator-l {
  position: absolute;
  /* background: #ef4444; */
  /* color: white;
  font-size: 0.6rem;
  padding: 0.1rem 0.3rem; */
  /* border-bottom-right-radius: 0 ; */
  /* border-bottom-left-radius: 0.25rem;
  border-bottom-right-radius: 0.25rem; */
  /* font-weight: 500; */
  z-index: 10;
  box-shadow: 0 8px 4px rgba(0, 0, 0, 0.2);
  overflow: visible;
  display: flex;
  align-items: center;
  justify-content: center;

  border-bottom-left-radius: 0 ;
  translate:   -23px 0 ;
  /* background-color: blue; */
  
}
.indicator-l:after
 
 {
    content: "";
    position: absolute;
    top: 100%;
    left: 0;
    width: 0;
    height: 0;
    border: 0 solid transparent;
    border-width: 10px 0 0 10px;
    /* border-top-color: #0736f1; */
  

}


.indicator-r {
  position: absolute;
 right:0;
  z-index: 10;
  box-shadow: 0 8px 4px rgba(0, 0, 0, 0.2);
  overflow: visible;
  display: flex;
  align-items: center;
  justify-content: center;
  border-bottom-right-radius: 0!important ;
  translate:  10px 0 ;
}
.indicator-r:after
 
 {
    content: "";
    position: absolute;
    top: 100%;
    right: 0;
    width: 0;
    height: 0;
    border: 0 solid transparent;
    border-width: 10px 0 0 10px;
 
    transform: rotate(270deg);
    border-top-color: #0736f1;

    /* border-top-color: #4b4b4f; */
}
.indicator-red:after,.indicator-red:after{
    /* border-top-color: red; */
       border-top-color: #0736f1;

}
/* .direction-right {
    
} */
/* .direction-left {

} */



/* Triangle indicator */
.indicator-triangle {
  position: absolute;

opacity: 0.8;
 
  width: 0;
  height: 0 ;
 
  border-style: solid;
 }

.indicator-triangle-left2 {
  border-width:  10px  10px   0px  0px;
 
    left:   0px;
 
  translate  :  0 5px; 
    transform: rotate(270deg);
  border-color: transparent #ef4444 transparent transparent;

}
.indicator-triangle-right {
  border-width:  5px  7px   0px  0px;

    transform: rotate(180deg);
  border-color: transparent #ef4444 transparent transparent;

}


/* Position variants with triangles */
.position22-top-right {
  top: 0;
  right: 0;
  border-radius: 0 0 0 0 ;
  /* border-radius: 0 0 0 0.25rem; */
}
.position22-top-right .indicator-triangle {
  bottom: -5px;
  right: 0px;
}

.position-top-left {
  top: 0;
  left: 0;
  /* border-radius: 0 0 0.25rem 0; */
}
.position-top-left .indicator-triangle {
  bottom: -4px;
  right: 0;
}

.position-bottom-right {
  bottom: 0;
  right: 0;
  /* border-radius: 0.25rem 0 0 0; */
}
.position-bottom-right .indicator-triangle {
  top: -4px;
  right: 0;
  transform: rotate(270deg);
}

.position-bottom-left {
  bottom: 0;
  left: 0;
  /* border-radius: 0 0.25rem 0 0; */
}
.position-bottom-left .indicator-triangle {
  top: -4px;
  right: 0;
  transform: rotate(270deg);
}

/* Size variants */
.size-small {
  font-size: 0.6rem;
  /* padding: 0.1rem 0.3rem; */
}

.size-medium {
  font-size: 0.7rem;
  /* padding: 0.15rem 0.4rem; */
}
.size-medium .indicator-triangle {
  border-width: 0 6px 5px 0;
}

.size-large {
  font-size: 0.8rem;
  /* padding: 0.2rem 0.5rem; */
}
.size-large .indicator-triangle {
  border-width: 0 7px 6px 0;
}

/* Add a subtle hover effect */
.indicator-l:hover {
  /* transform: translateY(-1px); */
  box-shadow: 0 3px 6px rgba(0, 0, 0, 0.25);
}
</style>

<style scoped>
    /* Default triangle color */
/* .indicator-red:after {
  border-top-color: #0736f1;
} */

/* Custom triangle colors */
.triangle-color-red:after {
  border-top-color: #ef4444 !important;
  opacity: .6;
}

.triangle-color-blue:after {
  border-top-color: #3b82f6 !important;

  opacity: .6;
}

.triangle-color-green:after {
  border-top-color: #10b981 !important;
  opacity: .6;
}
.triangle-color-light-green:after {
  border-top-color: lightgreen !important;
  opacity: .6;
}
.triangle-color-yellow:after {
  border-top-color: #f59e0b !important;
  opacity: .6;
}

.triangle-color-purple:after {
  border-top-color: #8b5cf6 !important;
  opacity: .6;
}

.triangle-color-pink:after {
  border-top-color: #ec4899 !important;
  opacity: .6;
}

.triangle-color-indigo:after {
  border-top-color: #6366f1 !important;
  opacity: .6;
}

.triangle-color-gray:after {
  border-top-color: #6b7280 !important;
  opacity: .6;
}

.triangle-color-black:after {
  border-top-color: #000000 !important;
  opacity: .6;
}

.triangle-color-white:after {
  border-top-color: #ffffff !important;
  opacity: .6;
}

</style>
