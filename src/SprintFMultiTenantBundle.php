<?php

namespace SprintF\Bundle\MultiTenant;

use SprintF\Bundle\MultiTenant\Doctrine\Filter\TenantFilter;
use SprintF\Bundle\MultiTenant\EventListener\TenantEventListener;
use SprintF\Bundle\MultiTenant\Registry\DoctrineRepositoryTenantRegistry;
use SprintF\Bundle\MultiTenant\Registry\TenantRegistryInterface;
use SprintF\Bundle\MultiTenant\Resolver\DomainsMapTenantResolver;
use SprintF\Bundle\MultiTenant\Resolver\DomainTenantResolver;
use SprintF\Bundle\MultiTenant\Resolver\QueryTenantResolver;
use SprintF\Bundle\MultiTenant\Resolver\SubdomainTenantResolver;
use SprintF\Bundle\MultiTenant\Resolver\TenantResolverInterface;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class SprintFMultiTenantBundle extends AbstractBundle
{
    public function prependExtension(ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        // Конфигурируем Doctrine, добавляя к ее конфигу фильтр запросов TenantFilter
        $builder->prependExtensionConfig('doctrine', ['orm' => ['filters' => [
            'tenant_filter' => [
                'class' => TenantFilter::class,
                'enabled' => false,
            ],
        ]]]);
    }

    public function configure(DefinitionConfigurator $definition): void
    {
        // Описываем конфигурацию бандла
        $definition->rootNode()
            ->children()

                // Конфигурация сущности арендатора
                ->scalarNode('tenant_entity')
                    ->cannotBeEmpty()
                    ->defaultValue('\\App\\Entity\\Tenant')
                    ->info('The fully qualified class name of tenant entity')
                ->end() // tenant_entity

                // Конфигурация поля в сущностях, указывающего на арендатора
                ->scalarNode('tenant_field')
                    ->cannotBeEmpty()
                    ->defaultValue('tenant')
                    ->info('The name of the tenant field in entities')
                ->end() // tenant_field

                // Конфигурация резолвера арендатора
                ->enumNode('resolver')
                    ->cannotBeEmpty()
                    // Список реализованных резолверов
                    ->values(['query', 'subdomain', 'domain','domains_map'])
                    ->defaultValue('query')
                    ->info('The name of the tenant resolver')
                ->end() // resolver

                // Конфигурация резолвера на основе данных из get-параметров запроса
                ->arrayNode('query')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('parameter')
                            ->defaultValue('tenant')
                            ->info('Query parameter name to use for tenant resolution')
                        ->end()
                    ->end()
                ->end() // query

                // Конфигурация резолвера на основе субдомена хоста запроса
                ->arrayNode('subdomain')
                    ->addDefaultsIfNotSet()
                    ->children()
                        // Базовый домен, относительно которого резолвер будет искать субдомен
                        ->scalarNode('base_domain')
                            ->defaultValue('localhost')
                            ->info('The base domain for subdomain resolution')
                        ->end() // base_domain
                        // Субдомены, исключающиеся из рассмотрения
                        ->arrayNode('excluded_subdomains')
                            ->scalarPrototype()->end()
                            ->defaultValue(['www', 'api', 'admin', 'mail', 'ftp'])
                            ->info('Subdomains to exclude from tenant resolution')
                        ->end() // excluded_subdomains
                    ->end() // children
                ->end() // subdomain

                // Конфигурация резолвера на основе домена хоста запроса
                ->arrayNode('domains_map')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('map')
                            ->useAttributeAsKey('domain')
                            ->scalarPrototype()->end()
                            ->defaultValue([])
                            ->info('Domains map (full domains to tenant slugs) for use for tenant resolution')
                        ->end()
                    ->end()
                ->end() // domain

            ->end() // children
        ;
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        // Импортируем оснвоной конфиг сервисов бандла.
        $container->import('../config/services.yaml');

        // Регистрируем реестр арендаторов, указываем ему имя класса сущности арендатора.
        $builder->register(DoctrineRepositoryTenantRegistry::class)
            ->setAutowired(true)
            ->setAutoconfigured(true)
            ->setArgument('$tenantEntityClass', $config['tenant_entity']);
        $builder->setAlias(TenantRegistryInterface::class, DoctrineRepositoryTenantRegistry::class);

        // Передаем важный параметр в TenantEventListener
        $builder->register(TenantEventListener::class)
            ->setAutowired(true)
            ->setAutoconfigured(true)
            ->setArgument('$tenantFieldName', $config['tenant_field']);

        // Регистрируем конкретный резолвер арендаторов, выбирая на основе конфигурации бандла:
        switch ($config['resolver']) {
            case 'query':
                // Резолвер на основе данных из get-параметров запроса
                $builder->register(QueryTenantResolver::class)
                    ->setAutowired(true)
                    ->setAutoconfigured(true)
                    ->setArgument('$parameterName', $config['query']['parameter']);
                $builder->setAlias(TenantResolverInterface::class, QueryTenantResolver::class);
                break;

            case 'subdomain':
                // Резолвер на основе субдомена хоста запроса
                $builder->register(SubdomainTenantResolver::class)
                    ->setAutowired(true)
                    ->setAutoconfigured(true)
                    ->setArgument('$baseDomain', $config['subdomain']['base_domain'])
                    ->setArgument('$excludedSubdomains', $config['subdomain']['excluded_subdomains']);
                $builder->setAlias(TenantResolverInterface::class, SubdomainTenantResolver::class);
                break;

            case 'domain':
                // Резолвер на основе хоста запроса
                $builder->register(DomainTenantResolver::class)
                    ->setAutowired(true)
                    ->setAutoconfigured(true);
                $builder->setAlias(TenantResolverInterface::class, DomainTenantResolver::class);
                break;

            case 'domains_map':
                // Резолвер на основе хоста запроса из файла конфигурации
                $builder->register(DomainsMapTenantResolver::class)
                    ->setAutowired(true)
                    ->setAutoconfigured(true)
                    ->setArgument('$domainsMap', $config['domains_map']['map']);
                $builder->setAlias(TenantResolverInterface::class, DomainsMapTenantResolver::class);
                break;
        }
    }
}
