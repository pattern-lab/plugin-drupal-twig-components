<?php
/**
 * @file
 * Add "create_attribute" function for Pattern Lab.
 */

use \Drupal\Core\Template\Attribute;

$function = new Twig_SimpleFunction('create_attribute', function ($attributes = []) {
  return new Attribute($attributes);
});
