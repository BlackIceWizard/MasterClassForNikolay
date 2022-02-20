<?php

namespace HomeWork\Model;

use Ramsey\Uuid\UuidInterface;

class Component
{
    private UuidInterface $id;
    private int $numberInStock;
    private string $name;

    public function __construct(UuidInterface $id, int $numberInStock, string $name)
    {
        $this->id = $id;
        $this->numberInStock = $numberInStock;
        $this->name = $name;
    }

    public function takeIn(int $number): void
    {
        $this->numberInStock += $number;
    }

    public function takeOut(int $number): void
    {
        if($this->numberInStock < $number) {
            throw new \DomainException('Not enough components in stock');
        }

        $this->numberInStock -= $number;
    }

    public function id(): UuidInterface
    {
        return $this->id;
    }

    public function numberInStock(): int
    {
        return $this->numberInStock;
    }

    public function name(): string
    {
        return $this->name;
    }
}