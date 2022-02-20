<?php

namespace HomeWork\Infrastructure\Database;

use DateTimeImmutable;
use Exception;
use HomeWork\Model\ComponentSet\ComponentSet;
use HomeWork\Model\ComponentSet\ComponentSetItem;
use HomeWork\Model\ComponentSet\ComponentSetStatus;
use Ramsey\Uuid\Uuid;
use RuntimeException;
use PDO;
use Ramsey\Uuid\UuidInterface;

class ComponentSetRepository
{
    private const DATE_TIME_FORMAT = 'Y-m-d H:i:s';

    private PDO $pdo;

    public function __construct(PdoProvider $pdoProvider)
    {
        $this->pdo = $pdoProvider->provide();
    }

    public function findById(UuidInterface $id): ?ComponentSet
    {
        $stmt = $this->pdo->prepare('SELECT * FROM component_sets where id = :id');
        $stmt->execute(['id' => $id->toString()]);

        while ($row = $stmt->fetch()) {
            return $this->hydrateOneComponentSet($row, $this->getItems($id));
        }

        return null;
    }

    /**
     * @return ComponentSet[]
     */
    public function findStatus(ComponentSetStatus $status): array
    {
        $result = [];
        $stmt = $this->pdo->prepare('SELECT * FROM component_sets where status = :status');
        $stmt->execute(['id' => $status->getValue()]);

        while ($row = $stmt->fetch()) {
            $result[] = $this->hydrateOneComponentSet($row, $this->getItems(Uuid::fromString($row['id'])));
        }

        return $result;
    }

    private function getItems(UuidInterface $componentSetId): array
    {
        $items = [];

        $stmt = $this->pdo->prepare('SELECT * FROM component_set_items where component_set_id = :component_set_id');
        $stmt->execute(['component_set_id' => $componentSetId->toString()]);

        while ($row = $stmt->fetch()) {
            $items[] = $this->hydrateComponentSetItem($row);
        }

        return $items;
    }

    /**
     * @param ComponentSetItem[] $items
     */
    private function hydrateOneComponentSet(array $componentSetData, array $items): ComponentSet
    {
        $status = new ComponentSetStatus($componentSetData['status']);

        switch ($status) {
            case ComponentSetStatus::CREATED():
                return ComponentSet::create(Uuid::fromString($componentSetData['id']), new DateTimeImmutable($componentSetData['created_at']));
            case ComponentSetStatus::COLLECTED():
                return ComponentSet::createCollected(
                    Uuid::fromString($componentSetData['id']),
                    new DateTimeImmutable($componentSetData['created_at']),
                    new DateTimeImmutable($componentSetData['collected_at']),
                    $items
                );
            case ComponentSetStatus::SENT():
                return ComponentSet::createSent(
                    Uuid::fromString($componentSetData['id']),
                    new DateTimeImmutable($componentSetData['created_at']),
                    new DateTimeImmutable($componentSetData['collected_at']),
                    new DateTimeImmutable($componentSetData['sent_at']),
                    $items
                );
            default:
                throw new RuntimeException('Unexpected status');
        }
    }

    private function hydrateComponentSetItem(array $data): ComponentSetItem
    {
        return new ComponentSetItem(Uuid::fromString($data['id']), Uuid::fromString($data['component_id']), $data['number']);
    }

    public function save(ComponentSet $componentSet): void
    {
        try {
            $this->pdo->beginTransaction();

            $this->saveComponentSet($componentSet);

            foreach ($componentSet->items() as $item) {
                $this->saveComponentSetItem($item, $componentSet->id());
            }

            $this->pdo->commit();
        }catch (Exception $e){
            $this->pdo->rollback();
            throw $e;
        }
    }

    public function saveComponentSet(ComponentSet $componentSet): void
    {
        $sql = <<<SQL
    INSERT INTO component_sets (id, status, created_at, collected_at, sent_at)
    VALUES (:id, :status, :created_at, :collected_at, :sent_at)
    ON DUPLICATE KEY UPDATE status=:d_status,
                            created_at=:d_created_at,
                            collected_at=:d_collected_at,
                            sent_at=:d_sent_at
    SQL;

        $stmt = $this->pdo->prepare($sql);
        
        $collectedAtFormatted = $componentSet->collectedAt() ? $componentSet->collectedAt()->format(self::DATE_TIME_FORMAT) : null;
        $sentAtFormatted = $componentSet->sentAt() ? $componentSet->sentAt()->format(self::DATE_TIME_FORMAT) : null;

        $stmt->execute([
            'id' => $componentSet->id()->toString(),
            'status' => $componentSet->status()->getValue(),
            'created_at' => $componentSet->createdAt()->format(self::DATE_TIME_FORMAT),
            'collected_at' => $collectedAtFormatted,
            'sent_at' => $sentAtFormatted,
            'd_status' => $componentSet->status()->getValue(),
            'd_created_at' => $componentSet->createdAt()->format(self::DATE_TIME_FORMAT),
            'd_collected_at' =>$collectedAtFormatted,
            'd_sent_at' => $sentAtFormatted,
        ]);
    }


    public function saveComponentSetItem(ComponentSetItem $componentSetItem, UuidInterface $componentSetId): void
    {
        $sql = <<<SQL
    INSERT INTO component_set_items (id, component_id, component_set_id, number)
    VALUES (:id, :component_id, :component_set_id, :number)
    ON DUPLICATE KEY UPDATE component_id=:d_component_id,
                            component_set_id=:d_component_set_id,
                            number=:d_number
    SQL;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'id' => $componentSetItem->id()->toString(),
            'component_id' => $componentSetItem->componentId()->toString(),
            'component_set_id' => $componentSetId->toString(),
            'number' => $componentSetItem->number(),
            'd_component_id' => $componentSetItem->componentId()->toString(),
            'd_component_set_id' => $componentSetId->toString(),
            'd_number' => $componentSetItem->number(),
        ]);
    }
}