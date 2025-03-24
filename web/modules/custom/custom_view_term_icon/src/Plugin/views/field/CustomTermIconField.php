<?php

declare(strict_types=1);

namespace Drupal\custom_view_term_icon\Plugin\views\field;

use Drupal\Component\Render\MarkupInterface;
use Drupal\Core\Render\Markup;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;

/**
 * Provides a custom Views field for displaying multiple taxonomy term icons.
 *
 * @ViewsField("term_icon")
 */
class CustomTermIconField extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function query(): void {
    // No database query needed as this is a computed field.
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values): string|MarkupInterface {
    if (empty($values->_relationship_entities) || !is_array($values->_relationship_entities)) {
      return '';
    }

    $output = '';

    // Iterate over all related taxonomy terms.
    foreach ($values->_relationship_entities as $relationship_key => $term) {
      // Ensure it's a valid taxonomy term entity.
      if (!$term instanceof \Drupal\taxonomy\Entity\Term) {
        continue;
      }

      // Determine vocabulary to select the correct field.
      $vocabulary = $term->bundle();
      $codename = '';
      switch ($vocabulary) {
        case 'sport':
          $codename = $term->get('field_sport_code')->value;
          $class_prefix = 'sport';
          break;

        case 'weather_conditional':
          $codename = $term->get('field_weather_code')->value;
          $class_prefix = 'weather';
          break;

        case 'noc':
          $codename = $term->get('flag_code')->value;
          $class_prefix = 'noc';
          break;

        default:
          continue; // Skip unknown vocabularies.
      }

      // If no valid code was found, skip this term.
      if (empty($codename)) {
        continue;
      }

      // Format codename: Convert to lowercase and replace spaces with underscores.
      $codename = strtolower(str_replace(' ', '_', $codename));

      // Append the rendered HTML for each taxonomy term.
      $output .= '<div class="' . $class_prefix . ' ' . $codename . '"></div>';
    }

    return Markup::create($output);
  }
}
