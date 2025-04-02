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
 * Provides a custom Views field for displaying discipline icons using a raw field key.
 *
 * @ViewsField("term_icon_extra")
 */
class CustomTermIconFieldExtra extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function defineOptions(): array {
    $options = parent::defineOptions();
    $options['field_key'] = ['default' => ''];
    $options['display_mode'] = ['default' => 'icon'];
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state): void {
    parent::buildOptionsForm($form, $form_state);

    $form['display_mode'] = [
      '#type' => 'select',
      '#title' => $this->t('Display mode'),
      '#options' => [
        'icon' => $this->t('Icon'),
        'background' => $this->t('Background'),
      ],
      '#default_value' => $this->options['display_mode'],
    ];

    $field_options = ['' => $this->t('- Select field -')];
    $fields = $this->view->display_handler->getOption('fields');

    if (!empty($fields) && is_array($fields)) {
      foreach ($fields as $key => $info) {
        $label = $info['label'] ?? $key;
        $field_options[$key] = "$key ($label)";
      }
    }

    $form['field_key'] = [
      '#type' => 'select',
      '#title' => $this->t('Field key'),
      '#options' => $field_options,
      '#default_value' => $this->options['field_key'],
      '#description' => $this->t('Choose field key (from ResultRow) used to fetch term ID.'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function query(): void {
    // Avoid adding this field to the query.
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values): string|MarkupInterface {
    $field_key = $this->options['field_key'] ?? '';
    $mode = $this->options['display_mode'] ?? 'icon';
    dump($values);
    dump($field_key);
    if (empty($field_key) || empty($values->{$field_key})) {
      return '';
    }

    /*$tid = (int) $values->{$field_key};
    $term = Term::load($tid);
    if (!$term instanceof Term) {
      return '';
    }

    $parent = $term->get('parent')->first()?->get('entity')->getValue();
    $code = 'fallback';

    if ($parent instanceof Term && !$parent->get('field_sport_code')->isEmpty()) {
      $code = $parent->get('field_sport_code')->value;
    }
    elseif (!$term->get('field_sport_code')->isEmpty()) {
      $code = $term->get('field_sport_code')->value;
    }

    $code = strtolower(str_replace(' ', '_', $code));

    $markup = match ($mode) {
      'background' => '<div class="discipline-bg ' . $code . '"></div>',
      default => '<div class="discipline ' . $code . '"></div>',
    };*/

    return Markup::create($markup);
  }

}
