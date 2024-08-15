<?php

declare(strict_types= 1);

namespace App\Infrastructure\Persistence\Doctrine\Repositories;

use App\Domain\Sender\Contracts\FileRepository as FileRepositoryContract;
use App\Domain\Sender\DTOs\CreateFileData;
use App\Infrastructure\Persistence\Doctrine\Entities\FileEntity;
use Doctrine\ORM\EntityRepository;

class FileRepository extends EntityRepository implements FileRepositoryContract
{
    public function create(CreateFileData $file): int
    {
        $file = new FileEntity(
            $file->name,
            $file->size,
            $file->mimeType,
        );

        $this->getEntityManager()->persist($file);
        $this->getEntityManager()->flush();

        return $file->getId();
    }
}
