<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    /**
     * Get school settings
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        $school = $user->school;
        
        if (!$school) {
            return response()->json([
                'success' => false,
                'message' => 'No school associated with this user',
            ], 404);
        }

        $settings = [
            'school' => [
                'id' => $school->id,
                'name' => $school->name,
                'email' => $school->email,
                'phone' => $school->phone,
                'address' => $school->address,
                'logo' => $school->logo,
                'subdomain' => $school->subdomain,
                'subscription_plan' => $school->subscription_plan,
                'license_valid_until' => $school->license_valid_until,
            ],
            'academic' => $school->meta['academic'] ?? [
                'current_session' => '2024/2025',
                'current_term' => 'First Term',
                'grading_system' => [
                    ['min' => 70, 'max' => 100, 'grade' => 'A', 'remark' => 'Excellent'],
                    ['min' => 60, 'max' => 69, 'grade' => 'B', 'remark' => 'Very Good'],
                    ['min' => 50, 'max' => 59, 'grade' => 'C', 'remark' => 'Good'],
                    ['min' => 40, 'max' => 49, 'grade' => 'D', 'remark' => 'Fair'],
                    ['min' => 0, 'max' => 39, 'grade' => 'E', 'remark' => 'Poor'],
                ],
                'assessment_weights' => [
                    'ca1' => 20,
                    'ca2' => 20,
                    'exam' => 60,
                ],
            ],
            'features' => $school->meta['features'] ?? [
                'attendance' => ['enabled' => true],
                'fees' => ['enabled' => true],
                'ai_comments' => ['enabled' => true],
                'duty_roster' => ['enabled' => true],
            ],
        ];

        return response()->json([
            'success' => true,
            'data' => $settings,
        ]);
    }

    /**
     * Update school settings
     */
    public function update(Request $request)
    {
        $user = $request->user();
        
        $school = $user->school;
        
        if (!$school) {
            return response()->json([
                'success' => false,
                'message' => 'No school associated with this user',
            ], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'logo' => 'nullable|string',
        ]);

        $school->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'School settings updated successfully',
            'data' => $school,
        ]);
    }

    /**
     * Update academic terms and sessions
     */
    public function updateTerms(Request $request)
    {
        $user = $request->user();
        
        $school = $user->school;
        
        if (!$school) {
            return response()->json([
                'success' => false,
                'message' => 'No school associated with this user',
            ], 404);
        }

        $validated = $request->validate([
            'current_session' => 'required|string',
            'current_term' => 'required|string|in:First Term,Second Term,Third Term',
            'grading_system' => 'nullable|array',
            'assessment_weights' => 'nullable|array',
            'assessment_weights.ca1' => 'nullable|numeric|min:0|max:100',
            'assessment_weights.ca2' => 'nullable|numeric|min:0|max:100',
            'assessment_weights.exam' => 'nullable|numeric|min:0|max:100',
        ]);

        // Update meta
        $meta = $school->meta ?? [];
        $meta['academic'] = array_merge($meta['academic'] ?? [], $validated);
        
        $school->update(['meta' => $meta]);

        return response()->json([
            'success' => true,
            'message' => 'Academic settings updated successfully',
            'data' => $meta['academic'],
        ]);
    }
}
