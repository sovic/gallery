<?php

namespace Sovic\Gallery\EntityManager;

use Doctrine\ORM\EntityManagerInterface;

abstract class AbstractEntityModel
{
    private EntityManagerInterface $entityManager;
    private object $entity;

    public function setEntityManager(EntityManagerInterface $entityManager): void
    {
        $this->entityManager = $entityManager;
    }

    protected function getEntityManager(): EntityManagerInterface
    {
        return $this->entityManager;
    }

    protected function setEntity($entity): void
    {
        $this->entity = $entity;
    }

    public function getEntity(): object
    {
        return $this->entity;
    }
}
