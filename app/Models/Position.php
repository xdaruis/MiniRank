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
        // TODO: SELECT date + position for a keyword, newest first.
        return [];
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
            'SELECT position FROM positions WHERE keyword_id = :id AND captured_at = :past LIMIT 1'
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
        // TODO: generate today's position, upsert, return [position, trend].
        return ['position' => null, 'trend' => 'stable'];
    }
}
