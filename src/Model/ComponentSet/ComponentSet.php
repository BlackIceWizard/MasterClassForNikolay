<?php

namespace HomeWork\Model\ComponentSet;

use DateTimeImmutable;
use DomainException;
use Ramsey\Uuid\UuidInterface;

class ComponentSet
{
    private UuidInterface $id;
    private ComponentSetStatus $status;
    private DateTimeImmutable $createdAt;
    private ?DateTimeImmutable $collectedAt;
    private ?DateTimeImmutable $sentAt;
    private array $componentSetItems;

    /**
     * @param ComponentSetItem[] $componentSetItems
     */
    private function __construct(
        UuidInterface $id,
        ComponentSetStatus $status,
        ?DateTimeImmutable $createdAt,
        ?DateTimeImmutable $collectedAt = null,
        ?DateTimeImmutable $sentAt = null,
        array $componentSetItems = []
    ) {
        $this->id = $id;
        $this->status = $status;
        $this->createdAt = $createdAt;
        $this->collectedAt = $collectedAt;
        $this->sentAt = $sentAt;
        $this->componentSetItems = $componentSetItems;
    }

    static public function create(UuidInterface $id, ?DateTimeImmutable $createdAt = null): self
    {
        return new self($id, ComponentSetStatus::CREATED(), $createdAt ?? new DateTimeImmutable());
    }

    static public function createCollected(
        UuidInterface $id,
        DateTimeImmutable $createdAt,
        DateTimeImmutable $collectedAt,
        array $componentSetItems
    ): self {
        return new self($id, ComponentSetStatus::COLLECTED(), $createdAt, $collectedAt, null, $componentSetItems);
    }

    static public function createSent(
        UuidInterface $id,
        DateTimeImmutable $createdAt,
        DateTimeImmutable $collectedAt,
        DateTimeImmutable $sentAt,
        array $componentSetItems
    ): self {
        return new self($id, ComponentSetStatus::SENT(), $createdAt, $collectedAt, $sentAt, $componentSetItems);
    }

    /**
     * @param ComponentSetItem[] $items
     */
    public function collect(array $items): void
    {
        if (! $this->status->isCreated()) {
            throw new DomainException('Status violation');
        }

        $this->componentSetItems = $items;
        $this->status = ComponentSetStatus::COLLECTED();
        $this->collectedAt = new DateTimeImmutable();
    }

    public function sent(): void
    {
        if (! $this->status->isCollected()) {
            throw new DomainException('Status violation');
        }

        $this->status = ComponentSetStatus::SENT();
        $this->sentAt = new DateTimeImmutable();
    }

    public function id(): UuidInterface
    {
        return $this->id;
    }

    public function status(): ComponentSetStatus
    {
        return $this->status;
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function collectedAt(): ?DateTimeImmutable
    {
        return $this->collectedAt;
    }

    public function sentAt(): ?DateTimeImmutable
    {
        return $this->sentAt;
    }

    /**
     * @return ComponentSetItem[]
     */
    public function items(): array
    {
        return $this->componentSetItems;
    }
}