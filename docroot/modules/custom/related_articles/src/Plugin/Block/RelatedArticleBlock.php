<?php

namespace Drupal\related_articles\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\node\Entity\Node;

/**
 * Provides a 'Related Articles' block.
 *
 * @Block(
 *   id = "related_article_block",
 *   admin_label = @Translation("Related Article Block"),
 * )
 */
class RelatedArticleBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {

    // Get current node details.
    $node = \Drupal::routeMatch()->getParameter('node');
    if ($node instanceof \Drupal\node\NodeInterface) {
      $current_nid = $node->id();
      $current_node_tag = $node->field_tags->target_id;
      $current_node_author_id = $node->getOwnerId();
    }
    
    // Calling service to fetch related articles for the given node.
    $service = \Drupal::service('related_articles.get_articles');
    $articles = $service->getRelatedArticles($current_nid, $current_node_tag, $current_node_author_id);

    return [
      '#markup' => $this->t($articles),
      '#cache' => array(
        'max-age' => 0,
      ),
    ];
  }

}
