<?php

declare(strict_types= 1);

namespace App\Infrastructure\Persistence\Doctrine\Repositories;

use App\Domain\Sender\Contracts\HostingRepository as HostingRepositoryContract;
use App\Domain\Sender\DTOs\HostingData;
use App\Infrastructure\Persistence\Doctrine\Entities\HostingEntity;
use Doctrine\ORM\EntityRepository;

class HostingRepository extends EntityRepository implements HostingRepositoryContract
{
    public function queryBySlugs(array $hostingSlugs): array
    {
        $hosts = $this->createQueryBuilder('h')
            ->where('h.slug IN (:slugs)')
            ->setParameter('slugs', $hostingSlugs)
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
