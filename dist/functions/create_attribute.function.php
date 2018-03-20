<?php

$function = new Twig_SimpleFunction(
  'create_attribute',
  function ($attributes = []) {
    foreach ($attributes as $key => $value) {
      print ' ' . $key . '="' . join(' ', $value) . '"';
    }
  },
  array('is_safe' => array('html'))
);