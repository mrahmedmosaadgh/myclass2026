import textTool from './textTool';
import imageTool from './imageTool';
import htmlTool from './htmlTool';
import rectangleTool from './rectangleTool';

export const tools = {
  text: textTool,
  image: imageTool,
  html: htmlTool,
  rectangle: rectangleTool
};

export function getToolActions(element, presentationStore) {
  const tool = tools[element.type];
  if (tool && tool.getMenuActions) {
    return tool.getMenuActions(element, presentationStore);
  }
  return [];
}
