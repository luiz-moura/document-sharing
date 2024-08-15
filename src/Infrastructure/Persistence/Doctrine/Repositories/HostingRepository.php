<?php

declare(strict_types= 1);

namespace App\Infrastructure\Persistence\Doctrine\Repositories;

use App\Domain\Sender\Contracts\HostingRepository as HostingRepositoryContract;
use App\Domain\Sender\DTOs\HostingData;
use App\Infrastructure\Persistence\Doctrine\Entities\HostingEntity;
use Doctrine\ORM\EntityRepository;

class HostingRepository extends EntityRepository implements HostingRepositoryContract
{
    public function queryByIds(array $hostingIds): array
    {
        $hosts = $this->createQueryBuilder('h')
            ->where('h.id IN (:ids)')
            ->setParameter('ids', $hostingIds)
            ->getQuery()
            ->getResult();

        return array_map(
            fn (HostingEntity $hosting) => new HostingData(
                $hosting->getId(),
                $hosting->getName(),
                $hosting->getSlug(),
            ),
            $hosts
        );
    }
}
