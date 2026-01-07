# Task Fiend E2E Tests

This directory contains end-to-end tests for Task Fiend using Playwright. These tests focus heavily on authorization and privacy to ensure users can only see and interact with data they have permission to access.

## Test Coverage

### Task Authorization Tests (`task-authorization.spec.js`)
Tests that verify task privacy and authorization rules:

- ✅ Users can only see their own tasks in task lists
- ✅ Users can see tasks they are assigned to
- ✅ Users cannot access task detail pages for unauthorized tasks
- ✅ Users cannot edit tasks they don't own
- ✅ Task creators can add and remove assignees
- ✅ Assignees have proper permissions on assigned tasks
- ✅ Users cannot see unassigned tasks in search results
- ✅ Inbox view only shows user's own tasks
- ✅ Today view only shows user's tasks due today

### Project Authorization Tests (`project-authorization.spec.js`)
Tests that verify project privacy and authorization rules:

- ✅ Users can only see their own projects in project lists
- ✅ Users can see projects they are assigned to
- ✅ Users cannot access project detail pages for unauthorized projects
- ✅ Users cannot edit projects they don't own
- ✅ Project creators can add and remove assignees
- ✅ Assignees can view assigned projects
- ✅ Tasks in projects inherit project visibility
- ✅ Users cannot create tasks in projects they can't access
- ✅ Users cannot see other users' projects in search
- ✅ Removing user from project hides it from their view

### Tag Visibility Tests (`tag-visibility.spec.js`)
Tests that verify tags are globally accessible:

- ✅ Tags created by one user are visible to all users
- ✅ All users can view tag details
- ✅ All users can edit any tag
- ✅ Users can use tags created by other users on their tasks
- ✅ Tag list shows tags from all users
- ✅ Tags appear in search for all users
- ✅ Tag changes are visible to all users
- ✅ **BUT** users still cannot see tasks tagged with tags if not authorized

## Setup

### Prerequisites

1. Node.js and npm installed
2. PHP and Composer installed
3. Laravel application set up with database configured

### Install Playwright

Playwright is already added to the project. To install browsers:

```bash
npx playwright install
```

### Database Setup

The tests automatically:
- Reset the database before running (`migrate:fresh`)
- Seed test users (user1@test.com, user2@test.com, user3@test.com)
- Clean up after tests

⚠️ **WARNING**: Tests will wipe your database! Use a separate test database.

## Running Tests

### Run all E2E tests

```bash
npm run test:e2e
```

Or using Playwright directly:

```bash
npx playwright test
```

### Run specific test file

```bash
npx playwright test task-authorization
npx playwright test project-authorization
npx playwright test tag-visibility
```

### Run tests in UI mode (interactive)

```bash
npx playwright test --ui
```

### Run tests in headed mode (see browser)

```bash
npx playwright test --headed
```

### Run tests in a specific browser

```bash
npx playwright test --project=chromium
npx playwright test --project=firefox
npx playwright test --project=webkit
```

### Debug tests

```bash
npx playwright test --debug
```

## Test Results

### View HTML Report

After tests run, view the HTML report:

```bash
npx playwright show-report
```

### View Traces

If a test fails, Playwright captures a trace. View it:

```bash
npx playwright show-trace trace.zip
```

## Configuration

### Playwright Config

Main configuration is in `playwright.config.js` at the project root.

Key settings:
- Base URL: `http://localhost:8000`
- Test directory: `./tests/e2e`
- Automatically starts Laravel dev server before tests
- Captures screenshots on failure
- Captures traces on first retry

### Test Database

**Important**: Configure a separate test database in `.env.testing`:

```env
DB_CONNECTION=sqlite
DB_DATABASE=/path/to/test-database.sqlite
```

Or use an in-memory SQLite database:

```env
DB_CONNECTION=sqlite
DB_DATABASE=:memory:
```

## Writing New Tests

### Test Structure

```javascript
import { test, expect } from '@playwright/test';
import { resetDatabase, seedTestData } from './helpers/db.js';
import { login, logout, testUsers } from './helpers/auth.js';

test.describe('Feature Name', () => {
  test.beforeAll(async () => {
    await resetDatabase();
    await seedTestData();
  });

  test('test description', async ({ page }) => {
    await login(page, testUsers.user1.email);
    // Your test code here
  });
});
```

### Available Helpers

#### Database Helpers (`./helpers/db.js`)

- `resetDatabase()` - Wipe and migrate database
- `seedTestData()` - Create test users
- `cleanDatabase()` - Clean up database
- `artisan(command)` - Run artisan commands

#### Auth Helpers (`./helpers/auth.js`)

- `login(page, email, password)` - Log in a user
- `logout(page)` - Log out current user
- `setupAuthenticatedSession(page, email, password)` - Set up authenticated session
- `testUsers` - Object with test user credentials
  - `testUsers.user1` - { email, password, name }
  - `testUsers.user2` - { email, password, name }
  - `testUsers.user3` - { email, password, name }

### Test User Credentials

All test users use password: `password123`

- user1@test.com - User One
- user2@test.com - User Two
- user3@test.com - User Three

## CI/CD Integration

### GitHub Actions

Add to `.github/workflows/playwright.yml`:

```yaml
name: Playwright Tests
on: [push, pull_request]
jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      - uses: actions/setup-node@v3
      - name: Install dependencies
        run: npm ci
      - name: Install Playwright
        run: npx playwright install --with-deps
      - name: Run Playwright tests
        run: npx playwright test
      - uses: actions/upload-artifact@v3
        if: always()
        with:
          name: playwright-report
          path: playwright-report/
```

## Troubleshooting

### Tests fail with "Cannot connect to server"

Ensure Laravel dev server is running:

```bash
php artisan serve
```

Or let Playwright start it automatically (configured in `playwright.config.js`).

### Database permission errors

Ensure test database file is writable:

```bash
chmod 666 database/test-database.sqlite
```

### Test timeout errors

Increase timeout in `playwright.config.js`:

```javascript
timeout: 30000, // 30 seconds
```

### Need to see what's happening

Run tests in headed mode:

```bash
npx playwright test --headed --slowmo=1000
```

## Best Practices

1. **Isolation**: Each test should be independent and not rely on other tests
2. **Cleanup**: Use `beforeAll` and `afterAll` hooks to set up and clean up
3. **Selectors**: Use semantic selectors (text, role) over CSS selectors when possible
4. **Assertions**: Use Playwright's built-in assertions for better error messages
5. **Wait**: Use `waitForURL`, `waitForSelector` instead of arbitrary timeouts
6. **Debug**: Use `page.pause()` to debug tests interactively

## Additional Resources

- [Playwright Documentation](https://playwright.dev)
- [Playwright Best Practices](https://playwright.dev/docs/best-practices)
- [Playwright API Reference](https://playwright.dev/docs/api/class-playwright)
