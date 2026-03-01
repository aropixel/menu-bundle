# Custom Data Sources

The Aropixel Menu Bundle allows you to add custom data sources (e.g., Products, Categories, External APIs) to your menu.

### 1. Creating a Custom Source

To create a new data source, you must implement the `Aropixel\MenuBundle\Source\MenuSourceInterface`.

```php
namespace App\Menu;

use Aropixel\MenuBundle\Entity\MenuInterface;
use Aropixel\MenuBundle\Source\MenuSourceInterface;
use App\Repository\ProductRepository;

class ProductMenuSource implements MenuSourceInterface
{
    public function __construct(private ProductRepository $productRepository) {}

    public function getName(): string { return 'product'; }
    public function getLabel(): string { return 'Products'; }
    public function getColor(): string { return 'bg-primary'; }

    public function getAvailableItems(array $menuItems): array
    {
        // Return a collection of items to display in the selector panel
        $products = $this->productRepository->findAll();
        $items = [];
        foreach ($products as $product) {
            $items[] = [
                'value' => $product->getId(),
                'label' => $product->getName(),
                'type' => 'product',
                'alreadyIncluded' => false // Logic to check if already in $menuItems
            ];
        }
        return $items;
    }

    public function getSelectionTemplate(): string
    {
        return 'menu/sources/product.html.twig';
    }

    public function supports(string $type): bool
    {
        return $type === 'product';
    }

    public function getPayload(MenuInterface $menuItem): array
    {
        // Data to be stored in the DOM 'data-payload' attribute and sent back on save
        return [
            'type' => 'product',
            'value' => $menuItem->getCustomId(), // Example field in your custom Menu entity
        ];
    }

    public function mapToEntity(array $data, MenuInterface $menuItem): void
    {
        // Logic to update the Menu entity from the received payload
        // $data['value'] contains the ID sent back by the JS
        $menuItem->setType('product');
        // $menuItem->setCustomId($data['value']);
    }
}
```

### 2. Selection Template

You must create a Twig template to render the selection form in the menu administration.

If you want a list of checkboxes (similar to pages):

```twig
{# templates/menu/sources/product.html.twig #}
<div>
    {% for resource in items %}
        <div class="custom-control custom-checkbox d-flex">
            <input type="checkbox"
                   class="custom-control-input me-2"
                   id="{{ source.name }}{{ loop.index0 }}"
                   name="{{ source.name }}[]"
                   value="{{ resource.value }}"
                   data-type="{{ resource.type }}"
                   data-label="{{ resource.label }}"
                   data-color="{{ source.color }}"
                   data-source="{{ source.name }}"
            >
            <label class="custom-control-label" for="{{ source.name }}{{ loop.index0 }}">
                <span {{ resource.alreadyIncluded ? "style='color: #666;'" : "" }}>{{ resource.label }}</span>
            </label>
        </div>
    {% endfor %}
</div>
```

The Stimulus controller `aropixel-menu` will automatically handle these checkboxes.

### 3. Registering the Source

Because the bundle uses `autoconfigure: true`, your class is automatically registered and tagged with `aropixel_menu.source` if it implements `MenuSourceInterface`.

If you are not using autoconfiguration, tag your service manually:

```yaml
services:
    App\Menu\ProductMenuSource:
        tags: ['aropixel_menu.source']
```

### 3. Usage in Templates

The bundle will automatically:
1. Create a new panel in the menu management interface for your source.
2. Handle the drag-and-drop and serialization of your custom items.
3. Call your `mapToEntity` method when saving the menu.

To display the link on the front-end, use the `MenuProvider`:

```twig
{# templates/layout.html.twig #}
{% set main_menu = menu_provider.getMenu('main') %}

<ul>
    {% for item in main_menu %}
        <li>
            <a href="{{ path('product_show', {id: item.customId}) }}">{{ item.title }}</a>
        </li>
    {% endfor %}
</ul>
```
