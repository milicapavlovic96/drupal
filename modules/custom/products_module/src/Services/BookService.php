<?php

namespace Drupal\products_module\Services;

use Drupal\comment\Entity\Comment;


class BookService{

    private $client;

public function getBookData(){
    $this->client = \Drupal::httpClient();
    $response = $this->client->request('GET','http://www.chilkatsoft.com/xml-samples/bookstore.xml');
    $xml=simplexml_load_string($response->getBody()->getContents());
   foreach($xml as $book){
       $title= (string) $book->title;
       $price= (string) $book->price;
       $comments= $book->comments;
       //Smestamo komentare u jedan niz
       $commentsArray=[];
       for($i=0;$i<count($comments->userComment);$i++)
       {
            $commentsArray[]=(string) $comments->userComment[$i];
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