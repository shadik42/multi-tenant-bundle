# Конфигурация бандла

## Сущность арендатора
Арендатор должен представлять собой класс, являющийся сущностью Doctrine.
Этот класс указывается в конфиге по умолчанию, как ...
```yaml
sprint_f_multi_tenant:
  
  # The fully qualified class name of tenant entity
  tenant_entity: '\\App\\Entity\\Tenant'
```
... и может быть изменен.

## Поле в сущностях, указывающее на арендатора
У тех сущностей, которые принадлежат арендатору (к примеру - пользователи), должно быть поле, связывающее сущность 
с конкретным арендатором.
К примеру:
```php
class User
{
    #[ManyToOne]
    private Tenant $tenant;
}
```
Имя этого поля задается в конфигурации бандла, и по умолчанию эта конфигурация выглядит так:
```yaml
sprint_f_multi_tenant:
  
  # The name of the tenant field in entities
  tenant_field: 'tenant'
```
Ожидаемое бандлом имя поля связи с арендатором можно изменить, используя конфигурацию.

## Способы определения контекста аренды (резолверы)

### Параметр запроса (query parameter)
Резолвер по умолчанию.

Активируется указанием
```yaml
sprint_f_multi_tenant:
  
  # The name of the tenant resolver
  resolver: query
```
в конфигурации бандла (именно это значение подставляется по умолчанию, если не выбрано никакое другое).

Работает следующим образом:
1. Определяется имя get-параметра запроса, содержащее символьное имя арендатора
2. Проводится поиск арендатора по его символьному имени.

Конфигурация по умолчанию:
```yaml
sprint_f_multi_tenant:
  
  query:
    # Query parameter name to use for tenant resolution
    parameter: tenant
```
В конфигурации резолвера можно изменить имя параметра запроса (по умолчанию `tenant`), по которому определяется арендатор.

### Субдомен (subdomain)
Активируется указанием
```yaml
sprint_f_multi_tenant:
  
  # The name of the tenant resolver
  resolver: subdomain
```
в конфигурации бандла.

Работает следующим образом:
1. Определяется субдомен запроса, относительно указанного в конфигурации базового домена.
2. Отбрасываются субдомены-исключения.
3. Проводится поиск арендатора по его символьному имени, совпадающего с субдоменом.

Конфигурация резолвера по умолчанию:
```yaml
sprint_f_multi_tenant:
  
  subdomain:
    # The base domain for subdomain resolution
    base_domain: localhost
    # Subdomains to exclude from tenant resolution
    excluded_subdomains: ['www', 'api', 'admin', 'mail', 'ftp']
```
В конфигурации можно настроить базовый домен (относительно которого будет определяться субдомен) и исключаемые из поиска
арендатора служебные субдомены.

### Домен (domain)
Активируется указанием
```yaml
sprint_f_multi_tenant:
  
  # The name of the tenant resolver
  resolver: domain
```
в конфигурации бандла

Работает следующим образом:
1. Определяется домен запроса
2. Проводится поиск арендатора по его домену. 

### Карта доменов (domains map)
Активируется указанием
```yaml
sprint_f_multi_tenant:

  # The name of the tenant resolver
  resolver: domains_map
```
в конфигурации бандла.

Работает следующим образом:
1. Определяется домен запроса.
2. Сопоставляется с указанной в конфиге картой соответствий "домен - символьное имя арендатора".
3. Проводится поиск арендатора по его символьному имени.

Конфигурация резолвера по умолчанию:
```yaml
sprint_f_multi_tenant:
  
  domains_map:
    # Domains map (full domains to tenant slugs) for use for tenant resolution
    map: []
```
Настройке подлежит карта соответствий "доменное имя - символьное имя арендатора". Пример:
```yaml
sprint_f_multi_tenant:
  
  domains_map:
    map:
      foo.example: foo
      test.example: test
```
