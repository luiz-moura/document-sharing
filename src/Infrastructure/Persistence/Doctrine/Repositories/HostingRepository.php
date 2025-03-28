<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Repositories;

use App\Domain\Sender\Contracts\HostingRepository as HostingRepositoryContract;
use App\Domain\Sender\DTOs\HostingEntity as DomainHostingEntity;
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
            fn (HostingEntity $hosting): DomainHostingEntity => new DomainHostingEntity(
                $hosting->getId(),
                $hosting->getSlug(),
                $hosting->getName(),
                $hosting->getRefreshableToken(),
                $hosting->getAccessToken(),
            ),
            $hostings
        );
    }

    public function findBySlug(string $slug): ?DomainHostingEntity
    {
        /** @var ?HostingEntity $entity */
        $entity = $this->findOneBy(['slug' => $slug]);

        if (is_null($entity)) {
            return null;
        }

        return new DomainHostingEntity(
            $entity->getId(),
            $entity->getSlug(),
            $entity->getName(),
            $entity->getRefreshableToken(),
            $entity->getAccessToken(),
        );
    }

    public function updateRefreshableTokenBySlug(string $slug, string $refreshableToken): void
    {
        /** @var ?HostingEntity $entity */
        $entity = $this->findOneBy(['slug' => $slug]);

        if (is_null($entity)) {
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

        if (is_null($entity)) {
            throw new RuntimeException(sprintf('Hosting with slug %s not found', $slug));
        }

        $entity->setAccessToken($accessToken);

        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();
    }
}
