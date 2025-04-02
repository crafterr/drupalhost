<?php

declare(strict_types=1);

namespace Drupal\custom_view_term_icon\Plugin\views\field;

use Drupal\Component\Render\MarkupInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Markup;
use Drupal\taxonomy\Entity\Term;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;

/**
 * Provides a custom Views field for displaying discipline icons or backgrounds.
 *
 * @ViewsField("term_icon")
 */
class CustomTermIconField extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function defineOptions(): array {
    $options = parent::defineOptions();
    $options['display_mode_template'] = ['default' => 'icon'];
    $options['term_reference'] = ['default' => ''];
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state): void {
    parent::buildOptionsForm($form, $form_state);

    // Display mode template.
    $form['display_mode_template'] = [
      '#type' => 'select',
      '#title' => $this->t('Display mode template'),
      '#options' => [
        'icon' => $this->t('Icon only (compact view)'),
        'background' => $this->t('Background with icon (large display)'),
      ],
      '#default_value' => $this->options['display_mode_template'],
    ];

    // Dynamic relationships list.
    $relationship_options = [];

    if (!empty($this->view) && $this->view->display_handler) {
      $relationships = $this->view->display_handler->getOption('relationships');
      if (is_array($relationships)) {
        foreach ($relationships as $machine_name => $definition) {
          $label = $machine_name;
          if (!empty($definition['label'])) {
            $label .= ' (' . $definition['label'] . ')';
          }
          $relationship_options[$machine_name] = $label;
        }
      }
    }

    $form['term_reference'] = [
      '#type' => 'select',
      '#title' => $this->t('Term relationship'),
      '#options' => $relationship_options ?: ['' => $this->t('- No relationships available -')],
      '#default_value' => $this->options['term_reference'] ?? '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function query(): void {
    // Leave empty to avoid the field being used in the query.
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values): string|MarkupInterface {
    $mode = $this->options['display_mode_template'] ?? 'icon';
    $term_key = $this->options['term_reference'] ?? '';

    if (empty($term_key) || !isset($values->_relationship_entities[$term_key])) {
      return '';
    }
    $term = $values->_relationship_entities[$term_key];
    if (!$term instanceof Term) {
      return '';
    }
    $parent = $term->get('parent')->first()?->get('entity')->getValue();

    $code = 'fallback';

    if ($parent instanceof Term && !empty($parent->get('field_sport_code')->value)) {
      $code = $parent->get('field_sport_code')->value;
    }
    elseif (!$parent instanceof Term && !empty($term->get('field_sport_code')->value)) {
      $code = $term->get('field_sport_code')->value;
    }

    $code = strtolower(str_replace(' ', '_', $code));

    $markup = match ($mode) {
      'background' => '<div class="discipline-bg ' . $code . '">Bg: ' . $code . '</div>',
      default => '<div class="discipline ' . $code . '">Ikona: ' . $code . '</div>',
    };

    return Markup::create($markup);
  }

}
