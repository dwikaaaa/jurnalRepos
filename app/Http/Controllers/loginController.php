<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class loginController extends Controller
{
    public function postLogin(Request $request)
    {
        try {

            $request->validate([
                'username' => 'required',
                'password' => 'required',
            ]);

            $user = User::where('username', $request->username)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'message' => 'kombinasi username dan password salah'
                ], 401);
            }

            $token = $user->createToken('user_token')->plainTextToken;

            return response()->json([
                'id' => $user->id,
                'username' => $user->username,
                'nama' => $user->nama,
                'level' => $user->level,
                'foto' => $user->foto,
                'token' => $token
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ]);
        }
    }
    
    public function logout(Request $request) {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'message' => 'unautorized'
                ], 401);
            }

            $user->currentAccessToken()->delete();

            return response()->json([
                'message' => 'logout success'
            ]);

        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ]);
        }
    }
}
