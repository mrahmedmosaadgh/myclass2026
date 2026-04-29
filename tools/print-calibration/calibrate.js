#!/usr/bin/env node

const puppeteer = require('puppeteer');
const fs = require('fs');
const path = require('path');
const crypto = require('crypto');

const A4_WIDTH_PX = 794;  // A4 @ 96dpi
const A4_HEIGHT_PX = 1123; // A4 @ 96dpi
const PX_TO_MM = 25.4 / 96;

const round = (value) => Math.round(value * 10) / 10;

const pxToMm = (px) => round(px * PX_TO_MM);

const computeHash = (data) => {
  const hash = crypto.createHash('sha256');
  hash.update(JSON.stringify(data));
  return `sha256-${hash.digest('hex')}`;
};

const getLayoutFromArgs = () => {
  const arg = process.argv.find((a) => a.startsWith('--layout='));
  const layoutAlias = arg ? arg.split('=')[1] : 'v1';

  if (layoutAlias === 'v1' || layoutAlias === 'v1.0') {
    return { layoutAlias: 'v1', layoutVersion: 'v1.0' };
  }

  return { layoutAlias, layoutVersion: layoutAlias };
};

const getEnvironmentFingerprint = async (browser) => {
  const version = await browser.version();
  const puppeteerVersion = require('puppeteer/package.json').version;
  return {
    puppeteerVersion,
    chromiumVersion: version.split('/')[1]?.split(' ')[0] || 'unknown',
    dpi: 96,
    os: process.platform
  };
};

const measureTemplate = async (browser, templatePath, samples = 3) => {
  const measurements = [];

  for (let i = 0; i < samples; i++) {
    const page = await browser.newPage();
    await page.setViewport({ width: A4_WIDTH_PX, height: A4_HEIGHT_PX });

    await page.goto(`file://${templatePath}`, { waitUntil: 'networkidle0' });
    await page.evaluateHandle('document.fonts.ready');

    const heightPx = await page.evaluate(() => {
      const el = document.getElementById('test-block');
      if (!el) return 0;
      return el.getBoundingClientRect().height;
    });

    measurements.push(pxToMm(heightPx));
    await page.close();
  }

  const avg = measurements.reduce((a, b) => a + b, 0) / measurements.length;
  return round(avg);
};

const main = async () => {
  const { layoutAlias, layoutVersion } = getLayoutFromArgs();
  const metricsVersion = `metrics-${layoutAlias}`;
  const font = 'Arial, sans-serif';
  const lineHeight = 1.4;

  console.log('Starting metrics calibration...');
  console.log(`Layout version: ${layoutVersion}`);
  console.log(`Metrics version: ${metricsVersion}`);

  const browser = await puppeteer.launch({ headless: true });

  const envFingerprint = await getEnvironmentFingerprint(browser);
  console.log('Environment fingerprint:', JSON.stringify(envFingerprint, null, 2));

  const templatesDir = path.join(__dirname, 'templates');
  const templates = [
    { id: 'mcq_2_choices', file: 'mcq-2-choices.html' },
    { id: 'mcq_4_choices', file: 'mcq-4-choices.html' },
    { id: 'mcq_6_choices', file: 'mcq-6-choices.html' },
    { id: 'essay', file: 'essay.html' },
    { id: 'long_text', file: 'long-text.html' },
    { id: 'rtl_arabic', file: 'rtl-arabic.html' },
    { id: 'image_block', file: 'image-block.html' },
    { id: 'mixed_case', file: 'mixed-case.html' }
  ];

  const values = {};

  for (const template of templates) {
    const templatePath = path.join(templatesDir, template.file);
    console.log(`Measuring ${template.id}...`);

    const height = await measureTemplate(browser, templatePath, 3);
    values[template.id] = height;

    console.log(`  → ${height}mm`);
  }

  await browser.close();

  const metricsData = {
    metricsVersion,
    layoutVersion,
    units: 'mm',
    font,
    lineHeight,
    environment: envFingerprint,
    values
  };

  const hash = computeHash({
    values,
    font,
    lineHeight
  });

  metricsData.hash = hash;

  const outputPath = path.join(__dirname, `metrics.${layoutAlias}.json`);
  fs.writeFileSync(outputPath, JSON.stringify(metricsData, null, 2));

  console.log('\nCalibration complete.');
  console.log(`Metrics saved to: ${outputPath}`);
  console.log(`Hash: ${hash}`);
};

main().catch(err => {
  console.error('Calibration failed:', err);
  process.exit(1);
});
