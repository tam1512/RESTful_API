<?php
namespace App\Middlewares;
use App\Models\BlacklistToken;
use App\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Pecee\Http\Middleware\IMiddleware;
use Pecee\Http\Request;
use System\Core\Auth;

class AuthorMiddleware implements IMiddleware {
   public function handle(Request $request): void {
      $authorization = $request->getHeader('Authorization');
     if(!$authorization) {
         errorResponse(401, 'Unauthorize');
     } else {
      $token = trim(str_replace('Bearer', '', $authorization));
      try {
         $blacklist = (new BlacklistToken())->find($token, "token");
         if($blacklist) {
            errorResponse(401, "Token is Blacklist");
         }

         $decoded = JWT::decode($token, new Key(env('JWT_SECRET'), 'HS256'));
         $userId = $decoded->sub;
         $userModel = new User();
         $user = $userModel->getUser($userId);
         if(!$user) {
            errorResponse(404, 'User not found');
         } else if(!$user['status']) {
            errorResponse(404, 'User blocked');
         } else {
            // $userTransformer = new \App\Transformers\User($user);
            // Auth::setUser((array)$userTransformer);
            $user['token'] = $token;
            $user['expire'] = date('Y-m-d H:i:s', $decoded->exp);
            Auth::setUser($user);
         }
      } catch(\Exception $e) {
         errorResponse(401, 'Unauthorize');
      }
     }
   }
}