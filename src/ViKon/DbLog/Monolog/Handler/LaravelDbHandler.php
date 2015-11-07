<?php

namespace ViKon\DbLog\Monolog\Handler;

use Carbon\Carbon;
use Illuminate\Contracts\Container\Container;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Handler\HandlerInterface;
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
    /** @type \Illuminate\Contracts\Container\Container */
    protected $container;

    /** @type \Monolog\Handler\HandlerInterface */
    protected $fallback;

    /** @type bool */
    protected $hasError = false;

    /**
     * EloquentDbHandler constructor.
     *
     * @param \Illuminate\Contracts\Container\Container $container
     * @param \Monolog\Handler\HandlerInterface         $fallback
     * @param bool|int                                  $level
     * @param bool|true                                 $bubble
     */
    public function __construct(Container $container, HandlerInterface $fallback, $level = Logger::DEBUG, $bubble = true)
    {
        parent::__construct($level, $bubble);

        $this->container = $container;
        $this->fallback  = $fallback;
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
     *
     * @throws \Exception
     */
    protected function save(array $records)
    {
        if ($this->hasError === true) {
            $this->fallback->handleBatch($records);

            return;
        }

        $data = [];
        foreach ($records as $record) {
            // ['message', 'context', 'level', 'channel', 'created_at', 'extra'] <= Single row format
            $data[] = [
                Log::FIELD_MESSAGE    => $record['message'],
                Log::FIELD_CONTEXT    => serialize((array)$record['context']),
                Log::FIELD_LEVEL      => Logger::getLevelName($record['level']),
                Log::FIELD_CHANNEL    => $record['channel'],
                Log::FIELD_CREATED_AT => $record['datetime'] instanceof \DateTime
                    ? Carbon::instance($record['datetime'])
                    : Carbon::now(),
                Log::FIELD_EXTRA      => serialize((array)$record['extra']),
            ];
        }

        try {
            $this->container->make('db')->connection()->table((new Log())->getTable())->insert($data);
        } catch (\Exception $ex) {
            $this->hasError = true;
            $this->fallback->handleBatch($records);

            throw $ex;
        }
    }
}