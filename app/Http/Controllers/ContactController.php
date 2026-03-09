<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactFormRequest;
use App\Mail\ContactConfirmation;
use App\Mail\ContactNotification;
use App\Models\ContactMessage;
use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function store(ContactFormRequest $request): RedirectResponse
    {
        $contactMessage = ContactMessage::create($request->validated());

        // Send confirmation email to the sender
        Mail::to($contactMessage->email)->send(new ContactConfirmation($contactMessage));

        // Send notification to admin emails from settings
        $adminEmails = $this->getAdminNotificationEmails();

        foreach ($adminEmails as $email) {
            Mail::to($email)->send(new ContactNotification($contactMessage));
        }

        return back()->with('success', 'Your message has been sent successfully. We\'ll get back to you within 24 hours.');
    }

    /**
     * @return array<string>
     */
    private function getAdminNotificationEmails(): array
    {
        $contactSettings = SiteSetting::getContactSettings();

        $emails = array_filter([
            $contactSettings['support_email'] ?? null,
            $contactSettings['sales_email'] ?? null,
        ]);

        return ! empty($emails) ? $emails : [config('mail.from.address')];
    }
}
