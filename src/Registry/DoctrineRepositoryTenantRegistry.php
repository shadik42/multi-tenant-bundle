<?php

namespace SprintF\Bundle\MultiTenant\Registry;

use Doctrine\ORM\EntityManagerInterface;
use SprintF\Bundle\MultiTenant\Tenant\TenantInterface;

/**
 * Реестр арендаторов, использующий для их поиска репозиторий сущностей Doctrine.
 */
class DoctrineRepositoryTenantRegistry implements TenantRegistryInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly string $tenantEntityClass,
    ) {
    }

    public function findAll(): iterable
    {
        return $this->entityManager->getRepository($this->tenantEntityClass)->findAll();
    }

    public function findOneById(\Stringable|int|string $id): ?TenantInterface
    {
        return $this->entityManager->getRepository($this->tenantEntityClass)->find($id);
    }

    public function findOneBySlug(string $slug): ?TenantInterface
    {
        return $this->entityManager->getRepository($this->tenantEntityClass)->findOneBy(['slug' => $slug]);
    }

    public function findOneByDomain(string $domain): ?TenantInterface
    {
        return $this->entityManager->getRepository($this->tenantEntityClass)->findOneBy(['domain' => $domain]);
    }
}
