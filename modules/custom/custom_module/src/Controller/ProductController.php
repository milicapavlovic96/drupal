<?php

namespace Drupal\custom_module\Controller;

use Drupal\Core\Controller\ControllerBase;

class ProductController extends ControllerBase {

    public function page(){
        
        $query = \Drupal::entityQuery('node');
        $query->condition('type', 'product');
        $items = $query->execute();

        return array(
            '#theme' => 'product_list',
            '#items' => $items,
            '#title' => 'Product list'
        );
    }
}