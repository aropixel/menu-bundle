# Installation

To install the Aropixel Menu Bundle, follow these steps:

### 1. Prerequisites

The Aropixel Menu Bundle requires the **Aropixel Admin Bundle** to be already installed and configured in your Symfony project.

### 2. Downloading the Bundle

Use Composer to add the bundle to your dependencies:

```bash
composer require aropixel/menu-bundle
```

### 3. Bundle Configuration

Create or modify the file `config/packages/aropixel_menu.yaml` to define your menus:

```yaml
aropixel_menu:
    entity: App\Entity\Menu # Your custom menu entity (see "Entity Customization")
    menus:
        main:
            name: "Main Menu"
            depth: 3            # Maximum nesting level
            strict_mode: false  # If true, an item cannot be added twice to the menu
        footer:
            name: "Footer Menu"
            depth: 1
```

### 4. Database Update

Apply migrations or update your schema:

```bash
php bin/console doctrine:schema:update --force
```

### 5. Assets and Stimulus

The bundle uses a **Stimulus controller** for the menu administration.

Since version 2.3, the Stimulus controller is **automatically detected** if you are using **AssetMapper** or the **Symfony UX Stimulus Bundle**. No manual configuration is required in `assets/controllers.json` or `assets/bootstrap.js`.

The controller is registered under the name `aropixel-menu`.

### 6. Including Routes

Add the bundle routes in `config/routes.yaml`:

```yaml
aropixel_menu:
    resource: '@AropixelMenuBundle/Resources/config/routes.yaml'
    prefix: /admin
```
