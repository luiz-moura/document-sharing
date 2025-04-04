<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Entities;

use App\Infrastructure\Persistence\Doctrine\Repositories\HostingRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: HostingRepository::class), ORM\Table(name: 'hosting')]
class HostingEntity
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private int $id;

    #[ORM\Column(type: 'string')]
    private string $name;

    #[ORM\Column(
        type: 'string',
        unique: true,
    )]
    private string $slug;

    #[ORM\Column(
        name: 'access_token',
        type: 'string',
        length: 2000,
        nullable: true,
    )]
    private ?string $accessToken = null;

    #[ORM\Column(
        name: 'refreshable_token',
        type: 'string',
        length: 2000,
        nullable: true,
    )]
    private ?string $refreshableToken = null;

    #[ORM\Column(
        name: 'created_at',
        type: 'datetimetz_immutable',
        insertable: false,
        updatable: false,
        options: ['default' => 'CURRENT_TIMESTAMP'],
    )]
    private $createdAt;

    public function __construct(
        string $name,
        string $slug,
    ) {
        $this->name = $name;
        $this->slug = $slug;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getRefreshableToken(): ?string
    {
        return $this->refreshableToken;
    }

    public function setRefreshableToken(string $refreshableToken): void
    {
        $this->refreshableToken = $refreshableToken;
    }

    public function getAccessToken(): ?string
    {
        return $this->accessToken;
    }

    public function setAccessToken(string $accessToken): void
    {
        $this->accessToken = $accessToken;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
}
