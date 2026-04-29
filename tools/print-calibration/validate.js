#!/usr/bin/env node

const puppeteer = require('puppeteer');
const fs = require('fs');
const path = require('path');

const A4_WIDTH_PX = 794;
const A4_HEIGHT_PX = 1123;
const PX_TO_MM = 25.4 / 96;

const round = (value) => Math.round(value * 10) / 10;

const pxToMm = (px) => round(px * PX_TO_MM);

const getLayoutFromArgs = () => {
  const arg = process.argv.find((a) => a.startsWith('--layout='));
  const layoutAlias = arg ? arg.split('=')[1] : 'v1';

  if (layoutAlias === 'v1' || layoutAlias === 'v1.0') {
    return { layoutAlias: 'v1', layoutVersion: 'v1.0' };
  }

  return { layoutAlias, layoutVersion: layoutAlias };
};

const getCaseFromArgs = () => {
  const arg = process.argv.find((a) => a.startsWith('--case='));
  return arg ? arg.split('=')[1] : 'baseline';
};

const loadMetrics = (layoutAlias) => {
  const metricsPath = path.join(__dirname, `metrics.${layoutAlias}.json`);

  if (!fs.existsSync(metricsPath)) {
    console.error(`Metrics file not found: ${metricsPath}`);
    console.error('Run calibration first: node calibrate.js');
    process.exit(1);
  }

  const data = fs.readFileSync(metricsPath, 'utf-8');
  return JSON.parse(data);
};

const assertMetricsConsistency = (metrics, layoutAlias, layoutVersion) => {
  const expectedMetricsVersion = `metrics-${layoutAlias}`;

  if (metrics.layoutVersion !== layoutVersion) {
    console.error(`Layout mismatch: expected ${layoutVersion}, got ${metrics.layoutVersion}`);
    process.exit(1);
  }

  if (metrics.metricsVersion !== expectedMetricsVersion) {
    console.error(`Metrics version mismatch: expected ${expectedMetricsVersion}, got ${metrics.metricsVersion}`);
    process.exit(1);
  }
};

const measureTemplate = async (browser, templatePath) => {
  const page = await browser.newPage();
  await page.setViewport({ width: A4_WIDTH_PX, height: A4_HEIGHT_PX });

  await page.goto(`file://${templatePath}`, { waitUntil: 'networkidle0' });
  await page.evaluateHandle('document.fonts.ready');

  const heightPx = await page.evaluate(() => {
    const el = document.getElementById('test-block');
    if (!el) return 0;
    return el.getBoundingClientRect().height;
  });

  await page.close();
  return pxToMm(heightPx);
};

const validateBaseline = async (browser, templatesDir, metrics, acceptableDriftMm) => {
  const templates = [
    { id: 'mcq_2_choices', file: 'mcq-2-choices.html' },
    { id: 'mcq_4_choices', file: 'mcq-4-choices.html' },
    { id: 'mcq_6_choices', file: 'mcq-6-choices.html' },
    { id: 'essay', file: 'essay.html' },
    { id: 'long_text', file: 'long-text.html' },
    { id: 'rtl_arabic', file: 'rtl-arabic.html' },
    { id: 'image_block', file: 'image-block.html' }
  ];

  let allPassed = true;

  for (const template of templates) {
    const templatePath = path.join(templatesDir, template.file);
    const expectedHeight = metrics.values[template.id];

    if (!expectedHeight) {
      console.warn(`⚠️  No calibrated value for ${template.id} — skipping`);
      continue;
    }

    const actualHeight = await measureTemplate(browser, templatePath);
    const drift = round(Math.abs(actualHeight - expectedHeight));

    const passed = drift <= acceptableDriftMm;
    allPassed = allPassed && passed;

    const status = passed ? '✅ PASS' : '❌ FAIL';
    console.log(`${status} ${template.id}: expected ${expectedHeight}mm, actual ${actualHeight}mm, drift ${drift}mm`);
  }

  return allPassed;
};

const validateMixedCase = async (browser, templatesDir, metrics, acceptableDriftMm) => {
  const mixedTemplatePath = path.join(templatesDir, 'mixed-case.html');

  const expected = metrics.values.mixed_case;

  if (!expected) {
    console.error('Missing calibrated value for mixed_case. Re-run calibration first.');
    return false;
  }

  const actual = await measureTemplate(browser, mixedTemplatePath);
  const drift = round(Math.abs(actual - expected));
  const passed = drift <= acceptableDriftMm;

  const status = passed ? '✅ PASS' : '❌ FAIL';
  console.log(`${status} mixed_case: expected ${expected}mm, actual ${actual}mm, drift ${drift}mm`);

  return passed;
};

const main = async () => {
  const { layoutAlias, layoutVersion } = getLayoutFromArgs();
  const validationCase = getCaseFromArgs();
  const acceptableDriftMm = 1.0;

  console.log('Starting metrics validation...');
  console.log(`Layout version: ${layoutVersion}`);
  console.log(`Case: ${validationCase}`);
  console.log(`Acceptable drift: ${acceptableDriftMm}mm per template\n`);

  const metrics = loadMetrics(layoutAlias);
  assertMetricsConsistency(metrics, layoutAlias, layoutVersion);
  console.log('Loaded metrics:', JSON.stringify(metrics, null, 2));
  console.log();

  const browser = await puppeteer.launch({ headless: true });

  const templatesDir = path.join(__dirname, 'templates');
  let allPassed = false;

  if (validationCase === 'mixed') {
    allPassed = await validateMixedCase(browser, templatesDir, metrics, acceptableDriftMm);
  } else {
    allPassed = await validateBaseline(browser, templatesDir, metrics, acceptableDriftMm);
  }

  await browser.close();

  console.log();
  if (allPassed) {
    console.log('✅ All metrics validated successfully.');
    console.log('Metrics are ready to be frozen and bound to layout version.');
  } else {
    console.log('❌ Validation failed.');
    console.log('Some metrics exceed acceptable drift. Recalibrate with: node calibrate.js');
    process.exit(1);
  }
};

main().catch(err => {
  console.error('Validation failed:', err);
  process.exit(1);
});
