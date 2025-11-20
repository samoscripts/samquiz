# Doctrine Entities

Ten katalog zawiera encje Doctrine ORM.

## Struktura

Encje Doctrine powinny być umieszczone w tym katalogu i używać atrybutów Doctrine:

```php
<?php

namespace App\Infrastructure\Persistence\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'table_name')]
class EntityName
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;
    
    // ...
}
```

## Migracje

Po utworzeniu lub zmianie encji, wygeneruj migrację:

```bash
php bin/console make:migration
php bin/console doctrine:migrations:migrate
```



