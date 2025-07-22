<?php

namespace App\Http\Controllers;

use App\Models\Hero;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class HeroController extends Controller
{
    public function index()
    {
        try {
            $hero = Hero::latest()->first();
            
            if (!$hero) {
                // Create a default hero record
                $hero = new Hero();
                $hero->name = 'Your Name';
                $hero->title = 'Web Developer';
                $hero->description = 'Your professional description here';
                $hero->experience_months = 0;
                $hero->tech_stack = json_encode(['HTML', 'CSS', 'JavaScript']);
                $hero->profile_image = '';
                $hero->save();
                
                // Fetch the newly created hero
                $hero = Hero::latest()->first();
            }
            
            return response()->json([
                'status' => 'success',
                'data' => $hero
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve hero data',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:191',
                'title' => 'required|string|max:191',
                'description' => 'required|string',
                'experience_months' => 'required|integer|min:0',
                'tech_stack' => 'required|array',
                'profile_image' => 'nullable|string'
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            $hero = Hero::latest()->first();
            
            if (!$hero) {
                $hero = new Hero();
            }
            
            $hero->name = $request->name;
            $hero->title = $request->title;
            $hero->description = $request->description;
            $hero->experience_months = $request->experience_months;
            $hero->tech_stack = json_encode($request->tech_stack);
            
            // Handle profile image (if provided)
            if ($request->filled('profile_image') && $request->profile_image != '' && 
                strpos($request->profile_image, 'data:image/') === 0) {
                // Process base64 image
                $image_parts = explode(';base64,', $request->profile_image);
                $image_type_aux = explode('image/', $image_parts[0]);
                $image_type = $image_type_aux[1];
                $image_base64 = base64_decode($image_parts[1]);
                
                $filename = 'profile_' . time() . '.' . $image_type;
                $path = public_path('storage/profile_images/');
                
                // Create directory if it doesn't exist
                if (!file_exists($path)) {
                    mkdir($path, 0755, true);
                }
                
                file_put_contents($path . $filename, $image_base64);
                // In the store method where you're setting the profile_image
$hero->profile_image = url('/storage/profile_images/' . $filename);
            }
            
            $hero->save();
            
            return response()->json([
                'status' => 'success',
                'message' => 'Hero section updated successfully',
                'data' => $hero
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update hero data',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}