<?php
namespace App\Controllers\V1;
use App\Models\User as UserModel;
use Rakit\Validation\Validator;


class User {
   private static $model;

   public function __construct() {
      if(!self::$model) {
         self::$model = new UserModel();
      }
   }
   public function index() {
      $sort = input('sort') ?? 'id';
      $order = input('order') ?? 'desc';
      $status = input('status');
      $query = input('query');
      $limit = (int)input('limit');
      $page = input('page') ?? 1;
      $offset = 0;
      if($limit) {
          $offset = ($page - 1) * $limit;
      }
      $count = self::$model->countRows(compact('sort', 'order', 'status', 'query'));
      $users = self::$model->getUsers(compact('sort', 'order', 'status', 'query', 'limit', 'offset'));
      return successResponse(data:$users, meta:$limit ? [
         'current_page' => $page,
         'total_rows' => $count,
         'total_pages' => ceil($count/$limit)
      ] : []);
      // echo "users";
   }
   public function find($id) {
      $user = self::$model->getUser($id);
      if(!empty($user)) {
         return successResponse(data:$user);
      }
      return errorResponse(404, 'User not found', [
         'error' => 'User not found',
         'id' => $id
      ]);
   }
   public function store() {
      $validator = new Validator;
      $validator->setMessages([
         'required' => ':attribute bắt buộc phải nhập.',
         'email:email' => ':attribute không hợp lệ.',
         'password:min' => ':attribute phải từ :min ký tự',
         'comfirm_password:same' => ':attribute không trùng khớp'
      ]);
      $validation = $validator->make(input()->all(), [
         'fullname' => 'required',
         'email' => [
            'required', 
            'email',
            function($email) {
               $check = self::$model->checkExist('email', $email); 
               if($check) {
                  return ':attribute đã tồn tại.';
               }
               return true;
            }
         ],
         'password' => 'required|min:6',
         'confirm_password' => 'required|same:password'
      ]);
      $validation->setAliases([
         'fullname' => 'Tên',
         'email' => 'Email',
         'password' => 'Mật khẩu',
         'confirm_password' => 'Nhập lại mật khẩu',
      ]);
      $validation->validate();

      if($validation->fails()) {
         $errors = $validation->errors();
         return errorResponse(404, 'Bad request', $errors->firstOfAll());
      } else {
         $data = [
            'fullname' => input('fullname'),
            'email' => input('email'),
            'password' => password_hash(input('password'), PASSWORD_DEFAULT)
         ];
         $user = self::$model->create($data);
         return successResponse(data:$user, status:201);
      }
   }

}