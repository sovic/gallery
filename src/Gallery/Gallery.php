<?php

namespace Sovic\Gallery\Gallery;

use DateTimeImmutable;
use InvalidArgumentException;
use League\Flysystem\Config;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\FilesystemException;
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
    private FilesystemAdapter $filesystemAdapter;

    public function __construct(GalleryEntity $entity)
    {
        $this->setEntity($entity);
    }

    public function setFilesystemAdapter(FilesystemAdapter $filesystemAdapter): void
    {
        $this->filesystemAdapter = $filesystemAdapter;
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

    public function getVideo(): ?array
    {
//        $expr = $this->entityManager->getExpressionBuilder();
//        $qb = $this->initQueryBuilder('video');
//        $qb->andWhere(
//            $qb->expr()->orX(
//                $expr->isNotNull('gi.name'),
//                $expr->isNotNull('gi.description')
//            )
//        );
//        $qb->orderBy('gi.sequence', 'ASC');
//        /** @var GalleryItem $item */
//        $items = $qb->getQuery()->getResult();
//        if (count($items) <= 0) {
//            return null;
//        }
//        $item = $items[0];
//        if ($item->getDescription() && Text::isUrl($item->getDescription())) {
//            $url = $item->getDescription();
//        } elseif ($item->getName() || $item->getDescription()) {
//            $filename = $item->getName() ?: $item->getDescription();
//            $url = '/dl/' . $item->getId() . '/' . $filename . '.' . $item->getExtension();
//        } else {
//            return null;
//        }
//
//        return [
//            'url' => $url,
//        ];

        return null;
    }

    public function getDocs(): array
    {
//        $expr = $this->entityManager->getExpressionBuilder();
//        $qb = $this->initQueryBuilder('reading');
//        $qb->andWhere(
//            $qb->expr()->orX(
//                $expr->isNotNull('gi.name'),
//                $expr->isNotNull('gi.description')
//            )
//        );
//        $qb->orderBy('gi.sequence', 'ASC');
//        $items = $qb->getQuery()->getResult();
//        $result = [];
//        /** @var GalleryItem $item */
//        foreach ($items as $item) {
//            $description = $item->getDescription();
//            if ($description && Text::isUrl($description)) {
//                $name = File::publicFileName(basename($item->getExtension()));
//                $url = $item->getDescription();
//            } elseif ($description || $item->getName()) {
//                $filename = !empty($item->getName()) ? $item->getName() : $description;
//                $name = File::publicFileName($filename, $item->getExtension());
//                $url = '/dl/' . $item->getId() . '/' . $filename . '.' . $item->getExtension();
//            } else {
//                continue;
//            }
//
//            $result[] = [
//                'name' => $name,
//                'url' => $url,
//            ];
//        }
//
//        return $result;
        return [];
    }

    public function getDownloads(): array
    {
//        $qb = $this->initQueryBuilder('downloads');
//        $qb->orderBy('gi.sequence', 'ASC');
//        $items = $qb->getQuery()->getResult();
//
//        $results = [];
//        /** @var GalleryItem $item */
//        foreach ($items as $item) {
//            $mediaPaths = GalleryHelper::getMediaPaths($item, $this->baseUrl, [GalleryHelper::SIZE_FULL]);
//            $result = [
//                'name' => File::publicFileName($item->getName(), $item->getExtension()),
//                'filename' => $item->getName() . '.' . $item->getExtension(),
//                'url' => $mediaPaths[GalleryHelper::SIZE_FULL],
//            ];
//            $results[] = $result;
//        }
//
//        return $results;
        return [];
    }

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

//        try {
//            $image = new Imagick($path);
//            $width = $image->getImageWidth();
//            $height = $image->getImageHeight();
//            $item->setWidth($width);
//            $item->setHeight($height);
//        } catch (Exception) {
//            // no dimensions
//        }

        $item->setWidth(2048);
        $item->setHeight(1363);
        $item->setCreateDate(new DateTimeImmutable());
        $item->setIsTemp(true);

        $em = $this->getEntityManager();
        $em->persist($item);
        $em->flush();

        $fileSystemFilename = $item->getId() . ($extension ? '.' . $extension : '');
        $fileSystemPath = $this->getGalleryStoragePath() . DIRECTORY_SEPARATOR . $fileSystemFilename;

        $config = ['public_url' => 'http://localhost/gallery/'];
        $filesystem = new Filesystem($this->filesystemAdapter, $config);
        $options = [
            Config::OPTION_VISIBILITY => Visibility::PUBLIC,
            Config::OPTION_DIRECTORY_VISIBILITY => Visibility::PUBLIC,
        ];
        $filesystem->write($fileSystemPath, file_get_contents($path), $options);

        $item->setPath($fileSystemPath);
        $item->setIsTemp(false);
        $em->persist($item);
        $em->flush();

        return $item;
    }

    public function getGalleryStoragePath(): string
    {
        /** @noinspection SpellCheckingInspection */
        $hash = md5($this->getEntity()->getId() . 'T3zmR34Swh4FZAA');
        $path = str_split(substr($hash, 0, 6));
        $path[] = $hash;

        return implode(DIRECTORY_SEPARATOR, $path);
    }
}
