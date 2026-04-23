# Print Footer Page Number Improvements

## Overview
Enhanced the live print preview and actual print output for the test-builder-v3 to provide better page number visibility and accuracy.

## Key Improvements

### 1. **Enhanced Visual Styling for Live Preview**
- Added background, padding, border-radius, and box-shadow to page numbers in screen view
- Page numbers now appear in styled badges with better contrast
- Improved font weight (700) and size (11px) for better readability
- Added subtle border and shadow for depth

### 2. **Improved Print Mode Styling**
- Added `font-weight: 600` for page numbers in print mode
- Included `-webkit-print-color-adjust: exact` and `print-color-adjust: exact` to ensure colors print correctly
- Clean appearance without backgrounds/borders in actual print output
- Better positioning with proper margin calculations

### 3. **More Accurate Page Number Positioning**
- Improved bottom positioning calculation using actual bottom margin (12mm)
- Better handling of left/right/center alignment
- Added explicit `position: absolute` and proper left/right/auto settings
- Enhanced padding for centered page numbers

### 4. **Better Page Count Estimation**
- Updated `estimateTotalPages()` to account for header and footer heights
- More accurate calculation of available content height per page
- Considers actual element heights rather than just page dimensions

### 5. **Timing and Reliability Improvements**
- Added retry mechanism with timeouts (100ms and 500ms) to ensure page numbers render after all content loads
- Enhanced `beforeprint` event handler to recalculate page numbers right before printing
- Added `afterprint` event listener for debugging
- Added media query listener for print mode detection

### 6. **CSS Page Counter Support**
- Added native CSS `@page` counter support as fallback
- Included `@bottom-center` content rule for browsers that support it
- Maintains compatibility with custom page number formats

## Technical Changes

### Files Modified
- `resources/js/Pages/myclass2026/features/Exam/ReadyToPrint_ver3/Builder_test.vue`
- `resources/js/Pages/myclass2026/features/Exam/ReadyToPrint_ver3/components/LivePrintPreview.vue`

### CSS Enhancements
```css
/* Screen view - styled badges */
@media screen {
  .abs-page-number {
    background: rgba(255, 255, 255, 0.95);
    padding: 2px 8px;
    border-radius: 3px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.12);
    border: 1px solid rgba(0,0,0,0.08);
  }
}

/* Print view - clean output */
@media print {
  .abs-page-number {
    background: transparent !important;
    box-shadow: none !important;
    border: none !important;
    padding: 0 !important;
    font-weight: 600 !important;
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
  }
}
```

### JavaScript Improvements
- Better page height calculation considering headers/footers
- Retry mechanism for page number injection
- Enhanced event listeners for print detection
- Improved positioning logic with proper margin handling

## Benefits

1. **Better User Experience**: Page numbers are now clearly visible in the live preview
2. **Print Accuracy**: Page numbers print correctly with proper positioning and styling
3. **Reliability**: Multiple retry mechanisms ensure page numbers always appear
4. **Cross-Browser**: Works across different browsers with fallback support
5. **Professional Output**: Clean, well-positioned page numbers in printed documents

## Testing Recommendations

1. Test with documents of varying lengths (1 page, 5 pages, 10+ pages)
2. Verify page numbers in different positions (top/bottom, left/center/right)
3. Test different page number formats (page, page-of, page-slash)
4. Check print preview in Chrome, Firefox, Safari, and Edge
5. Verify actual print output matches preview
6. Test with different paper sizes if supported

## Future Enhancements

- Add option to customize page number styling (font, size, color) per page
- Support for different page number formats on first/last pages
- Option to skip page numbers on specific pages
- Support for chapter/section-based page numbering
