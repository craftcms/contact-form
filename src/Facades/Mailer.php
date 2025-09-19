<?php

namespace CraftCms\ContactForm\Facades;

use CraftCms\ContactForm\Models\Submission;
use Illuminate\Support\Facades\Facade;

/**
 * @method static bool send(Submission $submission, bool $runValidation = true)
 * @method static string getFromEmail($from)
 * @method static string compileFromName(string $fromName = null)
 * @method static string compileSubject(string $subject = null)
 * @method static string compileTextBody(Submission $submission)
 * @method static string compileHtmlBody(string $textBody)
 *
 * @see \CraftCms\ContactForm\Mailer
 */
class Mailer extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \CraftCms\ContactForm\Mailer::class;
    }
}
