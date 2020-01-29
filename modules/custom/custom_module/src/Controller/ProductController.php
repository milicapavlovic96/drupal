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
      $allTags= $this->getAllTags();

        return array(
            '#theme' => 'product_list',
            '#items' => array('allProducts' => $products, 'allTags' =>$allTags),
            '#title' => 'Product list' 
          );
    }

    public function getData(){
        $config = $this->config('custom_module.settings');
        $filter= $this->yourAction();
        $selectedTag= $this-> selectedTag();
        $nes=$this->entityQuery->get('node');
        $nids = $this->entityQuery->get('node')->condition('type', 'product')->condition('field_tags1.entity.name', $selectedTag, 'CONTAINS')->condition('title',$filter,'CONTAINS')->pager($config->get('default_count'))->execute();
        $items = $this->entityTypeManager->getStorage('node')->loadMultiple($nids);
        $products= $this-> getProducts($items);
        
        return $products;
    }

    public function yourAction(){
      $filter=$this->request_stack->get('token');
      if($filter==null){
      $filter='';
      }
      return $filter;
  }

  public function selectedTag(){
    $selectedTag=$this->request_stack->get('selected');
    if($selectedTag==null){
      $selectedTag='';
      }
      return $selectedTag;
  }

  //Tagovi prosledjenog node-a
  public function getNodeTags($item){
    $field_tags=$item->field_tags1->referencedEntities();
    $tags=[];  
    foreach($field_tags as $tag){
      $term_obj = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($tag->tid->getValue()[0]['value']);
      $name=$term_obj->getName();
      $tags[]=$name;
    }
    return $tags;
  }

  //Svi tagovi za dropdown listu
  public function getAllTags(){
    $tags= \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree('tags');
    $tags_list=[null];
        foreach ($tags as $term) { 
          $term_obj = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($term->tid);
          $name=$term_obj->getName();
          $tags_list[]=$name;
    }
  return $tags_list;
}

  public function getProducts($items){
    foreach($items as $item){
      $title= $item->title->getValue()[0]['value'];
      $image= ImageStyle::load('large')->buildUrl($item->field_images->entity->getFileUri());
      $description= $item->field_description->getValue()[0]['value'];
      $field_tags= $this->getNodeTags($item);

      $product = array(
        'title'=> $title,  
        'description'=> $description,
        'image' => $image,
        'tags' => $field_tags
      );
      $products[]=$product;
    }
    return $products;
  }
}