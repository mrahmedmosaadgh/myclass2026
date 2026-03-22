// Centralized exports for all micro-components
// Add new components here and they'll be automatically available in Index.vue

// Core components
import MicroDropdown from './MicroDropdown.vue';
import AudioPlayer from './comptest/AudioPlayer.vue';
import SecureNumpad from './comptest/SecureNumpad/SecureNumpad.vue';
import QuestionDisplay from './comptest/realtimetest/QuestionDisplay.vue';
import QuestionInput from './comptest/realtimetest/QuestionInput.vue';
import VideoPlayer from './comptest/media/VideoPlayer.vue';
import VideoPlayerTabs from './comptest/media/VideoPlayerTabs.vue';
import RealtimeQuestions from './comptest/RealtimeQuestions.vue';

// Chart components
import EChartComponent from './comptest/test1/charts/EChartComponent.vue';
import EChartComponent_v2 from './comptest/test1/charts/EChartComponent_v2.vue';
import TestChartV2 from './comptest/test1/charts/TestChartV2.vue';
import DynamicTreeEditor from './comptest/test1/charts/DynamicTreeEditor.vue';
import DragDrop from './comptest/test1/charts/DragDrop.vue';

// Multiplication components
import mulitp from './comptest/test1/multiplication/mulitp.vue';
import multip2 from './comptest/test1/multiplication/multip2.vue';
import MultipleChoiceQuiz from './comptest/test1/multiplication/multip3/MultipleChoiceQuiz.vue';
import quizData from './comptest/test1/multiplication/multip3/quizData.js';
import InputQuiz from './comptest/test1/multiplication/multip4/InputQuiz.vue';

// IXL Line Plot components
import IXLLinePlotExample from './comptest/test1/smartscore/examples/IXLLinePlotExample.vue';

// Presentation Editor component
import { PresentationEditor } from './comptest/test1/ppt';

// MyTableSchedule component
import MyTableSchedule from './mytable/MyTableSchedule/MyTableSchedule.vue';

// Tasks Pro component
import TaskList from './comptest/test1/taskspro/TaskList.vue';

// AudioPlayerDemo component
import AudioPlayerDemo from './comptest/test1/files_audio_player/AudioPlayerDemo.vue';

// Export components as organized object with titles and configurations
const Components = {
    // Core components
    VideoPlayerTabs: {
        component: VideoPlayerTabs,
        title: 'Video Player Tabs',
        description: 'Multi-Tab Video Interface',
        defaultProps: {}
    },
    MicroDropdown: {
        component: MicroDropdown,
        title: 'Micro Dropdown',
        description: 'Legacy Component Test',
        defaultProps: { options: [] }
    },
    AudioPlayer: {
        component: AudioPlayer,
        title: 'Audio Player',
        description: 'Audio Playback Component',
        defaultProps: { src: "/audio/click-234708.mp3", allowReplayWhenPlaying: true, label: "Click Me (Instant)" }
    },
    SecureNumpad: {
        component: SecureNumpad,
        title: 'Secure Numpad',
        description: 'Custom Input with Sound',
        defaultProps: { modelValue: '', placeholder: "0.00", maxLength: 6, allowKeyboard: true }
    },
    QuestionDisplay: {
        component: QuestionDisplay,
        title: 'Question Display',
        description: 'Real-time Question Display',
        defaultProps: { questionTitle: "Live Poll", questionText: "How confident are you with today's lesson?", answers: [] }
    },
    QuestionInput: {
        component: QuestionInput,
        title: 'Question Input',
        description: 'Real-time Question Input',
        defaultProps: {}
    },
    VideoPlayer: {
        component: VideoPlayer,
        title: 'Video Player',
        description: 'Interactive Video Components',
        defaultProps: { 
            src: "https://www.w3schools.com/html/mov_bbb.mp4",
            title: "Big Buck Bunny",
            description: "A short animated film featuring a large rabbit, three rodents, and butterflies.",
            showControls: true,
            autoplay: false,
            loop: false,
            muted: false,
            audioChannel: "stereo",
            repeatMode: "none",
            repeatCount: 3,
            repeatTime: 5,
            disableExternalControls: true
        }
    },
    RealtimeQuestions: {
        component: RealtimeQuestions,
        title: 'Real-time Questions',
        description: 'Interactive Q&A System',
        defaultProps: {}
    },
    
    // Chart components
    EChartComponent: {
        component: EChartComponent,
        title: 'EChart Component',
        description: 'Chart Visualization',
        defaultProps: { title: "Sample Chart", type: "bar", labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May'] }
    },
    EChartComponent_v2: {
        component: EChartComponent_v2,
        title: 'EChart v2',
        description: 'Enhanced Chart Component',
        defaultProps: {}
    },
    TestChartV2: {
        component: TestChartV2,
        title: 'Test Chart v2',
        description: 'Chart Testing Component',
        defaultProps: {}
    },
    DynamicTreeEditor: {
        component: DynamicTreeEditor,
        title: 'Dynamic Tree Editor',
        description: 'Interactive Tree Structure',
        defaultProps: {}
    },
    DragDrop: {
        component: DragDrop,
        title: 'Drag & Drop',
        description: 'Drag and Drop Interface',
        defaultProps: {}
    },
    
    // Multiplication components
    mulitp: {
        component: mulitp,
        title: 'Multiplication Table',
        description: 'Math Learning Tool',
        defaultProps: {}
    },
    multip2: {
        component: multip2,
        title: 'Multiplication Drag & Drop',
        description: 'Interactive Math Practice',
        defaultProps: {}
    },
    MultipleChoiceQuiz: {
        component: MultipleChoiceQuiz,
        title: 'Multiple Choice Quiz',
        description: 'Quiz Component',
        defaultProps: { questions: [] }
    },
    quizData: {
        component: quizData,
        title: 'Quiz Data',
        description: 'Quiz Data Structure',
        defaultProps: {}
    },
    InputQuiz: {
        component: InputQuiz,
        title: 'Input Quiz',
        description: 'Input-based Quiz',
        defaultProps: {}
    },
    
    // IXL Line Plot
    IXLLinePlotExample: {
        component: IXLLinePlotExample,
        title: 'IXL Line Plot',
        description: 'Educational Line Plot',
        defaultProps: {}
    },
    
    // Presentation Editor
    PresentationEditor: {
        component: PresentationEditor,
        title: 'Presentation Editor',
        description: 'Slide Presentation Tool',
        defaultProps: {}
    },
    
    // MyTableSchedule
    MyTableSchedule: {
        component: MyTableSchedule,
        title: 'Schedule Timeline',
        description: 'Time Management Interface',
        defaultProps: {}
    },
    
    // Tasks Pro
    TaskList: {
        component: TaskList,
        title: 'Tasks Pro',
        description: 'Task Management System',
        defaultProps: {}
    },
    
    // AudioPlayerDemo
    AudioPlayerDemo: {
        component: AudioPlayerDemo,
        title: 'Audio Player Demo',
        description: 'Audio Component Demo',
        defaultProps: {}
    }
};

// Icon mapping for components
const getComponentIcon = (key) => {
    const iconMap = {
        'VideoPlayerTabs': '📑',
        'MicroDropdown': '🔽',
        'AudioPlayer': '🎵',
        'SecureNumpad': '🔢',
        'QuestionDisplay': '❓',
        'QuestionInput': '✏️',
        'VideoPlayer': '🎬',
        'EChartComponent': '📊',
        'EChartComponent_v2': '📈',
        'TestChartV2': '📉',
        'DynamicTreeEditor': '🌳',
        'DragDrop': '🎯',
        'mulitp': '✖️',
        'multip2': '🎯',
        'MultipleChoiceQuiz': '🧮',
        'quizData': '📋',
        'InputQuiz': '📋',
        'IXLLinePlotExample': '📈',
        'PresentationEditor': '📺',
        'MyTableSchedule': '📅',
        'TaskList': '📝',
        'AudioPlayerDemo': '🎧',
        'RealtimeQuestions': '❓'
    };
    return iconMap[key] || '🔧';
};

export { getComponentIcon, quizData, MultipleChoiceQuiz, TaskList };
export default Components;
