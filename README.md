# Mystique Module for ProcessWire CMS/CMF

This module allow you to create fields and store fields data on database by using a config file.

**Module includes and requires:** `FieldtypeMystique` and `InputfieldMystique`

You can create config files under `site/templates/configs/` folder. And these files need to be `.php` file and need to return an php array with fields key.

Mystique module sending `field options` to `ProcessWire` after making little bit modifications. Only difference is `showIf` option.

If you want to use `showIf` option for in file, use it like on example. Because Mystique module adding a prefix on field name for each Mystique field.

- `site/templates/configs/Mystique.seo-fields.php`

```php
<?php

namespace ProcessWire;

return [
    'title' => __('Seo fields'),
    'fields' => [
        'window_title' => [
            'label' => __('Window title'),
            'type' => Mystique::TEXT, // or InputfieldText
            'useLanguages' => true,
            'attr' => [
                'placeholder' => __('Enter a window title')
            ]
        ],
        'navigation_title' => [
            'label' => __('Navigation title'),
            'type' => Mystique::TEXT, // or InputfieldText
            'useLanguages' => true,
            'showIf' => [
                'window_title' => "!=''"
            ],
            'attr' => [
                'placeholder' => __('Enter a navigation title')
            ]
        ],
        'description' => [
            'label' => __('Description for search engines'),
            'type' => Mystique::TEXTAREA,
            'useLanguages' => true
        ]
    ]
];
```


