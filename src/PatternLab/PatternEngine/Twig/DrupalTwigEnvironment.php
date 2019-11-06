<?php

namespace PatternLab\PatternEngine\Twig;

use Drupal\Component\FrontMatter\Exception\FrontMatterParseException;
use Drupal\Component\FrontMatter\FrontMatter;
use Twig\Error\SyntaxError;

class DrupalTwigEnvironment extends \Twig_Environment {

  /**
   * {@inheritdoc}
   */
  public function compileSource($source, $name = NULL) {
    if (!$source instanceof \Twig_Source) {
      // @codingStandardsIgnoreLine (Twig deprecation).
      @trigger_error(sprintf('Passing a string as the $source argument of %s() is deprecated since version 1.27. Pass a Twig\Source instance instead.', __METHOD__), E_USER_DEPRECATED);
      $source = new \Twig_Source($source, $name);
    }

    // Note: always use \Drupal\Core\Serialization\Yaml here instead of the
    // "serializer.yaml" service. This allows the core serializer to utilize
    // core related functionality which isn't available as the standalone
    // component based serializer.
    $frontMatter = FrontMatter::create($source->getCode());

    // Reconstruct the source if there is front matter data detected. Prepend
    // the source with {% line \d+ %} to inform Twig that the source code
    // actually starts on a different line past the front matter data. This is
    // particularly useful when used in error reporting.
    try {
      if (($line = $frontMatter->getLine()) > 1) {
        $content = "{% line $line %}" . $frontMatter->getContent();
        $source = new \Twig_Source($content, $source->getName(), $source->getPath());
      }
    }
    catch (FrontMatterParseException $exception) {
      // Convert parse exception into a syntax exception for Twig and append
      // the path/name of the source to help further identify where it occurred.
      $message = sprintf($exception->getMessage() . ' in %s', $source->getPath() ?: $source->getName());
      throw new SyntaxError($message, $exception->getSourceLine(), $source, $exception);
    }

    return parent::compileSource($source, $name);
  }

  /**
   * Retrieves metadata associated with a template.
   *
   * @param string $name
   *   The name for which to calculate the template class name.
   *
   * @return array
   *   The template metadata, if any.
   *
   * @throws \Twig\Error\LoaderError
   * @throws \Twig\Error\SyntaxError
   *
   * @todo Simplify this method to just use $loader->getSourceContext($name).
   * @see https://www.drupal.org/project/drupal/issues/3041076
   */
  public function getTemplateMetadata($name) {
    $loader = $this->getLoader();
    $source = $loader instanceof \Twig_SourceContextLoaderInterface ? $loader->getSourceContext($name) : new \Twig_Source($loader->getSource($name), $name);

    // Note: always use \Drupal\Core\Serialization\Yaml here instead of the
    // "serializer.yaml" service. This allows the core serializer to utilize
    // core related functionality which isn't available as the standalone
    // component based serializer.
    try {
      return FrontMatter::create($source->getCode())->getData();
    }
    catch (FrontMatterParseException $exception) {
      // Convert parse exception into a syntax exception for Twig and append
      // the path/name of the source to help further identify where it occurred.
      $message = sprintf($exception->getMessage() . ' in %s', $source->getPath() ?: $source->getName());
      throw new SyntaxError($message, $exception->getSourceLine(), $source, $exception);
    }
  }

}
