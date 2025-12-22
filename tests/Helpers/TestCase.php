<?php

namespace Tests\Helpers;

use PHPUnit\Framework\TestCase as BaseTestCase;

/**
 * Base Test Case
 * Provides common test utilities
 */
abstract class TestCase extends BaseTestCase
{
    protected $dbHelper;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->dbHelper = new DatabaseHelper();
        $this->dbHelper->beginTransaction();
    }
    
    protected function tearDown(): void
    {
        $this->dbHelper->rollback();
        parent::tearDown();
    }
    
    /**
     * Assert that database has record
     */
    protected function assertDatabaseHas(string $table, array $data): void
    {
        $this->assertTrue(
            $this->dbHelper->hasRecord($table, $data),
            "Failed asserting that table '{$table}' has record with: " . json_encode($data)
        );
    }
    
    /**
     * Assert that database doesn't have record
     */
    protected function assertDatabaseMissing(string $table, array $data): void
    {
        $this->assertFalse(
            $this->dbHelper->hasRecord($table, $data),
            "Failed asserting that table '{$table}' doesn't have record with: " . json_encode($data)
        );
    }
    
    /**
     * Get count of records in table
     */
    protected function getRecordCount(string $table, array $where = []): int
    {
        return $this->dbHelper->countRecords($table, $where);
    }
}
