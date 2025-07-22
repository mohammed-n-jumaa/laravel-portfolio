<?php

namespace App\Http\Controllers;

use App\Models\Experience;
use App\Models\ExperienceSection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ExperienceController extends Controller
{
    public function getExperienceSection()
    {
        $section = ExperienceSection::first();
        
        if (!$section) {
            $section = ExperienceSection::create([
                'title' => 'My Experience',
                'subtitle' => 'Professional journey and growth as a web developer',
            ]);
        }
        
        return response()->json($section);
    }
    
    public function updateExperienceSection(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:191',
            'subtitle' => 'required|string|max:191',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        $section = ExperienceSection::first();
        
        if (!$section) {
            $section = new ExperienceSection();
        }
        
        $section->title = $request->title;
        $section->subtitle = $request->subtitle;
        $section->save();
        
        return response()->json($section);
    }
    
    public function getExperiences()
    {
        $experiences = Experience::orderBy('start_date', 'desc')->get();
        return response()->json($experiences);
    }
    
    public function getExperience($id)
    {
        $experience = Experience::findOrFail($id);
        return response()->json($experience);
    }
    
    public function storeExperience(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company' => 'required|string|max:191',
            'position' => 'required|string|max:191',
            'start_date' => 'required|string|max:191',
            'end_date' => 'required|string|max:191',
            'description' => 'required|string',
            'skills' => 'required|array',
            'logo' => 'nullable|string',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        $logoPath = null;
        if ($request->logo && preg_match('/^data:image\/(\w+);base64,/', $request->logo)) {
            $logoData = substr($request->logo, strpos($request->logo, ',') + 1);
            $logoData = base64_decode($logoData);
            $logoPath = 'experiences/' . time() . '.png';
            Storage::disk('public')->put($logoPath, $logoData);
            $logoPath = '/storage/' . $logoPath;
        }
        
        $experience = Experience::create([
            'company' => $request->company,
            'position' => $request->position,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'description' => $request->description,
            'skills' => json_encode($request->skills),
            'logo' => $logoPath,
        ]);
        
        return response()->json($experience, 201);
    }
    
    public function updateExperience(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'company' => 'required|string|max:191',
            'position' => 'required|string|max:191',
            'start_date' => 'required|string|max:191',
            'end_date' => 'required|string|max:191',
            'description' => 'required|string',
            'skills' => 'required|array',
            'logo' => 'nullable|string',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        $experience = Experience::findOrFail($id);
        
        if ($request->logo && preg_match('/^data:image\/(\w+);base64,/', $request->logo)) {
            if ($experience->logo && Storage::disk('public')->exists(str_replace('/storage/', '', $experience->logo))) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $experience->logo));
            }
            
            $logoData = substr($request->logo, strpos($request->logo, ',') + 1);
            $logoData = base64_decode($logoData);
            $logoPath = 'experiences/' . time() . '.png';
            Storage::disk('public')->put($logoPath, $logoData);
            
            $experience->logo = '/storage/' . $logoPath;
        }
        
        $experience->company = $request->company;
        $experience->position = $request->position;
        $experience->start_date = $request->start_date;
        $experience->end_date = $request->end_date;
        $experience->description = $request->description;
        $experience->skills = json_encode($request->skills);
        $experience->save();
        
        return response()->json($experience);
    }
    
    public function deleteExperience($id)
    {
        $experience = Experience::findOrFail($id);
        
        if ($experience->logo && Storage::disk('public')->exists(str_replace('/storage/', '', $experience->logo))) {
            Storage::disk('public')->delete(str_replace('/storage/', '', $experience->logo));
        }
        
        $experience->delete();
        
        return response()->json(['message' => 'Experience deleted successfully']);
    }
}