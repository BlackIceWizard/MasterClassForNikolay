<?php

namespace HomeWork\Model;

use Ramsey\Uuid\UuidInterface;

class Component
{
    private UuidInterface $id;
    private int $numberAtStock;
    private string $name;

    public function __construct(UuidInterface $id, int $numberAtStock, string $name)
    {
        $this->id = $id;
        $this->numberAtStock = $numberAtStock;
        $this->name = $name;
    }

    public function takeIn(int $number): void
    {
        $this->numberAtStock += $number;
    }

    public function takeOut(int $number): void
    {
        if($this->numberAtStock < $number) {
            throw new \DomainException('Not enough components in stock');
        }

        $this->numberAtStock -= $number;
    }

    public function id(): UuidInterface
    {
        return $this->id;
    }

    public function numberAtStock(): int
    {
        return $this->numberAtStock;
    }

    public function name(): string
    {
        return $this->name;
    }
}