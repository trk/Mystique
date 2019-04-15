# Mystique Module for ProcessWire CMS/CMF

[![IMAGE ALT TEXT HERE](http://img.youtube.com/vi/OlMxaQA5vTY/0.jpg)](http://www.youtube.com/watch?v=OlMxaQA5vTY)

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
        ],
        'page_tpye' => [
            'label' => __('Type'),
            'type' => Mystique::SELECT,
            'options' => [
                'basic' => __('Basic page'),
                'gallery' => __('Gallery'),
                'blog' => __('Blog')
            ]
        ],
        'show_on_nav' => [
            'label' => __('Display this page on navigation'),
            'type' => Mystique::CHECKBOX
        ]
    ]
];
```

Searching data inside this module is limited, because all data storing as a json value on to database.

Making search operator not important for Mystique field, on search process it will be changed with `%=` operator.

### Search example
```php
// Looking for checlbox value, `=` operator will force converted to `%=` operator
$navigationPages = pages()->find('template=basic-page, mystique_field.show_on_nav=1');
// Looking for checlbox value, `=` operator will force converted to `%=` operator
$navigationPages = pages()->find('template=basic-page, mystique_field.page_type=basic');
```


