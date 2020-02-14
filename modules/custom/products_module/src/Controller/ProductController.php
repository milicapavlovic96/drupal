<?php
namespace Drupal\products_module\Controller;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\node\Entity\Node;
use \stdClass;
use Drupal\image\Entity\ImageStyle;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\products_module\Services\BookService;

class ProductController extends ControllerBase {

  protected $entityQuery;
  protected $entityTypeManager;
  protected $submittedToken;
  protected $request_stack;
  protected $filter;
  protected $book_service;

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.query'),
      $container->get('entity_type.manager'),
      $container->get('request_stack'),
      $container->get('products_module.book')
    );
  }
  
  public function __construct(QueryFactory $entityQuery, EntityTypeManagerInterface $entityTypeManager, RequestStack $request_stack, BookService $book_service) {
    $this->entityQuery = $entityQuery;
    $this->entityTypeManager = $entityTypeManager;
    $this->request_stack = $request_stack->getCurrentRequest();
    $this->filter='cvet';
    $this->book_service=$book_service;
  }

  public function product(){
    $products = $this->getAllProducts();
    $allTags= $this->getAllTags();
    /**
     * Ovde pozivam injectovani Book Service koji poziva external url i vraca xml file sa knjigama
     */
    $nesto= $this->book_service->getBookData();

      return array(
          '#theme' => 'product_list',
          '#items' => array('allProducts' => $products, 'allTags' =>$allTags),
          '#title' => 'Product list',
          '#pager' => [
            '#type' => 'pager'
          ]
        );
  }
  /**
   * Ovde getujemo listu proizvoda. Prvi pozivamo metode koje kupe filtere (ako ih ima), a onda getujemo proizvode uz pomoć entityQuery-a.
   */
  public function getAllProducts(){
    $config = $this->config('products_module.settings');
    $filter= $this->titleFilter();
    $selectedTag= $this-> selectedTag();
    if($filter!='' && $selectedTag!=''){
      $nids = $this->entityQuery->get('node')->condition('type', 'product')
          ->condition('field_tags1.entity.name', $selectedTag, 'CONTAINS')
          ->condition('title',$filter,'CONTAINS')
          ->pager($config->get('productsPerPage'))
          ->execute();
    }
    else if($filter!='' && $selectedTag==''){
      $nids = $this->entityQuery->get('node')->condition('type', 'product')
          ->condition('title',$filter,'CONTAINS')
          ->pager($config->get('productsPerPage'))
          ->execute();
    }
    else if($filter=='' && $selectedTag!=''){
      $nids = $this->entityQuery->get('node')->condition('type', 'product')
          ->condition('field_tags1.entity.name', $selectedTag, 'CONTAINS')
          ->pager($config->get('productsPerPage'))
          ->execute();
    }else {
        $nids = $this->entityQuery->get('node')->condition('type', 'product')
          ->pager($config->get('productsPerPage'))
          ->execute();
    }
    if(count($nids)==0)
    {
      $products=[];
      return $products;
    }
    else{
      $items = $this->entityTypeManager->getStorage('node')->loadMultiple($nids);
      $products= $this-> getProducts($items);
      return $products;
    }
  }
  /**
   * Pretraga proizvoda po nazivu, ovde getujemo tekst koji je korisnik uneo
   */
  public function titleFilter(){
    $filter=$this->request_stack->get('title_filter');
    if($filter==null){
    $filter='';
    }
    return $filter;
  }
  /**
   * Filtiramo proizvode prema tagovima koje sadrže. Ovde getujemo tag koji je korisnik izabrao na dropdown listi
   */
  public function selectedTag(){
    $selectedTag=$this->request_stack->get('selectedTag');
    if($selectedTag==null){
      $selectedTag='';
    }
      return $selectedTag;
  }
  /**
   * Tagovi prosledjenog node-a
   */
  public function getNodeTags($item){
    $field_tags=$item->field_tags1->referencedEntities();
    $tags=[];  
    foreach($field_tags as $tag){
      $term_obj = $this->entityTypeManager->getStorage('taxonomy_term')->load($tag->tid->getValue()[0]['value']);
      $name=$term_obj->getName();
      $tags[]=$name;
    }
    return $tags;
  }
  /**
   * Getujemo sve dostupne tagove za dropdown listu
   */ 
  public function getAllTags(){
    $tags= $this->entityTypeManager->getStorage('taxonomy_term')->loadTree('tags');
    $tags_list=[null];
      foreach ($tags as $term) { 
        $term_obj = $this->entityTypeManager->getStorage('taxonomy_term')->load($term->tid);
        $name=$term_obj->getName();
        $tags_list[]=$name;
    }
    return $tags_list;
  }
  /**
   * Ovde iz svakog učitanog item-a uzimamo potrebne podatke. Vraćamo niz proizvoda. 
   */
  public function getProducts($items){
    foreach($items as $item){
      $img_urls=[];
      $title= $item->title->value;
      foreach ($item->field_images as $image) {
        if ($image->entity) {
          $img_urls[] = $image->entity->url();
        }
      }
      //$image= ImageStyle::load('large')->buildUrl($item->field_images->entity->getFileUri());
      $description= $item->get('field_description')->value;
      $field_tags= $this->getNodeTags($item);
      

      $product = array(
        'title'=> $title,  
        'description'=> $description,
        //'image' => $image,
        'image' => $img_urls,
        'tags' => $field_tags
      );
      $products[]=$product;
    }
    return $products;
  }
}