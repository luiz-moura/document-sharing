<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Repositories;

use App\Domain\Sender\Contracts\FileHostingRepository as FileHostingRepositoryContract;
use App\Domain\Sender\DTOs\CreateFileHostingData;
use App\Domain\Sender\Enums\FileHostingStatus;
use App\Infrastructure\Persistence\Doctrine\Entities\FileHostingEntity;
use Doctrine\ORM\EntityRepository;

class FileHostingRepository extends EntityRepository implements FileHostingRepositoryContract
{
    public function create(CreateFileHostingData $fileHosting): int
    {
        $fileHostingEntity = new FileHostingEntity(
            $fileHosting->fileId,
            $fileHosting->hostingId,
        );

        $this->getEntityManager()->persist($fileHostingEntity);
        $this->getEntityManager()->flush();

        return $fileHostingEntity->getId();
    }

    public function updateStatus(int $fileHostingId, FileHostingStatus $status): void
    {
        $fileHostingEntity = $this->find($fileHostingId);

        $fileHostingEntity->setStatus($status);

        $this->getEntityManager()->persist($fileHostingEntity);
        $this->getEntityManager()->flush();
    }
}
