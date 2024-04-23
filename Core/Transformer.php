<?php
namespace System\Core;
class Transformer {
   private $data;
   private $resources;
   private $isResource;
   public function __construct($resources, $multi = false) {
      $this->resources = $resources;
      if(!$multi) {
         $this->isResource = 'detail';
      } else {
         $this->isResource = 'list';
      }
   }

   public function getOuput() {
      if($this->isResource == 'detail') {
         $this->setAttribute($this->resources);
         $this->data = $this->response();
      }

      if($this->isResource == 'list') {
         foreach($this->resources as $resource) {
            $this->setAttribute($resource);
            $this->data[] = $this->response();
         }
      }

      return $this->data;
   }

   private function setAttribute($arr) {
      foreach ($arr as $key => $value) {
         $this->{$key} = $value;
      }
   }
}