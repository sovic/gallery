<?php

namespace Sovic\Gallery\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Sovic\Gallery\Entity\Gallery;
use Sovic\Gallery\Entity\GalleryItem;

class GalleryItemRepository extends EntityRepository
{
    private function getGalleryQueryBuilder(Gallery $gallery): QueryBuilder
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('gi')
            ->from(Gallery::class, 'g')
            ->where('g.model = :model');
        $qb->leftJoin(GalleryItem::class, 'gi', Join::WITH, 'gi.galleryId = g.id');
        $qb->setParameter(':model', $gallery->getModel());
        $qb->andWhere('gi.isTemp = 0');
        $qb->andWhere('g.modelId = :model_id');
        $qb->setParameter(':model_id', $gallery->getModelId());
        $qb->andWhere('g.name = :gallery_name');
        $qb->setParameter(':gallery_name', $gallery->getName());

        return $qb;
    }

    private function getBatchQueryBuilder(string $model, array $modelIds): QueryBuilder
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('gi');
        $qb->from(GalleryItem::class, 'gi');
        $qb->andWhere('gi.model = :model');
        $qb->setParameter(':model', $model);
        $qb->andWhere('gi.modelId IN (:model_id)');
        $qb->setParameter(':model_id', $modelIds);
        $qb->andWhere('gi.isTemp = 0');

        return $qb;
    }

    public function findByGallery(Gallery $gallery, ?int $offset = null, ?int $limit = null): array
    {
        $qb = $this->getGalleryQueryBuilder($gallery);
        if ($offset) {
            $qb->setFirstResult($offset);
        }
        if ($limit) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }

    public function findGalleryCoverImage(Gallery $gallery): ?GalleryItem
    {
        $qb = $this->getGalleryQueryBuilder($gallery);
        $qb->orderBy('gi.isCover', 'DESC');
        $qb->setMaxResults(1);

        return $qb->getQuery()->getSingleResult();
    }

    public function findGalleriesCovers(string $model, array $modelIds): array
    {
        $qb = $this->getBatchQueryBuilder($model, $modelIds);
        $qb->andWhere('gi.isCover = 1');

        return $qb->getQuery()->getResult();
    }

    public function findGalleryHeroImage(Gallery $gallery): ?GalleryItem
    {
        $qb = $this->getGalleryQueryBuilder($gallery);
        // TODO single item, multiple variants
        $qb->andWhere($qb->expr()->orX('gi.isHero = 1', 'gi.isHeroMobile = 1'));
        $qb->setMaxResults(1);

        return $qb->getQuery()->getSingleResult();
    }
}
