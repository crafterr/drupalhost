<?php

declare(strict_types=1);

namespace Drupal\custom_view_term_icon\Plugin\views\field;

use Drupal\Component\Render\MarkupInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Render\Markup;
use Drupal\Core\Session\AccountInterface;
use Drupal\taxonomy\Entity\Term;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a custom Views field for displaying discipline icons or backgrounds for multi-value taxonomy references.
 *
 * @ViewsField("term_icon_multi")
 */
class CustomTermIconFieldMulti extends FieldPluginBase {

  /**
   * Constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param array $plugin_definition
   *   The plugin implementation definition.
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

  }
  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition
    );
  }
  /**
   * {@inheritdoc}
   */
  public function defineOptions(): array {
    $options = parent::defineOptions();
    $options['display_mode_template'] = ['default' => 'icon'];
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state): void {
    parent::buildOptionsForm($form, $form_state);

    $form['display_mode_template'] = [
      '#type' => 'select',
      '#title' => $this->t('Display mode template'),
      '#options' => [
        'icon' => $this->t('Icon only (compact view)'),
        'background' => $this->t('Background with icon (large display)'),
      ],
      '#default_value' => $this->options['display_mode_template'],
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
    $entity = $values->_entity;

    if (!$entity || !$entity->hasField('field_sports') || $entity->get('field_sports')->isEmpty()) {
      return '';
    }

    $markup = [];
    dump($values);
    foreach ($entity->get('field_sports')->referencedEntities() as $term) {
      if (!$term instanceof Term) {
        continue;
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

      $markup[] = match ($mode) {
        'background' => '<div class="discipline-bg ' . $code . '">Bg: ' . $code . '</div>',
        default => '<div class="discipline ' . $code . '">icon: ' . $code . '</div>',
      };
    }

    return Markup::create(implode('', $markup));
  }

}
