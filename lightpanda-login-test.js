import { chromium } from 'playwright';
import { spawn } from 'child_process';
import path from 'path';

(async () => {
  console.log('Starting Lightpanda server...');
  
  // Start the Lightpanda CDP server
  const lightpandaProcess = spawn(path.join(process.cwd(), 'bin', 'lightpanda'), ['serve', '--host', '127.0.0.1', '--port', '9222'], {
    stdio: 'ignore', // ignore stdout/stderr for cleaner test output
  });

  // Give the server a moment to start
  await new Promise(resolve => setTimeout(resolve, 2000));

  try {
    console.log('Connecting to Lightpanda instance over CDP...');
    // Connect to the running Lightpanda instance
    const browser = await chromium.connectOverCDP('http://127.0.0.1:9222');
    const context = browser.contexts()[0] || await browser.newContext();
    const page = await context.newPage();

    console.log('Navigating to login page...');
    await page.goto('http://127.0.0.1:8001/login');

    console.log('Target page reached. Simulating login operations...');
    await page.fill('#email', 'tuhn06837');
    await page.fill('#password', '12345678');
    
    // Log input values to verify the form is actually being filled
    const emailValue = await page.$eval('#email', el => el.value);
    const passValue = await page.$eval('#password', el => el.value);
    console.log(`Inputs verified - Email: ${emailValue}, Password Length: ${passValue.length}`);

    console.log('Dispatching submit event to form via page.evaluate...');
    await page.evaluate(() => {
        const form = document.querySelector('form');
        if (form) {
            form.dispatchEvent(new Event('submit', { cancelable: true, bubbles: true }));
        }
    });
    
    console.log('Waiting for potential redirect or error message...');
    await page.waitForTimeout(5000); // Wait 5 seconds for processing
    
    const currentUrl = page.url();
    const pageTitle = await page.title();
    console.log(`Final URL after login attempt: ${currentUrl}`);
    console.log(`Final Page Title: ${pageTitle}`);

    if (currentUrl.includes('/login')) {
        console.log('Login failed to redirect. Checking for error messages on page...');
        const errorText = await page.evaluate(() => {
            const errorEls = Array.from(document.querySelectorAll('.text-red-600, .invalid-feedback, [role="alert"]'));
            return errorEls.map(el => el.textContent.trim()).filter(t => t.length > 0);
        });
        if (errorText.length > 0) {
            console.log(`Error messages found: ${JSON.stringify(errorText)}`);
        } else {
            console.log('No visible error messages found on page.');
        }
    } else {
        console.log('Success! Redirected away from login page.');
    }

    try {
        console.log('Attempting standard screenshot...');
        await page.screenshot({ path: 'login-result.png' });
    } catch (e) {
        console.log('Screenshot failed.');
    }

    await browser.close();
  } catch (error) {
    console.error('Test execution failed:', error.message);
  } finally {
    console.log('Shutting down Lightpanda server...');
    lightpandaProcess.kill('SIGKILL');
    process.exit(0);
  }
})();
