<?php
use Drupal\Core\DrupalKernel;
use Symfony\Component\HttpFoundation\Request;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

// Wczytaj Drupala
$autoloader = require_once __DIR__ . '/../vendor/autoload.php';
$request = Request::createFromGlobals();
$kernel = DrupalKernel::createFromRequest($request, $autoloader, 'prod');
$kernel->boot();

// Pobierz artykuły do eksportu
$nodes = \Drupal::entityTypeManager()->getStorage('node')
  ->loadByProperties(['type' => 'article']);

$client = new Client();

foreach ($nodes as $node) {
  $data = [
    'id' => $node->id(),
    'title' => $node->getTitle(),
    'body' => $node->get('body')->value,
  ];

  try {
    // Wysyłamy `PUT` do API
    $response = $client->put("http://drupal10projecttohost.ddev.site/api-fetch.php", [
      RequestOptions::JSON => $data,
      'headers' => [
        'Authorization' => 'Bearer YOUR_ACCESS_TOKEN',
        'Content-Type' => 'application/json',
      ],
    ]);

    echo "✅ Sent node {$node->id()} to API. Status: " . $response->getStatusCode() . "<br>";
  } catch (\Exception $e) {
    echo "❌ Error sending node {$node->id()} to API: " . $e->getMessage() . "<br>";
  }
}
