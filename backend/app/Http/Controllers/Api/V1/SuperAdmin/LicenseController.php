<?php

namespace App\Http\Controllers\Api\V1\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\LicensePlan;
use App\Models\SchoolSubscription;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LicenseController extends Controller
{
    /**
     * List all license plans
     */
    public function plans(Request $request)
    {
        $query = LicensePlan::withCount('subscriptions');

        if ($request->has('active')) {
            $query->where('is_active', $request->boolean('active'));
        }

        $plans = $query->orderBy('priority', 'desc')
            ->orderBy('price')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $plans,
        ]);
    }

    /**
     * Create a new license plan
     */
    public function storePlan(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:50|unique:license_plans,code|alpha_dash',
            'description' => 'nullable|string',
            'duration_months' => 'required|integer|min:1|max:120',
            'user_limit' => 'nullable|integer|min:1',
            'student_limit' => 'nullable|integer|min:1',
            'storage_gb' => 'nullable|numeric|min:0',
            'price' => 'required|numeric|min:0',
            'currency' => 'nullable|string|size:3',
            'features' => 'nullable|array',
            'trial_days' => 'nullable|integer|min:0',
            'priority' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
            'is_public' => 'nullable|boolean',
        ]);

        $plan = LicensePlan::create($validated);

        // Log activity
        activity()
            ->causedBy(auth()->user())
            ->performedOn($plan)
            ->withProperties(['plan_code' => $plan->code])
            ->log('license_plan_created');

        return response()->json([
            'success' => true,
            'message' => 'License plan created successfully',
            'data' => $plan,
        ], 201);
    }

    /**
     * Get license plan details
     */
    public function showPlan($id)
    {
        $plan = LicensePlan::withCount('subscriptions')
            ->with(['subscriptions' => function ($q) {
                $q->with('school:id,name')
                  ->latest()
                  ->limit(10);
            }])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $plan,
        ]);
    }

    /**
     * Update license plan
     */
    public function updatePlan(Request $request, $id)
    {
        $plan = LicensePlan::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:100',
            'code' => ['sometimes', 'string', 'max:50', 'alpha_dash', Rule::unique('license_plans')->ignore($id)],
            'description' => 'nullable|string',
            'duration_months' => 'sometimes|integer|min:1|max:120',
            'user_limit' => 'nullable|integer|min:1',
            'student_limit' => 'nullable|integer|min:1',
            'storage_gb' => 'nullable|numeric|min:0',
            'price' => 'sometimes|numeric|min:0',
            'currency' => 'nullable|string|size:3',
            'features' => 'nullable|array',
            'trial_days' => 'nullable|integer|min:0',
            'priority' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
            'is_public' => 'nullable|boolean',
        ]);

        $plan->update($validated);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($plan)
            ->withProperties(['changes' => $validated])
            ->log('license_plan_updated');

        return response()->json([
            'success' => true,
            'message' => 'License plan updated successfully',
            'data' => $plan,
        ]);
    }

    /**
     * Delete license plan
     */
    public function destroyPlan($id)
    {
        $plan = LicensePlan::withCount('subscriptions')->findOrFail($id);

        if ($plan->subscriptions_count > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete plan with active subscriptions. Deactivate the plan instead.',
            ], 400);
        }

        $plan->delete();

        activity()
            ->causedBy(auth()->user())
            ->withProperties(['plan_code' => $plan->code])
            ->log('license_plan_deleted');

        return response()->json([
            'success' => true,
            'message' => 'License plan deleted successfully',
        ]);
    }

    // =============================================
    // SUBSCRIPTION MANAGEMENT
    // =============================================

    /**
     * List all subscriptions
     */
    public function subscriptions(Request $request)
    {
        $query = SchoolSubscription::with(['school:id,name,subdomain', 'licensePlan:id,name,code']);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by plan
        if ($request->has('plan_id')) {
            $query->where('license_plan_id', $request->plan_id);
        }

        // Filter expiring soon
        if ($request->has('expiring')) {
            $days = (int) $request->expiring;
            $query->expiringSoon($days);
        }

        // Filter expired
        if ($request->boolean('expired')) {
            $query->expired();
        }

        // Search by school name
        if ($request->has('search')) {
            $search = $request->search;
            $query->whereHas('school', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('subdomain', 'like', "%{$search}%");
            });
        }

        $subscriptions = $query->orderBy('end_date', 'asc')
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $subscriptions,
        ]);
    }

    /**
     * Create subscription for a school
     */
    public function storeSubscription(Request $request)
    {
        $validated = $request->validate([
            'school_id' => 'required|exists:schools,id',
            'license_plan_id' => 'required|exists:license_plans,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'auto_renew' => 'nullable|boolean',
            'payment_reference' => 'nullable|string',
            'payment_method' => 'nullable|string',
            'amount_paid' => 'nullable|numeric|min:0',
        ]);

        $plan = LicensePlan::findOrFail($validated['license_plan_id']);
        
        $startDate = isset($validated['start_date']) 
            ? \Carbon\Carbon::parse($validated['start_date']) 
            : now();
            
        $endDate = isset($validated['end_date']) 
            ? \Carbon\Carbon::parse($validated['end_date']) 
            : $startDate->copy()->addMonths($plan->duration_months);

        $subscription = SchoolSubscription::create([
            'school_id' => $validated['school_id'],
            'license_plan_id' => $validated['license_plan_id'],
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => 'active',
            'auto_renew' => $validated['auto_renew'] ?? false,
            'payment_reference' => $validated['payment_reference'] ?? null,
            'payment_method' => $validated['payment_method'] ?? null,
            'amount_paid' => $validated['amount_paid'] ?? $plan->price,
            'created_by' => auth()->id(),
        ]);

        // Update school subscription plan
        School::where('id', $validated['school_id'])->update([
            'subscription_plan' => $plan->code,
            'license_valid_until' => $endDate,
        ]);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($subscription)
            ->withProperties(['school_id' => $validated['school_id'], 'plan' => $plan->code])
            ->log('subscription_created');

        return response()->json([
            'success' => true,
            'message' => 'Subscription created successfully',
            'data' => $subscription->load(['school', 'licensePlan']),
        ], 201);
    }

    /**
     * Renew subscription
     */
    public function renewSubscription(Request $request, $id)
    {
        $subscription = SchoolSubscription::with('licensePlan')->findOrFail($id);

        $validated = $request->validate([
            'months' => 'nullable|integer|min:1|max:120',
            'payment_reference' => 'nullable|string',
            'amount_paid' => 'nullable|numeric|min:0',
        ]);

        $months = $validated['months'] ?? $subscription->licensePlan->duration_months;
        
        // Extend from current end date if still active, otherwise from today
        $startDate = $subscription->end_date->isFuture() 
            ? $subscription->end_date 
            : now();
        
        $newEndDate = $startDate->copy()->addMonths($months);

        $subscription->update([
            'start_date' => $subscription->end_date->isFuture() ? $subscription->start_date : now(),
            'end_date' => $newEndDate,
            'status' => 'active',
            'payment_reference' => $validated['payment_reference'] ?? $subscription->payment_reference,
            'amount_paid' => $validated['amount_paid'] ?? $subscription->licensePlan->price,
            'meta' => array_merge($subscription->meta ?? [], [
                'renewed_at' => now()->toISOString(),
                'renewed_by' => auth()->id(),
            ]),
        ]);

        // Update school
        $subscription->school->update([
            'license_valid_until' => $newEndDate,
            'active' => true,
        ]);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($subscription)
            ->withProperties(['new_end_date' => $newEndDate])
            ->log('subscription_renewed');

        return response()->json([
            'success' => true,
            'message' => 'Subscription renewed successfully',
            'data' => $subscription->fresh(['school', 'licensePlan']),
        ]);
    }

    /**
     * Cancel subscription
     */
    public function cancelSubscription(Request $request, $id)
    {
        $subscription = SchoolSubscription::findOrFail($id);

        $validated = $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $subscription->update([
            'status' => 'cancelled',
            'meta' => array_merge($subscription->meta ?? [], [
                'cancelled_at' => now()->toISOString(),
                'cancelled_by' => auth()->id(),
                'cancellation_reason' => $validated['reason'] ?? null,
            ]),
        ]);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($subscription)
            ->withProperties(['reason' => $validated['reason'] ?? 'No reason provided'])
            ->log('subscription_cancelled');

        return response()->json([
            'success' => true,
            'message' => 'Subscription cancelled successfully',
        ]);
    }

    /**
     * Upgrade/Downgrade subscription plan
     */
    public function changePlan(Request $request, $id)
    {
        $subscription = SchoolSubscription::with('licensePlan')->findOrFail($id);

        $validated = $request->validate([
            'license_plan_id' => 'required|exists:license_plans,id',
            'prorate' => 'nullable|boolean',
        ]);

        $oldPlan = $subscription->licensePlan;
        $newPlan = LicensePlan::findOrFail($validated['license_plan_id']);

        $subscription->update([
            'license_plan_id' => $newPlan->id,
            'meta' => array_merge($subscription->meta ?? [], [
                'plan_changed_at' => now()->toISOString(),
                'changed_by' => auth()->id(),
                'previous_plan' => $oldPlan->code,
            ]),
        ]);

        // Update school
        $subscription->school->update([
            'subscription_plan' => $newPlan->code,
        ]);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($subscription)
            ->withProperties([
                'old_plan' => $oldPlan->code,
                'new_plan' => $newPlan->code,
            ])
            ->log('subscription_plan_changed');

        return response()->json([
            'success' => true,
            'message' => 'Subscription plan changed successfully',
            'data' => $subscription->fresh(['school', 'licensePlan']),
        ]);
    }

    /**
     * Get subscription statistics
     */
    public function subscriptionStats()
    {
        $stats = [
            'total' => SchoolSubscription::count(),
            'active' => SchoolSubscription::active()->count(),
            'expiring_30_days' => SchoolSubscription::expiringSoon(30)->count(),
            'expiring_7_days' => SchoolSubscription::expiringSoon(7)->count(),
            'expired' => SchoolSubscription::expired()->count(),
            'cancelled' => SchoolSubscription::where('status', 'cancelled')->count(),
            'by_plan' => SchoolSubscription::selectRaw('license_plan_id, count(*) as count')
                ->where('status', 'active')
                ->groupBy('license_plan_id')
                ->with('licensePlan:id,name,code')
                ->get(),
            'revenue_this_month' => SchoolSubscription::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('amount_paid'),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}
