<?php

namespace Drupal\custom_module\Controller;

use Drupal\Core\Controller\ControllerBase;


class ProductController extends ControllerBase  {

    public function page(){

        $query= \Drupal::entityQuery('node');
        $nids = $query->condition('type', 'product')->execute();

        $items = \Drupal\node\Entity\Node::loadMultiple($nids);


        foreach($items as $item){
            $product=\Drupal::entityTypeManager()->getViewBuilder('node')->view($item);
            $products[]=render($product);
        }
        
        return array(
            '#theme' => 'product_list',
            '#items' => $products,
            '#title' => 'Product list'
        );
    }
}