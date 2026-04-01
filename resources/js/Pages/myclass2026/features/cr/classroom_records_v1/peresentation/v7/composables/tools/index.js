import textTool from './textTool';
import mathTool from './mathTool';
import imageTool from './imageTool';
import htmlTool from './htmlTool';
import rectangleTool from './rectangleTool';

export const tools = {
  text: textTool,
  math: mathTool,
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
