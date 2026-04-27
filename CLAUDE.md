# CLAUDE.md — MenuBundle

> **IMPORTANT — maintenance safeguard**
> This file documents implicit contracts that are not apparent from reading the code.
> **Any change to an invariant listed here must be reflected here immediately.**
> A stale CLAUDE.md is actively misleading — better to delete it than let it lie.

## Documentation

- [Index](doc/index.md)
- [Installation](doc/installation.md)
- [Entities](doc/entities.md)
- [Menu sources](doc/sources.md)
- [Front-end rendering](doc/front-end.md)

---

## Non-obvious invariants

### `Menu` (MappedSuperclass + Gedmo Nested Set)

- `Menu` is `#[ORM\MappedSuperclass]` + `#[Gedmo\Tree(type: 'nested')]`.
- **Never set `lft`, `rgt`, `lvl`, `root`** — these fields are fully managed by Gedmo Nested Set on `flush()`.
- **Never call `setSlug()`** — `Menu::$slug` is a Gedmo Slug with `TreeSlugHandler` (path separated by `/`). It is computed from the title and the hierarchy.

### Persist order in fixtures

The order of `persist()` calls must follow the hierarchy: **root first, then children left to right**. Gedmo computes `lft`/`rgt` based on insertion order.

```php
$manager->persist($root);     // root first
$manager->persist($child1);   // children next, left-to-right order
$manager->persist($child2);
```

### `type` field — semantics

`Menu::$type` is the **menu tree identifier** (e.g. `'main'`, `'footer'`), not the item type. Every node in a navigation tree shares the same `type`.

### `MenuSourceInterface` — auto-tagging

Menu sources implement `MenuSourceInterface` (8 methods). They are auto-discovered via autoconfiguration — **no need to declare the tag manually** when autoconfiguration is enabled on the container.

Methods to implement: `getName()`, `getLabel()`, `getColor()`, `getAvailableItems()`, `getSelectionTemplate()`, `supports()`, `getPayload()`, `mapToEntity()`, `resolveUrl()`.

### Fixtures — `doctrine/data-fixtures` v2 API

`hasReference()` and `getReference()` **require 2 arguments** since v2:

```php
// Correct
$this->hasReference('project-1', \App\Entity\Project::class)
$this->getReference('project-1', \App\Entity\Project::class)

// Wrong — throws an error in v2
$this->hasReference('project-1')
```

### Required configuration

Available menus must be declared in `config/packages/aropixel_menu.yaml`:

```yaml
aropixel_menu:
    menus:
        main:
            name: 'Main navigation'
            depth: 3
```

Without this config, the menu controller does not know which trees exist.
