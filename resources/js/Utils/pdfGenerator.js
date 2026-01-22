/**
 * PDF Generation Utility
 * Dynamically loads heavy dependencies only when needed
 */

/**
 * Generate a PDF from an HTML element
 * @param {HTMLElement} element - The DOM element to convert to PDF
 * @param {Object} options - Configuration options
 * @param {Object} options.canvasOptions - Options for html2canvas
 * @param {Object} options.pdfOptions - Options for jsPDF
 * @returns {Promise<jsPDF>} The generated PDF instance
 */
export async function generateCertificatePDF(element, options = {}) {
    // Dynamically import heavy libraries only when this function is called
    const [{ default: html2canvas }, { default: jsPDF }] = await Promise.all([
        import('html2canvas'),
        import('jspdf')
    ])

    const canvas = await html2canvas(element, {
        scale: 2,
        useCORS: true,
        logging: false,
        allowTaint: true,
        ...options.canvasOptions
    })

    const imgData = canvas.toDataURL('image/png')
    const pdf = new jsPDF({
        orientation: 'landscape',
        unit: 'px',
        format: [1123, 794],
        ...options.pdfOptions
    })

    pdf.addImage(imgData, 'PNG', 0, 0, 1123, 794)
    return pdf
}

/**
 * Capture an HTML element as a PNG image
 * @param {HTMLElement} element - The DOM element to capture
 * @param {Object} options - Options for html2canvas
 * @returns {Promise<string>} Data URL of the captured image
 */
export async function captureElementAsImage(element, options = {}) {
    const { default: html2canvas } = await import('html2canvas')

    const canvas = await html2canvas(element, {
        scale: 2,
        useCORS: true,
        logging: false,
        ...options
    })

    return canvas.toDataURL('image/png')
}

/**
 * Generate a multi-page PDF from multiple elements
 * @param {HTMLElement[]} elements - Array of DOM elements to convert
 * @param {Object} options - Configuration options
 * @returns {Promise<jsPDF>} The generated PDF instance
 */
export async function generateMultiPagePDF(elements, options = {}) {
    const [{ default: html2canvas }, { default: jsPDF }] = await Promise.all([
        import('html2canvas'),
        import('jspdf')
    ])

    const pdf = new jsPDF({
        orientation: 'landscape',
        unit: 'px',
        format: [1123, 794],
        ...options.pdfOptions
    })

    for (let i = 0; i < elements.length; i++) {
        const canvas = await html2canvas(elements[i], {
            scale: 2,
            useCORS: true,
            logging: false,
            allowTaint: true,
            ...options.canvasOptions
        })

        const imgData = canvas.toDataURL('image/png')

        if (i > 0) {
            pdf.addPage()
        }

        pdf.addImage(imgData, 'PNG', 0, 0, 1123, 794)
    }

    return pdf
}
