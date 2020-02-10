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
    
    /**
     * Ova metoda se poziva u controlleru, vraca xml fajl sa svim knjigama
     */
    public function getBookData(){
        $this->client = \Drupal::httpClient();
        $response = $this->client->request('GET','http://www.chilkatsoft.com/xml-samples/bookstore.xml');
        $xml=simplexml_load_string($response->getBody()->getContents());
        $this->createNewEntity($xml);
    }
    
    /**
     * U ovoj metodi getujemo podatke o knjigama iz xml fajla
     */ 
    public function createNewEntity($xml){
        foreach($xml as $book){
            $title= (string) $book->title;
            /**
             * Proveravamo da li content sa tim nazivom vec postoji
             */ 
            $nids = \Drupal::entityQuery('node')->condition('type', 'book')
            ->condition('title', $title, 'CONTAINS')->execute();
            if($nids==null)
            {
                $price= (string) $book->price;
                $comments= $book->comments;
                /**
                 * Ukoliko ima komentara stavljamo ih u niz stringova (ako se prvo ne prebaci u string nece da se prikaze)
                 */ 
                if($comments->userComment != null){
                    $numberOfComments = $comments->userComment->count();
                    $commentsArray=[];
                    for($i=0;$i<$numberOfComments;$i++)
                    {
                        $commentsArray[]=(string) $comments->userComment[$i];
                    }
                }
                $isbn= (string) $book->attributes()->ISBN;
                /**
                 * Kreiramo novi node
                 */
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
