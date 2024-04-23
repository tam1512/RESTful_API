<?php
namespace App\Controllers\V1;
use App\Models\User;
use Firebase\JWT\JWT;
class Auth {
   public function login() {
      $email = input('email');
      $password = input('password');

      if(!$email || !$password) {
         return errorResponse(400, "Vui lòng nhập email và mật khẩu.");
      }
      $userModel = new User();
      $user = $userModel->getUser($email, 'email');
      if(!$user) {
         return errorResponse(404, "Tài khoản không tồn tại.");
      }

      $passwordHash = $user['password'];
      if(!password_verify($password, $passwordHash)) {
         return errorResponse(401, "Mật khẩu không đúng.");
      }
      
      //Tạo token JWT
      $payload = [
         'sub' => $user['id'],
         'iat' => time(),
         'exp' => time() + env('JWT_EXP')
     ];
     $accessToken = JWT::encode($payload, env('JWT_SECRET'), 'HS256');
     return successResponse(data: [
      'access_token' => $accessToken
     ]);

   }

   public function profile() {
      return successResponse(data:\System\Core\Auth::getUser());
   }
}