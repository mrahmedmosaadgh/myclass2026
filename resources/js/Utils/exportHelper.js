import * as XLSX from 'xlsx/xlsx.mjs';

export const exportToExcel = ({
    items = [],
    columns = [],
    fileName = 'export',
    sheetName = 'Sheet1'
}) => {
    try {
        // Create headers and data
        const headers = columns.map(col => col.label);
        const dataRows = items.map(item =>
            columns.map(col => {
                let value;
                if (col.key.includes('.')) {
                    const keys = col.key.split('.');
                    value = item;
                    for (const key of keys) {
                        value = value?.[key];
                    }
                } else {
                    value = item[col.key];
                }
                return value ?? '';
            })
        );

        // Create worksheet
        const ws = XLSX.utils.aoa_to_sheet([headers, ...dataRows]);
        
        // Create workbook and export
        const wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, sheetName);
        XLSX.writeFile(wb, `${fileName}.xlsx`);
        
        return true;
    } catch (error) {
        console.error('Export error:', error);
        return false;
    }
};

export const exportData = exportToExcel;

export const importFromExcel = (file, options = {}) => {
    return new Promise((resolve, reject) => {
        try {
            const reader = new FileReader();
            
            reader.onload = (e) => {
                try {
                    const data = new Uint8Array(e.target.result);
                    const workbook = XLSX.read(data, { type: 'array' });
                    const firstSheet = workbook.Sheets[workbook.SheetNames[0]];
                    
                    // Convert to JSON with header handling
                    let jsonData = XLSX.utils.sheet_to_json(firstSheet, {
                        header: options.header || undefined,
                        defval: options.defval || ''
                    });
                    
                    // Transform data if needed
                    if (options.transform) {
                        jsonData = jsonData.map(options.transform);
                    }
                    
                    resolve({
                        data: jsonData,
                        workbook,
                        sheetName: workbook.SheetNames[0]
                    });
                } catch (error) {
                    reject(error);
                }
            };
            
            reader.onerror = (error) => reject(error);
            reader.readAsArrayBuffer(file);
        } catch (error) {
            reject(error);
        }
    });
};

export const downloadTemplate = (columns, fileName = 'template') => {
    const templateData = [columns.map(col => col.label)];
    exportToExcel({
        items: [],
        columns,
        fileName,
        sheetName: 'Template'
    });
};
