# Page Number Position Fix

## The Issue

Page numbers are appearing in the middle of the content area instead of at the bottom of each page.

## What I Changed

### 1. Improved Bottom Position Calculation

**Before:**
```javascript
var bottomPos = pageBottom - bottomMarginPx + edgePadPx;
```

**After:**
```javascript
var fontHeightPx = Math.ceil(fontPt * 1.6);
var bottomPos = pageBottom - fontHeightPx - edgePadPx;
```

This now properly calculates the bottom position by:
- Taking the page bottom position
- Subtracting the font height (so text doesn't overflow)
- Subtracting edge padding for proper spacing

### 2. Added Extensive Debug Logging

The console will now show:
- Page height in pixels
- Each page break position
- For each page number:
  - Page top and bottom positions
  - Calculated position
  - Detailed calculation breakdown

## What to Do

1. **Rebuild:**
   ```bash
   npm run build
   ```

2. **Hard refresh:** Ctrl+Shift+R

3. **Open Live Print Preview** and check console

## Expected Console Output

```
[PAGE NUMBER DEBUG] pageHeightPx: 1031
[PAGE NUMBER DEBUG] Found 0 explicit page breaks
[PAGE NUMBER DEBUG] Page positions array: [0]
[PAGE NUMBER DEBUG] Creating page numbers for 6 pages
[PAGE NUMBER DEBUG] Page 1 positioning - pageTop: 0 pageBottom: 1031 pageHeightPx: 1031
[PAGE NUMBER DEBUG] Bottom position: 1005px (pageBottom: 1031 - fontHeight: 16 - edgePad: 10 )
[PAGE NUMBER DEBUG] Page 2 positioning - pageTop: 1031 pageBottom: 2062 pageHeightPx: 1031
[PAGE NUMBER DEBUG] Bottom position: 2036px (pageBottom: 2062 - fontHeight: 16 - edgePad: 10 )
...
```

## What to Check

1. **Are the page numbers at the bottom now?**
   - They should appear near the bottom of each page
   - Not in the middle of content

2. **Check the console logs:**
   - Share the positioning logs if still wrong
   - The numbers will help me adjust the calculation

3. **Test different positions:**
   - Try "Bottom Left", "Bottom Center", "Bottom Right"
   - All should work correctly

## If Still Wrong

Share these details:
1. Screenshot showing where page numbers appear
2. Console logs showing the positioning calculations
3. What position setting you selected (Bottom Center, etc.)

The detailed logs will show me exactly what's happening with the calculations!
