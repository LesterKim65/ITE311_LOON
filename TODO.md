# Fix Registration as Teacher

## Completed Steps
- [x] Identified mismatch: form uses 'teacher', backend uses 'instructor'
- [x] Updated migration to use 'teacher' in ENUM
- [x] Updated seeder to use 'teacher' and changed name/email
- [x] Altered existing database table to change ENUM to 'teacher'
- [x] Validation in Auth.php already uses 'teacher'

## Next Steps
- [ ] Test registration by going to /register and selecting teacher role
- [ ] If seeder error persists, truncate users table and re-seed
- [ ] Verify login and dashboard for teacher role
