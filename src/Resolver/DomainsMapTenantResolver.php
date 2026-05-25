<?php

namespace SprintF\Bundle\MultiTenant\Resolver;

use SprintF\Bundle\MultiTenant\Registry\TenantRegistryInterface;
use SprintF\Bundle\MultiTenant\Tenant\TenantInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Резолвер арендатора на основе хоста запроса и конфигурации бандла.
 */
class DomainsMapTenantResolver implements TenantResolverInterface
{
    public function __construct(
        private readonly TenantRegistryInterface $registry,
        private readonly array $domainsMap = [],
    ) {
    }

    public function resolveTenant(Request $request): ?TenantInterface
    {
        $host = $request->getHost();

        if (str_contains($host, ':')) {
            $host = explode(':', $host)[0];
        }
        $host = trim(strtolower($host));

        if (!isset($this->domainsMap[$host])) {
            return null;
        }

        $slug = $this->domainsMap[$host];

        return $this->registry->findOneBySlug($slug);
    }
}
