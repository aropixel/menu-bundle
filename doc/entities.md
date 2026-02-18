# Entity Customization

The MenuBundle allows you to customize its base entity (`Menu`) to add properties or modify its behavior. This system is based on Doctrine's `MappedSuperclass`.

### 1. Extending the Entity

To add fields to the menu entity, you must create an entity in your application that inherits from the bundle's `Menu` entity.

```php
// src/Entity/Menu.php
namespace App\Entity;

use Aropixel\MenuBundle\Entity\Menu as BaseMenu;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'app_menu')]
class Menu extends BaseMenu
{
    // Add your custom fields here
    // Example: a checkbox to open in a new tab
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $newTab = false;

    public function isNewTab(): bool
    {
        return $this->newTab;
    }

    public function setNewTab(bool $newTab): self
    {
        $this->newTab = $newTab;
        return $this;
    }
}
```

### 2. Configuring the Bundle

Once your entity is created, you must inform the bundle to use your class. This is done in `config/packages/aropixel_menu.yaml`:

```yaml
aropixel_menu:
    entity: App\Entity\Menu
```

The bundle will then handle:
1. Replacing all internal relations to use your `App\Entity\Menu` class.
2. Using your entity for the controllers and the drag-and-drop management.

### 3. Repository

If you need a custom repository, ensure it extends `Aropixel\MenuBundle\Repository\MenuRepository`.
