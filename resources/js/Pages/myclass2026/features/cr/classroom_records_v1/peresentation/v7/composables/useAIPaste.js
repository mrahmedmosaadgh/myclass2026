import { usePresentationStore } from '../stores/presentationStore';
import { useUIStore } from '../stores/uiStore';

export function useAIPaste() {
  const presentation = usePresentationStore();
  const ui = useUIStore();

  function generateQuestionElements(questionsData, target, format = 'v2', layoutMode = 'individual') {
    if (!Array.isArray(questionsData)) {
      questionsData = [questionsData];
    }

    if (layoutMode === 'extended' && target === 'new') {
      // Extended mode: Add one slide, stack all questions vertically
      presentation.addSlide();
      const slide = presentation.currentSlide;
      if (!slide) return;

      let currentY = 50;
      let totalContentHeight = 0;
      const gap = 20;

      questionsData.forEach((questionObj, index) => {
        const startZ = slide.elements.length + 1;
        let elementHeight = 0;

        if (format === 'v3') {
          // INTERACTIVE GROUP MCQ BLOCK (VERSION 3)
          let mappedOptions = [];
          if (questionObj.options && Array.isArray(questionObj.options)) {
            mappedOptions = questionObj.options.map((optText, i) => {
               const labels = ['A', 'B', 'C', 'D', 'E', 'F'];
               return { id: labels[i], text: optText };
            });
          }

          let correctStr = questionObj.answer || '';
          let matchId = mappedOptions.find(o => correctStr.includes(o.id) || correctStr.includes(o.text))?.id;
          
          elementHeight = 520; // Approximate height for a group-mcq block
          const groupMcqBlock = {
            id: 'el-' + Date.now() + Math.random().toString(36).substr(2, 5),
            type: 'group-mcq',
            questionData: {
              question: questionObj.question || 'Missing Question',
              options: mappedOptions,
              correctId: matchId || (mappedOptions.length > 0 ? mappedOptions[0].id : null)
            },
            x: 100,
            y: currentY,
            width: 850,
            height: elementHeight,
            zIndex: startZ,
            visibilityOption: 'always-visible',
            isVisible: true,
          };
          presentation.addElement(groupMcqBlock);
          currentY += elementHeight + gap;
          totalContentHeight += elementHeight + gap;
        } else if (format === 'v2') {
          // INTERACTIVE MCQ BLOCK (VERSION 2)
          let mappedOptions = [];
          if (questionObj.options && Array.isArray(questionObj.options)) {
            mappedOptions = questionObj.options.map((optText, i) => {
               const labels = ['A', 'B', 'C', 'D', 'E', 'F'];
               return { id: labels[i], text: optText };
            });
          }

          let correctStr = questionObj.answer || '';
          let matchId = mappedOptions.find(o => correctStr.includes(o.id) || correctStr.includes(o.text))?.id;
          
          elementHeight = 480;
          const mcqBlock = {
            id: 'el-' + Date.now() + Math.random().toString(36).substr(2, 5),
            type: 'mcq',
            questionData: {
              question: questionObj.question || 'Missing Question',
              options: mappedOptions,
              correctId: matchId || (mappedOptions.length > 0 ? mappedOptions[0].id : null)
            },
            x: 100,
            y: currentY,
            width: 800,
            height: elementHeight,
            zIndex: startZ,
            visibilityOption: 'always-visible',
            isVisible: true,
          };
          presentation.addElement(mcqBlock);
          currentY += elementHeight + gap;
          totalContentHeight += elementHeight + gap;
        } else {
          // GENERIC BLOCKS (VERSION 1)
          let block1Content = `### **${questionObj.question || 'Missing Question'}**`;
          
          if (questionObj.options && Array.isArray(questionObj.options)) {
              block1Content += `\n\n`;
              questionObj.options.forEach(opt => {
                  block1Content += `${opt}\n`;
              });
          }

          const block1Height = 180;
          const block1 = {
            id: 'el-' + Date.now() + Math.random().toString(36).substr(2, 5),
            type: 'math',
            content: block1Content,
            x: 100,
            y: currentY,
            width: 600,
            height: block1Height,
            zIndex: startZ,
            visibilityOption: 'always-visible',
            isVisible: true,
            color: '#1f2937'
          };
          currentY += block1Height + gap;
          totalContentHeight += block1Height + gap;

          const block2Height = 100;
          const block2 = {
            id: 'el-' + Date.now() + Math.random().toString(36).substr(2, 5),
            type: 'math',
            content: `**Correct Answer:**\n${questionObj.answer || 'Not provided'}`,
            x: 100,
            y: currentY,
            width: 600,
            height: block2Height,
            zIndex: startZ + 1,
            visibilityOption: 'always-visible',
            isVisible: true,
            isConcealed: true,
            color: '#059669'
          };
          currentY += block2Height + gap;
          totalContentHeight += block2Height + gap;

          presentation.addElement(block1);
          presentation.addElement(block2);
        }
      });

      // Auto-extend slide height to fit all content
      // Add some padding at the bottom (100px)
      const requiredHeight = totalContentHeight + 100;
      const minHeight = 600; // Minimum slide height
      slide.height = Math.max(requiredHeight, minHeight);
    } else {
      // Individual mode: Add slide for each question (original behavior)
      questionsData.forEach((questionObj, index) => {
        if (target === 'new') {
            presentation.addSlide();
        }

        const slide = presentation.currentSlide;
        if (!slide) return;

        const startZ = slide.elements.length + 1;

        if (format === 'v3') {
          // INTERACTIVE GROUP MCQ BLOCK (VERSION 3)
          let mappedOptions = [];
          if (questionObj.options && Array.isArray(questionObj.options)) {
            mappedOptions = questionObj.options.map((optText, i) => {
               const labels = ['A', 'B', 'C', 'D', 'E', 'F'];
               return { id: labels[i], text: optText };
            });
          }

          let correctStr = questionObj.answer || '';
          let matchId = mappedOptions.find(o => correctStr.includes(o.id) || correctStr.includes(o.text))?.id;
          
          const groupMcqBlock = {
            id: 'el-' + Date.now() + Math.random().toString(36).substr(2, 5),
            type: 'group-mcq',
            questionData: {
              question: questionObj.question || 'Missing Question',
              options: mappedOptions,
              correctId: matchId || (mappedOptions.length > 0 ? mappedOptions[0].id : null)
            },
            x: 100,
            y: 50,
            width: 850,
            height: 500,
            zIndex: startZ,
            visibilityOption: 'always-visible',
            isVisible: true,
          };
          presentation.addElement(groupMcqBlock);

        } else if (format === 'v2') {
          // INTERACTIVE MCQ BLOCK (VERSION 2)
          let mappedOptions = [];
          if (questionObj.options && Array.isArray(questionObj.options)) {
            mappedOptions = questionObj.options.map((optText, i) => {
               const labels = ['A', 'B', 'C', 'D', 'E', 'F'];
               return { id: labels[i], text: optText };
            });
          }

          let correctStr = questionObj.answer || '';
          let matchId = mappedOptions.find(o => correctStr.includes(o.id) || correctStr.includes(o.text))?.id;
          
          const mcqBlock = {
            id: 'el-' + Date.now() + Math.random().toString(36).substr(2, 5),
            type: 'mcq',
            questionData: {
              question: questionObj.question || 'Missing Question',
              options: mappedOptions,
              correctId: matchId || (mappedOptions.length > 0 ? mappedOptions[0].id : null)
            },
            x: 100,
            y: 50,
            width: 800,
            height: 480,
            zIndex: startZ,
            visibilityOption: 'always-visible',
            isVisible: true,
          };
          presentation.addElement(mcqBlock);

        } else {
          // GENERIC BLOCKS (VERSION 1)
          let block1Content = `### **${questionObj.question || 'Missing Question'}**`;
          
          if (questionObj.options && Array.isArray(questionObj.options)) {
              block1Content += `\n\n`;
              questionObj.options.forEach(opt => {
                  block1Content += `${opt}\n`;
              });
          }

          const block1 = {
            id: 'el-' + Date.now() + Math.random().toString(36).substr(2, 5),
            type: 'math',
            content: block1Content,
            x: 100,
            y: 70,
            width: 600,
            height: 180,
            zIndex: startZ,
            visibilityOption: 'always-visible',
            isVisible: true,
            color: '#1f2937'
          };

          const block2 = {
            id: 'el-' + Date.now() + Math.random().toString(36).substr(2, 5),
            type: 'math',
            content: `**Correct Answer:**\n${questionObj.answer || 'Not provided'}`,
            x: 100,
            y: 320,
            width: 600,
            height: 100,
            zIndex: startZ + 1,
            visibilityOption: 'always-visible',
            isVisible: true,
            isConcealed: true,
            color: '#059669'
          };

          presentation.addElement(block1);
          presentation.addElement(block2);
        }
      });
    }
  }

  return { generateQuestionElements };
}
