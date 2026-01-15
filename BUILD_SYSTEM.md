# Improved Build System

## Overview

We've enhanced the build system for this Laravel application using Vite with several performance and analysis improvements.

## New Build Scripts

- `npm run build`: Standard production build with clean output directory
- `npm run build:clean`: Clean build (removes dist folder first)
- `npm run build:prod`: Optimized production build with minification
- `npm run build:analyze`: Bundle analysis build with visualization
- `npm run build:perf`: Performance-focused build for analysis

## Key Improvements

### 1. Bundle Analysis
Added [rollup-plugin-visualizer](https://www.npmjs.com/package/rollup-plugin-visualizer) for visualizing bundle composition. Run `npm run build:analyze` to generate an interactive HTML report showing what's included in your bundle.

### 2. Better Chunking Strategy
Improved code splitting with:
- Manual chunk grouping for vendor libraries
- Separate chunks for UI components, internationalization, and offline functionality
- Asset hashing for better cache invalidation

### 3. Production Optimizations
- Sourcemaps disabled in production
- Terser minification enabled
- Improved asset file naming with hashes
- More aggressive tree-shaking

### 4. Performance Enhancements
- CSS code splitting enabled
- Compressed size reporting activated
- Better compression algorithms for assets

## Bundle Analysis

After running `npm run build:analyze`, you'll find a `stats.html` file in the root directory that contains an interactive visualization of your bundle. This allows you to identify large dependencies and potential optimization opportunities.

## Performance Monitoring

The system includes warnings for oversized chunks to help identify when code splitting might be needed. Pay attention to the console output during builds for suggestions on improving performance.

## File Organization

Assets are now organized by type with content hash:
- Images: `assets/images/[name]-[hash][extname]`
- Fonts: `assets/fonts/[name]-[hash][extname]`
- CSS: `assets/css/[name]-[hash][extname]`
- JS: `assets/js/[name]-[hash].js`

This ensures proper cache invalidation when content changes.