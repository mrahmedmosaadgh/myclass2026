# Debug Instructions for Page Number Format Issue

## Step 1: Rebuild the Assets

The JavaScript changes need to be compiled. Run one of these commands:

```bash
# For development (with hot reload)
npm run dev

# OR for production build
npm run build
```

## Step 2: Clear Browser Cache

After rebuilding, clear your browser cache or do a hard refresh:
- **Chrome/Edge**: Ctrl+Shift+R (Windows) or Cmd+Shift+R (Mac)
- **Firefox**: Ctrl+F5 (Windows) or Cmd+Shift+R (Mac)
- **Safari**: Cmd+Option+R (Mac)

## Step 3: Open Browser Console

1. Open the test-builder-v3 page
2. Open browser DevTools (F12 or right-click → Inspect)
3. Go to the Console tab
4. Click "Live Print Preview" button

## Step 4: Check Console Logs

You should see debug messages like:

```
[PAGE NUMBER DEBUG - GENERATE] pageNumberFormat from settings: page-of
[PAGE NUMBER DEBUG - GENERATE] pageOptions.printFooter: {showPageNumbers: true, pageNumberFormat: "page-of", ...}
[PAGE NUMBER DEBUG] injectAbsolutePageNumbers - format from settings: page-of
[PAGE NUMBER DEBUG] injectAbsolutePageNumbers - position: bottom-center
[PAGE NUMBER DEBUG] Total pages estimated: 5
[PAGE NUMBER DEBUG] Creating page numbers for 5 pages
[PAGE NUMBER DEBUG] buildPreviewText called with: {fmt: "page-of", currentPage: 1, totalPages: 5}
[PAGE NUMBER DEBUG] buildPreviewText result: 1 of 5
[PAGE NUMBER DEBUG] Page 1 text: 1 of 5
[PAGE NUMBER DEBUG] Page 2 text: 2 of 5
...
```

## Step 5: What to Look For

### If you see the logs:

1. **Check the format value**: Look at `pageNumberFormat from settings`
   - Should be: 'page', 'page-of', 'page-slash', or 'fraction'
   - If it's something else, the settings aren't being saved correctly

2. **Check the buildPreviewText result**: 
   - 'page' should give: "1", "2", "3"
   - 'page-of' should give: "1 of 5", "2 of 5"
   - 'page-slash' should give: "Page 1 / 5", "Page 2 / 5"
   - 'fraction' should give: "1/5", "2/5"

3. **If the result is wrong**: The format logic has an issue
4. **If the result is correct but display is wrong**: CSS or DOM issue

### If you DON'T see the logs:

1. The build didn't work - try rebuilding
2. Browser cache issue - clear cache and hard refresh
3. Wrong file being loaded - check network tab for the correct JS file

## Step 6: Test Different Formats

In the Settings → Footer → Page Numbers section:
1. Select "1 of 5" format
2. Click somewhere to save
3. Open Live Print Preview
4. Check console logs
5. Verify page numbers show "1 of 5", "2 of 5", etc.

Repeat for each format:
- "Page 1" → should show: 1, 2, 3
- "1 of 5" → should show: 1 of 5, 2 of 5
- "Page 1 / 5" → should show: Page 1 / 5, Page 2 / 5
- "1/5" → should show: 1/5, 2/5

## Common Issues

### Issue: Format is always 'page' (default)
**Cause**: Settings not being saved
**Fix**: Check the `savePageState` function is being called when you change the dropdown

### Issue: Logs show correct format but wrong output
**Cause**: The buildPreviewText function has wrong logic
**Fix**: Check the if/else conditions in the function

### Issue: No logs appear
**Cause**: Old JavaScript still cached
**Fix**: 
1. Stop dev server
2. Delete `public/build` folder
3. Run `npm run build` again
4. Hard refresh browser (Ctrl+Shift+R)

### Issue: Page numbers don't appear at all
**Cause**: "Show page numbers in footer" toggle is OFF
**Fix**: Turn on the toggle in Settings → Footer → Page Numbers

## Share Console Output

If the issue persists, copy ALL the console logs that start with `[PAGE NUMBER DEBUG]` and share them. This will help identify exactly where the problem is.
