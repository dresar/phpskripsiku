<?php
/**
 * Repository: readings table
 * Semua akses database untuk readings melalui class ini
 */

declare(strict_types=1);

namespace App;

use PDO;

final class ReadingRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /** Insert satu reading (dari MQTT payload) */
    public function insert(float $ph, float $tds, float $suhu, string $status): int
    {
        $sql = 'INSERT INTO readings (ph, tds, suhu, status) VALUES (:ph, :tds, :suhu, :status)';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'ph'    => $ph,
            'tds'   => $tds,
            'suhu'  => $suhu,
            'status'=> $status,
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    /** Ambil 1 data terakhir */
    public function getLatest(): ?array
    {
        $stmt = $this->pdo->query('SELECT * FROM readings ORDER BY id DESC LIMIT 1');
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /** Ambil N data terakhir */
    public function getHistory(int $limit = 50, ?string $dateFrom = null, ?string $dateTo = null): array
    {
        $sql = 'SELECT * FROM readings WHERE 1=1';
        $params = [];
        if ($dateFrom) {
            $sql .= ' AND date(created_at) >= :date_from';
            $params['date_from'] = $dateFrom;
        }
        if ($dateTo) {
            $sql .= ' AND date(created_at) <= :date_to';
            $params['date_to'] = $dateTo;
        }
        $sql .= ' ORDER BY id DESC LIMIT :limit';
        $params['limit'] = $limit;

        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k === 'limit' ? ':limit' : ':' . $k, $v, $k === 'limit' ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /** Statistik: rata-rata dan total hari ini, distribusi status */
    public function getStats(?string $dateFrom = null, ?string $dateTo = null): array
    {
        $where = '1=1';
        $params = [];
        if ($dateFrom) {
            $where .= ' AND date(created_at) >= :date_from';
            $params['date_from'] = $dateFrom;
        }
        if ($dateTo) {
            $where .= ' AND date(created_at) <= :date_to';
            $params['date_to'] = $dateTo;
        }

        $stmt = $this->pdo->prepare("
            SELECT 
                AVG(ph) as avg_ph,
                AVG(tds) as avg_tds,
                AVG(suhu) as avg_suhu,
                COUNT(*) as total
            FROM readings
            WHERE {$where}
        ");
        $stmt->execute($params);
        $agg = $stmt->fetch();

        $stmt2 = $this->pdo->prepare("
            SELECT status, COUNT(*) as count
            FROM readings
            WHERE {$where}
            GROUP BY status
        ");
        $stmt2->execute($params);
        $byStatus = $stmt2->fetchAll();

        return [
            'avg_ph'     => $agg ? (float) ($agg['avg_ph'] ?? 0) : 0,
            'avg_tds'    => $agg ? (float) ($agg['avg_tds'] ?? 0) : 0,
            'avg_suhu'   => $agg ? (float) ($agg['avg_suhu'] ?? 0) : 0,
            'total'      => $agg ? (int) ($agg['total'] ?? 0) : 0,
            'by_status'  => $byStatus,
        ];
    }

    /** Untuk export CSV: data dengan filter tanggal */
    public function getForExport(?string $dateFrom = null, ?string $dateTo = null, int $max = 1000): array
    {
        return $this->getHistory($max, $dateFrom, $dateTo);
    }
}
