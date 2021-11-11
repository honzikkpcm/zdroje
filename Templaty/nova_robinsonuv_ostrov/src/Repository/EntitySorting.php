<?php

namespace App\Repository;
use Doctrine\DBAL\ConnectionException;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class EntitySorting
 * @package App\Repository
 */
class EntitySorting
{
    /** @var EntityManagerInterface */
    private $em;

    /**
     * @param EntityManagerInterface $connection
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    /**
     * @param string $entity
     * @param array $parent
     * @return int
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getLastSorting(string $entity, array $parent = null): int
    {
        if (empty($entity))
            throw new \InvalidArgumentException('Invalid argument has been entered.');

        $table = $this->getTableName($entity);

        if (isset($parent)) {
            $column = key($parent);
            $stmt = $this->em->getConnection()->prepare("SELECT MAX(sorting) FROM \"$table\" WHERE \"$column\"=?");
            $stmt->execute([current($parent)]);
        } else {
            $stmt = $this->em->getConnection()->prepare("SELECT MAX(sorting) FROM \"$table\"");
            $stmt->execute();
        }

        return (int)$stmt->fetch()['max'];
    }

    /**
     * @param string $entity
     * @param array $parent
     * @return int
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getFirstSorting(string $entity, array $parent = null): int
    {
        if (empty($entity))
            throw new \InvalidArgumentException('Invalid argument has been entered.');

        $table = $this->getTableName($entity);

        if (isset($parent)) {
            $column = key($parent);
            $stmt = $this->em->getConnection()->prepare("SELECT MIN(sorting) FROM \"$table\" WHERE \"$column\"=?");
            $stmt->execute([current($parent)]);
        } else {
            $stmt = $this->em->getConnection()->prepare("SELECT MIN(sorting) FROM \"$table\"");
            $stmt->execute();
        }

        return (int)$stmt->fetch()['min'];
    }

    /**
     * @param string $entity
     * @param int $id
     * @param string $parent
     * @throws ConnectionException
     * @throws DBALException
     */
    public function sortingUp(string $entity, int $id, string $parent = null): void
    {
        if (empty($entity) || empty($id))
            throw new \InvalidArgumentException('Invalid argument has been entered.');

        $table = $this->getTableName($entity);
        $parentColumn = $parent ?? 'id';
        $stmt = $this->em->getConnection()->prepare("SELECT sorting,\"$parentColumn\" FROM \"$table\" WHERE id=?");
        $stmt->execute([$id]);
        $sortingData = $stmt->fetch();
        $sorting = $sortingData['sorting'];

        if ($sorting === $this->getFirstSorting($entity, $parent ? [$parent => $sortingData[$parent]] : null))
            throw new \UnexpectedValueException('Operation failed. Change the order was unsuccessful. Please check whether an item is no longer on the highest or lowest position.');

        // get ID of nearest lower sorting
        if ($parent) {
            $stmt = $this->em->getConnection()->prepare("SELECT id,sorting FROM \"$table\" WHERE sorting < ? AND \"$parent\"=? ORDER BY sorting DESC LIMIT 1");
            $stmt->execute([$sorting, $sortingData[$parent]]);
        } else {
            $stmt = $this->em->getConnection()->prepare("SELECT id,sorting FROM \"$table\" WHERE sorting < ? ORDER BY sorting DESC LIMIT 1");
            $stmt->execute([$sorting]);
        }
        $sortingData = $stmt->fetch();

        try {
            $this->em->getConnection()->beginTransaction();
            $stmt = $this->em->getConnection()->prepare("UPDATE \"$table\" SET sorting=? WHERE id=?");
            $stmt->execute([$sortingData['sorting'], $id]);
            $stmt = $this->em->getConnection()->prepare("UPDATE \"$table\" SET sorting=? WHERE id=?");
            $stmt->execute([$sorting, $sortingData['id']]);
            $this->em->getConnection()->commit();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();
            throw $e;
        }
    }

    /**
     * @param string $entity
     * @param int $id
     * @param string $parent
     * @throws ConnectionException
     * @throws DBALException
     */
    public function sortingDown(string $entity, int $id, string $parent = null): void
    {
        if (empty($entity) || empty($id))
            throw new \InvalidArgumentException('Invalid argument has been entered.');

        $table = $this->getTableName($entity);
        $parentColumn = $parent ?? 'id';
        $stmt = $this->em->getConnection()->prepare("SELECT sorting,\"$parentColumn\" FROM \"$table\" WHERE id=?");
        $stmt->execute([$id]);
        $sortingData = $stmt->fetch();
        $sorting = $sortingData['sorting'];

        if ($sorting === $this->getLastSorting($entity, $parent ? [$parent => $sortingData[$parent]] : null))
            throw new \UnexpectedValueException('Operation failed. Change the order was unsuccessful. Please check whether an item is no longer on the highest or lowest position.');

        // get ID of nearest greather sorting
        if ($parent) {
            $stmt = $this->em->getConnection()->prepare("SELECT id,sorting FROM \"$table\" WHERE sorting > ? AND \"$parent\"=? ORDER BY sorting ASC LIMIT 1");
            $stmt->execute([$sorting, $sortingData[$parent]]);
        } else {
            $stmt = $this->em->getConnection()->prepare("SELECT id,sorting FROM \"$table\" WHERE sorting > ? ORDER BY sorting DESC LIMIT 1");
            $stmt->execute([$sorting]);
        }
        $sortingData = $stmt->fetch();

        try {
            $this->em->getConnection()->beginTransaction();
            $stmt = $this->em->getConnection()->prepare("UPDATE \"$table\" SET sorting=? WHERE id=?");
            $stmt->execute([$sortingData['sorting'], $id]);
            $stmt = $this->em->getConnection()->prepare("UPDATE \"$table\" SET sorting=? WHERE id=?");
            $stmt->execute([$sorting, $sortingData['id']]);
            $this->em->getConnection()->commit();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();
            throw $e;
        }
    }

    // private ---------------------------------------------------------------------------------------------------------

    /**
     * @param string $entity
     * @return string
     */
    private function getTableName(string $entity): string
    {
        return $this->em->getClassMetadata($entity)->getTableName();
    }

}
