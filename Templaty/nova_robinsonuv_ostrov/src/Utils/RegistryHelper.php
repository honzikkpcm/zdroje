<?php

namespace App\Utils;

use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * Helper function for common operations with doctrine registry
 */
class RegistryHelper
{
    /**
     * Persists given entities with default manager and flushes changes to database.
     *
     * @param array $entities
     * @param ManagerRegistry $registry
     */
    public static function store(array $entities, ManagerRegistry $registry)
    {
        $manager = $registry->getManager();
        foreach ($entities as $entity) {
            $manager->persist($entity);
        }
        $manager->flush();
    }
}