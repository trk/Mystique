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

    if ($page instanceof Page && $page->id && $page->template->name == 'page') {
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