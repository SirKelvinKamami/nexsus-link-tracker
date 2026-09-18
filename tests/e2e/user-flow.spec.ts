import { expect, test } from '@playwright/test';

/**
 * The exact user flow that repeatedly broke in production, now verified in
 * a real browser against a real (sqlite) Laravel server on every run:
 *
 *   signup -> auto-login -> dashboard -> pick a link block -> create a link
 *   -> share page renders the link
 *
 * Selectors match the blade views: register form (#name, #littlelink_name,
 * #email, #password), the block-type modal on the add-link page, and the
 * fields injected from blocks/link/form.blade.php.
 */
const runId = Date.now().toString(36);
const handle = `e2e${runId}`;
const linkTitle = 'E2E Test Link';
const linkUrl = `https://example.com/e2e-${runId}`;

test('signup, create a link, and see it on the share page', async ({ page }) => {
    // --- 1. Sign up (ALLOW_REGISTRATION=true; auto-login on success) ---
    await page.goto('/register');
    await expect(page).toHaveTitle(/Nexsus/i);

    await page.fill('#name', 'E2E Browser User');
    await page.fill('#littlelink_name', handle);
    await page.fill('#email', `${handle}@example.test`);
    await page.fill('#password', 'e2ePassword1');
    await page.click('form:has(#email) button[type="submit"]');

    await page.waitForURL(/dashboard/, { timeout: 30_000 });
    await expect(page.locator('body')).toContainText('E2E Browser User');

    // --- 2. Open the add-link studio page and pick the "link" block ---
    await page.goto('/studio/add-link');
    await expect(page.locator('#btnLinkType')).toBeVisible();

    await page.click('#btnLinkType');
    const linkTypeOption = page.locator('.doSelectLinkType[data-typeid="link"]');
    await expect(linkTypeOption).toBeVisible();
    await linkTypeOption.click();

    // The typed form fields are injected via AJAX into #link_params.
    const titleField = page.locator("#link_params input[name='title']");
    await expect(titleField).toBeVisible({ timeout: 15_000 });
    await titleField.fill(linkTitle);
    await page.fill("#link_params input[name='link']", linkUrl);

    await page.click('#my-form button[type="submit"]');
    await page.waitForURL(/studio\/links/, { timeout: 30_000 });
    await expect(page.locator('body')).toContainText(linkTitle);

    // --- 3. The public share page renders the created link ---
    await page.goto(`/@${handle}`);
    await expect(page).toHaveTitle(new RegExp(handle, 'i'));
    await expect(page.locator('body')).toContainText(linkTitle);
    const shareLink = page.locator(`a[href="${linkUrl}"]`);
    await expect(shareLink.first()).toBeVisible();
});
