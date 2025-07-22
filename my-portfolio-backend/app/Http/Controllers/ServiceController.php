<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\ServiceSection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ServiceController extends Controller
{
    public function getServiceSection()
    {
        $serviceSection = ServiceSection::first();
        $services = Service::all();

        return response()->json([
            'section' => $serviceSection,
            'services' => $services
        ]);
    }

    public function updateServiceSection(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:191',
            'subtitle' => 'required|string|max:191',
            'info_text' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $serviceSection = ServiceSection::first();
        if (!$serviceSection) {
            $serviceSection = new ServiceSection();
        }

        $serviceSection->title = $request->title;
        $serviceSection->subtitle = $request->subtitle;
        $serviceSection->info_text = $request->info_text;
        $serviceSection->save();

        return response()->json(['message' => 'Service section updated successfully', 'section' => $serviceSection]);
    }

    public function getServices()
    {
        $services = Service::all();
        return response()->json($services);
    }

    public function getService($id)
    {
        $service = Service::findOrFail($id);
        return response()->json($service);
    }

    public function storeService(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:191',
            'description' => 'required|string',
            'icon' => 'required|string|max:191',
            'technologies' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $service = new Service();
        $service->title = $request->title;
        $service->description = $request->description;
        $service->icon = $request->icon;
        $service->technologies = json_encode($request->technologies);
        $service->save();

        return response()->json(['message' => 'Service created successfully', 'service' => $service], 201);
    }

    public function updateService(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:191',
            'description' => 'required|string',
            'icon' => 'required|string|max:191',
            'technologies' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $service = Service::findOrFail($id);
        $service->title = $request->title;
        $service->description = $request->description;
        $service->icon = $request->icon;
        $service->technologies = json_encode($request->technologies);
        $service->save();

        return response()->json(['message' => 'Service updated successfully', 'service' => $service]);
    }

    public function deleteService($id)
    {
        $service = Service::findOrFail($id);
        $service->delete();

        return response()->json(['message' => 'Service deleted successfully']);
    }
}