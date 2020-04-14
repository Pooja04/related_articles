<?php

namespace Drupal\related_articles\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\node\Entity\Node;
use Drupal\Core\Cache\Cache;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\related_articles\Services\ArticlesServices;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;

/**
 * Provides a 'Related Articles' block.
 *
 * @Block(
 *   id = "related_article_block",
 *   admin_label = @Translation("Related Article Block"),
 * )
 */
class RelatedArticleBlock extends BlockBase implements ContainerFactoryPluginInterface {
  protected $articleservices;
  protected $routeMatch;

  /**
   * Constructs a Related Service Block.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param array $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\related_articles\Services\ArticlesServices $article_services
   *   The related article manager service.
   * @param \Drupal\Core\Routing\RouteMatchInterface $routeMatch
   *   The route match interface.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ArticlesServices $article_services, RouteMatchInterface $routeMatch) {
    //parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->articleservices = $article_services;
    $this->routeMatch = $routeMatch;
  }

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('related_articles.get_articles'),
      $container->get('current_route_match')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {

    // Get current node details.
    $node = $this->routeMatch->getParameter('node');
    if ($node instanceof \Drupal\node\NodeInterface) {
      $current_nid = $node->id();
      $current_node_tag = $node->get('field_tags')->getValue('value');
      $current_node_tag = array_column($current_node_tag,'target_id');
      $current_node_author_id = $node->getOwnerId();
    }
    
    // Calling service to fetch related articles for the given node.
    $articles = $this->articleservices->getRelatedArticles($current_nid, $current_node_tag, $current_node_author_id);

    return [
      '#markup' => $this->t($articles),
    ];
  }

  public function getCacheTags() {
    //With this when your node change your block will rebuild
    if ($node = $this->routeMatch->getParameter('node')) {
      //if there is node add its cachetag
      return Cache::mergeTags(parent::getCacheTags(), array('node_list:article'));
    }
    else {
      //Return default tags instead.
      return parent::getCacheTags();
    }
  }

  public function getCacheContexts() {
    //if you depends on \Drupal::routeMatch()
    //you must set context of this block with 'route' context tag.
    //Every new route this block will rebuild
    return Cache::mergeContexts(parent::getCacheContexts(), array('route'));
  }

}
