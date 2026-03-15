<?php

namespace Drupal\articles_api\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\node\Entity\Node;

class ArticlesController extends ControllerBase {

  /**
   * Returns 3 hardcoded articles as JSON.
   */
  public function getArticles(): JsonResponse {

    // Hardcoded node IDs that exist
    $nids = [6, 9, 10];

    // Entity query with IN operator
    $query = \Drupal::entityQuery('node')
      ->condition('nid', $nids, 'IN')
      ->condition('type', 'article')
      ->accessCheck(TRUE);

    $result = $query->execute();
    $nodes = Node::loadMultiple($result);

    $data = [];
    $cache_tags = []; // initialize cache tags

    foreach ($nodes as $node) {
      $data[] = [
        'nid' => $node->id(),
        'title' => $node->label(),
      ];

      // Collect cache-tags safely
      $node_tags = $node->getCacheTags();
      if (!empty($node_tags)) {
        $cache_tags = array_merge($cache_tags, $node_tags);
      }
    }

    $response = new JsonResponse($data);

    // Optional: Max-age (seconds)
    // $response->headers->set('X-Drupal-Cache-Max-Age', 120);

    // Add cache-tags header
    $response->headers->set('X-Drupal-Cache-Tags', implode(',', $cache_tags));

    return $response;
  }

}