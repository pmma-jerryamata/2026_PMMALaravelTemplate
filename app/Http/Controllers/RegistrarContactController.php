<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class RegistrarContactController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:254'],
            'message' => ['required', 'string', 'max:3000'],
        ]);

        if (config('mail.default') !== 'smtp') {
            return to_route('contact')->withInput($data)->withErrors([
                'contact' => 'The form is temporarily unavailable. Please email or call the Registrar.',
            ]);
        }

        $body = "Name: {$data['name']}\nEmail: {$data['email']}\n\n{$data['message']}";

        try {
            $sent = Mail::raw($body, function (Message $message) use ($data): void {
                $message->to(config('registrar.contact.email'))
                    ->replyTo($data['email'])
                    ->subject('Registrar portal inquiry');
            });
        } catch (TransportExceptionInterface $exception) {
            Log::warning('Registrar contact mail delivery failed.', ['exception_type' => $exception::class]);
            $sent = null;
        }

        if ($sent === null) {
            return to_route('contact')->withInput($data)->withErrors([
                'contact' => 'We could not send your message. Try again or contact the Registrar directly.',
            ]);
        }

        return to_route('contact')->with('status', 'Your message was sent to the Office of the Registrar.');
    }
}
