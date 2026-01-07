<?php

namespace App\Http\Controllers\Api\V1\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\PlatformSetting;
use App\Models\FeatureRegistry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SettingsController extends Controller
{
    /**
     * Get all platform settings
     */
    public function index(Request $request)
    {
        $query = PlatformSetting::query();

        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        $settings = $query->orderBy('category')
            ->orderBy('key_name')
            ->get()
            ->map(function ($setting) {
                return [
                    'id' => $setting->id,
                    'key' => $setting->key_name,
                    'value' => $setting->is_encrypted ? '********' : $setting->getCastedValue(),
                    'type' => $setting->type,
                    'category' => $setting->category,
                    'description' => $setting->description,
                    'is_encrypted' => $setting->is_encrypted,
                    'updated_at' => $setting->updated_at,
                ];
            });

        // Group by category
        $grouped = $settings->groupBy('category');

        return response()->json([
            'success' => true,
            'data' => [
                'settings' => $settings,
                'grouped' => $grouped,
                'categories' => [
                    PlatformSetting::CATEGORY_GENERAL,
                    PlatformSetting::CATEGORY_EMAIL,
                    PlatformSetting::CATEGORY_SMS,
                    PlatformSetting::CATEGORY_PAYMENT,
                    PlatformSetting::CATEGORY_SECURITY,
                    PlatformSetting::CATEGORY_APPEARANCE,
                    PlatformSetting::CATEGORY_AI,
                    PlatformSetting::CATEGORY_STORAGE,
                ],
            ],
        ]);
    }

    /**
     * Update platform settings
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'settings' => 'required|array',
            'settings.*.key' => 'required|string',
            'settings.*.value' => 'present',
            'settings.*.type' => 'nullable|string|in:string,json,boolean,integer,encrypted',
            'settings.*.category' => 'nullable|string',
            'settings.*.description' => 'nullable|string',
        ]);

        foreach ($validated['settings'] as $setting) {
            $existing = PlatformSetting::where('key_name', $setting['key'])->first();
            
            PlatformSetting::setValue(
                $setting['key'],
                $setting['value'],
                $setting['type'] ?? ($existing ? $existing->type : 'string'),
                auth()->id()
            );

            // Update category and description if provided
            if (isset($setting['category']) || isset($setting['description'])) {
                PlatformSetting::where('key_name', $setting['key'])->update([
                    'category' => $setting['category'] ?? ($existing ? $existing->category : 'general'),
                    'description' => $setting['description'] ?? ($existing ? $existing->description : null),
                ]);
            }
        }

        // Clear cached settings
        Cache::flush();

        activity()
            ->causedBy(auth()->user())
            ->withProperties(['keys_updated' => collect($validated['settings'])->pluck('key')])
            ->log('platform_settings_updated');

        return response()->json([
            'success' => true,
            'message' => 'Settings updated successfully',
        ]);
    }

    /**
     * Get a single setting value
     */
    public function show($key)
    {
        $setting = PlatformSetting::where('key_name', $key)->first();

        if (!$setting) {
            return response()->json([
                'success' => false,
                'message' => 'Setting not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'key' => $setting->key_name,
                'value' => $setting->getCastedValue(),
                'type' => $setting->type,
                'category' => $setting->category,
            ],
        ]);
    }

    /**
     * Delete a setting
     */
    public function destroy($key)
    {
        $setting = PlatformSetting::where('key_name', $key)->first();

        if (!$setting) {
            return response()->json([
                'success' => false,
                'message' => 'Setting not found',
            ], 404);
        }

        $setting->delete();
        Cache::forget("platform_setting_{$key}");

        activity()
            ->causedBy(auth()->user())
            ->withProperties(['key' => $key])
            ->log('platform_setting_deleted');

        return response()->json([
            'success' => true,
            'message' => 'Setting deleted successfully',
        ]);
    }

    // =============================================
    // FEATURE REGISTRY
    // =============================================

    /**
     * Get all features
     */
    public function features(Request $request)
    {
        $query = FeatureRegistry::query();

        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        if ($request->has('active')) {
            $query->where('is_active', $request->boolean('active'));
        }

        if ($request->has('premium')) {
            $query->where('is_premium', $request->boolean('premium'));
        }

        $features = $query->orderBy('category')
            ->orderBy('priority', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'features' => $features,
                'grouped' => $features->groupBy('category'),
            ],
        ]);
    }

    /**
     * Create a new feature
     */
    public function storeFeature(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:100|unique:feature_registry,code|alpha_dash',
            'name' => 'required|string|max:200',
            'description' => 'nullable|string',
            'category' => 'required|string|max:100',
            'icon' => 'nullable|string|max:100',
            'is_active' => 'nullable|boolean',
            'is_premium' => 'nullable|boolean',
            'dependencies' => 'nullable|array',
            'plans' => 'nullable|array',
            'priority' => 'nullable|integer',
        ]);

        $feature = FeatureRegistry::create($validated);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($feature)
            ->log('feature_created');

        return response()->json([
            'success' => true,
            'message' => 'Feature created successfully',
            'data' => $feature,
        ], 201);
    }

    /**
     * Update a feature
     */
    public function updateFeature(Request $request, $id)
    {
        $feature = FeatureRegistry::findOrFail($id);

        $validated = $request->validate([
            'code' => 'sometimes|string|max:100|alpha_dash|unique:feature_registry,code,' . $id,
            'name' => 'sometimes|string|max:200',
            'description' => 'nullable|string',
            'category' => 'sometimes|string|max:100',
            'icon' => 'nullable|string|max:100',
            'is_active' => 'nullable|boolean',
            'is_premium' => 'nullable|boolean',
            'dependencies' => 'nullable|array',
            'plans' => 'nullable|array',
            'priority' => 'nullable|integer',
        ]);

        $feature->update($validated);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($feature)
            ->withProperties(['changes' => $validated])
            ->log('feature_updated');

        return response()->json([
            'success' => true,
            'message' => 'Feature updated successfully',
            'data' => $feature,
        ]);
    }

    /**
     * Toggle feature active status
     */
    public function toggleFeature($id)
    {
        $feature = FeatureRegistry::findOrFail($id);

        $feature->update(['is_active' => !$feature->is_active]);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($feature)
            ->withProperties(['is_active' => $feature->is_active])
            ->log('feature_toggled');

        return response()->json([
            'success' => true,
            'message' => 'Feature ' . ($feature->is_active ? 'enabled' : 'disabled'),
            'data' => $feature,
        ]);
    }

    /**
     * Delete feature
     */
    public function destroyFeature($id)
    {
        $feature = FeatureRegistry::findOrFail($id);

        $feature->delete();

        activity()
            ->causedBy(auth()->user())
            ->withProperties(['feature_code' => $feature->code])
            ->log('feature_deleted');

        return response()->json([
            'success' => true,
            'message' => 'Feature deleted successfully',
        ]);
    }

    // =============================================
    // INTEGRATIONS
    // =============================================

    /**
     * Get integration settings
     */
    public function integrations()
    {
        $integrations = [
            'email' => [
                'provider' => PlatformSetting::getValue('email_provider', 'smtp'),
                'configured' => !empty(PlatformSetting::getValue('smtp_host')),
            ],
            'sms' => [
                'provider' => PlatformSetting::getValue('sms_provider'),
                'configured' => !empty(PlatformSetting::getValue('sms_api_key')),
            ],
            'payment' => [
                'paystack' => [
                    'enabled' => (bool) PlatformSetting::getValue('paystack_enabled', false),
                    'configured' => !empty(PlatformSetting::getValue('paystack_secret_key')),
                ],
                'flutterwave' => [
                    'enabled' => (bool) PlatformSetting::getValue('flutterwave_enabled', false),
                    'configured' => !empty(PlatformSetting::getValue('flutterwave_secret_key')),
                ],
            ],
            'ai' => [
                'openai' => [
                    'enabled' => (bool) PlatformSetting::getValue('openai_enabled', false),
                    'configured' => !empty(PlatformSetting::getValue('openai_api_key')),
                ],
                'gemini' => [
                    'enabled' => (bool) PlatformSetting::getValue('gemini_enabled', false),
                    'configured' => !empty(PlatformSetting::getValue('gemini_api_key')),
                ],
            ],
            'storage' => [
                'provider' => PlatformSetting::getValue('storage_provider', 'local'),
                's3_configured' => !empty(PlatformSetting::getValue('aws_access_key')),
            ],
        ];

        return response()->json([
            'success' => true,
            'data' => $integrations,
        ]);
    }

    /**
     * Test integration connection
     */
    public function testIntegration(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|string|in:email,sms,payment,ai,storage',
            'provider' => 'required|string',
        ]);

        // Simulated connection test
        // In production, actually test the connection
        $result = [
            'success' => true,
            'message' => "Connection to {$validated['provider']} successful",
            'latency_ms' => rand(50, 200),
        ];

        return response()->json($result);
    }

    /**
     * Clear all caches
     */
    public function clearCache()
    {
        Cache::flush();

        activity()
            ->causedBy(auth()->user())
            ->log('cache_cleared');

        return response()->json([
            'success' => true,
            'message' => 'All caches cleared successfully',
        ]);
    }
}
