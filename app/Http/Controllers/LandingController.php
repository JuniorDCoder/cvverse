<?php

namespace App\Http\Controllers;

use App\Models\PricingPlan;
use App\Models\SiteSetting;
use App\Models\Testimonial;
use App\Services\GeoLocationService;
use App\Services\PlanService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Laravel\Fortify\Features;

class LandingController extends Controller
{
    public function welcome()
    {
        return Inertia::render('Welcome', [
            'canRegister' => Features::enabled(Features::registration()),
            'testimonials' => Testimonial::active()->featured()->orderBy('sort_order')->get(),
            'stats' => SiteSetting::getStats(),
        ]);
    }

    public function about()
    {
        return Inertia::render('landing/About');
    }

    public function services()
    {
        return Inertia::render('landing/Services');
    }

    public function contact()
    {
        return Inertia::render('landing/Contact', [
            'contactData' => SiteSetting::getContactPageData(),
        ]);
    }

    public function pricing(Request $request, GeoLocationService $geoService, PlanService $planService)
    {
        $ip = $request->ip();
        $country = $geoService->getCountryCode($ip);

        // Default to Cameroon for localhost/dev to show XAF plans
        if ($country === null) {
            $country = 'CM';
        }

        // Default to USD if not Cameroon
        $currency = ($country === 'CM') ? 'XAF' : 'USD';

        // Fetch plans based on currency
        $plans = PricingPlan::where('is_active', true)
            ->where('currency', $currency)
            ->get();

        return Inertia::render('landing/Pricing', [
            'plans' => $plans,
            'country' => $country,
            'currency' => $currency,
            'currentPlan' => $planService->currentPlanData($request->user()),
        ]);
    }
}
