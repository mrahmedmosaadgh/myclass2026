import MultipleChoice from './MultipleChoice.vue'
import TrueFalse from './TrueFalse.vue'
import OpenEnded from './OpenEnded.vue'
export default { resolve(type){ switch(type){ case 'MultipleChoice': return MultipleChoice; case 'TrueFalse': return TrueFalse; case 'OpenEnded': return OpenEnded; default: return MultipleChoice } } }
