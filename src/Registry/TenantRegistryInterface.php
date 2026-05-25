<?php

namespace SprintF\Bundle\MultiTenant\Registry;

use SprintF\Bundle\MultiTenant\Tenant\TenantInterface;

/**
 * Реестр арендаторов.
 * Общий интерфейс для получения арендаторов из различных хранилищ.
 */
interface TenantRegistryInterface
{
    /**
     * Список всех арендаторов в приложении.
     *
     * @return iterable|TenantInterface[]
     */
    public function findAll(): iterable;

    /**
     * Метод поиска арендатора по уникальному идентификатору.
     */
    public function findOneById(int|string|\Stringable $id): ?TenantInterface;

    /**
     * Метод поиска арендатора по уникальному символическому имени.
     */
    public function findOneBySlug(string $slug): ?TenantInterface;

    /**
     * Метод поиска арендатора по уникальному домену.
     */
    public function findOneByDomain(string $domain): ?TenantInterface;
}
