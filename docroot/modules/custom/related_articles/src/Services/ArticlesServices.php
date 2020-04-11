<?php

/**
 * @file providing the service that gives related article for the given node.
 *
 */

namespace Drupal\related_articles\Services;

use Drupal\taxonomy\Entity\Term;

class ArticlesServices {

  public function __construct() {
    $this->number_of_articles = 10;
  }

  /**
   * Returns a related articles.
   */
  public function getRelatedArticles($current_nid, $current_node_tag, $current_node_author_id) {
    $article_count = $this->number_of_articles;
    $node_storage = \Drupal::service('entity_type.manager')->getStorage('node');
    // firstCategoryCount : Display nodes in same category by same author first.
    $ids = $node_storage->getQuery()->condition('status', 1)
      ->condition('type', 'article')
      ->condition('nid', $current_nid, '!=')->condition('field_tags', $current_node_tag)
      ->condition('uid', $current_node_author_id)
      ->sort('created', 'DESC')
      ->range(0, $article_count)
      ->execute();
    
    // secondCategoryCount : Display nodes in same category by different author.
    $ids += $node_storage->getQuery()->condition('status', 1)
      ->condition('type', 'article')
      ->condition('nid', $current_nid, '!=')
      ->condition('field_tags', $current_node_tag)
      ->condition('uid', $current_node_author_id, '!=')
      ->sort('created', 'DESC')
      ->range(0, ($article_count - count($ids)))
      ->execute();
    
    // thirdCategoryCount : Display nodes in different category by same author.
    $ids += $node_storage->getQuery()
      ->condition('status', 1)
      ->condition('type', 'article')
      ->condition('nid', $current_nid, '!=')
      ->condition('field_tags', $current_node_tag, '!=')
      ->condition('uid', $current_node_author_id)
      ->sort('created', 'DESC')
      ->range(0, ($article_count - count($ids)))
      ->execute();
    
    // fourthCategoryCount Display nodes in different category by different author next.
    $ids += $node_storage->getQuery()->condition('status', 1)
      ->condition('type', 'article')
      ->condition('nid', $current_nid, '!=')
      ->condition('field_tags', $current_node_tag, '!=')
      ->condition('uid', $current_node_author_id, '!=')
      ->sort('created', 'DESC')
      ->range(0, ($article_count - count($ids)))
      ->execute();


    $articles = "";
    foreach ($ids as $id) {
      $node = $node_storage->load($id);

      $term = Term::load($node->field_tags->target_id);
      $term_name = $term->getName();

      $account = \Drupal\user\Entity\User::load($node->getOwnerId()); // pass your uid
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
