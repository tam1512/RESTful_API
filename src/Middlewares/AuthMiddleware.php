<?php
namespace App\Middlewares;
use App\Models\User;
use Pecee\Http\Middleware\IMiddleware;
use Pecee\Http\Request;
class AuthMiddleware implements IMiddleware {
   public function handle(Request $request): void {
      $apiKeyStatus = env('API_KEY_SYSTEM') == 'false' ? false : true;
      $apiKey = $request->getHeader('x-api-key');
      if($apiKey) {
         if($apiKeyStatus) {
            if($apiKey != env('API_KEY')) {
               errorResponse(401, 'Unauthorize', "API key not match");
            }
         } else {
            $userModel = new User();
            $user = $userModel->getUser($apiKey, 'api_key');
            if(!$user) {
               errorResponse(401, 'Unauthorize', "API key not match");
            }
         }
      } else {
         errorResponse(401, 'Unauthorize', "No API key");
      }
   }
}