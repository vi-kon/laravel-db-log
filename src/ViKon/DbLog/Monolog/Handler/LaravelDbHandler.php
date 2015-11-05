<?php

namespace ViKon\DbLog\Monolog\Handler;

use Carbon\Carbon;
use Illuminate\Database\Connection;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
use ViKon\DbLog\Model\Log;

/**
 * Class LaravelDbHandler
 *
 * @package ViKon\DbLog\Monolog\Handler
 *
 * @author  Kovács Vince<vincekovacs@hotmail.com>
 */
class LaravelDbHandler extends AbstractProcessingHandler
{
    /** @var \Illuminate\Database\Connection */
    protected $connection;

    /**
     * @param \Illuminate\Database\Connection $connection Database connection from Laravel, where logs are inserted
     * @param bool|int                        $level      The minimum logging level at which this handler will be
     *                                                    triggered
     * @param bool                            $bubble     Whether the messages that are handled can bubble up the stack
     *                                                    or not
     */
    public function __construct(Connection $connection, $level = Logger::DEBUG, $bubble = true)
    {
        $this->connection = $connection;

        parent::__construct($level, $bubble);
    }

    /**
     * {@inheritDoc}
     */
    public function handleBatch(array $records)
    {
        $processedRecords = [];

        foreach ($records as $record) {
            if (!$this->isHandling($record)) {
                continue;
            }
            $processedRecords[] = $this->processRecord($record);
        }

        if ($processedRecords !== []) {
            $this->save($processedRecords);
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function write(array $record)
    {
        $this->save([$record]);
    }

    /**
     * Save records into database in batch
     *
     * @param array $records
     */
    protected function save(array $records)
    {
        $data = [];
        foreach ($records as $record) {
            // ['message', 'context', 'level', 'channel', 'created_at', 'extra'] <= Single row format
            $data[] = [
                $record['message'],
                serialize((array)$record['context']),
                $record['level'],
                $record['channel'],
                $record['datetime'] instanceof \DateTime ? Carbon::instance($record['datetime']) : Carbon::now(),
                serialize((array)$record['extra']),
            ];
        }

        $this->connection->table((new Log())->getTable())->insert($data);
    }
}