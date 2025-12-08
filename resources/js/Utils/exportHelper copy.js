import * as XLSX from 'xlsx';

export const exportToExcel = ({
    items = [],
    columns = [],
    fileName = 'export',
    sheetName = 'Sheet1'
}) => {
    try {
        // Create headers from columns
        const headers = columns.map(col => col.label);

        // Create data rows using column keys
        const dataRows = items.map(item => 
            columns.map(col => {
                // Handle nested properties (e.g., 'school.name')
                if (col.key.includes('.')) {
                    const keys = col.key.split('.');
                    let value = item;
                    for (const key of keys) {
                        value = value?.[key];
                    }
                    return value || '';
                }
                
                // Handle boolean values
                if (typeof item[col.key] === 'boolean') {
                    return item[col.key] ? 'Active' : 'Inactive';
                }
                
                return item[col.key] || '';
            })
        );

        // Combine headers and data
        const wsData = [headers, ...dataRows];

        // Create worksheet
        const ws = XLSX.utils.aoa_to_sheet(wsData);
        const wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, sheetName);

        // Generate filename with timestamp
        const timestamp = new Date().toISOString().split('T')[0];
        const fullFileName = `${fileName}_${timestamp}.xlsx`;

        XLSX.writeFile(wb, fullFileName);
    } catch (error) {
        console.error('Export failed:', error);
        alert('Failed to export data');
    }
};