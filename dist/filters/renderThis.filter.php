<?php
// Drupal render filter
$filter = new Twig_SimpleFilter('renderThis', function ($string) {
  return $string;
});
