<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Entities;

use App\Domain\Sender\Enums\FileHostingStatus;
use App\Infrastructure\Persistence\Doctrine\Repositories\FileHostingRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FileHostingRepository::class), ORM\Table(name: 'hosted_files')]
class FileHostingEntity
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private int $id;

    #[ORM\Column(name: 'file_id', type: 'integer')]
    private int $fileId;

    #[ORM\Column(name: 'hosting_id', type: 'integer')]
    private int $hostingId;

    #[ORM\Column(type: 'string', options: ['default' => FileHostingStatus::TO_SEND->value])]
    private string $status = FileHostingStatus::TO_SEND->value;

    #[ORM\Column(
        name: 'external_file_id',
        type: 'string',
        nullable: true,
    )]
    private ?string $externalFileId;

    #[ORM\Column(
        name: 'web_view_link',
        type: 'string',
        nullable: true,
    )]
    private ?string $webViewLink;

    #[ORM\Column(
        name: 'web_content_link',
        type: 'string',
        nullable: true,
    )]
    private ?string $webContentLink;

    #[ORM\Column(
        name: 'created_at',
        type: 'datetimetz_immutable',
        insertable: false,
        updatable: false,
        options: ['default' => 'CURRENT_TIMESTAMP'],
    )]
    private DateTimeImmutable $createdAt;

    public function __construct(
        int $fileId,
        int $hostingId,
    ) {
        $this->fileId = $fileId;
        $this->hostingId = $hostingId;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getFileId(): int
    {
        return $this->fileId;
    }

    public function getHostingId(): int
    {
        return $this->hostingId;
    }

    public function getStatus(): FileHostingStatus
    {
        return FileHostingStatus::from($this->status);
    }

    public function setStatus(FileHostingStatus $status): self
    {
        $this->status = $status->value;

        return $this;
    }

    public function getExternalFileId(): string
    {
        return $this->externalFileId;
    }

    public function setExternalFileId(string $externalFileId): self
    {
        $this->externalFileId = $externalFileId;

        return $this;
    }

    public function getWebViewLink(): string
    {
        return $this->webViewLink;
    }

    public function setWebViewLink(string $webViewLink): self
    {
        $this->webViewLink = $webViewLink;

        return $this;
    }

    public function getWebContentLink(): string
    {
        return $this->webContentLink;
    }

    public function setWebContentLink(string $webContentLink): self
    {
        $this->webContentLink = $webContentLink;

        return $this;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
}
