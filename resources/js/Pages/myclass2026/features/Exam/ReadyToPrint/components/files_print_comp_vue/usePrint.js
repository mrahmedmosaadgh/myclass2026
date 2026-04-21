// composables/usePrint.js
// Reusable print composable for Vue 3 + Laravel

export function usePrint() {
  /**
   * Print a specific DOM element by ref or selector
   * @param {Ref|string} target - Vue ref or CSS selector string
   * @param {Object} options
   * @param {string} options.title - Window/tab title during print
   * @param {string} options.pageSize - CSS page size: 'A4', 'letter', etc.
   * @param {string} options.orientation - 'portrait' | 'landscape'
   * @param {string} options.extraStyles - Additional CSS string to inject
   */
  const printElement = (target, options = {}) => {
    const {
      title = document.title,
      pageSize = 'A4',
      orientation = 'portrait',
      extraStyles = '',
    } = options

    const el = typeof target === 'string'
      ? document.querySelector(target)
      : target?.value ?? target

    if (!el) {
      console.warn('[usePrint] Target element not found.')
      return
    }

    const printWindow = window.open('', '_blank', 'width=900,height=700')
    if (!printWindow) {
      alert('Please allow popups to enable printing.')
      return
    }

    const styles = [...document.styleSheets]
      .map(sheet => {
        try {
          return [...sheet.cssRules].map(r => r.cssText).join('\n')
        } catch {
          return ''
        }
      })
      .join('\n')

    printWindow.document.write(`
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8" />
  <title>${title}</title>
  <style>
    @page { size: ${pageSize} ${orientation}; margin: 20mm; }
    body { margin: 0; padding: 0; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    @media print { .no-print { display: none !important; } }
    ${styles}
    ${extraStyles}
  </style>
</head>
<body>
  ${el.outerHTML}
  <script>
    window.onload = () => { window.print(); window.close(); }
  <\/script>
</body>
</html>
    `)

    printWindow.document.close()
  }

  /**
   * Fetch a Laravel route and print the response HTML
   * @param {string} url - Laravel route URL (e.g. /invoices/5/print)
   * @param {Object} options - Same as printElement options
   */
  const printFromUrl = async (url, options = {}) => {
    const {
      title = 'Print',
      pageSize = 'A4',
      orientation = 'portrait',
      extraStyles = '',
    } = options

    try {
      const res = await fetch(url, {
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'text/html',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
        },
        credentials: 'same-origin',
      })

      if (!res.ok) throw new Error(`HTTP ${res.status}`)

      const html = await res.text()
      const printWindow = window.open('', '_blank', 'width=900,height=700')

      if (!printWindow) {
        alert('Please allow popups to enable printing.')
        return
      }

      printWindow.document.write(`
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8" />
  <title>${title}</title>
  <style>
    @page { size: ${pageSize} ${orientation}; margin: 20mm; }
    body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    @media print { .no-print { display: none !important; } }
    ${extraStyles}
  </style>
</head>
<body>
  ${html}
  <script>
    window.onload = () => { window.print(); window.close(); }
  <\/script>
</body>
</html>
      `)

      printWindow.document.close()
    } catch (err) {
      console.error('[usePrint] Failed to fetch print content:', err)
    }
  }

  /**
   * Trigger browser's native window.print() for the entire page
   */
  const printPage = () => window.print()

  return { printElement, printFromUrl, printPage }
}
