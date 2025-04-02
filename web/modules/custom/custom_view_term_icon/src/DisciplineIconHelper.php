<?php

declare(strict_types=1);

namespace Drupal\custom_view_term_icon;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\taxonomy\Entity\Term;
/**
 * @todo Add class description.
 */
class DisciplineIconHelper {

  /**
   * Constructs a DisciplineIconHelper object.
   */
  public function __construct(protected readonly EntityTypeManagerInterface $entityTypeManager) {}

  public function getIconMarkupFromTerm(?Term $term, string $mode = 'icon'): string {
    if (!$term instanceof Term) {
      return '';
    }

    $code = '';

    // Priorytet: najpierw parent z kodem
    $parent = $term->get('parent')->entity ?? null;
    if ($parent instanceof Term && !$parent->get('field_sport_code')->isEmpty()) {
      $code = $parent->get('field_sport_code')->value;
    }
    // Jeśli nie ma parenta z kodem, użyj bieżącego terma
    elseif (!$term->get('field_sport_code')->isEmpty()) {
      $code = $term->get('field_sport_code')->value;
    }

    if (empty($code)) {
      return '<div class="discipline fallback" title="No code"></div>';
    }

    $code = strtolower(str_replace(' ', '_', $code));

    return match ($mode) {
      'background' => '<div class="discipline-bg ' . $code . '">'.$code.'</div>',
      default => '<div class="discipline ' . $code . '" title=">'.$code.'</div>',
    };
  }

  public function getIconFromNode(\Drupal\node\NodeInterface $node): string {
    if ($node->hasField('field_sport_article') && !$node->get('field_sport_article')->isEmpty()) {
      $term = $node->get('field_sport_article')->entity;
      return $this->getIconMarkupFromTerm($term);
    }
    return '';
  }



}
