<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Integration tests for the Student CV removal flow.
 *
 * These tests run against the local development MySQL database and the real
 * storage directory (app/storage/uploads). Every fixture row and physical
 * file is created with a unique marker and removed in tearDown().
 */
final class CVRemovalTest extends TestCase
{
    private array $createdUsers = [];
    private array $createdStudents = [];
    private array $createdFiles = [];
    private array $createdPaths = [];
    private string $storageBase;

    protected function setUp(): void
    {
        $this->storageBase = file_upload_service_storage_base();
    }

    protected function tearDown(): void
    {
        $db = get_database_connection();

        foreach ($this->createdFiles as $id) {
            $stmt = $db->prepare('DELETE FROM files WHERE id = ?');
            $stmt->execute([$id]);
        }

        foreach ($this->createdStudents as $id) {
            $stmt = $db->prepare('DELETE FROM students WHERE id = ?');
            $stmt->execute([$id]);
        }

        foreach ($this->createdUsers as $id) {
            $stmt = $db->prepare('DELETE FROM users WHERE id = ?');
            $stmt->execute([$id]);
        }

        foreach ($this->createdPaths as $path) {
            if (is_file($path)) {
                @unlink($path);
            }
        }
    }

    private function makeUser(): int
    {
        $db = get_database_connection();
        $email = 'cvtest.' . bin2hex(random_bytes(6)) . '@test.local';
        $stmt = $db->prepare(
            "INSERT INTO users (role, email, password_hash, status) VALUES ('student', ?, 'unused', 'active')"
        );
        $stmt->execute([$email]);
        $id = (int) $db->lastInsertId();
        $this->createdUsers[] = $id;

        return $id;
    }

    private function makeStudent(int $userId, string $name = 'CV Test Student'): int
    {
        $db = get_database_connection();
        $stmt = $db->prepare(
            'INSERT INTO students (user_id, full_name) VALUES (?, ?)'
        );
        $stmt->execute([$userId, $name]);
        $id = (int) $db->lastInsertId();
        $this->createdStudents[] = $id;

        return $id;
    }

    private function makePhysicalFile(string $subdir, string $storedName, string $content = 'fake cv'): string
    {
        $dir = $this->storageBase . DIRECTORY_SEPARATOR . $subdir;

        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $path = $dir . DIRECTORY_SEPARATOR . $storedName;
        file_put_contents($path, $content);
        $this->createdPaths[] = $path;

        return $path;
    }

    private function makeFileRow(int $userId, string $path, string $type = 'cv'): int
    {
        $db = get_database_connection();
        $storedName = basename($path);
        $stmt = $db->prepare(
            'INSERT INTO files (user_id, type, original_name, stored_name, path, mime_type, size_bytes)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $userId,
            $type,
            $storedName,
            $storedName,
            $path,
            'application/pdf',
            is_file($path) ? filesize($path) : 1,
        ]);
        $id = (int) $db->lastInsertId();
        $this->createdFiles[] = $id;

        return $id;
    }

    private function setCv(int $studentId, int $fileId): void
    {
        $db = get_database_connection();
        $stmt = $db->prepare('UPDATE students SET cv_file_id = ? WHERE id = ?');
        $stmt->execute([$fileId, $studentId]);
    }

    private function cvOf(int $studentId): ?int
    {
        return student_profile_repository_get_cv_file_id($studentId);
    }

    private function fileRowExists(int $fileId): bool
    {
        return file_repository_find($fileId) !== null;
    }

    public function testSuccessfulDeletionRemovesFileAndMetadata(): void
    {
        $userId = $this->makeUser();
        $studentId = $this->makeStudent($userId);
        $path = $this->makePhysicalFile('cvtest', 'cv_success_' . bin2hex(random_bytes(4)) . '.pdf');
        $fileId = $this->makeFileRow($userId, $path);
        $this->setCv($studentId, $fileId);

        $result = student_profile_remove_cv($studentId);

        $this->assertArrayNotHasKey('error', $result);
        $this->assertSame('CV removed successfully.', $result['data']['message']);
        $this->assertFileDoesNotExist($path, 'The physical CV file must be removed from storage.');
        $this->assertFalse($this->fileRowExists($fileId), 'The files record must be deleted.');
        $this->assertNull($this->cvOf($studentId), 'The student CV reference must be cleared.');
    }

    public function testNoCvReturns404(): void
    {
        $userId = $this->makeUser();
        $studentId = $this->makeStudent($userId);

        $result = student_profile_remove_cv($studentId);

        $this->assertSame(true, $result['error'] ?? null);
        $this->assertSame(404, $result['status'] ?? null);
        $this->assertSame('No CV found.', $result['message'] ?? null);
    }

    public function testMissingPhysicalFileStillCleansMetadata(): void
    {
        $userId = $this->makeUser();
        $studentId = $this->makeStudent($userId);

        // The files row exists but the physical file was already removed.
        $missingPath = $this->storageBase . DIRECTORY_SEPARATOR . 'cvtest' . DIRECTORY_SEPARATOR
            . 'cv_gone_' . bin2hex(random_bytes(4)) . '.pdf';
        $fileId = $this->makeFileRow($userId, $missingPath);
        $this->setCv($studentId, $fileId);

        $result = student_profile_remove_cv($studentId);

        $this->assertArrayNotHasKey('error', $result);
        $this->assertFalse($this->fileRowExists($fileId), 'Stale files record must be cleaned.');
        $this->assertNull($this->cvOf($studentId), 'Stale CV reference must be cleared.');
    }

    public function testStudentIsolationNeverDeletesAnotherUsersFile(): void
    {
        $ownerUser = $this->makeUser();
        $ownerStudent = $this->makeStudent($ownerUser, 'CV Owner');
        $path = $this->makePhysicalFile('cvtest', 'cv_owner_' . bin2hex(random_bytes(4)) . '.pdf');
        $ownerFileId = $this->makeFileRow($ownerUser, $path);
        $this->setCv($ownerStudent, $ownerFileId);

        // A different student profile incorrectly points at the owner's file.
        $otherUser = $this->makeUser();
        $otherStudent = $this->makeStudent($otherUser, 'CV Intruder');
        $this->setCv($otherStudent, $ownerFileId);

        $result = student_profile_remove_cv($otherStudent);

        $this->assertArrayNotHasKey('error', $result);
        $this->assertNull($this->cvOf($otherStudent), 'The stale reference on the other student is cleared.');

        $this->assertTrue($this->fileRowExists($ownerFileId), 'The owner files record must be untouched.');
        $this->assertFileExists($path, 'The owner physical file must be untouched.');
        $this->assertSame($ownerFileId, $this->cvOf($ownerStudent), 'The owner CV reference must be untouched.');
    }

    public function testUnsafePathIsNeverDeletedButMetadataIsCleaned(): void
    {
        $userId = $this->makeUser();
        $studentId = $this->makeStudent($userId);

        // A stored path that escapes the storage directory.
        $outsidePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'masar_escape_'
            . bin2hex(random_bytes(4)) . '.txt';
        file_put_contents($outsidePath, 'do not delete');
        $this->createdPaths[] = $outsidePath;

        $fileId = $this->makeFileRow($userId, $outsidePath);
        $this->setCv($studentId, $fileId);

        $result = student_profile_remove_cv($studentId);

        $this->assertArrayNotHasKey('error', $result);
        $this->assertFileExists($outsidePath, 'A file outside the storage directory must never be deleted.');
        $this->assertFalse($this->fileRowExists($fileId), 'The unsafe files record is still cleaned.');
        $this->assertNull($this->cvOf($studentId), 'The CV reference is still cleared.');
    }

    public function testPathTraversalIsRejected(): void
    {
        $this->assertFalse(file_upload_service_is_safe_storage_path(''));
        $this->assertFalse(file_upload_service_is_safe_storage_path('uploads/../../secret'));
        $this->assertFalse(file_upload_service_is_safe_storage_path('../etc/passwd'));
        $this->assertFalse(file_upload_service_is_safe_storage_path('C:/Windows/win.ini'));
        $this->assertFalse(file_upload_service_is_safe_storage_path($this->storageBase . '/../config'));
        $this->assertTrue(
            file_upload_service_is_safe_storage_path($this->storageBase . '/cvtest/some_cv.pdf')
        );
    }

    public function testSafePathOutsideBaseIsRejected(): void
    {
        $fake = $this->storageBase . '_evil/file.pdf';
        $this->assertFalse(file_upload_service_is_safe_storage_path($fake));
    }
}