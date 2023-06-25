<?php

namespace Sovic\Gallery\Gallery;

use DateTimeImmutable;
use Imagick;
use ImagickException;
use InvalidArgumentException;
use League\Flysystem\Config;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\Visibility;
use Sovic\Gallery\Entity\Gallery as GalleryEntity;
use Sovic\Gallery\Entity\GalleryItem;
use Sovic\Gallery\EntityManager\AbstractEntityModel;
use Sovic\Gallery\Repository\GalleryItemRepository;

/**
 * @property GalleryEntity $entity
 * @method GalleryEntity getEntity()
 */
class Gallery extends AbstractEntityModel
{
    private FilesystemOperator $filesystemOperator;

    public function __construct(GalleryEntity $entity)
    {
        $this->setEntity($entity);
    }

    public function setFilesystemOperator(FilesystemOperator $filesystemOperator): void
    {
        $this->filesystemOperator = $filesystemOperator;
    }

    public function getCoverImage(): ?array
    {
        /** @var GalleryItemRepository $repo */
        $repo = $this->getEntityManager()->getRepository(GalleryItem::class);
        $cover = $repo->findGalleryCoverImage($this->getEntity());
        if (!$cover) {
            return null;
        }

        return (new GalleryItemResultSet([$cover]))->toArray()[0];
    }

    public function getHeroImage(): ?array
    {
        /** @var GalleryItemRepository $repo */
        $repo = $this->getEntityManager()->getRepository(GalleryItem::class);
        $hero = $repo->findGalleryHeroImage($this->getEntity());
        if (!$hero) {
            return null;
        }

        return (new GalleryItemResultSet([$hero]))->toArray()[0];
    }

    public function getItems(?int $offset = null, ?int $limit = null): array
    {
        /** @var GalleryItemRepository $repo */
        $repo = $this->getEntityManager()->getRepository(GalleryItem::class);
        $items = $repo->findByGallery($this->getEntity(), $offset, $limit);

        return (new GalleryItemResultSet($items))->toArray();
    }

    public function getItemsResultSet(?int $offset = null, ?int $limit = null): GalleryItemResultSet
    {
        /** @var GalleryItemRepository $repo */
        $repo = $this->getEntityManager()->getRepository(GalleryItem::class);
        $items = $repo->findByGallery($this->getEntity(), $offset, $limit);

        return (new GalleryItemResultSet($items));
    }
//
//    public function getVideo(): ?array
//    {
////        $expr = $this->entityManager->getExpressionBuilder();
////        $qb = $this->initQueryBuilder('video');
////        $qb->andWhere(
////            $qb->expr()->orX(
////                $expr->isNotNull('gi.name'),
////                $expr->isNotNull('gi.description')
////            )
////        );
////        $qb->orderBy('gi.sequence', 'ASC');
////        /** @var GalleryItem $item */
////        $items = $qb->getQuery()->getResult();
////        if (count($items) <= 0) {
////            return null;
////        }
////        $item = $items[0];
////        if ($item->getDescription() && Text::isUrl($item->getDescription())) {
////            $url = $item->getDescription();
////        } elseif ($item->getName() || $item->getDescription()) {
////            $filename = $item->getName() ?: $item->getDescription();
////            $url = '/dl/' . $item->getId() . '/' . $filename . '.' . $item->getExtension();
////        } else {
////            return null;
////        }
////
////        return [
////            'url' => $url,
////        ];
//
//        return null;
//    }
//
//    public function getDocs(): array
//    {
////        $expr = $this->entityManager->getExpressionBuilder();
////        $qb = $this->initQueryBuilder('reading');
////        $qb->andWhere(
////            $qb->expr()->orX(
////                $expr->isNotNull('gi.name'),
////                $expr->isNotNull('gi.description')
////            )
////        );
////        $qb->orderBy('gi.sequence', 'ASC');
////        $items = $qb->getQuery()->getResult();
////        $result = [];
////        /** @var GalleryItem $item */
////        foreach ($items as $item) {
////            $description = $item->getDescription();
////            if ($description && Text::isUrl($description)) {
////                $name = File::publicFileName(basename($item->getExtension()));
////                $url = $item->getDescription();
////            } elseif ($description || $item->getName()) {
////                $filename = !empty($item->getName()) ? $item->getName() : $description;
////                $name = File::publicFileName($filename, $item->getExtension());
////                $url = '/dl/' . $item->getId() . '/' . $filename . '.' . $item->getExtension();
////            } else {
////                continue;
////            }
////
////            $result[] = [
////                'name' => $name,
////                'url' => $url,
////            ];
////        }
////
////        return $result;
//        return [];
//    }
//
//    public function getDownloads(): array
//    {
////        $qb = $this->initQueryBuilder('downloads');
////        $qb->orderBy('gi.sequence', 'ASC');
////        $items = $qb->getQuery()->getResult();
////
////        $results = [];
////        /** @var GalleryItem $item */
////        foreach ($items as $item) {
////            $mediaPaths = GalleryHelper::getMediaPaths($item, $this->baseUrl, [GalleryHelper::SIZE_FULL]);
////            $result = [
////                'name' => File::publicFileName($item->getName(), $item->getExtension()),
////                'filename' => $item->getName() . '.' . $item->getExtension(),
////                'url' => $mediaPaths[GalleryHelper::SIZE_FULL],
////            ];
////            $results[] = $result;
////        }
////
////        return $results;
//        return [];
//    }

    /**
     * @param string $path
     * @return GalleryItem[]
     * @throws FilesystemException
     * @throws ImagickException
     */
    public function uploadPath(string $path): array
    {
        $uploadedItems = [];
        if (is_file($path)) {
            $uploadedItems[] = $this->uploadFromPath($path);
        }
        if (is_dir($path)) {
            $files = array_diff(scandir($path), ['.', '..']);
            foreach ($files as $file) {
                $uploadedItems[] = $this->uploadFromPath($path . DIRECTORY_SEPARATOR . $file);
            }
        }

        return $uploadedItems;
    }

    /**
     * @throws ImagickException
     * @throws FilesystemException
     */
    public function uploadFromPath(string $path): GalleryItem
    {
        if (!file_exists($path)) {
            throw new InvalidArgumentException('invalid path');
        }
        $extension = pathinfo($path, PATHINFO_EXTENSION);
        $filename = pathinfo($path, PATHINFO_FILENAME);

        $item = new GalleryItem();
        $item->setGallery($this->getEntity());
        $item->setExtension($extension);
        $item->setName($filename);
        $item->setModel($this->getEntity()->getModel());
        $item->setModelId($this->getEntity()->getModelId());

        // image data
        $image = new Imagick($path);
        $width = $image->getImageWidth();
        $height = $image->getImageHeight();
        $item->setWidth($width);
        $item->setHeight($height);

        $item->setCreateDate(new DateTimeImmutable());
        $item->setIsTemp(true);
        if ($this->getCoverImage() === null) {
            $item->setIsCover(true);
        }

        $em = $this->getEntityManager();
        $em->persist($item);
        $em->flush();

        $fileSystemFilename = $item->getId() . ($extension ? '.' . $extension : '');
        $storagePath = $this->getGalleryStoragePath();
        $fileSystemPath = $storagePath . DIRECTORY_SEPARATOR . $fileSystemFilename;

        $filesystem = $this->filesystemOperator;
        $filesystem->createDirectory($storagePath, [
            Config::OPTION_DIRECTORY_VISIBILITY => Visibility::PUBLIC,
        ]);
        $filesystem->write($fileSystemPath, file_get_contents($path));

        $item->setPath($fileSystemPath);
        $item->setIsTemp(false);
        $em->persist($item);
        $em->flush();

        return $item;
    }

    private function getGalleryStoragePath(): string
    {
        /** @noinspection SpellCheckingInspection */
        $hash = md5($this->getEntity()->getId() . 'T3zmR34Swh4FZAA'); // TODO config salt
        $path = str_split(substr($hash, 0, 6));
        $path[] = $hash;

        return implode(DIRECTORY_SEPARATOR, $path);
    }

    /**
     * @throws FilesystemException
     */
    public function delete(): void
    {
        $filesystem = $this->filesystemOperator;
        $filesystem->delete($this->getGalleryStoragePath());

        $em = $this->getEntityManager();
        $em->remove($this->getEntity());
        $em->flush();
    }
}
