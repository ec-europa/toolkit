<?php

/**
 * @file
 * Contains the overwritten field base export.
 *
 * These are exact copies from features.field.inc but as features does not allow
 * us to alter the export data, we implement them ourselves. The other hooks are
 * simply calling their original.
 *
 * The only modified COMPONENT hook is COMPONENT_features_export_render(). Here
 * we do 2 things:
 *  1. We modify the export array to lock the fields.
 *  2. We check if there might be a datetime field instead of datestamp and we
 *     notify the user that it's not compliant with the requirements.
 */

/**
 * Implements hook_features_export_render().
 */
function _toolkit_dev_field_base_features_export_render($module, $data, $export = NULL) {
  $code = array();
  $code[] = '  $field_bases = array();';
  $code[] = '';
  foreach ($data as $identifier) {
    if ($field = features_field_base_load($identifier)) {
      // Lock the field.
      $field['locked'] = 1;
      // If there is a datetime field, we have to stop the export. Depending on
      // the way we export we take different actions.
      if ($export && $field['type'] == 'datetime') {
        $replacements = [
          '%label' => $field['field_name'],
          '%type' => $field['type'],
          '%message' => 'This is not allowed, please use datestamp instead',
        ];
        watchdog('features', 'Attempt to update field %label with type %type: %message.', $replacements);
        drupal_set_message(t('Attempt to update field %label with type %type: %message.', $replacements));
        if (drupal_is_cli() && function_exists('drush_main')) {
          throw new \Exception('Cannot export feature: ' . format_string('Attempt to update field %label with type %type: %message.', $replacements));
        }
        else {
          drupal_goto('/admin/structure/features/' . $module . '/recreate_en');
        }
      }
      // Remove unwanted data.
      unset($field['columns']);
      unset($field['foreign keys']);
      // Only remove the 'storage' declaration if the field is using the default
      // storage type.
      if ($field['storage']['type'] == variable_get('field_storage_default', 'field_sql_storage')) {
        unset($field['storage']);
      }
      // If we still have a storage declaration here it means that a non-default
      // storage type was altered into to the field definition. And no one would
      // never need to change the 'details' key, so don't render it.
      if (isset($field['storage']['details'])) {
        unset($field['storage']['details']);
      }

      _field_instance_features_export_sort($field);
      $field_export = features_var_export($field, '  ');
      $field_prefix = '  // Exported field_base: ';
      $field_identifier = features_var_export($identifier);
      if (features_field_export_needs_wrap($field_prefix, $field_identifier)) {
        $code[] = rtrim($field_prefix);
        $code[] = "  // {$field_identifier}.";
      }
      else {
        $code[] = $field_prefix . $field_identifier . '.';
      }
      $code[] = "  \$field_bases[{$field_identifier}] = {$field_export};";
      $code[] = "";
    }
  }
  $code[] = '  return $field_bases;';
  $code = implode("\n", $code);
  return array('field_default_field_bases' => $code);
}

/**
 * Implements hook_features_revert().
 */
function _toolkit_dev_field_base_features_revert($module) {
  field_base_features_rebuild($module);
  xdebug_break();
}

/**
 * Implements hook_features_export_options().
 */
function _toolkit_dev_field_base_features_export_options() {
  return field_base_features_export_options();
}

/**
 * Implements hook_features_export().
 */
function _toolkit_dev_field_base_features_export($data, &$export, $module_name = '') {
  return field_base_features_export($data, $export, $module_name);
}
