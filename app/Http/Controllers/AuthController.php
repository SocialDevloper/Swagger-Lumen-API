<?php

namespace App\Http\Controllers;

use App\Models\User;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    /**
     * @OA\Post(
     ** path="/api/login",
     *   tags={"Login"},
     *   summary="Login",
     *   operationId="login",
     *
     *   @OA\Parameter(
     *      name="email",
     *      in="query",
     *      required=true,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *   @OA\Parameter(
     *      name="password",
     *      in="query",
     *      required=true,
     *      @OA\Schema(
     *          type="string"
     *      )
     *   ),
     *   @OA\Response(
     *      response=200,
     *       description="Success",
     *      @OA\MediaType(
     *           mediaType="application/json",
     *      )
     *   ),
     *   @OA\Response(
     *      response=401,
     *       description="Unauthenticated"
     *   ),
     *   @OA\Response(
     *      response=400,
     *      description="Bad Request"
     *   ),
     *   @OA\Response(
     *      response=404,
     *      description="not found"
     *   ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     *)
     **/

    public function login(Request $request)
    {
        $email    = $request->email;
        $password = $request->password;

        // check email & password not empty
        if (empty($email) || empty($password)) {
            return response()->json([
                'status'      => 'error',
                'status_code' => 422,
                'message'     => 'You must fill all the fields.',
            ]);
        }

        // Check if email is valid
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json([
                'status'      => 'error',
                'status_code' => 422,
                'message'     => 'You must enter a valid email.',
            ]);
        }
        
        $client = new Client();

        try {
            return $client->post(config('service.passport.login_endpoint'), [
                "form_params" => [
                    "client_secret" => config('service.passport.client_secret'),
                    "grant_type"    => "password",
                    "client_id"     => config('service.passport.client_id'),
                    "username"      => $request->email,
                    "password"      => $request->password
                ]
            ]);
        } catch (BadResponseException $e) {
            //return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
            return response()->json([
                'status'      => 'error',
                'status_code' => 422,
                'message'     => 'Invalid credentials please try again.',
            ]);
        }
    }

    /**
     * @OA\Post(
     ** path="/api/register",
     *   tags={"Register"},
     *   summary="Register",
     *   operationId="register",
     *
     *  @OA\Parameter(
     *      name="name",
     *      in="query",
     *      required=true,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *  @OA\Parameter(
     *      name="email",
     *      in="query",
     *      required=true,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *   @OA\Parameter(
     *      name="password",
     *      in="query",
     *      required=true,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *   @OA\Response(
     *      response=201,
     *       description="Success",
     *      @OA\MediaType(
     *           mediaType="application/json",
     *      )
     *   ),
     *   @OA\Response(
     *      response=401,
     *       description="Unauthenticated"
     *   ),
     *   @OA\Response(
     *      response=400,
     *      description="Bad Request"
     *   ),
     *   @OA\Response(
     *      response=404,
     *      description="not found"
     *   ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     *)
     **/
    
    public function register(Request $request)
    {
        $name     = $request->name;
        $email    = $request->email;
        $password = $request->password;

        // Check if field is not empty
        if (empty($name) or empty($email) or empty($password)) {
            return response()->json([
                'status'      => 'error',
                'status_code' => 422,
                'message'     => 'You must fill all the fields.',
            ]);
        }

        // Check if email is valid
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json([
                'status'      => 'error',
                'status_code' => 422,
                'message'     => 'You must enter a valid email.',
            ]);
        }

        // Check if password is greater than 5 character
        if (strlen($password) < 6) {
            return response()->json([
                'status'      => 'error',
                'status_code' => 422,
                'message'     => 'Password should be min 6 character.',
            ]);
        }

        // Check if user already exist
        if (User::where('email', '=', $email)->exists()) {
            return response()->json([
                'status'      => 'error',
                'status_code' => 422,
                'message'     => 'User already exists with this email.',
            ]);
        }

        // Create new user
        try {
            $user           = new User();
            $user->name     = $name;
            $user->email    = $email;
            $user->password = app('hash')->make($password);

            if ($user->save()) {
                // Will call login method
                return $this->login($request);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status'      => 'error',
                'status_code' => 422,
                'message'     => $e->getMessage(),
            ]);
        }
    }

    public function logout(Request $request)
    {
        try {
            auth()->user()->tokens()->each(function ($token) {
                $token->delete();
            });

            return response()->json([
                'status'      => 'success',
                'status_code' => 200,
                'message'     => 'Logged out successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'      => 'error',
                'status_code' => 422,
                'message'     => $e->getMessage(),
            ]);
        }
    }
}
