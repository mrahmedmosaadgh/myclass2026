export default {
  getMenuActions(element, presentationStore) {
    return [
      {
        id: 'bg-color',
        title: 'Background Color',
        icon: '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m19 11-8-8-8.6 8.6a2 2 0 0 0 0 2.8l5.2 5.2c.8.8 2 .8 2.8 0L19 11Z"></path><path d="m5 2 5 5"></path><path d="M2 13h15"></path><path d="M22 20a2 2 0 1 1-4 0c0-1.6 1.7-2.4 2-4 .3 1.6 2 2.4 2 4Z"></path></svg>',
        action: () => {
           const colorInput = document.createElement('input');
           colorInput.type = 'color';
           colorInput.value = (element.bgColor && element.bgColor.startsWith('#') && element.bgColor.length === 7) 
             ? element.bgColor 
             : '#93c5fd';
           
           colorInput.oninput = (e) => {
             presentationStore.updateElement({ id: element.id, changes: { bgColor: e.target.value } });
           };
           
           colorInput.click();
        }
      }
    ];
  }
};
