<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use JWTAuth;
use App\User;
use App\Role;
use App\Permission;
use Auth;
use JWTAuthException;
class UserController extends Controller
{   

    private $user;
    public function __construct(User $user){
        $this->user = $user;
    }
   
    public function register(Request $request){
        $user = $this->user->create([
          'name' => $request->get('name'),
          'email' => $request->get('email'),
          'password' => bcrypt($request->get('password'))
        ]);
        return response()->json(['status'=>true,'message'=>'User created successfully','data'=>$user]);
    }
    
    public function login(Request $request){
        $credentials = $request->only('email', 'password');
        $token = null;
        try {
           if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json(['invalid_email_or_password'], 422);
           }
        } catch (JWTAuthException $e) {
            return response()->json(['failed_to_create_token'], 500);
        }
        // return response()->json(compact('token'));
        
        /** get all permission and user role after logedin */
        $user = Auth::user();
        $userId = $user->id;
        // get all user abilities
        $abilities = $user->allPermissions();
        $userRole = [];
        // get all user roles
        foreach ($user->Roles as $role) {
            $userRole[] = $role->name;
        }

        return response()->json(compact('token', 'userId', 'userRole', 'abilities'));
    }


    public function getAuthUser(Request $request){
        $user = JWTAuth::toUser($request->token);
        return response()->json(['result' => $user]);
    }
}  