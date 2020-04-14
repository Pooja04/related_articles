<?php

/**
 * @file providing the service that gives related article for the given node.
 *
 */

namespace Drupal\related_articles\Services;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;

class ArticlesServices {
  protected $entityTypeManager;
  protected $config_factory;

  /**
   * Constructs a new Related Service Block.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, ConfigFactoryInterface $config_factory) {
    $this->entityTypeManager = $entity_type_manager;
    $this->config_factory = $config_factory;
  }

  /**
   * Returns a related articles.
   */
  public function getRelatedArticles($current_nid, $current_node_tag, $current_node_author_id) {
    $article_count = $this->config_factory->get('related_articles.settings')->get('total_articles');
    // firstCategoryCount : Display nodes in same category by same author first.
    $ids = $this->entityTypeManager->getStorage('node')->getQuery()->condition('status', 1)
      ->condition('type', 'article')
      ->condition('nid', $current_nid, '!=')->condition('field_tags', $current_node_tag, 'IN')
      ->condition('uid', $current_node_author_id)
      ->sort('created', 'DESC')
      ->range(0, $article_count)
      ->execute();
    
    // secondCategoryCount : Display nodes in same category by different author.
    $ids += $this->entityTypeManager->getStorage('node')->getQuery()->condition('status', 1)
      ->condition('type', 'article')
      ->condition('nid', $current_nid, '!=')
      ->condition('field_tags', $current_node_tag, 'IN')
      ->condition('uid', $current_node_author_id, '!=')
      ->sort('created', 'DESC')
      ->range(0, ($article_count - count($ids)))
      ->execute();
    
    // thirdCategoryCount : Display nodes in different category by same author.
    $ids += $this->entityTypeManager->getStorage('node')->getQuery()
      ->condition('status', 1)
      ->condition('type', 'article')
      ->condition('nid', $current_nid, '!=')
      ->condition('field_tags', $current_node_tag, 'NOT IN')
      ->condition('uid', $current_node_author_id)
      ->sort('created', 'DESC')
      ->range(0, ($article_count - count($ids)))
      ->execute();
    
    // fourthCategoryCount Display nodes in different category by different author next.
    $ids += $this->entityTypeManager->getStorage('node')->getQuery()->condition('status', 1)
      ->condition('type', 'article')
      ->condition('nid', $current_nid, '!=')
      ->condition('field_tags', $current_node_tag, 'NOT IN')
      ->condition('uid', $current_node_author_id, '!=')
      ->sort('created', 'DESC')
      ->range(0, ($article_count - count($ids)))
      ->execute();


    $articles = "";
    foreach ($ids as $id) {
      $node = $this->entityTypeManager->getStorage('node')->load($id);
      $node_tags = $node->get('field_tags')->getValue('value');
      $node_tags = array_column($node_tags, 'target_id');
      $terms = $this->entityTypeManager->getStorage('taxonomy_term')->loadMultiple($node_tags);
      $term_name = '';
      foreach ($terms as $term) {
        $term_name .= $term->get('name')->getString() . ' ';
      }

      $account = $this->entityTypeManager->getStorage('user')->load($node->getOwnerId());
      $author_name = $account->getUsername();
     
      $articles .= '<b>' . $node->title->value . '</b>'
        . ' <h5> Article Tag: ' . $term_name . '</h5>'
        . ' <h5> Author name: ' . $author_name . '</h5>'
        . '<a href="/node/' . $id . '"> Read more</a>' . '<hr><br>';
    }
    $articles .= "Total count of articles: ". $article_count;
    return $articles;
  }

}
