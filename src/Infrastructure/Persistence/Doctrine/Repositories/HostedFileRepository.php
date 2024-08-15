<?php

declare(strict_types= 1);

namespace App\Infrastructure\Persistence\Doctrine\Repositories;

use App\Domain\Sender\Contracts\FileHostingRepository as FileHostingRepositoryContract;
use App\Domain\Sender\DTOs\CreateFileHostingData;
use App\Domain\Sender\DTOs\UpdateAccessLinkFileHostingData;
use App\Infrastructure\Persistence\Doctrine\Entities\HostedFileEntity;
use Doctrine\ORM\EntityRepository;

class HostedFileRepository extends EntityRepository implements FileHostingRepositoryContract
{
    public function create(CreateFileHostingData $hostedFile): int
    {
        $hostedFile = new HostedFileEntity(
            $hostedFile->fileId,
            $hostedFile->hosting->id,
            $hostedFile->status->value,
        );

        $this->getEntityManager()->persist($hostedFile);
        $this->getEntityManager()->flush();

        return $hostedFile->getId();
    }

    public function updateAccessLink(int $fileHostingId, UpdateAccessLinkFileHostingData $fileHosting): void
    {
        $hostedFile = $this->find($fileHostingId);

        $hostedFile->setStatus($fileHosting->status)
            ->setWebContentLink($fileHosting->webContentLink)
            ->setWebViewLink($fileHosting->webViewLink)
            ->setExternalFileId($fileHosting->externalFileId);

        $this->getEntityManager()->persist($hostedFile);
        $this->getEntityManager()->flush();
    }
}
