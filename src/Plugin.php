<?php

declare(strict_types=1);

namespace CraftCms\ContactForm;

use CraftCms\Cms\Cp\SelectOptions;
use CraftCms\Cms\Plugin\Plugin as BasePlugin;
use CraftCms\Cms\Support\Flash;
use CraftCms\Cms\SystemMessage\Models\SystemMessage;
use CraftCms\Cms\Ui\Controls\Control;
use CraftCms\Cms\Ui\Controls\Lightswitch;
use CraftCms\Cms\Ui\Controls\Text;
use CraftCms\Cms\Ui\Enums\ControlMode;
use CraftCms\Cms\Ui\Nodes\Field;
use CraftCms\Cms\Ui\Ui;
use CraftCms\Cms\Ui\UiContext;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

use function CraftCms\Cms\t;

/**
 * @method Settings getSettings()
 */
class Plugin extends BasePlugin
{
    public string $schemaVersion = '1.0.0';

    public bool $hasCpSettings = true;

    public function boot(): void
    {
        $this->app->singleton(Settings::class, fn () => $this->getSettings());

        RateLimiter::for('contact-form', function (Request $request) {
            $limit = app(Settings::class)->rateLimit;

            // `null` has special significance, but isn't handled by other limit methods:
            if ($limit === null) {
                return Limit::none();
            }

            return Limit::perMinute($limit)
                ->by($request->getClientIp())
                ->response(function (Request $request, array $headers) {
                    $message = t('You’ve sent too many messages. Please try again in a minute.', category: 'contact-form');

                    if ($request->expectsJson()) {
                        return response()->json(['message' => $message], 429, $headers);
                    }

                    // Back to the form, like a validation failure:
                    Flash::error($message);

                    return back()->withInput($request->except('attachment'))->withHeaders($headers);
                });
        });
    }

    protected function getSystemMessages(): array
    {
        return [
            'contactform_submission' => fn () => new SystemMessage([
                'key' => 'contactform_submission',
                'heading' => 'When the contact form is submitted',
                // This template is comparable to how the legacy mailer compiled subjects:
                'subject' => '{{ [settings.prependSubject, subject ?? null]|filter|join(" - ") }}',
                'body' => <<<'BODY'
A contact form was just submitted on {{ systemName }}.

{% if fromName %}- **{{ 'From'|t }}:** {{ fromName }}
{% endif %}- **{{ 'Email'|t }}:** {{ fromEmail }}

{{ summary }}
BODY,
                // `fromName` and `fromEmail` are listed by core, as the sender:
                'variables' => [
                    'subject' => t('Subject', category: 'contact-form'),
                    'message' => t('Message', category: 'contact-form'),
                    'summary' => t('Submission summary', category: 'contact-form'),
                ],
            ]),
        ];
    }

    protected static function createSettings(): Settings
    {
        return new Settings;
    }

    public function settingsUi(UiContext $context = new UiContext): Ui
    {
        // Settings defined by the config file can’t be changed here:
        $overrides = array_keys(config("craft.$this->handle", []));

        $field = fn (string $label, Control $control, ?string $instructions = null) => Field::make($label, $control)
            ->instructions($instructions)
            ->when(in_array($control->path(), $overrides, true), function (Field $field) use ($control) {
                $control->mode(ControlMode::ReadOnly);
                $field->warning(t('This is being overridden by the {setting} config setting in your {file} config file.', [
                    'setting' => "`{$control->path()}`",
                    'file' => '`config/craft/contact-form.php`',
                ], 'contact-form'));
            });

        return Ui::make([
            $field(
                t('To Email', category: 'contact-form'),
                Text::make('toEmail')->textExpanderTriggers(SelectOptions::getEnvTextExpanderTriggers()),
                t('The email address(es) that the contact form will send to. Separate multiple email addresses with commas.', category: 'contact-form'),
            )->required(),
            $field(
                t('Sender Text', category: 'contact-form'),
                Text::make('prependSender'),
                t('Text that will be prepended to the email’s From Name to inform who the Contact Form actually was sent by.', category: 'contact-form'),
            ),
            $field(
                t('Subject Text', category: 'contact-form'),
                Text::make('prependSubject'),
                t('Text that will be prepended to the email’s Subject to flag that it comes from the Contact Form.', category: 'contact-form'),
            ),
            $field(
                t('Allow attachments?', category: 'contact-form'),
                Lightswitch::make('allowAttachments'),
            ),
            $field(
                t('Success Flash Message', category: 'contact-form'),
                Text::make('successFlashMessage'),
                t('The flash message displayed after successfully sending a message.', category: 'contact-form'),
            )->required(),
        ]);
    }
}
