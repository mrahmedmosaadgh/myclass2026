export default {
  getMenuActions(element, presentationStore) {
    return [
      {
        id: 'edit-html',
        title: 'Edit HTML',
        icon: '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"></polyline><polyline points="8 6 2 12 8 18"></polyline></svg>',
        action: () => {
           const html = prompt('Edit HTML:', element.content);
           if (html !== null) {
             presentationStore.updateElement({ id: element.id, changes: { content: html } });
           }
        }
      }
    ];
  }
};
