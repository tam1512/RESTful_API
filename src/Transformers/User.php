<?php
namespace App\Transformers;
use System\Core\Transformer;
class User extends Transformer {
   public function response() {
      return [
         'id' => $this->id,
         'fullname' => $this->fullname,
         'email' => $this->email,
         'avatar' => $this->avatar,
         'status' => $this->status,
         'statusText' => $this->status ? 'Kích hoạt' : 'Chưa kích hoạt',
         'createdAt' => $this->created_at,
         'updatedAt' => $this->updated_at
      ];
   }
}