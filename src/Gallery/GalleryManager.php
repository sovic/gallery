<?php

namespace Sovic\Gallery\Gallery;

use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use League\Flysystem\FilesystemAdapter;

final class GalleryManager
{
    private array $galleries = [
        'documents',
        'downloads',
    ];

    private EntityManagerInterface $entityManager;

    private FilesystemAdapter $filesystemAdapter;

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

    public function setFilesystemAdapter(FilesystemAdapter $filesystemAdapter): void
    {
        $this->filesystemAdapter = $filesystemAdapter;
    }

    public function loadGallery(?string $galleryName = null): Gallery
    {
        if ($galleryName === null || $galleryName === $this->modelName) {
            $galleryName = $this->modelName;
        } else if (!in_array($galleryName, $this->galleries, true)) {
            $errorMessage = 'invalid gallery name [' . implode('|', $this->galleries) . ']';
            throw new InvalidArgumentException($errorMessage);
        }

        $repo = $this->entityManager->getRepository(\Sovic\Gallery\Entity\Gallery::class);
        $entity = $repo->findOneBy(
            [
                'model' => $this->modelName,
                'modelId' => $this->modelId,
                'name' => $galleryName,
            ]
        );
        if ($entity === null) {
            $entity = new \Sovic\Gallery\Entity\Gallery();
            $entity->setModel($this->modelName);
            $entity->setModelId($this->modelId);
            $entity->setName($galleryName);
        }

        $gallery = new Gallery($entity);
        $gallery->setEntityManager($this->entityManager);
        $gallery->setFilesystemAdapter($this->filesystemAdapter);

        return $gallery;
    }
}
