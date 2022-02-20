<?php

namespace HomeWork\Infrastructure\Database;

use DateTimeImmutable;
use HomeWork\Model\Component;
use HomeWork\Model\ComponentSet\ComponentSet;
use HomeWork\Model\ComponentSet\ComponentSetItem;
use HomeWork\Model\ComponentSet\ComponentSetStatus;
use Ramsey\Uuid\Uuid;
use RuntimeException;
use PDO;
use Ramsey\Uuid\UuidInterface;

class ComponentRepository
{
    private PDO $pdo;

    public function __construct(PdoProvider $pdoProvider)
    {
        $this->pdo = $pdoProvider->provide();
    }

    public function findById(UuidInterface $id): ?Component
    {
        $stmt = $this->pdo->prepare('SELECT * FROM components where id = :id');
        $stmt->execute(['id' => $id->toString()]);

        while ($row = $stmt->fetch()) {
            return $this->hydrateOneComponent($row);
        }

        return null;
    }

    public function save(Component $component): void
    {
        $sql = <<<SQL
INSERT INTO components (id, number_in_stock, name)
VALUES (:id, :number_in_stock, :name)
ON DUPLICATE KEY UPDATE number_in_stock=:d_number_in_stock,
                        name=:d_name
SQL;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'id' => $component->id()->toString(),
            'number_in_stock' => $component->numberInStock(),
            'd_number_in_stock' => $component->numberInStock(),
            'name' => $component->name(),
            'd_name' => $component->name(),
        ]);
    }

    private function hydrateOneComponent(array $data): Component
    {
        return new Component(Uuid::fromString($data['id']), $data['number'], $data['name']);
    }
}