<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Repositories;

use App\Domain\Sender\Contracts\HostingRepository as HostingRepositoryContract;
use App\Domain\Sender\DTOs\HostingData;
use App\Infrastructure\Persistence\Doctrine\Entities\HostingEntity;
use Doctrine\ORM\EntityRepository;
use RuntimeException;

class HostingRepository extends EntityRepository implements HostingRepositoryContract
{
    public function queryBySlugs(array $hostingSlugs): array
    {
        $hostings = $this->createQueryBuilder('h')
            ->where('h.slug IN (:slugs)')
            ->setParameter('slugs', $hostingSlugs)
            ->getQuery()
            ->getResult();

        return array_map(
            fn (HostingEntity $hosting): HostingData => new HostingData(
                $hosting->getId(),
                $hosting->getSlug(),
                $hosting->getName(),
                $hosting->getRefreshableToken(),
                $hosting->getAccessToken(),
            ),
            $hostings
        );
    }

    public function findBySlug(string $slug): ?HostingData
    {
        /** @var ?HostingEntity $entity */
        $entity = $this->findOneBy(['slug' => $slug]);

        return $entity ? new HostingData(
            $entity->getId(),
            $entity->getSlug(),
            $entity->getName(),
            $entity->getRefreshableToken(),
            $entity->getAccessToken(),
        ) : null;
    }

    public function updateRefreshableTokenBySlug(string $slug, string $refreshableToken): void
    {
        /** @var ?HostingEntity $entity */
        $entity = $this->findOneBy(['slug' => $slug]);

        if (! $entity) {
            throw new RuntimeException(sprintf('Hosting with slug %s not found', $slug));
        }

        $entity->setRefreshableToken($refreshableToken);

        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();
    }

    public function updateAccessTokenBySlug(string $slug, string $accessToken): void
    {
        /** @var ?HostingEntity $entity */
        $entity = $this->findOneBy(['slug' => $slug]);

        if (! $entity) {
            throw new RuntimeException(sprintf('Hosting with slug %s not found', $slug));
        }

        $entity->setAccessToken($accessToken);

        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();
    }
}
