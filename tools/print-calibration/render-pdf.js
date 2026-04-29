#!/usr/bin/env node

const fs = require('fs');
const path = require('path');
const puppeteer = require('puppeteer');

const argMap = Object.fromEntries(
  process.argv
    .slice(2)
    .filter((arg) => arg.startsWith('--'))
    .map((arg) => {
      const [key, ...rest] = arg.slice(2).split('=');
      return [key, rest.join('=')];
    })
);

const inputPath = argMap.input;
const outputPath = argMap.output;
const maxPages = Number(argMap.maxPages || 60);

if (!inputPath || !outputPath) {
  console.error('Missing required args. Usage: node render-pdf.js --input=... --output=... [--maxPages=60]');
  process.exit(1);
}

if (!fs.existsSync(inputPath)) {
  console.error(`Input HTML not found: ${inputPath}`);
  process.exit(1);
}

(async () => {
  const browser = await puppeteer.launch({ headless: true });

  try {
    const page = await browser.newPage();
    await page.setViewport({ width: 794, height: 1123 });

    const html = fs.readFileSync(inputPath, 'utf-8');
    await page.setContent(html, { waitUntil: 'networkidle0' });
    await page.evaluateHandle('document.fonts.ready');

    const estimatedPages = await page.evaluate(() => {
      const pxPerMm = 96 / 25.4;
      const marginMm = 12;
      const printableHeight = 1123 - (marginMm * pxPerMm * 2);
      const bodyHeight = Math.max(
        document.body?.scrollHeight || 0,
        document.documentElement?.scrollHeight || 0
      );

      if (printableHeight <= 0) return 1;
      return Math.max(1, Math.ceil(bodyHeight / printableHeight));
    });

    if (estimatedPages > maxPages) {
      throw new Error(`Estimated page count ${estimatedPages} exceeded maxPages=${maxPages}`);
    }

    await page.pdf({
      path: outputPath,
      format: 'A4',
      printBackground: true,
      preferCSSPageSize: true,
      scale: 1
    });

    const browserVersion = await browser.version();
    const meta = {
      puppeteerVersion: require('puppeteer/package.json').version,
      chromiumVersion: browserVersion.split('/')[1] || 'unknown',
      estimatedPages,
      outputPath: path.resolve(outputPath)
    };

    process.stdout.write(JSON.stringify(meta));
  } finally {
    await browser.close();
  }
})().catch((err) => {
  console.error(err?.message || String(err));
  process.exit(1);
});
