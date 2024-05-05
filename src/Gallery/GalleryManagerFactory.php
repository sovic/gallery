<?php

namespace Sovic\Gallery\Gallery;

use Doctrine\ORM\EntityManagerInterface;
use Sovic\Gallery\Entity\GalleryModelInterface;

readonly class GalleryManagerFactory
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    public function loadByModel(GalleryModelInterface $galleryModel): GalleryManager
    {
        $manager = new GalleryManager($galleryModel->getGalleryModelName(), $galleryModel->getGalleryModelId());
        $manager->setEntityManager($this->entityManager);

        return $manager;
    }
}
