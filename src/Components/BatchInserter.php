<?php

namespace SamIT\Yii2\Components;

use yii\db\Connection;

/**
 * Class BatchInserter
 * Simple batch inserter for usage with Yii2.
 *
 */
class BatchInserter
{
    /**
     * @var int The number of records to insert per query.
     */
    protected $batchSize;
    
    /**
     * @var string[] The column names
     */
    protected $columns;
    
    /**
     * @var boolean Whether to sort the records by column names. (Allows for associative records where the keys may not be in the same order).
     */
    protected $sort;
    
    /**
     *
     * @var string
     */
    protected $table;

    /**
     * @var \Closure A callback to be called when the inserter wants to log something.
     */
    protected $logCallback;


    private $rowCache = [];

    protected $strategy;

    /**
     * Inserter that will always commit immediately after its parent.
     * @var self
     */
    protected $childInserter;

    /**
     * BatchInserter constructor.
     * @param string $table The name of the table
     * @param array $columns The column names
     * @param Connection $db The database connection to use
     * @param int|null $batchSize The batch size.
     * @param bool $sort Whether to sort records / columns.
     * @param \Closure|null $logCallback A callback for logging output.
     * @param bool $ignoreDuplicates If true the query will be modified to use `INSERT IGNORE`, tested on MySQL only.
     * @param BatchInserter|null $child Optional child inserter.
     */
    public function __construct(
        string $table,
        array $columns,
        Connection $db,
        int $batchSize = null,
        bool $sort = false,
        \Closure $logCallback = null,
        string $strategy = 'INSERT',
        self $child = null
    )
    {
        $this->table = $table;

        $this->strategy = $strategy;

        $this->sort = $sort;

        if ($this->sort) {
            sort($columns);
        }
        $this->columns = $columns;

        $this->batchSize = $batchSize ?? intval(50000 / count($columns));

        $this->logCallback = $logCallback ?? function($text) { echo $text; };

        $this->childInserter = $child;
    }

    /**
     * @param string $text The message to log.
     * @return void
     */
    protected function log($text)
    {
        $callback = $this->logCallback;
        $callback($text);
    }
    
    public function insert($values) 
    {
        if ($this->sort) {
            ksort($values);
        }
        $this->rowCache[] = $values;
        if (count($this->rowCache) >= $this->batchSize) {
            $this->commit();
        }
    }
    
    public function commit() 
    {
        if (!empty($this->rowCache)) {
            $this->log("Sending " . count($this->rowCache) . " records to database({$this->table})\n");
            $command = $this->db->createCommand()->batchInsert($this->table, $this->columns, $this->rowCache);
            $command->sql = strtr($command->sql, ['INSERT' => $this->strategy]);
            $this->rowCache = [];
            $this->log("Insert result: " . $command->execute());
        }

        if (isset($this->childInserter)) {
            $this->log("Committing child records\n");
            $this->childInserter->commit();
        }
    }
    
    public function __destruct() 
    {
        if (!empty($this->rowCache)) {
            $this->log("Committing from destructor.\n");
            $this->commit();
        }
    }
}