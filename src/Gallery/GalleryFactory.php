<?php

namespace Sovic\Gallery\Gallery;

use Sovic\Gallery\EntityManager\EntityModelFactory;

final class GalleryFactory extends EntityModelFactory
{
    public function loadByEntity(?\Sovic\Gallery\Entity\Gallery $entity = null): ?Gallery
    {
        return $this->loadEntityModel($entity, \Sovic\Gallery\Entity\Gallery::class);
    }

    public function loadById(int $id): ?Gallery
    {
        return $this->loadModelBy(
            \Sovic\Gallery\Entity\Gallery::class,
            Gallery::class,
            ['id' => $id]
        );
    }
}
