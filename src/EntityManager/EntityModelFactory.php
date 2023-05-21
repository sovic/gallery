<?php

namespace Sovic\Gallery\EntityManager;

use Doctrine\ORM\EntityManagerInterface;

abstract class EntityModelFactory
{
    protected EntityManagerInterface $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager
    ) {
        $this->entityManager = $entityManager;
    }

    protected function loadEntityModel(mixed $entity, string $modelClass): mixed
    {
        if (null === $entity) {
            return null;
        }

        $model = new $modelClass();
        $model->setEntityManager($this->entityManager);
        $model->setEntity($entity);

        return $model;
    }

    public function getEntityManager(): EntityManagerInterface
    {
        return $this->entityManager;
    }

    protected function loadModelById(string $entityClass, string $modelClass, int $id): mixed
    {
        return $this->loadModelBy($entityClass, $modelClass, ['id' => $id]);
    }

    protected function loadModelBy(
        string $entityClass,
        string $modelClass,
        array  $criteria,
        ?array $orderBy = null
    ): mixed {
        $repository = $this->entityManager->getRepository($entityClass);
        $entity = $repository->findOneBy($criteria, $orderBy);
        if (!$entity) {
            return null;
        }

        return $this->loadEntityModel($entity, $modelClass);
    }
}
