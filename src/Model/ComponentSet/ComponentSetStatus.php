<?php

namespace HomeWork\Model\ComponentSet;

use MyCLabs\Enum\Enum;

/**
 * @method static ComponentSetStatus CREATED()
 * @method static ComponentSetStatus COLLECTED()
 * @method static ComponentSetStatus SENT()
 */
class ComponentSetStatus extends Enum
{
    private const CREATED = 'created';
    private const COLLECTED = 'collected';
    private const SENT = 'sent';

    public function isCreated(): bool {
        return $this->equals(self::CREATED());
    }

    public function isCollected(): bool {
        return $this->equals(self::COLLECTED());
    }

    public function isSent(): bool {
        return $this->equals(self::SENT());
    }
}