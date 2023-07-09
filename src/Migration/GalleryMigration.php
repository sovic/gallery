<?php

namespace Sovic\Gallery\Migration;

use Doctrine\ORM\EntityManagerInterface;
use Sovic\Gallery\Entity\Gallery;
use Sovic\Gallery\Entity\GalleryItem;
use Sovic\Gallery\Repository\GalleryItemRepository;

class GalleryMigration extends AbstractMigration
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    public function migrate(array $options = []): void
    {
        if (empty($options['cover_image_gallery_names'])) {
            $options['cover_image_gallery_names'] = ['page', 'post'];
        }

        $galleries = $this->entityManager->getRepository(Gallery::class)->findBy(
            [
                'name' => $options['cover_image_gallery_names'],
            ]
        );
        foreach ($galleries as $entity) {
            $gallery = new \Sovic\Gallery\Gallery\Gallery($entity);
            $gallery->setEntityManager($this->entityManager);

            // set default cover image
            $gallery->setDefaultCoverImage();
        }

        /** @var GalleryItemRepository $galleryItemRepo */
        $galleryItemRepo = $this->entityManager->getRepository(GalleryItem::class);

        $galleries = $this->entityManager->getRepository(Gallery::class)->findAll();
        foreach ($galleries as $entity) {
            $gallery = new \Sovic\Gallery\Gallery\Gallery($entity);
            $gallery->setEntityManager($this->entityManager);

            // fix empty model_id items
            $items = $galleryItemRepo->findBy([
                'galleryId' => $gallery->getId(),
                'modelId' => 0,
            ]);
            foreach ($items as $item) {
                $item->setModel($gallery->getEntity()->getModel());
                $item->setModelId($gallery->getEntity()->getModelId());
                $this->entityManager->persist($item);
            }
        }
        $this->entityManager->flush();
    }
}
