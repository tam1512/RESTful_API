<?php 
namespace App\Models;
use System\Core\Model;
class User extends Model {
   public function getUsers($options = []) {
      extract($options);
      $user = $this->db->table('users')->select('id, fullname, email, status, created_at, updated_at')->orderBy($sort, $order);
      if($status === 1 || $status === 0 || $status === '1' || $status === '0') {
         $user->where('status', '=', $status);
      }
      if($query) {
         $user->where(function ($builder) use ($query) {
            $builder->where('fullname', 'like', "%$query%");
            $builder->orWhere('email', 'like', "%$query%");
         });
      }
      if($limit && isset($offset)) {
         $user->limit($limit, $offset);
      }
      return $user->get();
   }

   public function countRows($options = []) {
      extract($options);
      $user = $this->db->table('users')->orderBy($sort, $order);
      if($status === 1 || $status === 0 || $status === '1' || $status === '0') {
         $user->where('status', '=', $status);
      }
      if($query) {
         $user->where(function ($builder) use ($query) {
            $builder->where('fullname', 'like', "%$query%");
            $builder->orWhere('email', 'like', "%$query%");
         });
      }
      return $user->count();
   }
   public function getUser($value, $field = 'id') {
      return $this->db->table('users')->select('id, fullname, email, password, status, created_at, updated_at')->where($field, '=', $value)->first();
   }
   public function checkExist($field = 'id', $value, $id = 0) {
      $count = $this->db->table('users')->where($field, '=', $value);
      if($id > 0) {
         $count = $count->where('id', '!=', $id);
      }
      $count = $count->count();
      return $count > 0;
   }

   public function create($data) {
      $this->db->table('users')->insert($data);
      $id = $this->db->lastId();

      $user = $this->db->table('users')->where('id', '=', $id)->first();
      if(!empty($user)) {
         unset($user['password']);
      }
      return $user;
   }

   public function updateUser($data, $value, $field = "id") {
      return $this->db->table('users')->where($field, "=", $value)->update($data);
   }

   public function deleteUser($id) {
      return $this->db->table('users')->where("id", "=", $id)->delete();
   }
   public function deleteUsers($ids) {
      return $this->db->table('users')->whereIn('id', $ids)->delete();
   }

   public function courses($userId) {
      return $this->db->table('courses as c')->join('users_courses as uc', "uc.course_id = c.id")->where('uc.user_id', '=', $userId)->get();
   }
}