<?php

declare(strict_types=1);

namespace Drupal\custom_view_term_icon\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\node\Entity\Node;
use Drupal\file\Entity\File;
use Drupal\Core\Render\Markup;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\File\FileUrlGenerator;
/**
 * Returns responses for Custom View Term Icon routes.
 */
final class CustomViewTermIconController extends ControllerBase {

  /**
   * The file URL generator service.
   *
   * @var \Drupal\Core\File\FileUrlGenerator
   */
  protected FileUrlGenerator $fileUrlGenerator;

  /**
   * Constructs a NewsController.
   */
  public function __construct(FileUrlGenerator $file_url_generator) {
    $this->fileUrlGenerator = $file_url_generator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('file_url_generator')
    );
  }
  /**
   * Builds the response.
   */
  public function __invoke(): array {

    $header = [
      'id' => $this->t('ID'),
      'title' => $this->t('Title'),
      'weather_condition' => $this->t('Weather Condition'),
      'weather_icon' => $this->t('Weather Icon'),
    ];

    $rows = [];

    // Pobieramy TID (ID terminu) dla "weather_conditional"
    $query = \Drupal::entityQuery('taxonomy_term')
      ->condition('vid', 'weather_conditional')
      ->accessCheck(TRUE); // ✅ Sprawdzanie uprawnień

    $weather_tids = $query->execute();

    if (!empty($weather_tids)) {
      // Pobieramy artykuły, które są powiązane z terminem "weather_conditional"
      $query = \Drupal::entityQuery('node')
        ->condition('status', 1) // Tylko opublikowane
        ->condition('type', 'article')
        ->condition('field_weather', array_values($weather_tids), 'IN') // Filtrujemy tylko po tych terminach
        ->accessCheck(TRUE) // ✅ Sprawdzanie uprawnień
        ->sort('created', 'DESC')
        ->range(0, 5);

      $nids = $query->execute();

      if (!empty($nids)) {
        $nodes = Node::loadMultiple($nids);

        foreach ($nodes as $node) {
          // Pobieramy tytuł artykułu jako link
          $url = Url::fromRoute('entity.node.canonical', ['node' => $node->id()]);
          $title = Link::fromTextAndUrl($node->getTitle(), $url)->toString();

          // Pobieramy terminy taksonomiczne z weather_conditional
          $weather_terms = [];
          $weather_icon = '';

          if ($node->hasField('field_weather') && !$node->get('field_weather')->isEmpty()) {
            foreach ($node->get('field_weather')->referencedEntities() as $term) {
              $weather_terms[] = $term->getName();
              // Pobieramy bezpośrednio plik z pola "field_weather_icon"
              if ($term->hasField('field_weather_icon') && !$term->get('field_weather_icon')->isEmpty()) {
                $file = $term->get('field_weather_icon')->entity;
                if ($file instanceof File) {
                  $image_url = $this->fileUrlGenerator->generateAbsoluteString($file->getFileUri());
                  $weather_icon = Markup::create('<img src="' . $image_url . '" width="50" height="50" alt="Weather Icon">');
                }
              }
            }
          }

          // Dodajemy do tabeli
          $rows[] = [
            'id' => $node->id(),
            'title' => $title,
            'weather_condition' => implode(', ', $weather_terms), // Połącz terminy przecinkami
            'weather_icon' => $weather_icon, // Ikona pogody
          ];
        }
      }
    }

    // Generowanie tabeli w Drupalu
    $build['weather_news_table'] = [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#empty' => $this->t('No news found for this weather condition.'),
    ];

    return $build;
  }

}
