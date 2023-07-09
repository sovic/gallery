<?php

namespace Sovic\Gallery\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;

/**
 * Sovic\Gallery\Entity\Gallery
 *
 * @ORM\Table(
 *     name="gallery",
 *     indexes={
 *         @ORM\Index(name="model_model_id", columns={"model","model_id"}),
 *         @ORM\Index(name="model_model_id_name", columns={"model","model_id","name"})
 *     }
 * )
 * @ORM\Entity
 */
class Gallery
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected int $id;

    /**
     * @ORM\Column(name="session_id", type="string", length=32, nullable=true, options={"default"="NULL"})
     */
    protected ?string $sessionId;

    /**
     * @ORM\Column(name="model", type="string", length=100, nullable=false)
     */
    protected string $model;

    /**
     * @ORM\Column(name="model_id", type="integer", nullable=false)
     */
    protected int $modelId;

    /**
     * @ORM\Column(name="name", type="string", length=100, nullable=false)
     */
    protected string $name;

    /**
     * @ORM\Column(name="timestamp", type="integer", nullable=true, options={"default"="NULL"})
     */
    protected ?int $timestamp;

    /**
     * @ORM\Column(name="users_id", type="integer", nullable=true, options={"default"="NULL"})
     */
    protected ?int $usersId;

    /**
     * @ORM\Column(name="is_processed", type="boolean", nullable=false, options={"default"="0"})
     */
    protected bool $isProcessed = false;

    /**
     * @var GalleryItem[]|PersistentCollection
     *
     * @ORM\OneToMany(targetEntity="GalleryItem", mappedBy="gallery", fetch="LAZY")
     */
    protected mixed $galleryItems;

    /**
     * @ORM\Column(name="path", type="string", length=255, nullable=true, options={"default"=NULL})
     */
    protected ?string $path = null;

    /**
     * @ORM\Column(name="create_date", type="datetime_immutable", nullable=false)
     */
    protected DateTimeImmutable $createDate;

    public function getId(): int
    {
        return $this->id;
    }

    public function getSessionId(): ?string
    {
        return $this->sessionId;
    }

    public function setSessionId(?string $sessionId): void
    {
        $this->sessionId = $sessionId;
    }

    public function getModel(): string
    {
        return $this->model;
    }

    public function setModel(string $model): void
    {
        $this->model = $model;
    }

    public function getModelId(): int
    {
        return $this->modelId;
    }

    public function setModelId(int $modelId): void
    {
        $this->modelId = $modelId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getTimestamp(): ?int
    {
        return $this->timestamp;
    }

    public function setTimestamp(?int $timestamp): void
    {
        $this->timestamp = $timestamp;
    }

    public function getUsersId(): ?int
    {
        return $this->usersId;
    }

    public function setUsersId(?int $usersId): void
    {
        $this->usersId = $usersId;
    }

    public function isIsProcessed(): bool
    {
        return $this->isProcessed;
    }

    public function setIsProcessed(bool $isProcessed): void
    {
        $this->isProcessed = $isProcessed;
    }

    public function getGalleryItems(): mixed
    {
        return $this->galleryItems;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(?string $path): void
    {
        $this->path = $path;
    }

    public function getCreateDate(): DateTimeImmutable
    {
        return $this->createDate;
    }

    public function setCreateDate(DateTimeImmutable $createDate): void
    {
        $this->createDate = $createDate;
    }
}
