<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class ProjectController extends Controller
{
    /**
     * Display a listing of the projects.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $projects = Project::all();
        
        // Convert technologies from JSON to array when returning
        $projects->transform(function ($project) {
            $project->technologies = json_decode($project->technologies);
            $project->image_url = $this->getImageUrl($project->image);
            return $project;
        });
        
        return response()->json($projects);
    }

    /**
     * Store a newly created project in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:191',
            'description' => 'required|string',
            'image' => 'nullable|string', // Changed from 'required' to 'nullable'
            'project_url' => 'required|string|max:191',
            'code_url' => 'required|string|max:191',
            'technologies' => 'required|array',
            'category' => 'required|string|max:191',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Handle base64 image if provided
        $image = $request->image;
        if ($image && strpos($image, 'data:image') === 0) {
            $savedImage = $this->saveBase64Image($image);
            
            if ($savedImage === null) {
                return response()->json(['errors' => ['image' => ['Failed to process the image. Please try a different image or format.']]], 422);
            }
            
            $image = $savedImage;
        }

        $project = Project::create([
            'title' => $request->title,
            'description' => $request->description,
            'image' => $image,
            'project_url' => $request->project_url,
            'code_url' => $request->code_url,
            'technologies' => json_encode($request->technologies),
            'category' => $request->category,
        ]);

        // Decode technologies for the response
        $project->technologies = json_decode($project->technologies);
        $project->image_url = $this->getImageUrl($project->image);

        return response()->json($project, 201);
    }

    /**
     * Display the specified project.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $project = Project::findOrFail($id);
        $project->technologies = json_decode($project->technologies);
        $project->image_url = $this->getImageUrl($project->image);
        
        return response()->json($project);
    }

    /**
     * Update the specified project in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:191',
            'description' => 'required|string',
            'image' => 'nullable|string',  // Changed from 'required' to 'nullable'
            'project_url' => 'required|string|max:191',
            'code_url' => 'required|string|max:191',
            'technologies' => 'required|array',
            'category' => 'required|string|max:191',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $project = Project::findOrFail($id);

        // Handle base64 image if provided
        $image = $request->image;
        if (strpos($image, 'data:image') === 0) {
            // If there's an old image and it's not a URL, delete it
            if ($project->image && strpos($project->image, 'http') !== 0) {
                Storage::delete('public/' . $project->image);
            }
            $image = $this->saveBase64Image($image);
        }

        $project->update([
            'title' => $request->title,
            'description' => $request->description,
            'image' => $image,
            'project_url' => $request->project_url,
            'code_url' => $request->code_url,
            'technologies' => json_encode($request->technologies),
            'category' => $request->category,
        ]);

        // Decode technologies for the response
        $project->technologies = json_decode($project->technologies);
        $project->image_url = $this->getImageUrl($project->image);

        return response()->json($project);
    }

    /**
     * Remove the specified project from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $project = Project::findOrFail($id);
        
        // Delete the image file if it's not a URL
        if ($project->image && strpos($project->image, 'http') !== 0) {
            Storage::delete('public/' . $project->image);
        }
        
        $project->delete();
        
        return response()->json(null, 204);
    }
    
    /**
     * Save base64 image and return the path
     *
     * @param  string  $base64Image
     * @return string
     */
    private function saveBase64Image($base64Image)
    {
        try {
            // Extract the image data from the base64 string
            $image_parts = explode(";base64,", $base64Image);
            
            // Check if we have a valid base64 string
            if (count($image_parts) < 2) {
                return null; // Invalid base64 string
            }
            
            $image_type_aux = explode("image/", $image_parts[0]);
            
            // Check if we have a valid image type
            if (count($image_type_aux) < 2) {
                return null; // Invalid image type
            }
            
            $image_type = $image_type_aux[1];
            $image_base64 = base64_decode($image_parts[1]);
            
            // Check if decoding was successful
            if ($image_base64 === false) {
                return null; // Failed to decode base64
            }
            
            // Generate a unique filename
            $filename = 'projects/' . uniqid() . '.' . $image_type;
            
            // Save the image to storage
            $saved = Storage::put('public/' . $filename, $image_base64);
            
            if (!$saved) {
                return null; // Failed to save image
            }
            
            return $filename;
        } catch (\Exception $e) {
            \Log::error('Error saving base64 image: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get the full URL for the image.
     *
     * @param  string  $imagePath
     * @return string
     */
    private function getImageUrl($imagePath)
    {
        if (!$imagePath) {
            return null;
        }

        if (strpos($imagePath, 'http') === 0) {
            return $imagePath;
        }

        return asset('storage/' . $imagePath);
    }
}