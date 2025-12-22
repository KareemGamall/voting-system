# Voting System - Automated Tests

## Setup

### 1. Install Dependencies
```bash
composer install
```

### 2. Create Test Database
Create a separate test database to avoid affecting your development data:

```sql
CREATE DATABASE voting_system_test;
```

Then run the migrations on the test database:
```bash
php database/migrate.php
```
(Make sure to temporarily change the database name in config/database.php or use the test environment)

### 3. Configure Test Database
Edit `phpunit.xml` if needed to set your test database credentials:
```xml
<env name="DB_HOST" value="localhost"/>
<env name="DB_NAME" value="voting_system_test"/>
<env name="DB_USER" value="root"/>
<env name="DB_PASS" value=""/>
```

## Running Tests

### Run All Tests
```bash
composer test
# or
./vendor/bin/phpunit
```

### Run Specific Test Suite
```bash
# Unit tests only
composer test-unit

# Feature tests only
composer test-feature
```

### Run Specific Test File
```bash
./vendor/bin/phpunit tests/Unit/Models/ElectionTest.php
```

### Run Specific Test Method
```bash
./vendor/bin/phpunit --filter it_can_cast_a_vote
```

### Generate Coverage Report
```bash
composer test-coverage
# Opens coverage/index.html
```

## Test Structure

```
tests/
├── bootstrap.php              # Test initialization
├── Helpers/
│   ├── TestCase.php          # Base test class
│   ├── DatabaseHelper.php    # Database utilities
│   └── TestDataFactory.php   # Test data generation
├── Unit/                      # Unit tests
│   └── Models/
│       ├── ElectionTest.php  # Election model tests
│       ├── VoteTest.php      # Vote model tests
│       └── ResultTest.php    # Result model tests
└── Feature/                   # Integration tests
    ├── VotingFlowTest.php    # Complete voting flow
    └── AuthenticationTest.php # Auth flow
```

## Test Coverage

### Unit Tests
- **ElectionTest**: Election status updates, CRUD operations
- **VoteTest**: Vote casting, duplicate prevention, vote counting
- **ResultTest**: Result calculation, tie detection, percentage calculation

### Feature Tests
- **VotingFlowTest**: Complete voting workflow from election creation to results
- **AuthenticationTest**: User registration, login, role management

## Key Test Scenarios

### ✅ Election Management
- Create elections with different statuses
- Auto-update status based on dates
- Retrieve elections by status

### ✅ Voting
- Cast votes successfully
- Prevent duplicate votes for same candidate
- Allow voting for multiple positions
- Count votes accurately

### ✅ Results & Ties
- Calculate results correctly
- Detect tied candidates
- Return null winner when tied
- Calculate accurate percentages

### ✅ Authentication
- User registration
- Login validation
- Admin vs Voter roles

## Writing New Tests

### 1. Unit Test Example
```php
<?php
namespace Tests\Unit\Models;

use Tests\Helpers\TestCase;
use Tests\Helpers\TestDataFactory;

class MyTest extends TestCase
{
    /** @test */
    public function it_does_something()
    {
        // Arrange
        $data = TestDataFactory::makeElection();
        
        // Act
        $result = $model->doSomething($data);
        
        // Assert
        $this->assertTrue($result);
        $this->assertDatabaseHas('table', ['key' => 'value']);
    }
}
```

### 2. Use Test Data Factory
```php
// Create test users
$user = TestDataFactory::makeUser();
$admin = TestDataFactory::makeUser(['is_admin' => 1]);

// Create test elections
$upcoming = TestDataFactory::makeElection();
$active = TestDataFactory::makeActiveElection();
$completed = TestDataFactory::makeCompletedElection();

// Create test candidates
$candidate = TestDataFactory::makeCandidate($electionId);
$candidates = TestDataFactory::makeCandidates($electionId, 3);
```

## Assertions

```php
// Database assertions
$this->assertDatabaseHas('table', ['column' => 'value']);
$this->assertDatabaseMissing('table', ['column' => 'value']);

// Count records
$count = $this->getRecordCount('table', ['where' => 'clause']);
$this->assertEquals(5, $count);

// Standard PHPUnit assertions
$this->assertTrue($condition);
$this->assertFalse($condition);
$this->assertEquals($expected, $actual);
$this->assertNull($value);
$this->assertNotNull($value);
$this->assertCount(3, $array);
```

## CI/CD Integration

Add to `.github/workflows/tests.yml`:
```yaml
name: Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '7.4'
      - name: Install dependencies
        run: composer install
      - name: Run tests
        run: composer test
```

## Troubleshooting

### Database Connection Issues
- Ensure test database exists
- Check credentials in `phpunit.xml`
- Verify migrations are run on test database

### Transaction Rollback Issues
- Tests automatically rollback after each test
- If you see duplicate data, check that transactions are working

### Namespace Issues
- Ensure autoload is configured in `composer.json`
- Run `composer dump-autoload`

## Best Practices

1. ✅ Each test should be independent
2. ✅ Use descriptive test names: `it_does_something`
3. ✅ Follow Arrange-Act-Assert pattern
4. ✅ Test one thing per test method
5. ✅ Use factories for test data
6. ✅ Clean up in tearDown()
7. ✅ Don't test framework code, test your logic

## Next Steps

- Add more edge case tests
- Test controller actions
- Add API endpoint tests
- Increase code coverage
- Add performance tests
