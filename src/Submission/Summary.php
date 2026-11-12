<?php

declare(strict_types=1);

namespace CraftCms\ContactForm\Submission;

use CraftCms\Cms\Support\Arr;
use CraftCms\Cms\Support\Str;

use function CraftCms\Cms\t;

class Summary
{
    /**
     * Builds a Markdown representation of the submission, hoisting any nested/complex fields into a list.
     */
    public function compile(string|array $message): string
    {
        // A plain-text message is just a body:
        if (is_string($message)) {
            $message = ['body' => $message];
        }

        // Was a nested `body` field submitted, explicitly? We don’t want that in the bulleted list (it will get appended, later):
        $body = Arr::pull($message, 'body');

        // The template already separates the summary from what comes before it:
        $summary = ltrim($this->packNestedLists($message), "\n");

        if ($body) {
            $body = preg_replace('/\R/u', "\n", $body);
            $summary .= ($summary === '' ? '' : "\n\n").$body;
        }

        return $summary;
    }

    private function packNestedLists(array $array, int $level = 0): string
    {
        // Assume “lists” (non-associative arrays) can be flattened into a single line:
        if (array_is_list($array)) {
            return implode(', ', $array);
        }

        // If we’ve gotten this far, the next value will be “complex”, so the returned value should begin with a new line:
        $items = [];

        foreach ($array as $key => $value) {
            // Handle arrays recursively:
            if (is_array($value)) {
                $value = $this->packNestedLists($value, $level + 1);
            }

            $items[] = sprintf(
                '%s- **%s:** %s',
                str_repeat('  ', $level),
                t($key, category: 'site'),
                $value,
            );
        }

        // Concatenate, ensuring the first item starts on a new line:
        return Str::start(implode("\n", $items), "\n");
    }
}
