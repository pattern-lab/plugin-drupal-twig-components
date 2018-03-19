<?php

$function = new Twig_SimpleFunction(
  'create_attribute',
  function ($attributes = []) {
    if (isset($attributes) && isset($attributes['class'])) {
      $classes = join(' ', $attributes['class']);
      return ' class="' . $classes .'"';
    }
  },
  array('is_safe' => array('html'))
);