<?php

namespace FluentFormPro\Components\Post;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

use FluentForm\App\Api\FormProperties;
use FluentForm\App\Helpers\Helper;
use FluentForm\Framework\Helpers\ArrayHelper;

class AcfHelper
{
    use Getter;
    public static function getAcfFields($postType)
    {
        if (!class_exists('\ACF')) {
            return [
                'general' => [],
                'advanced' => []
            ];
        }

        $field_groups = acf_get_field_groups(array(
            'post_type' => $postType
        ));

        return self::classifyFields($field_groups);
    }

    public static function getAcfUserFields()
    {
        if (!class_exists('\ACF')) {
            return [
                'general' => [],
                'advanced' => []
            ];
        }

        $field_groups = acf_get_field_groups([
            "user_role"     => "all"
        ]);

        return self::classifyFields($field_groups);
    }

    private static function classifyFields($field_groups)
    {
        $generalAcfFields = self::getGeneralFields();
        $advancedAcfFields = self::getAdvancedFields();

        $generalFields = [];
        $advancedFields = [];

        foreach ($field_groups as $field_group) {
            $fields = acf_get_fields($field_group);
            foreach ($fields as $field) {
                if (in_array($field['type'], $generalAcfFields)) {
                    $generalFields[$field['key']] = [
                        'type' => $field['type'],
                        'label' => $field['label'],
                        'name' => $field['name'],
                        'key' => $field['key']
                    ];
                } else if (isset($advancedAcfFields[$field['type']])) {
                    $settings = $advancedAcfFields[$field['type']];
                    $advancedFields[$field['key']] = [
                        'type' => $field['type'],
                        'label' => $field['label'],
                        'name' => $field['name'],
                        'key' => $field['key'],
                        'acceptable_fields' => $settings['acceptable_fields'],
                        'help_message' => $settings['help']
                    ];
                }
            }
        }

        return [
            'general' => $generalFields,
            'advanced' => $advancedFields
        ];
    }

    public static function getGeneralFields()
    {
        $generalFields = [
            'text',
            'textarea',
            'number',
            'range',
            'email',
            'url',
            'password',
            'wysiwyg',
            'date_picker',
            'date_time_picker',
            'time_picker',
            'color_picker'
        ];
        $generalFields = apply_filters_deprecated(
            'fluent_post_acf_accepted_general_fields',
            [
                $generalFields
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/post_acf_accepted_general_fields',
            'Use fluentform/post_acf_accepted_general_fields instead of fluent_post_acf_accepted_general_fields.'
        );
        return apply_filters('fluentform/post_acf_accepted_general_fields', $generalFields);
    }

    public static function getAdvancedFields()
    {
        $advancedFields = [
            'select' => [
                'acceptable_fields' => ['select'],
                'help' => __('Select select field for this mapping', 'fluentformpro')
            ],
            'checkbox' => [
                'acceptable_fields' => ['input_checkbox'],
                'help' => __('Select checkbox field for this mapping', 'fluentformpro')
            ],
            'radio' => [
                'acceptable_fields' => ['input_radio'],
                'help' => __('Select radio field for this mapping', 'fluentformpro')
            ],
            'button_group' => [
                'acceptable_fields' => ['input_radio'],
                'help' => __('Select radio field for this mapping', 'fluentformpro')
            ],
            'true_false' => [
                'acceptable_fields' => ['terms_and_condition', 'gdpr_agreement'],
                'help' => __('Select single checkbox field for this mapping', 'fluentformpro')
            ],
            'file' => [
                'acceptable_fields' => ['input_file'],
                'help' => __('Select File upload field for this mapping', 'fluentformpro')
            ],
            'gallery' => [
                'acceptable_fields' => ['input_image'],
                'help' => __('Select Image upload field for this mapping', 'fluentformpro')
            ],
            'image' => [
                'acceptable_fields' => ['input_image'],
                'help' => __('Select Image upload field for this mapping', 'fluentformpro')
            ],
            'repeater' => [
                'acceptable_fields' => ['repeater_field'],
                'help' => __('Please select repeat field. Your ACF repeat and form field columns need to be equal', 'fluentformpro')
            ]
        ];
        $advancedFields = apply_filters_deprecated(
            'fluent_post_acf_accepted_advanced_fields',
            [
                $advancedFields
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/post_acf_accepted_advanced_fields',
            'Use fluentform/post_acf_accepted_advanced_fields instead of fluent_post_acf_accepted_advanced_fields.'
        );
        return apply_filters('fluentform/post_acf_accepted_advanced_fields', $advancedFields);
    }

    public static function prepareGeneralFieldsData($fields, $postType, $isUpdate = false)
    {
        $acfFields = self::getFieldsConfig($postType);

        if (!$acfFields) {
            return [];
        }

        $metaValues = [];

        foreach ($fields as $field) {
            $fieldValue = ArrayHelper::get($field, 'field_value');
            $fieldKey = ArrayHelper::get($field, 'field_key');

            if (!$fieldKey || !isset($acfFields[$fieldKey])) {
                continue;
            }

            if (!$isUpdate && !$fieldValue) {
                continue;
            }

            $fieldConfig = $acfFields[$fieldKey];
            if (in_array($fieldConfig['type'], ['date_picker', 'date_time_picker', 'time_picker'])) {
                $format = ArrayHelper::get($field,'format');
                if (strpos($format, 'K') !== false) {
                    $format = str_replace('K', 'A', $format);
                }
                if ($format && $date = \DateTime::createFromFormat($format, $fieldValue)) {
                    if ($fieldConfig['type'] == 'date_time_picker') {
                        $fieldValue = $date->format('Y-m-d H:i:s');
                    } elseif ($fieldConfig['type']=== 'time_picker') {
                        $fieldValue = $date->format('H:i:s');
                    } else {
                        $fieldValue = $date->format('Ymd');
                    }
                }
            }
            $mataName = $fieldConfig['name'];
            $metaValues[$mataName] = $fieldValue;
        }

        return $metaValues;

    }

    public static function prepareAdvancedFieldsData($fields, $formData, $postType, $isUpdate = false)
    {
        $metaValues = [];
        foreach ($fields as $field) {
            $fieldValue = ArrayHelper::get($formData, $field['field_value']);
            if (!$isUpdate && !$fieldValue) {
                continue;
            }
            $fieldKey = $field['field_key'];
            $fieldConfig = acf_get_field($fieldKey);
            if (!$fieldConfig) {
                continue;
            }

            $fieldData = [];

            $type = $fieldConfig['type'];
            if ($type == 'image' || $type == 'file') {
                $fieldData = self::extractImageValue($fieldValue, $fieldConfig);
            } else if ($type == 'gallery') {
                $fieldData = self::extractImagesValue($fieldValue, $fieldConfig);
            } else if ($type == 'repeater') {
                $fieldData = self::extractRepeaterValue($fieldValue, $fieldConfig);
            } else if ($type == 'true_false') {
                $mataName = $fieldConfig['name'];
                if ($fieldValue != 'on') {
                    continue;
                }
                $fieldData = [
                    $mataName => 1,
                    '_' . $mataName => $fieldConfig['key']
                ];
            } else if ($type == 'select' || $type == 'checkbox' || $type == 'radio' || $type == 'button_group') {
                $fieldData = self::extractCheckableValue($fieldValue, $fieldConfig);
            } else {
                continue;
            }
            if ($fieldData) {
                $metaValues = array_merge($metaValues, $fieldData);
            }
        }
        return $metaValues;
    }

    public static function resolveAcfDateFieldFormat($acfFields, $form)
    {
        if (!$acfFields || !$form) return $acfFields;
        $formFields = (new FormProperties($form))->inputs();
        foreach ($acfFields as $index => $field) {
            $name = Helper::getInputNameFromShortCode(ArrayHelper::get($field, 'field_value', ''));
            if (!$name) continue;
            $formField = ArrayHelper::get($formFields, $name);
            if (
                ArrayHelper::get($formField, 'element') === 'input_date' &&
                $format = ArrayHelper::get($formField, 'raw.settings.date_format')
            ) {
                $acfFields[$index]['format'] = $format;
            }
        }
        return $acfFields;
    }

    protected static function getFieldsConfig($postType)
    {
        if (!class_exists('ACF')) {
            return [];
        }

        $field_groups = acf_get_field_groups([
            'post_type' => $postType
        ]);

        $formattedFields = [];

        $acceptedFields = self::getGeneralFields();

        $acceptedFields = apply_filters_deprecated(
            'fluent_post_acf_accepted_fileds',
            [
                $acceptedFields
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/post_acf_accepted_fields',
            'Use fluentform/post_acf_accepted_fields instead of fluent_post_acf_accepted_fileds.'
        );
        $acceptedFields = apply_filters('fluentform/post_acf_accepted_fields', $acceptedFields);

        foreach ($field_groups as $field_group) {
            $fields = acf_get_fields($field_group);
            foreach ($fields as $field) {
                if (in_array($field['type'], $acceptedFields)) {
                    $formattedFields[$field['key']] = [
                        'type' => $field['type'],
                        'label' => $field['label'],
                        'name' => $field['name'],
                        'key' => $field['key']
                    ];
                }
            }
        }

        return $formattedFields;
    }

    /*
     * Extract Field values
     */

    private static function extractImageValue($fieldValue, $fieldConfig)
    {
        if (!array($fieldValue)) {
            return [];
        }

        $firstItem = $fieldValue[0];
        if (!$firstItem) {
            return [];
        }
        //maybe remove attachment for acf post meta update
        if (static::maybeRemoveMetaImages($firstItem)) {
            return [];
        }
    
        $attachmentId = (new PostFormHandler())->getAttachmentToImageUrl($firstItem);
        $mataName = $fieldConfig['name'];
        return [
            $mataName => $attachmentId
        ];
    }

    private static function extractImagesValue($fieldValue, $fieldConfig)
    {
        if (!array($fieldValue)) {
            return [];
        }
        $imageArrays = [];
        foreach ($fieldValue as $item) {
            //maybe remove attachment for acf post meta update
            if (static::maybeRemoveMetaImages($item)) {
                continue;
            }
            $attachmentId = (new PostFormHandler())->getAttachmentToImageUrl($item);
            if ($attachmentId) {
                $imageArrays[] = $attachmentId;
            }
        }

        if (!$imageArrays) {
            return [];
        }

        $mataName = $fieldConfig['name'];

        return [
            $mataName => $imageArrays
        ];
    }

    private static function extractRepeaterValue($values, $fieldConfig)
    {
        if (!array($values) || !$values) {
            return [];
        }

        $subFields = ArrayHelper::get($fieldConfig, 'sub_fields', []);
        $mataName = $fieldConfig['name'];

        $itemValues = [];
        foreach ($values as $value) {
            $item = [];
            foreach ($subFields as $subfieldIndex => $subField) {
                $item[$subField['name']] = $value[$subfieldIndex];
            }
            $itemValues[] = $item;
        }

        if(!$itemValues) {
            return [];
        }

        return [$mataName => $itemValues];
    }

    private static function extractCheckableValue($value, $fieldConfig)
    {
        $mataName = $fieldConfig['name'];

        return [
            $mataName => $value
        ];
    }

    public static function maybeUpdateWithAcf($metaKey, $metaValue, $postId = false)
    {
        if (!$postId) {
            $postId = "user_" . get_current_user_id();
        }
        if (class_exists('\ACF') && get_field($metaKey, $postId) && function_exists('update_field')) {
            return update_field($metaKey, $metaValue, $postId);
        }
        return false;
    }
}
