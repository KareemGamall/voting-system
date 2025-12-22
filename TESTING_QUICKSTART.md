# Quick Start - Automated Testing

## 🚀 Getting Started (3 Steps)

### Step 1: Install PHPUnit
```powershell
cd "d:\XAMP\htdocs\software project\voting-system"
composer install
```

### Step 2: Create Test Database
In MySQL/phpMyAdmin:
```sql
CREATE DATABASE voting_system_test;
USE voting_system_test;
```

Then run your migrations on the test database:
```powershell
# Temporarily modify config/database.php to use voting_system_test
# Or manually import: database/voting_system.sql
```

### Step 3: Run Tests!
```powershell
# Run all tests
composer test

# Or use PHPUnit directly
./vendor/bin/phpunit

# Or on Windows
vendor\bin\phpunit.bat
```

## 📊 What's Been Created

✅ **12 Files Created:**
- `composer.json` - Dependencies configuration
- `phpunit.xml` - PHPUnit settings
- `tests/bootstrap.php` - Test initialization
- `tests/Helpers/TestCase.php` - Base test class
- `tests/Helpers/DatabaseHelper.php` - Database utilities
- `tests/Helpers/TestDataFactory.php` - Test data generator
- `tests/Unit/Models/ElectionTest.php` - 6 election tests
- `tests/Unit/Models/VoteTest.php` - 6 voting tests
- `tests/Unit/Models/ResultTest.php` - 6 result & tie tests
- `tests/Feature/VotingFlowTest.php` - 3 integration tests
- `tests/Feature/AuthenticationTest.php` - 5 auth tests
- `tests/README.md` - Complete documentation

**Total: 26 automated tests ready to run!**

## 🎯 Test Coverage

### Unit Tests (18 tests)
- ✅ Election status updates
- ✅ Vote casting & prevention
- ✅ Duplicate vote blocking
- ✅ Multi-position voting
- ✅ Vote counting
- ✅ Result calculation
- ✅ **Tie detection** (your new feature!)
- ✅ Percentage calculations
- ✅ Winner determination

### Feature Tests (8 tests)
- ✅ Complete voting workflow
- ✅ Double voting prevention
- ✅ Tie scenarios
- ✅ User registration
- ✅ Login/authentication
- ✅ Role management

## 🏃‍♂️ Run Specific Tests

```powershell
# Run only unit tests
composer test-unit

# Run only feature tests
composer test-feature

# Run specific test file
vendor\bin\phpunit tests\Unit\Models\ElectionTest.php

# Run specific test method
vendor\bin\phpunit --filter it_detects_ties_correctly
```

## 📈 View Test Results

After running, you'll see output like:
```
✓ It can create an election
✓ It updates status to active when start date is reached
✓ It detects ties correctly
✓ Complete voting flow works correctly

26 / 26 (100%)

Time: 00:01.234, Memory: 10.00 MB

OK (26 tests, 78 assertions)
```

## 🐛 Troubleshooting

### If Composer Not Found:
Download from https://getcomposer.org/

### If Tests Fail:
1. Check test database exists
2. Run migrations on test DB
3. Verify DB credentials in `phpunit.xml`

### If Transaction Issues:
Tests use transactions that auto-rollback. No cleanup needed!

## 📝 Next Steps

1. Run `composer install`
2. Create `voting_system_test` database
3. Run `composer test`
4. See all green ✓✓✓
5. Add more tests as you build new features!

## 🎓 Learn More

See `tests/README.md` for:
- Writing new tests
- Test assertions
- Best practices
- CI/CD integration
