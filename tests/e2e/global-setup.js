import { resetDatabase, seedTestData } from './helpers/db.js';

/**
 * Global setup - runs once before all tests
 * This resets the database once instead of before each test file
 */
export default async function globalSetup() {
  console.log('\n🔄 Setting up test database...');

  try {
    await resetDatabase();
    await seedTestData();
    console.log('✅ Test database ready\n');
  } catch (error) {
    console.error('❌ Failed to set up test database:', error);
    throw error;
  }
}
