<?php

namespace CraftCms\ContactForm\Data;

use CraftCms\Cms\Support\Arr;
use CraftCms\Cms\Support\Str;
use function CraftCms\Cms\t;

class Summary
{
    /**
     * Builds a string representation of the submission, hoisting any nested/complex fields into a list.
     */
    public function compile(array $message): string
    {
        // Was a nested `body` field submitted, explicitly?
        // (This has historically been used as the “default” message content, and will be handled later!)
        $body = Arr::pull($message, 'body');

        // Try one more time—the `SubmissionRequest` may have normalized it into a single-element, anonymous array, which we can return immediately:
        if ($body === null && array_is_list($message) && count($message) === 1) {
            return Arr::first($message);
        }

        $text = $this->packNestedLists($message);

        if ($body) {
            $body = preg_replace('/\R/u', "\n", $body);
            $text .= "\n\n".$body;
        }

        return $text;
    }

    private function packNestedLists(array $array, int $level = 0): string
    {
        // Assume lists (no keys) can be flattened into a single line:
        if (array_is_list($array)) {
            return join(', ', $array);
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
        return Str::start(join("\n", $items), "\n");
    }
}
