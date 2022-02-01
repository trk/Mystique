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
