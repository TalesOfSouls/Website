<?php
/**
 * Tales of Souls
 *
 * PHP Version 8.2
 *
 * @package   Application\Frontend\Provider
 * @version   1.0.0
 */
declare(strict_types=1);

namespace Application\Frontend\Provider;

use phpOMS\DataStorage\Database\Connection\ConnectionAbstract;

/**
 * Base class of the website data providers.
 *
 * The data providers load the models with the mappers and prepare them for the templates.
 * Aggregations (sums, ranks, counts over several tables) are done with plain SQL because
 * the mappers only work on single models.
 *
 * @package Application\Frontend\Provider
 * @since   1.0.0
 */
abstract class DataAbstract
{
    /**
     * Constructor.
     *
     * @param ConnectionAbstract $db Database connection
     *
     * @since 1.0.0
     */
    public function __construct(protected ConnectionAbstract $db)
    {
    }

    /**
     * Run a select query
     *
     * @param string $sql    Query
     * @param array  $params Parameters
     *
     * @return array<int, array<string, mixed>>
     *
     * @since 1.0.0
     */
    protected function rows(string $sql, array $params = []) : array
    {
        $stmt = $this->db->con->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Run a select query and return the first column of the first row
     *
     * @param string $sql    Query
     * @param array  $params Parameters
     *
     * @return mixed
     *
     * @since 1.0.0
     */
    protected function value(string $sql, array $params = []) : mixed
    {
        $stmt = $this->db->con->prepare($sql);
        $stmt->execute($params);

        $value = $stmt->fetchColumn();

        return $value === false ? null : $value;
    }

    /**
     * Placeholders for an IN (...) condition
     *
     * @param array $values Values
     *
     * @return string
     *
     * @since 1.0.0
     */
    protected static function in(array $values) : string
    {
        return empty($values) ? 'NULL' : \implode(',', \array_fill(0, \count($values), '?'));
    }

    /**
     * Format seconds as 1:05:30 / 05:30
     *
     * @param int $seconds Seconds
     *
     * @return string
     *
     * @since 1.0.0
     */
    public static function formatDuration(int $seconds) : string
    {
        $h = \intdiv($seconds, 3600);
        $m = \intdiv($seconds % 3600, 60);
        $s = $seconds % 60;

        return $h > 0 ? \sprintf('%d:%02d:%02d', $h, $m, $s) : \sprintf('%02d:%02d', $m, $s);
    }

    /**
     * Format large numbers (12.4M, 830K)
     *
     * @param int|float $value Value
     *
     * @return string
     *
     * @since 1.0.0
     */
    public static function formatNumber(int|float $value) : string
    {
        if ($value >= 1000000) {
            return \number_format($value / 1000000, 1) . 'M';
        }

        if ($value >= 1000) {
            return \number_format($value / 1000, 1) . 'K';
        }

        return (string) (int) $value;
    }
}
