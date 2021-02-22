# Mystique Module for ProcessWire CMS/CMF

[![QUICK VIDEO TUTORIAL](http://img.youtube.com/vi/qkYIOmJmiuU/0.jpg)](http://www.youtube.com/watch?v=qkYIOmJmiuU)


`Mystique` module allow you to create dynamic fields and store dynamic fields data on database by using a config file.


## Requirements

* ProcessWire `3.0` or newer
* PHP `7.0` or newer
* FieldtypeMystique
* InputfieldMystique

## Installation

Install the module from the [modules directory](https://modules.processwire.com/modules/mystique/):

Via `Composer`:

```
composer require trk/mystique
```

Via `git clone`:

```
cd your-processwire-project-folder/
cd site/modules/
git clone https://github.com/trk/Mystique.git
```

#### Module in live reaction with your `Mystique config file`

This mean if you `remove a field` from your `config file`, field will be removed from edit screen. As you see on `Youtube Video`.

### Mystique module will check these paths for get `config` files

- site/modules/*/configs/`{Mystique.*.php, mystique.*.php, mystique.*.json, Mystique.*.json}`
- site/templates/configs/`{Mystique.*.php, mystique.*.php, mystique.*.json, Mystique.*.json}`

All config files need to return a `Valid JSON Array` or `PHP array` like examples.

If your config file return a `Closure`, `$page`, `$field` and `$value` will passed to your config


#### Usage almost same with ProcessWire Inputfield Api, only difference is `attr`, `attrs`, `set`, `wrapAttr`, `wrapAttrs` and `showIf` usage like on example.

- `site/modules/Mystique/configs/Mystique.magician.php`

```php
<?php

namespace ProcessWire;

/**
 * Resource: magic of mystique field
 */
return function ($page = null, $field = null, $value = null) {

    $fields = [
        'hello' => [
            'label' => 'Are you ready for a Magic ?',
            'type' => 'select',
            'options' => [
                'no' => 'No',
                'yes' => 'Yes'
            ],
            'required' => true,
            'defaultValue' => 'no'
        ]
    ];

    if ($page instanceof Page && $page->template->name == 'page') {
        $fields['current_page'] = [
            'label' => 'Current page title : ' . $page->title,
            'value' => $page->title,
            'showIf' => [
                'hello' => '=yes'
            ],
            'columnWidth' => 50
        ];
    }

    if ($field instanceof Field) {
        $fields['current_field'] = [
            'label' => 'Current field label : ' . $field->label,
            'value' => $field->label,
            'showIf' => [
                'hello' => '=yes'
            ],
            'columnWidth' => 50
        ];
    }

    return [
        'name' => 'magician',
        'title' => 'Do A Magic ?',
        'fields' => $fields
    ];

};
```
- `site/modules/Mystique/configs/Mystique.example-dive.php`

```php
<?php

namespace ProcessWire;

/**
 * Resource : Example dive deeper
 */
return [
    'title' => __('Example : Dive'),
    'fields' => [
        'title' => [
            'label' => __('Title'),
            'type' => Mystique::TEXT,
            'useLanguages' => true
        ],
        'checkbox' => [
            'label' => __('Checkbox test'),
            'type' => Mystique::CHECKBOX,
            'value' => true
        ],
        'headline' => [
            'label' => __('Headline'),
            'type' => Mystique::TEXT,
            'useLanguages' => true,
            'defaultValue' => 'headline'
        ],
        'summary' => [
            'label' => __('Summary'),
            'type' => Mystique::TEXTAREA,
            'useLanguages' => true
        ],
        'fieldset' => [
            'label' => __('Fieldset'),
            'type' => Mystique::FIELDSET,
            'children' => [
                'fieldset_title' => [
                    'label' => 'Title',
                    'type' => Mystique::TEXT
                ],
                'fieldset_description' => [
                    'label' => 'Fieldset Description',
                    'type' => Mystique::TEXTAREA
                ],
                'another_fieldset' => [
                    'type' => Mystique::FIELDSET,
                    'label' => __('Another Fieldset'),
                    'showIf' => [
                        'fieldset_title' => "!=''"
                    ],
                    'children' => [
                        'another_fieldset_title' => [
                            'label' => 'Title',
                            'type' => Mystique::TEXT
                        ],
                        'another_fieldset_description' => [
                            'label' => 'Fieldset Description',
                            'type' => Mystique::TEXTAREA
                        ]
                    ]
                ]
            ]
        ],
        'content' => [
            'label' => __('Content'),
            'type' => Mystique::TEXTAREA,
            'useLanguages' => true
        ]
    ]
];
```

### Example:

- `site/templates/configs/Mystique.seo-fields.php`

```php
<?php

namespace ProcessWire;

/**
 * Resource : seo-fields
 */
return [
    'name' => 'seo-fields',
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

Searching data on `Mystique` field is `limited`. Because, `Mystique` saving data to database in `json` format. When you make search for `Mystique` field, operator not important. Operator will be changed with `%=` operator.

#### Search example

```php
$navigationPages = pages()->find('my_mystique_field.show_on_nav=1');
$navigationPages = pages()->find('my_mystique_field.page_tpye=gallery');
```


