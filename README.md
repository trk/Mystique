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

This mean if you `remove a field` from your `config file`, field will be removed from edit screen. As you see on `youtube video`.


#### ~~Using `Mystique` with your module or use different `configs path`, `autoload` need to be `true` for modules~~

~~Default configs path is `site/templates/configs/`, and your config file name need to start with `Mystique.` and need to end with `.php` extension.~~

### _Adding custom path not supporting anymore !_
```php
// Add your custom path inside your module class`init` function, didn't tested outside
public function init()
{
    $path = __DIR__ . DIRECTORY_SEPARATOR . 'configs' . DIRECTORY_SEPARATOR;
    Mystique::add($path);
}
```

### Mystique module will search `site/modules/**/configs/Mystique.*.php` and `site/templates/Mystique.*.php` paths for `Mystique` config files.

All config files need to return a `PHP ARRAY` like examples. 


#### Usage almost same with ProcessWire Inputfield Api, only difference is `set` and `showIf` usage like on example.

```php
<?php

namespace ProcessWire;

/**
 * Resource : testing-mystique
 */
return [
    'title' => __('Testing Mystique'),
    'fields' => [
        'text_field' => [
            'label' => __('You can use short named types'),
            'description' => __('In file showIf working like example'),
            'notes' => __('Also you can use $input->set() method'),
            'type' => 'text',
            'showIf' => [
                'another_text' => "=''"
            ],
            'set' => [
                'showCount' => InputfieldText::showCountChars,
                'maxlength' => 255
            ],
            'attr' => [
                'attr-foo' => 'bar',
                'attr-bar' => 'foo'
            ]
        ],
        'another_text' => [
            'label' => __('Another text field (default type is text)')
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


