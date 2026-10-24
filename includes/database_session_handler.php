<?php
final class DatabaseSessionHandler implements SessionHandlerInterface
{
    public function __construct(private mysqli $connection)
    {
    }

    public function open(string $path, string $name): bool
    {
        return true;
    }

    public function close(): bool
    {
        return true;
    }

    public function read(string $id): string|false
    {
        $stmt = $this->connection->prepare('SELECT session_data FROM app_sessions WHERE session_id=? AND last_activity >= ?');
        if (!$stmt) return false;
        $minimumActivity = time() - (int) ini_get('session.gc_maxlifetime');
        $stmt->bind_param('si', $id, $minimumActivity);
        if (!$stmt->execute()) return false;
        $stmt->bind_result($data);
        return $stmt->fetch() ? $data : '';
    }

    public function write(string $id, string $data): bool
    {
        $stmt = $this->connection->prepare('INSERT INTO app_sessions (session_id,session_data,last_activity) VALUES (?,?,?) ON DUPLICATE KEY UPDATE session_data=VALUES(session_data),last_activity=VALUES(last_activity)');
        if (!$stmt) return false;
        $now = time();
        $stmt->bind_param('ssi', $id, $data, $now);
        return $stmt->execute();
    }

    public function destroy(string $id): bool
    {
        $stmt = $this->connection->prepare('DELETE FROM app_sessions WHERE session_id=?');
        if (!$stmt) return false;
        $stmt->bind_param('s', $id);
        return $stmt->execute();
    }

    public function gc(int $max_lifetime): int|false
    {
        $stmt = $this->connection->prepare('DELETE FROM app_sessions WHERE last_activity < ?');
        if (!$stmt) return false;
        $expiredBefore = time() - $max_lifetime;
        $stmt->bind_param('i', $expiredBefore);
        if (!$stmt->execute()) return false;
        return $stmt->affected_rows;
    }
}
