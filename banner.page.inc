<?php

/**
 * @file
 * Contains banner.page.inc.
 *
 * Page callback for Banner entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Banner templates.
 *
 * Default template: banner.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_banner(array &$variables) {
  // Fetch Banner Entity Object.
  $banner = $variables['elements']['#banner'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
