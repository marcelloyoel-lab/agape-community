import { chromium } from "playwright";
import fs from "node:fs/promises";

const [htmlPath, outputPath] = process.argv.slice(2);

if (!htmlPath || !outputPath) {
    console.error(
        "Usage: node scripts/generate-poster.mjs <html-path> <output-path>",
    );

    process.exit(1);
}

let browser;

try {
    const html = await fs.readFile(htmlPath, "utf8");

    browser = await chromium.launch({
        headless: true,
    });

    const page = await browser.newPage({
        viewport: {
            width: 1080,
            height: 1350,
        },
        deviceScaleFactor: 2,
    });

    await page.setContent(html, {
        waitUntil: "networkidle",
    });

    const poster = page.locator(".poster");

    await poster.waitFor({
        state: "visible",
    });

    await poster.screenshot({
        path: outputPath,
        type: "jpeg",
        quality: 95,
    });

    console.log(`Poster generated: ${outputPath}`);
} catch (error) {
    console.error("Poster generation failed:", error);

    process.exitCode = 1;
} finally {
    if (browser) {
        await browser.close();
    }
}
