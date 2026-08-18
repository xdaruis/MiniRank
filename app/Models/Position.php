<?php

declare(strict_types=1);

namespace App\Models;

class Position
{
    public function __construct(private \PDO $db)
    {
    }

    public function history(int $keywordId): array
    {
        $stmt = $this->db->prepare(
            'SELECT captured_at, position FROM positions WHERE keyword_id = :id ORDER BY captured_at DESC, id DESC'
        );
        $stmt->execute(['id' => $keywordId]);
        return $stmt->fetchAll();
    }

    public function current(int $keywordId): ?int
    {
        $stmt = $this->db->prepare(
            'SELECT position FROM positions WHERE keyword_id = :id ORDER BY captured_at DESC, id DESC LIMIT 1'
        );
        $stmt->execute(['id' => $keywordId]);
        $value = $stmt->fetchColumn();
        return $value === false ? null : (int) $value;
    }

    public function trend(int $keywordId): string
    {
        $today = date('Y-m-d');
        $past = date('Y-m-d', strtotime('-7 days'));

        $todayPosition = $this->positionOn($keywordId, $today, 'DESC');

        $stmt = $this->db->prepare(
            'SELECT position FROM positions
             WHERE keyword_id = :id AND captured_at <= :past
             ORDER BY captured_at DESC, id DESC LIMIT 1'
        );
        $stmt->execute(['id' => $keywordId, 'past' => $past]);
        $pastPosition = $stmt->fetchColumn();

        if ($pastPosition === false) {
            $pastPosition = $this->positionOn($keywordId, null, 'ASC');
        }

        if ($todayPosition === null || $pastPosition === null) {
            return 'stable';
        }

        if ($todayPosition < (int) $pastPosition) {
            return 'improved';
        }

        if ($todayPosition > (int) $pastPosition) {
            return 'declined';
        }

        return 'stable';
    }

    private function positionOn(int $keywordId, ?string $date, string $order): ?int
    {
        $allowed = ['DESC', 'ASC'];
        if (!in_array($order, $allowed, true)) {
            throw new \InvalidArgumentException('Invalid order');
        }

        $sql = 'SELECT position FROM positions WHERE keyword_id = :id';
        $params = ['id' => $keywordId];

        if ($date !== null) {
            $sql .= ' AND captured_at = :date';
            $params['date'] = $date;
        }

        $sql .= " ORDER BY captured_at $order, id $order LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $value = $stmt->fetchColumn();
        return $value === false ? null : (int) $value;
    }

    public function refreshForToday(int $keywordId): array
    {
        $prev = $this->current($keywordId);
        if ($prev === null) {
            $position = random_int(1, 100);
        } else {
            $position = min(100, max(1, $prev + random_int(-3, 3)));
        }

        $stmt = $this->db->prepare(
            'INSERT INTO positions (keyword_id, position, captured_at)
             VALUES (:id, :position, :today)
             ON CONFLICT(keyword_id, captured_at) DO UPDATE SET position = excluded.position'
        );
        $stmt->execute([
            'id' => $keywordId,
            'position' => $position,
            'today' => date('Y-m-d'),
        ]);

        return [
            'keyword_id' => $keywordId,
            'position' => $position,
            'trend' => $this->trend($keywordId),
        ];
    }
}
