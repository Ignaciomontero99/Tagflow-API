# Tagflow Fixtures

Colección completa de fixtures para el schema profesional de Tagflow.

## Instalar dependencias

```bash
composer require --dev doctrine/doctrine-fixtures-bundle fakerphp/faker
```

## Ejecutar todo

```bash
php bin/console doctrine:fixtures:load --group=all --no-interaction
```

## Ejecutar por grupos

```bash
php bin/console doctrine:fixtures:load --group=base --no-interaction
php bin/console doctrine:fixtures:load --group=social --no-interaction
php bin/console doctrine:fixtures:load --group=messaging --no-interaction
php bin/console doctrine:fixtures:load --group=moderation --no-interaction
php bin/console doctrine:fixtures:load --group=security --no-interaction
```

## Quitar el límite de 300 segundos

### `composer.json`

```json
{
  "config": {
    "process-timeout": 0
  }
}
```

### CMD

```cmd
set COMPOSER_PROCESS_TIMEOUT=0
```

### PowerShell

```powershell
$env:COMPOSER_PROCESS_TIMEOUT=0
```

## Nota importante

Estos fixtures siguen el schema SQL profesional que preparamos.  
Si tus entities usan otros nombres de propiedades o setters, ajusta estos puntos:

- `setPasswordHash()` / `setPassword()`
- `setProfileImageUrl()`
- `setConversationType()`
- `setReferenceType()`
- `setLastReadMessage()` o `setLastReadMessageId()`
