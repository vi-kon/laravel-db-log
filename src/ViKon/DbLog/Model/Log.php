<?php

namespace ViKon\DbLog\Model;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Log
 *
 * @package ViKon\DbLog\Model
 *
 * @author  Kovács Vince<vincekovacs@hotmail.com>
 *
 * @property int            $id
 * @property string         $message
 * @property string         $context
 * @property int            $level
 * @property string         $channel
 * @property \Carbon\Carbon $datetime
 * @property string         $extra
 */
class Log extends Model
{
    const TABLE_NAME = 'logs';

    const FIELD_ID         = 'id';
    const FIELD_MESSAGE    = 'message';
    const FIELD_CONTEXT    = 'context';
    const FIELD_LEVEL      = 'level';
    const FIELD_CHANNEL    = 'channel';
    const FIELD_CREATED_AT = 'created_at';
    const FIELD_EXTRA      = 'extra';

    /**
     * {@inheritDoc}
     */
    public function __construct(array $attributes = [])
    {
        $this->table      = static::TABLE_NAME;
        $this->timestamps = false;
        $this->dates      = [
            static::FIELD_CREATED_AT,
        ];

        parent::__construct($attributes);
    }
}