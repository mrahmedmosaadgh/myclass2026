export default {
  getMenuActions(element, presentationStore) {
    return [
      {
        id: 'font-color',
        title: 'Text Color',
        icon: '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20"></path><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>',
        action: () => {
           const colorInput = document.createElement('input');
           colorInput.type = 'color';
           colorInput.value = (element.color && element.color.startsWith('#') && element.color.length === 7) 
             ? element.color 
             : '#000000';
           
           colorInput.oninput = (e) => {
             presentationStore.updateElement({ id: element.id, changes: { color: e.target.value } });
           };
           
           colorInput.click();
        }
      },
      {
        id: 'paste-replace',
        title: 'Paste & Replace All',
        icon: '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path><rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect><path d="m9 14 2 2 4-4"></path></svg>',
        action: async () => {
           try {
             try {
                 const items = await navigator.clipboard.read();
                 let html = null;
                 let plain = null;

                 for (let item of items) {
                     if (item.types.includes('text/html')) {
                         const blob = await item.getType('text/html');
                         html = await blob.text();
                     } else if (item.types.includes('text/plain')) {
                         const blob = await item.getType('text/plain');
                         plain = await blob.text();
                     }
                 }

                 if (html) {
                     presentationStore.updateElement({ id: element.id, changes: { content: html } });
                     return;
                 } else if (plain) {
                     presentationStore.updateElement({ id: element.id, changes: { content: plain.replace(/\\n/g, '<br>') } });
                     return;
                 }
             } catch (e) {
                 // Fallback for older browsers
                 const rawText = await navigator.clipboard.readText();
                 if (rawText) {
                     presentationStore.updateElement({ id: element.id, changes: { content: rawText.replace(/\\n/g, '<br>') } });
                 }
             }
           } catch (err) {
             alert('Clipboard access denied. Please allow permissions or use Ctrl+V inside the text box instead.');
           }
        }
      },
      {
        id: 'toggle-conceal',
        title: element.isConcealed ? 'Remove Answer Conceal' : 'Make Concealed Answer',
        icon: element.isConcealed 
          ? '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-eye"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>'
          : '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-eye-off"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24M1 1l22 22"></path></svg>',
        action: () => {
          presentationStore.updateElement({ id: element.id, changes: { isConcealed: !element.isConcealed } });
        }
      }
    ];
  }
};
