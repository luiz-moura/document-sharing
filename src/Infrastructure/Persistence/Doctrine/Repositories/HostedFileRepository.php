<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Repositories;

use App\Domain\Sender\Contracts\HostedFileRepository as HostedFileRepositoryContract;
use App\Domain\Sender\DTOs\CreateHostedFileData;
use App\Domain\Sender\DTOs\UpdateAccessLinkHostedFileData;
use App\Domain\Sender\Enums\FileStatusOnHostEnum;
use App\Infrastructure\Persistence\Doctrine\Entities\HostedFileEntity;
use Doctrine\ORM\EntityRepository;

class HostedFileRepository extends EntityRepository implements HostedFileRepositoryContract
{
    public function create(CreateHostedFileData $hostedFile): int
    {
        $hostedFileEntity = new HostedFileEntity(
            $hostedFile->fileId,
            $hostedFile->hostingId,
            $hostedFile->status->value,
        );

        $this->getEntityManager()->persist($hostedFileEntity);
        $this->getEntityManager()->flush();

        return $hostedFileEntity->getId();
    }

    public function updateAccessLink(int $hostedFileId, UpdateAccessLinkHostedFileData $hostedFile): void
    {
        $hostedFileEntity = $this->find($hostedFileId);

        $hostedFileEntity->setStatus($hostedFile->status)
            ->setWebContentLink($hostedFile->webContentLink)
            ->setWebViewLink($hostedFile->webViewLink)
            ->setExternalFileId($hostedFile->externalFileId);

        $this->getEntityManager()->persist($hostedFileEntity);
        $this->getEntityManager()->flush();
    }

    public function updateStatus(int $hostedFileId, FileStatusOnHostEnum $status): void
    {
        $hostedFileEntity = $this->find($hostedFileId);

        $hostedFileEntity->setStatus($status);

        $this->getEntityManager()->persist($hostedFileEntity);
        $this->getEntityManager()->flush();
    }
}
