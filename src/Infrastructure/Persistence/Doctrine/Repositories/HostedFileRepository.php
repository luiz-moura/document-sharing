<?php

declare(strict_types= 1);

namespace App\Infrastructure\Persistence\Doctrine\Repositories;

use App\Domain\Sender\Contracts\HostedFileRepository as HostedFileRepositoryContract;
use App\Domain\Sender\DTOs\CreateHostedFileData;
use App\Domain\Sender\DTOs\UpdateAccessLinkHostedFileData;
use App\Infrastructure\Persistence\Doctrine\Entities\HostedFileEntity;
use Doctrine\ORM\EntityRepository;

class HostedFileRepository extends EntityRepository implements HostedFileRepositoryContract
{
    public function create(CreateHostedFileData $hostedFile): int
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

    public function updateAccessLink(int $hostedFileId, UpdateAccessLinkHostedFileData $hostedFile): void
    {
        $hostedFile = $this->find($hostedFileId);

        $hostedFile->setStatus($hostedFile->status)
            ->setWebContentLink($hostedFile->webContentLink)
            ->setWebViewLink($hostedFile->webViewLink)
            ->setExternalFileId($hostedFile->externalFileId);

        $this->getEntityManager()->persist($hostedFile);
        $this->getEntityManager()->flush();
    }
}
