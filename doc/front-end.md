# Front-end Usage

The bundle exposes two Twig filters and one Twig function to render menus in your templates.

### Twig function and filters

| Symbol | Type | Description |
|---|---|---|
| `get_menu(code)` | function | Returns the menu items for the given menu code |
| `\|get_link` | filter | Resolves the URL of a menu item |
| `\|is_section` | filter | Returns `true` if the item is a section (no link) |

### Basic usage

```twig
{% for item in get_menu('main') %}
    {% if item|is_section %}
        <span>{{ item.title }}</span>
    {% else %}
        <a href="{{ item|get_link }}">{{ item.title }}</a>
    {% endif %}
{% endfor %}
```

### Nested menus

```twig
{% for item in get_menu('main') %}
    {% if item.children|length > 0 %}
        <li>
            <span>{{ item.title }}</span>
            <ul>
                {% for child in item.children %}
                    <li><a href="{{ child|get_link }}">{{ child.title }}</a></li>
                {% endfor %}
            </ul>
        </li>
    {% else %}
        <li><a href="{{ item|get_link }}">{{ item.title }}</a></li>
    {% endif %}
{% endfor %}
```

### How link resolution works

The `|get_link` filter delegates to the `MenuSourceChain`, which iterates over all registered sources and calls `resolveUrl()` on the one that supports the item's type.

Built-in source types:

| Type | Resolved URL |
|---|---|
| `link` | The raw URL stored on the item |
| `page` | Route generated via `page_route` config key (default: `app_page_show`) |
| `section` | `#` (not a link) |

To add a custom source type, see [Custom Data Sources](sources.md).

### Configuring the page route (optional)

If your page route differs from the default (`app_page_show`), configure it:

```yaml
aropixel_menu:
    page_route: my_custom_page_show
```

The route must accept a `slug` parameter.
