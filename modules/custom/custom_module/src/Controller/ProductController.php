<?php
namespace Drupal\custom_module\Controller;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\node\Entity\Node;
use \stdClass;
use Drupal\image\Entity\ImageStyle;

class ProductController extends ControllerBase {

    protected $entityQuery;
    protected $entityTypeManager;
    protected $submittedToken;

    public static function create(ContainerInterface $container) {
        return new static(
        $container->get('entity.query'),
        $container->get('entity_type.manager')
        );
      }
    
    public function __construct(QueryFactory $entityQuery, EntityTypeManagerInterface $entityTypeManager) {
        $this->entityQuery = $entityQuery;
        $this->entityTypeManager = $entityTypeManager;
      }

    public function product(){

        $products = $this->getData();

        return array(
            '#theme' => 'product_list',
            '#items' => $products,
            '#title' => 'Product list'
        );
    }

    public function getData(){
        $nids = $this->entityQuery->get('node')->condition('type', 'product')->execute();
        $items = $this->entityTypeManager->getStorage('node')->loadMultiple($nids);

        foreach($items as $item){
            $title= $item->title->getValue()[0]['value'];
            $image= ImageStyle::load('large')->buildUrl($item->field_images->entity->getFileUri());
            $description= $item->field_description->getValue()[0]['value'];  

            $product = array(
              'title'=> $title,  
              'description'=> $description,
              'image' => $image
            );
            $products[]=$product;
        }
        return $products;
    }
}