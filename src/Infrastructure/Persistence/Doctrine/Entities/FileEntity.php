<?php

declare(strict_types= 1);

namespace App\Infrastructure\Persistence\Doctrine\Entities;

use App\Infrastructure\Persistence\Doctrine\Repositories\FileRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FileRepository::class), ORM\Table(name: 'files')]
class FileEntity
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private int $id;

    #[ORM\Column(type: 'string')]
    private string $name;

    #[ORM\Column(type: 'integer')]
    private int $size;

    #[ORM\Column(name: 'mime_type', type: 'string')]
    private string $mimeType;

    #[ORM\Column(name: 'created_at', type: 'datetimetz_immutable')]
    private DateTimeImmutable $createdAt;

    public function __construct(
        string $name,
        int $size,
        string $mimeType,
    ) {
        $this->name = $name;
        $this->size = $size;
        $this->mimeType = $mimeType;
        $this->createdAt = new DateTimeImmutable('now');
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function getMimeType(): string
    {
        return $this->mimeType;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
}
