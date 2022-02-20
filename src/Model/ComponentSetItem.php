<?php

namespace HomeWork\Model;

use Ramsey\Uuid\UuidInterface;

class ComponentSetItem
{
    private UuidInterface $id;
    private UuidInterface $componentId;
    private int $number;

    public function __construct(
        UuidInterface $id,
        UuidInterface $componentId,
        int $number
    ) {
        $this->id = $id;
        $this->componentId = $componentId;
        $this->number = $number;
    }

    public function id(): UuidInterface
    {
        return $this->id;
    }

    public function componentId(): UuidInterface
    {
        return $this->componentId;
    }

    public function number(): int
    {
        return $this->number;
    }
}

