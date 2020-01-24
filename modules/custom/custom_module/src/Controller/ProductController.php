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
use Symfony\Component\HttpFoundation\RequestStack;

class ProductController extends ControllerBase {

    protected $entityQuery;
    protected $entityTypeManager;
    protected $submittedToken;
    protected $request_stack;
    protected $filter;


    public static function create(ContainerInterface $container) {
        return new static(
        $container->get('entity.query'),
        $container->get('entity_type.manager'),
        $container->get('request_stack')
        );
      }
    
    public function __construct(QueryFactory $entityQuery, EntityTypeManagerInterface $entityTypeManager, RequestStack $request_stack) {
        $this->entityQuery = $entityQuery;
        $this->entityTypeManager = $entityTypeManager;
        $this->request_stack = $request_stack->getCurrentRequest();
       $this->filter='cvet';
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
      $filter= $this->yourAction();
        $nids = $this->entityQuery->get('node')->condition('type', 'product')->condition('title',$filter,'CONTAINS')->execute();
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

    public function yourAction(){
      $filter=$this->request_stack->get('token');
      if($filter==null){
      $filter='';
      }
      return $filter;
  }
}