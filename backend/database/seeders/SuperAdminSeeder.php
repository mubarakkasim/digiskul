<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LicensePlan;
use App\Models\FeatureRegistry;
use App\Models\PlatformSetting;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        // ========================================
        // LICENSE PLANS
        // ========================================
        $plans = [
            [
                'name' => 'Basic',
                'code' => 'basic',
                'description' => 'Essential features for small schools',
                'duration_months' => 12,
                'user_limit' => 20,
                'student_limit' => 200,
                'storage_gb' => 5,
                'price' => 50000,
                'currency' => 'NGN',
                'features' => ['attendance', 'students', 'classes', 'subjects'],
                'trial_days' => 14,
                'priority' => 1,
                'is_active' => true,
                'is_public' => true,
            ],
            [
                'name' => 'Standard',
                'code' => 'standard',
                'description' => 'Full features for growing schools',
                'duration_months' => 12,
                'user_limit' => 50,
                'student_limit' => 500,
                'storage_gb' => 20,
                'price' => 150000,
                'currency' => 'NGN',
                'features' => ['attendance', 'students', 'classes', 'subjects', 'fees', 'timetable', 'duty_roster', 'report_cards'],
                'trial_days' => 14,
                'priority' => 2,
                'is_active' => true,
                'is_public' => true,
            ],
            [
                'name' => 'Premium',
                'code' => 'premium',
                'description' => 'Advanced features with AI tools',
                'duration_months' => 12,
                'user_limit' => 100,
                'student_limit' => 1000,
                'storage_gb' => 50,
                'price' => 300000,
                'currency' => 'NGN',
                'features' => ['attendance', 'students', 'classes', 'subjects', 'fees', 'timetable', 'duty_roster', 'report_cards', 'ai_comments', 'library', 'cbt'],
                'trial_days' => 30,
                'priority' => 3,
                'is_active' => true,
                'is_public' => true,
            ],
            [
                'name' => 'Enterprise',
                'code' => 'enterprise',
                'description' => 'Unlimited features for large institutions',
                'duration_months' => 12,
                'user_limit' => null,
                'student_limit' => null,
                'storage_gb' => 200,
                'price' => 500000,
                'currency' => 'NGN',
                'features' => ['all'],
                'trial_days' => 30,
                'priority' => 4,
                'is_active' => true,
                'is_public' => false,
            ],
        ];

        foreach ($plans as $plan) {
            LicensePlan::updateOrCreate(['code' => $plan['code']], $plan);
        }

        // ========================================
        // FEATURE REGISTRY
        // ========================================
        $features = [
            ['code' => 'attendance', 'name' => 'Attendance Management', 'category' => 'academic', 'icon' => '📋', 'is_premium' => false],
            ['code' => 'students', 'name' => 'Student Management', 'category' => 'academic', 'icon' => '🎓', 'is_premium' => false],
            ['code' => 'classes', 'name' => 'Class Management', 'category' => 'academic', 'icon' => '🏫', 'is_premium' => false],
            ['code' => 'subjects', 'name' => 'Subject Management', 'category' => 'academic', 'icon' => '📚', 'is_premium' => false],
            ['code' => 'timetable', 'name' => 'Timetable', 'category' => 'academic', 'icon' => '📅', 'is_premium' => false],
            ['code' => 'duty_roster', 'name' => 'Duty Roster', 'category' => 'academic', 'icon' => '📝', 'is_premium' => false],
            ['code' => 'report_cards', 'name' => 'Report Cards', 'category' => 'reporting', 'icon' => '📊', 'is_premium' => false],
            ['code' => 'fees', 'name' => 'Fee Management', 'category' => 'financial', 'icon' => '💰', 'is_premium' => false],
            ['code' => 'ai_comments', 'name' => 'AI-Generated Comments', 'category' => 'ai', 'icon' => '🤖', 'is_premium' => true],
            ['code' => 'library', 'name' => 'Library Management', 'category' => 'academic', 'icon' => '📖', 'is_premium' => true],
            ['code' => 'cbt', 'name' => 'Computer-Based Testing', 'category' => 'academic', 'icon' => '💻', 'is_premium' => true],
            ['code' => 'sms', 'name' => 'SMS Notifications', 'category' => 'communication', 'icon' => '📱', 'is_premium' => true],
            ['code' => 'parent_portal', 'name' => 'Parent Portal', 'category' => 'communication', 'icon' => '👨‍👩‍👧', 'is_premium' => false],
            ['code' => 'hostel', 'name' => 'Hostel Management', 'category' => 'academic', 'icon' => '🏠', 'is_premium' => true],
        ];

        foreach ($features as $feature) {
            FeatureRegistry::updateOrCreate(
                ['code' => $feature['code']],
                array_merge($feature, ['is_active' => true, 'priority' => 0])
            );
        }

        // ========================================
        // PLATFORM SETTINGS
        // ========================================
        $settings = [
            ['key_name' => 'platform_name', 'value' => 'DIGISKUL', 'type' => 'string', 'category' => 'general'],
            ['key_name' => 'platform_tagline', 'value' => 'Smart School Management', 'type' => 'string', 'category' => 'general'],
            ['key_name' => 'support_email', 'value' => 'support@digiskul.app', 'type' => 'string', 'category' => 'general'],
            ['key_name' => 'max_login_attempts', 'value' => '5', 'type' => 'integer', 'category' => 'security'],
            ['key_name' => 'session_timeout_minutes', 'value' => '60', 'type' => 'integer', 'category' => 'security'],
            ['key_name' => 'require_mfa_super_admin', 'value' => '0', 'type' => 'boolean', 'category' => 'security'],
            ['key_name' => 'default_timezone', 'value' => 'Africa/Lagos', 'type' => 'string', 'category' => 'general'],
            ['key_name' => 'default_currency', 'value' => 'NGN', 'type' => 'string', 'category' => 'general'],
        ];

        foreach ($settings as $setting) {
            PlatformSetting::updateOrCreate(
                ['key_name' => $setting['key_name']],
                $setting
            );
        }

        $this->command->info('Super Admin data seeded successfully!');
    }
}
