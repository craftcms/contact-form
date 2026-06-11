<?php

namespace CraftCms\ContactForm;

use CraftCms\Cms\Plugin\Plugin as BasePlugin;
use CraftCms\Cms\SystemMessage\Events\SystemMessagesResolving;
use CraftCms\Cms\SystemMessage\Models\SystemMessage;
use CraftCms\ContactForm\Http\Requests\SettingsRequest;

use Illuminate\Support\Facades\Event;
use function CraftCms\Cms\template;

class Plugin extends BasePlugin
{
    public string $schemaVersion = '1.0.0';

    public bool $hasCpSettings = true;

    public function bootPlugin(): void
    {
        Event::listen(function (SystemMessagesResolving $event) {
            $event->messages->push(new SystemMessage([
                'key' => 'contactform_submission',
                'heading' => 'When the contact form is submitted',
                'subject' => 'Contact form submission from {{ fromName }}!',
                'body' => <<<BODY
A contact form was just submitted on {{ systemName }}.

- **From:** {{ fromName }}
- **Email:** {{ fromEmail }}
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

        return template('contact-form/_settings.twig', [
            'settings' => $this->getSettings(),
            'overrides' => array_keys($overrides),
        ]);
    }
}
