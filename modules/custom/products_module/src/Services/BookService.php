<?php

namespace Drupal\products_module\Services;

use Drupal\comment\Entity\Comment;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\Query\QueryFactory;

class BookService{

    protected $client;
    protected $xml;
    protected $entityQuery;
    protected $entityTypeManager;
        
    public function getBookData(){
        $this->client = \Drupal::httpClient();
        $response = $this->client->request('GET','http://www.chilkatsoft.com/xml-samples/bookstore.xml');
        $xml=simplexml_load_string($response->getBody()->getContents());
        $this->createNewEntity($xml);
    }
    
    public function createNewEntity($xml){
        foreach($xml as $book){
            $title= (string) $book->title;
            //Proveravamo da li content sa tim nazivom vec postoji
            $nids = \Drupal::entityQuery('node')->condition('type', 'book')
            ->condition('title', $title, 'CONTAINS')->execute();
            if($nids==null)
            {
                $price= (string) $book->price;
                $comments= $book->comments;
                //Ukoliko ima komentara stavljamo ih u niz stringova
                if($comments->userComment != null){
                    $numberOfComments = $comments->userComment->count();
                    $commentsArray=[];
                    for($i=0;$i<$numberOfComments;$i++)
                    {
                        $commentsArray[]=(string) $comments->userComment[$i];
                    }
                }
                $isbn= (string) $book->attributes()->ISBN;
                $node = \Drupal::entityTypeManager()->getStorage('node')->create(array(
                    'type' => 'book',
                    'title' => $title,
                    'field_price' => $price,
                    'field_comments'=> $commentsArray,
                    'field_isbn' => $isbn));
                $node->save();
            }
        }
    }
}
