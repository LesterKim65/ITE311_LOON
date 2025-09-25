# Task: Fix Dashboard "Whoops!" Error

## Steps to Complete

- [ ] Step 1: Edit `app/Controllers/Auth.php` to fix view path from 'auth/dashboard' to 'dashboard' and add try-catch error handling around database queries to prevent failures if tables/data missing.
- [ ] Step 2: Run migrations to ensure all tables (users, courses, enrollments, etc.) are created: `php spark migrate`.
- [ ] Step 3: Run UserSeeder to populate initial user data: `php spark db:seed UserSeeder`.
- [ ] Step 4: Test the dashboard by logging in and accessing `/dashboard` (create a test user via register if needed). Verify no "Whoops!" error and role-based content displays (even if empty).
- [ ] Step 5: If queries still fail (e.g., no sample courses/enrollments), add basic seed data for courses/enrollments or create a new seeder.

After completing all steps, the dashboard should load successfully.
