<?php

namespace CraftCms\ContactForm;

use CraftCms\Cms\Plugin\Plugin as BasePlugin;
use CraftCms\Cms\SystemMessage\Events\SystemMessagesResolving;
use CraftCms\Cms\SystemMessage\Models\SystemMessage;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use function CraftCms\Cms\template;

class Plugin extends BasePlugin
{
    public string $schemaVersion = '1.0.0';

    public bool $hasCpSettings = true;

    public function bootPlugin(): void
    {
        RateLimiter::for('contact-form', function (Request $request) {
            $limit = $this->getSettings()->rateLimit;

            // `null` has special significance, but isn't handled by other limit methods:
            if ($limit === null) {
                return Limit::none();
            }

            return Limit::perMinute($limit)->by($request->getClientIp());
        });

        Event::listen(function (SystemMessagesResolving $event) {
            $event->messages->push(new SystemMessage([
                'key' => 'contactform_submission',
                'heading' => 'When the contact form is submitted',
                // This template is comparable to how the legacy mailer compiled subjects:
                'subject' => '{{ [settings.prependSubject, subject ?? null]|filter|join(" - ") }}',
                'body' => <<<BODY
A contact form was just submitted on {{ systemName }}.

- **{{ 'From'|t }}:** {{ fromName }}
- **{{ 'Email'|t }}:** {{ fromEmail }}
{{ summary }}
BODY,
            ]));
        });
    }

    protected function createSettingsModel(): ?Settings
    {
        return new Settings;
    }

    protected function settingsHtml(): ?string
    {
        // Get the settings that are being defined by the config file
        $overrides = config('craft.'.strtolower($this->handle), []);

        return template('contact-form/_settings', [
            'settings' => $this->getSettings(),
            'overrides' => array_keys($overrides),
        ]);
    }
}
