<?php

namespace SprintF\Bundle\MultiTenant\Tenant;

/**
 * Общий интерфейс для сущностей арендаторов.
 */
interface TenantInterface
{
    /**
     * Уникальный идентификатор сущности арендатора.
     * null предусматривается на случай "новой" сущности, еще не получившей идентификатор.
     */
    public function getId(): int|string|\Stringable|null;

    /**
     * Уникальное символическое имя арендатора.
     */
    public function getSlug(): string;

    /**
     * Уникальный домен арендатора.
     */
    public function getDomain(): ?string;
}
