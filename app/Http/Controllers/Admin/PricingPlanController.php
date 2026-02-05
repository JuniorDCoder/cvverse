<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PricingPlan;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PricingPlanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('admin/Pricing/Index', [
            'pricingPlans' => PricingPlan::latest()->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('admin/Pricing/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:pricing_plans,slug',
            'price' => 'required|numeric|min:0',
            'currency' => 'required|string|max:3',
            'interval' => 'required|string|in:monthly,yearly,one_time',
            'features' => 'required|array',
            'features.*' => 'string',
            'is_active' => 'boolean',
        ]);

        PricingPlan::create($validated);

        return redirect()->route('admin.pricing-plans.index')->with('success', 'Pricing plan created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(PricingPlan $pricingPlan)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PricingPlan $pricingPlan)
    {
        return Inertia::render('admin/Pricing/Edit', [
            'pricingPlan' => $pricingPlan,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PricingPlan $pricingPlan)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:pricing_plans,slug,'.$pricingPlan->id,
            'price' => 'required|numeric|min:0',
            'currency' => 'required|string|max:3',
            'interval' => 'required|string|in:monthly,yearly,one_time',
            'features' => 'required|array',
            'features.*' => 'string',
            'is_active' => 'boolean',
        ]);

        $pricingPlan->update($validated);

        return redirect()->route('admin.pricing-plans.index')->with('success', 'Pricing plan updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PricingPlan $pricingPlan)
    {
        $pricingPlan->delete();

        return redirect()->route('admin.pricing-plans.index')->with('success', 'Pricing plan deleted successfully.');
    }
}
