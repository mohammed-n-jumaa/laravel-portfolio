<?php

namespace App\Http\Controllers;

use App\Models\Skill;
use App\Models\SkillItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SkillController extends Controller
{
    // Get all skills
    public function index()
    {
        try {
            $skill = Skill::first();
            
            if (!$skill) {
                return response()->json([
                    'title' => 'My Skills',
                    'subtitle' => 'Technologies I work with',
                    'description' => 'Description of my skills',
                    'skills' => []
                ]);
            }
            
            $skillItems = SkillItem::where('skill_id', $skill->id)->get();
            
            return response()->json([
                'id' => $skill->id,
                'title' => $skill->title,
                'subtitle' => $skill->subtitle,
                'description' => $skill->description,
                'skills' => $skillItems
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching skills: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch skills'], 500);
        }
    }

    // Store a new skill
    public function store(Request $request)
    {
        DB::beginTransaction();
        
        try {
            // Validate the main skill data
            $validatedData = $request->validate([
                'title' => 'required|string|max:255',
                'subtitle' => 'required|string|max:255',
                'description' => 'required|string',
                'skillItems' => 'nullable|array',
            ]);
            
            // Create the main skill
            $skill = Skill::create([
                'title' => $validatedData['title'],
                'subtitle' => $validatedData['subtitle'],
                'description' => $validatedData['description'],
            ]);
            
            // Add skill items if provided
            if (isset($request->skillItems) && is_array($request->skillItems)) {
                foreach ($request->skillItems as $item) {
                    // Store the image in separate storage if it's a base64 string
                    $imageUrl = $this->processImage($item['image'] ?? '');
                    
                    $skill->skillItems()->create([
                        'name' => $item['name'],
                        'category' => $item['category'],
                        'image' => $imageUrl,
                        'description' => $item['description'],
                    ]);
                }
            }
            
            DB::commit();
            
            return response()->json([
                'id' => $skill->id,
                'title' => $skill->title,
                'subtitle' => $skill->subtitle,
                'description' => $skill->description,
                'skills' => $skill->skillItems
            ], 201);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating skill: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    // Get a specific skill
    public function show(Skill $skill)
    {
        try {
            $skillItems = SkillItem::where('skill_id', $skill->id)->get();
            
            return response()->json([
                'id' => $skill->id,
                'title' => $skill->title,
                'subtitle' => $skill->subtitle,
                'description' => $skill->description,
                'skills' => $skillItems
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching skill: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch skill'], 500);
        }
    }

    // Update a skill
    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        
        try {
            $skill = Skill::findOrFail($id);
            
            // Update the main skill
            $skill->update([
                'title' => $request->title,
                'subtitle' => $request->subtitle,
                'description' => $request->description,
            ]);
            
            // Handle skill items
            if (isset($request->skillItems) && is_array($request->skillItems)) {
                // Delete existing skill items
                SkillItem::where('skill_id', $skill->id)->delete();
                
                // Create new skill items
                foreach ($request->skillItems as $item) {
                    // Store the image in separate storage if it's a base64 string
                    $imageUrl = $this->processImage($item['image'] ?? '');
                    
                    SkillItem::create([
                        'skill_id' => $skill->id,
                        'name' => $item['name'],
                        'category' => $item['category'],
                        'image' => $imageUrl,
                        'description' => $item['description'],
                    ]);
                }
            }
            
            DB::commit();
            
            return response()->json([
                'id' => $skill->id,
                'title' => $skill->title,
                'subtitle' => $skill->subtitle,
                'description' => $skill->description,
                'skills' => $skill->fresh()->skillItems
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating skill: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    // Delete a skill
    // Make sure to have proper error logging in your destroy method
// For deleting individual skill items
public function destroy($id)
{
    try {
        Log::info('Attempting to delete skill item with ID: ' . $id);
        
        // Find the skill item by ID
        $skillItem = SkillItem::find($id);
        
        if (!$skillItem) {
            Log::warning('Skill item not found with ID: ' . $id);
            return response()->json(['error' => 'Skill item not found'], 404);
        }
        
        // Delete the skill item
        $skillItem->delete();
        Log::info('Successfully deleted skill item with ID: ' . $id);
        
        return response()->json(['message' => 'Skill item deleted successfully'], 200);
    } catch (\Exception $e) {
        Log::error('Error deleting skill item: ' . $e->getMessage());
        return response()->json(['error' => 'Failed to delete skill item: ' . $e->getMessage()], 500);
    }
}
    
    // Process image method
    private function processImage($imageData)
    {
        // If it's already a URL, return it as is
        if (filter_var($imageData, FILTER_VALIDATE_URL)) {
            return $imageData;
        }
        
        // If it's a base64 string, save it to storage
        if (preg_match('/^data:image\/(\w+);base64,/', $imageData, $matches)) {
            $imageType = $matches[1];
            $imageData = substr($imageData, strpos($imageData, ',') + 1);
            $imageData = base64_decode($imageData);
            
            if ($imageData === false) {
                return '';
            }
            
            // Use Laravel's Storage facade instead of direct file operations
            $filename = 'skill_' . time() . '_' . Str::random(10) . '.' . $imageType;
            
            // Store in the public disk
            Storage::disk('public')->put('skills/' . $filename, $imageData);
            
            // Return the correct URL for public access
            return asset('storage/skills/' . $filename);
        }
        
        // If it's something else, return empty string
        return $imageData;
    }
}