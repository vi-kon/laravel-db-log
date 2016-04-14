<?php

namespace ViKon\DbLog;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\BufferHandler;
use Monolog\Handler\FingersCrossedHandler;
use Monolog\Handler\GroupHandler;
use Monolog\Handler\NativeMailerHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use ViKon\DbLog\Monolog\Handler\LaravelDbHandler;

/**
 * Class DbLogger
 *
 * @package ViKon\DbLog
 *
 * @author  Kovács Vince<vincekovacs@hotmail.com>
 */
class DbLogger
{
    /** @type \Illuminate\Contracts\Foundation\Application */
    protected $app;

    /** @type \Monolog\Handler\GroupHandler */
    protected $buffer;

    /**
     * DbLogRegister constructor.
     *
     * @param \Illuminate\Contracts\Foundation\Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;

        $this->app->terminating(function () {
            if ($this->buffer !== null) {
                $this->buffer->close();
            }
        });
    }

    /**
     * @param \Monolog\Logger $logger
     */
    public function __invoke(Logger $logger)
    {
        $config = $this->app->make(Repository::class);

        $handlers      = [];
        $errorHandlers = [];

        // Add mail error handler
        if ($config->get('log.mail.enabled', false) === true) {
            $errorHandlers[] = new NativeMailerHandler($config->get('log.mail.to'),
                                                       $config->get('log.mail.subject'),
                                                       $config->get('log.mail.from'),
                                                       Logger::DEBUG);
        }

        if ($errorHandlers !== []) {
            $handlers[] = new FingersCrossedHandler(new GroupHandler($errorHandlers));
        }

        // Handler for database handling
        $fallbackHandler = new StreamHandler($config->get('log.db.fallback', storage_path('/logs/db-fallback.log')));
        $fallbackHandler->setFormatter(new JsonFormatter(JsonFormatter::BATCH_MODE_NEWLINES));
        $dbHandler  = new LaravelDbHandler($this->app, $fallbackHandler);
        $handlers[] = $this->buffer = new BufferHandler($dbHandler, $config->get('log.db.level', Logger::INFO));

        // Register all handlers in logger
        $group = new GroupHandler($handlers);
        $group->pushProcessor(new UidProcessor());
        $logger->pushHandler($group);
    }
}