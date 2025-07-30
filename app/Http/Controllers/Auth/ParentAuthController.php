<?php

namespace App\Http\Controllers\Auth;

use App\Models\Parnt;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\Auth\ParentRegisterRequest;
use App\Http\Resources\Auth\ParentRegisterResource;

class ParentAuthController extends Controller
{
        // public function __construct()
    // {
    //     $this->middleware('auth:admin',
    //      ['except' => ['register','login','verify','logout']]);
    // }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(LoginRequest $request)
    {
        $validator = Validator::make($request->all(), $request->rules());


        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if (!$token = auth()->guard('parnt')->attempt($validator->validated())) {
            return response()->json(['message' => 'Invalid data'], 422);

        }


        return $this->createNewToken($token);
    }


    public function register(ParentRegisterRequest $request)
    {


        $validator = Validator::make($request->all(), $request->rules());

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        if ($request->hasFile('img')) {
            $imagePath = $request->file('img')->store(Parnt::storageFolder);
            // dd($imagePath);
        }

        $parentData = array_merge(
            $validator->validated(),
            ['password' => bcrypt($request->password)]
        );

        if (isset($imagePath)) {
            $parentData['img'] = $imagePath;
        }

        // dd($parentData);

        $parent = Parnt::create($parentData);

        // $parent->save();
        // $admin->notify(new EmailVerificationNotification());

        return response()->json([
            'message' => 'Parent Registration successful',
            'parent' =>new ParentRegisterResource($parent)
        ]);
    }




    /**
     * Log the admin out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->guard('parnt')->logout();
        return response()->json([
            'message' => 'Parent successfully signed out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->createNewToken(auth()->guard('parnt')->refresh());
    }

    /**
     * Get the authenticated Admin.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function userProfile()
    {
        return response()->json(["data" => auth()->guard('parnt')->user()]);
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function createNewToken($token)
    {
        $parent = Parnt::find(auth()->guard('parnt')->id());
        return response()->json([

            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->guard('parnt')->factory()->getTTL() * 60,
            // 'parent' => auth()->guard('parnt')->user(),

            'parent' => $parent,
        ]);
    }
}
