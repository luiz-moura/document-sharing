<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Repositories;

use App\Domain\Sender\Contracts\FileRepository as FileRepositoryContract;
use App\Domain\Sender\DTOs\CreateFileData;
use App\Infrastructure\Persistence\Doctrine\Entities\FileEntity;
use Doctrine\ORM\EntityRepository;

class FileRepository extends EntityRepository implements FileRepositoryContract
{
    public function create(CreateFileData $file): int
    {
        $fileEntity = new FileEntity(
            $file->uuid,
            $file->filename,
            $file->size,
            $file->mimeType,
        );

        $this->getEntityManager()->persist($fileEntity);
        $this->getEntityManager()->flush();

        return $fileEntity->getId();
    }
}
