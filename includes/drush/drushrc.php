<?php

/**
 * @file
 * Custom overrides.
 */

if (str_ends_with(getenv('HOSTNAME'), '-acc') || 
    str_ends_with(getenv('HOSTNAME'), '-prod')) {
  $options['l'] = end(explode(",", getenv('VIRTUAL_HOST')));
  $options['r'] = getenv('DOCUMENT_ROOT');
}
