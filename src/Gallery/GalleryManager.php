<?php

namespace Sovic\Gallery\Gallery;

use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use League\Flysystem\FilesystemOperator;

final class GalleryManager
{
    private array $galleries = [
        'documents',
        'downloads',
    ];

    private EntityManagerInterface $entityManager;

    private FilesystemOperator $filesystemOperator;

    private string $modelName;
    private int $modelId;

    public function __construct(string $modelName, int $modelId)
    {
        $this->modelName = $modelName;
        $this->modelId = $modelId;
    }

    /**
     * @required
     */
    public function setEntityManager(EntityManagerInterface $entityManager): void
    {
        $this->entityManager = $entityManager;
    }

    public function setFilesystemOperator(FilesystemOperator $filesystemOperator): void
    {
        $this->filesystemOperator = $filesystemOperator;
    }

    /**
     * Get gallery, if it doesn't exist, create it.
     */
    public function loadGallery(?string $galleryName = null): Gallery
    {
        $galleryName = $this->validateGalleryName($galleryName);
        $gallery = $this->getGallery($galleryName);

        return $gallery ?? $this->createGallery($galleryName);
    }

    public function createGallery(?string $galleryName = null): Gallery
    {
        $galleryName = $this->validateGalleryName($galleryName);

        $entity = new \Sovic\Gallery\Entity\Gallery();
        $entity->setModel($this->modelName);
        $entity->setModelId($this->modelId);
        $entity->setName($galleryName);

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        $gallery = new Gallery($entity);
        $gallery->setEntityManager($this->entityManager);
        if (isset($this->filesystemOperator)) {
            $gallery->setFilesystemOperator($this->filesystemOperator);
        }

        return $gallery;
    }

    public function getGallery(?string $galleryName = null): ?Gallery
    {
        $galleryName = $this->validateGalleryName($galleryName);
        $repo = $this->entityManager->getRepository(\Sovic\Gallery\Entity\Gallery::class);
        $entity = $repo->findOneBy(
            [
                'model' => $this->modelName,
                'modelId' => $this->modelId,
                'name' => $galleryName,
            ]
        );
        if ($entity === null) {
            return null;
        }

        $gallery = new Gallery($entity);
        $gallery->setEntityManager($this->entityManager);
        if (isset($this->filesystemOperator)) {
            $gallery->setFilesystemOperator($this->filesystemOperator);
        }

        return $gallery;
    }

    private function validateGalleryName(?string $galleryName): string
    {
        if ($galleryName === null || $galleryName === $this->modelName) {
            $galleryName = $this->modelName;
        } else if (!in_array($galleryName, $this->galleries, true)) {
            $errorMessage = 'invalid gallery name [' . implode('|', $this->galleries) . ']';
            throw new InvalidArgumentException($errorMessage);
        }

        return $galleryName;
    }
}
