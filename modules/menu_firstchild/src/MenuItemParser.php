<?php

namespace Drupal\menu_firstchild;

use Drupal\Core\Menu\MenuTreeParameters;
use Drupal\Core\Url;

/**
 * Class MenuItemParser.
 *
 * @package Drupal\menu_firstchild
 */
class MenuItemParser {

  /**
   * Parses a menu item and modifies it if menu_firstchild is enabled.
   *
   * @param array $item
   *   Menu item array.
   * @param string $menu_name
   *   Menu machine name.
   *
   * @return array
   *   Menu item array.
   */
  public function parse(array $item, $menu_name) {
    // If menu_firstchild is enabled on the menu item, continue parsing it.
    if ($this->enabled($item)) {
      $url = $this->firstChildUrl($item, $menu_name);

      // Create a new URL so we don't copy attributes etc.
      if ($url->isRouted()) {
        $item['url'] = Url::fromRoute($url->getRouteName(), $url->getRouteParameters());
      }
      else {
        $item['url'] = Url::fromUri($url->getUri());
      }

      // Add a class on the menu item so it can be themed accordingly.
      $item['attributes']->addClass('menu-firstchild');
    }

    // Parse all children if any are found.
    if (!empty($item['below'])) {
      foreach ($item['below'] as &$below) {
        $below = $this->parse($below, $menu_name);
      }
    }

    return $item;
  }

  /**
   * Returns the URL of the first child of given menu item.
   *
   * This does take into account menu_firstchild.
   *
   * @param array $item
   *   Menu item array.
   * @param string $menu_name
   *   Menu machine name.
   *
   * @return \Drupal\Core\Url
   *   URL to use in the link.
   */
  protected function firstChildUrl(array $item, $menu_name) {
    // Init menu tree.
    $menu_tree = \Drupal::menuTree();

    // Get parameters of given link.
    $id = $item['original_link']->getPluginId();
    $parameters = new MenuTreeParameters();
    $parameters->setRoot($id)->excludeRoot()->setMaxDepth(1)->onlyEnabledLinks();

    // Load the tree based on this set of parameters.
    $tree = $menu_tree->load($menu_name, $parameters);

    // Transform the tree.
    $manipulators = [
      // Only show links that are accessible for the current user.
      ['callable' => 'menu.default_tree_manipulators:checkAccess'],
      // Use the default sorting of menu links.
      ['callable' => 'menu.default_tree_manipulators:generateIndexAndSort'],
    ];

    // Get tree.
    $tree = $menu_tree->transform($tree, $manipulators);

    // Children found, get url of first one.
    if (count($tree)) {
      $first_child = reset($tree);
      $url = $first_child->link->getUrlObject();
    }
    else {
      $url = Url::fromRoute('<none>');
    }

    return $url;
  }

  /**
   * Returns whether menu_firstchild is enabled on a menu item.
   *
   * @param array $item
   *   Menu item array.
   *
   * @return bool
   *   Returns TRUE if menu_firstchild is enabled on the menu item.
   */
  protected function enabled(array $item) {
    $options = $item['url']->getOption('menu_firstchild');
    return !empty($options['enabled']);
  }

}
