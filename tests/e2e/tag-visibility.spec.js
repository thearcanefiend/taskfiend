import { test, expect } from '@playwright/test';
import { login, logout, testUsers } from './helpers/auth.js';

/**
 * Tag Visibility Tests
 *
 * These tests verify that tags are globally accessible to all users.
 * Unlike tasks and projects, tags should be visible and manageable by any user.
 */

test.describe('Tag Visibility & Global Access', () => {
  test.beforeEach(async ({ page }) => {
    // Ensure clean state for each test
    await page.goto('/login');
  });

  test('tags created by one user are visible to all users', async ({ page }) => {
    // User 1 creates a tag
    await login(page, testUsers.user1.email);
    await page.goto('/tags/create');
    const uniqueTagName = `Global Tag ${Date.now()}`;
    await page.fill('input[name="name"]', uniqueTagName);
    await page.fill('textarea[name="description"]', 'This tag should be visible to all users');
    await page.click('button[type="submit"]');
    await page.waitForURL(/\/tags\/\d+/);
    await logout(page);

    // User 2 should see the tag
    await login(page, testUsers.user2.email);
    await page.goto('/tags');
    await expect(page.locator(`text=${uniqueTagName}`)).toBeVisible();
    await logout(page);

    // User 3 should also see the tag
    await login(page, testUsers.user3.email);
    await page.goto('/tags');
    await expect(page.locator(`text=${uniqueTagName}`)).toBeVisible();
  });

  test('all users can view tag details', async ({ page }) => {
    // User 1 creates a tag
    await login(page, testUsers.user1.email);
    await page.goto('/tags/create');
    await page.fill('input[name="name"]', 'Viewable Tag');
    await page.fill('textarea[name="description"]', 'Everyone should see this description');
    await page.click('button[type="submit"]');

    await page.waitForURL(/\/tags\/(\d+)/);
    const tagUrl = page.url();
    const tagId = tagUrl.match(/\/tags\/(\d+)/)[1];
    await logout(page);

    // User 2 can view the tag detail
    await login(page, testUsers.user2.email);
    await page.goto(`/tags/${tagId}`);
    await expect(page.locator('text=Viewable Tag')).toBeVisible();
    await expect(page.locator('text=Everyone should see this description')).toBeVisible();
  });

  test('all users can edit any tag', async ({ page }) => {
    // User 1 creates a tag
    await login(page, testUsers.user1.email);
    await page.goto('/tags/create');
    await page.fill('input[name="name"]', 'Editable Tag');
    await page.fill('textarea[name="description"]', 'Original description');
    await page.click('button[type="submit"]');

    await page.waitForURL(/\/tags\/(\d+)/);
    const tagUrl = page.url();
    const tagId = tagUrl.match(/\/tags\/(\d+)/)[1];
    await logout(page);

    // User 2 edits the tag
    await login(page, testUsers.user2.email);
    await page.goto(`/tags/${tagId}/edit`);

    // Should be able to access edit page
    await expect(page.locator('input[name="name"]')).toBeVisible();

    // Edit the tag
    await page.fill('textarea[name="description"]', 'Updated by User 2');
    await page.click('button[type="submit"]');

    // Verify the update
    await page.waitForURL(/\/tags\/\d+/);
    await expect(page.locator('text=Updated by User 2')).toBeVisible();
  });

  test('users can use tags created by other users on their tasks', async ({ page }) => {
    // User 1 creates a tag
    await login(page, testUsers.user1.email);
    await page.goto('/tags/create');
    const sharedTagName = `Shared Tag ${Date.now()}`;
    await page.fill('input[name="name"]', sharedTagName);
    await page.click('button[type="submit"]');
    await page.waitForURL(/\/tags\/\d+/);
    await logout(page);

    // User 2 creates a task and applies User 1's tag
    await login(page, testUsers.user2.email);
    await page.goto('/tasks/create');
    await page.fill('input[name="name"]', 'Task with Shared Tag');

    // Apply the tag created by User 1
    await page.selectOption('select[name="tags[]"]', { label: sharedTagName });
    await page.click('button[type="submit"]');

    // Verify tag is applied
    await page.waitForURL(/\/tasks\/\d+/);
    await expect(page.locator(`text=${sharedTagName}`)).toBeVisible();
  });

  test('tag list shows tags from all users', async ({ page }) => {
    // User 1 creates a tag
    await login(page, testUsers.user1.email);
    await page.goto('/tags/create');
    await page.fill('input[name="name"]', 'User 1 Tag');
    await page.click('button[type="submit"]');
    await page.waitForURL(/\/tags\/\d+/);
    await logout(page);

    // User 2 creates a tag
    await login(page, testUsers.user2.email);
    await page.goto('/tags/create');
    await page.fill('input[name="name"]', 'User 2 Tag');
    await page.click('button[type="submit"]');
    await page.waitForURL(/\/tags\/\d+/);

    // User 2 should see both tags
    await page.goto('/tags');
    await expect(page.locator('text=User 1 Tag')).toBeVisible();
    await expect(page.locator('text=User 2 Tag')).toBeVisible();
    await logout(page);

    // User 3 should also see both tags
    await login(page, testUsers.user3.email);
    await page.goto('/tags');
    await expect(page.locator('text=User 1 Tag')).toBeVisible();
    await expect(page.locator('text=User 2 Tag')).toBeVisible();
  });

  test('tags appear in search for all users', async ({ page }) => {
    // User 1 creates a searchable tag
    await login(page, testUsers.user1.email);
    await page.goto('/tags/create');
    const searchableTag = `Searchable ${Date.now()}`;
    await page.fill('input[name="name"]', searchableTag);
    await page.fill('textarea[name="description"]', 'Should appear in all user searches');
    await page.click('button[type="submit"]');
    await page.waitForURL(/\/tags\/\d+/);
    await logout(page);

    // User 2 searches for the tag
    await login(page, testUsers.user2.email);
    await page.goto('/search');
    await page.fill('input[name="query"]', searchableTag);
    await page.click('button[type="submit"]');

    // Tag should appear in search results
    await expect(page.locator(`text=${searchableTag}`)).toBeVisible();
  });

  test('deleting a tag affects all users (global impact)', async ({ page }) => {
    // User 1 creates a tag
    await login(page, testUsers.user1.email);
    await page.goto('/tags/create');
    const deletableTag = `Deletable ${Date.now()}`;
    await page.fill('input[name="name"]', deletableTag);
    await page.click('button[type="submit"]');

    await page.waitForURL(/\/tags\/(\d+)/);
    const tagUrl = page.url();
    const tagId = tagUrl.match(/\/tags\/(\d+)/)[1];
    await logout(page);

    // User 2 verifies tag exists
    await login(page, testUsers.user2.email);
    await page.goto('/tags');
    await expect(page.locator(`text=${deletableTag}`)).toBeVisible();
    await logout(page);

    // User 3 deletes the tag (if deletion is allowed)
    await login(page, testUsers.user3.email);
    await page.goto(`/tags/${tagId}/edit`);

    // Attempt to delete (if there's a delete button)
    const deleteButton = page.locator('button:has-text("Delete"), form[method="post"]:has(input[name="_method"][value="delete"]) button');
    const hasDeleteButton = await deleteButton.count() > 0;

    if (hasDeleteButton) {
      // Note: Per spec, deletion might not be allowed (only archiving)
      // But testing the global impact if deletion exists
      await deleteButton.click();
      await page.waitForURL(/\/tags/);

      // User 2 should no longer see the tag
      await logout(page);
      await login(page, testUsers.user2.email);
      await page.goto('/tags');
      await expect(page.locator(`text=${deletableTag}`)).not.toBeVisible();
    }
  });

  test('tag changes made by one user are visible to all users', async ({ page }) => {
    // User 1 creates a tag
    await login(page, testUsers.user1.email);
    await page.goto('/tags/create');
    await page.fill('input[name="name"]', 'Mutable Tag');
    await page.fill('textarea[name="description"]', 'Original description');
    await page.click('button[type="submit"]');

    await page.waitForURL(/\/tags\/(\d+)/);
    const tagUrl = page.url();
    const tagId = tagUrl.match(/\/tags\/(\d+)/)[1];
    await logout(page);

    // User 2 edits the tag description
    await login(page, testUsers.user2.email);
    await page.goto(`/tags/${tagId}/edit`);
    await page.fill('textarea[name="description"]', 'Modified by User 2');
    await page.click('button[type="submit"]');
    await logout(page);

    // User 1 should see the changes made by User 2
    await login(page, testUsers.user1.email);
    await page.goto(`/tags/${tagId}`);
    await expect(page.locator('text=Modified by User 2')).toBeVisible();
    await expect(page.locator('text=Original description')).not.toBeVisible();
  });

  test('but users still cannot see tasks tagged with tags if not authorized', async ({ page }) => {
    // User 1 creates a tag
    await login(page, testUsers.user1.email);
    await page.goto('/tags/create');
    const tagName = `Privacy Test Tag ${Date.now()}`;
    await page.fill('input[name="name"]', tagName);
    await page.click('button[type="submit"]');
    await page.waitForURL(/\/tags\/\d+/);

    // User 1 creates a private task with this tag
    await page.goto('/tasks/create');
    await page.fill('input[name="name"]', 'Private Task with Tag');
    await page.selectOption('select[name="tags[]"]', { label: tagName });
    await page.click('button[type="submit"]');
    await page.waitForURL(/\/tasks\/\d+/);
    await logout(page);

    // User 2 can see the tag exists
    await login(page, testUsers.user2.email);
    await page.goto('/tags');
    await expect(page.locator(`text=${tagName}`)).toBeVisible();

    // But User 2 should NOT see User 1's private task
    await page.goto('/tasks');
    await expect(page.locator('text=Private Task with Tag')).not.toBeVisible();

    // And searching for the tag should not reveal User 1's task to User 2
    await page.goto('/search');
    await page.fill('input[name="query"]', tagName);
    await page.click('button[type="submit"]');

    // User 2 might see the tag itself in results, but not User 1's private task
    await expect(page.locator('text=Private Task with Tag')).not.toBeVisible();
  });
});
