<?php
namespace App\Models;
use System\Core\Model;

class BlacklistToken extends Model {
   public function find($value, $field="user_id") {
      return $this->db->table('blacklist_tokens')->where($field, '=', $value)->first();
   }

   public function create($data) {
      $this->db->table('blacklist_tokens')->insert($data);
   }

   public function deleteToken($userId) {
      $this->db->table('blacklist_tokens')->where('user_id', '=', $userId)->delete();
   }
}