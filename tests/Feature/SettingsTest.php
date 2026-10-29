<?php

use CraftCms\Cms\Http\Controllers\PluginsController;
use CraftCms\Cms\User\Elements\User;
use Inertia\Testing\AssertableInertia;

it('renders the settings page, locking settings overridden by the config file', function () {
    $this->actingAs(User::findOne())
        ->get(action([PluginsController::class, 'editSettings'], ['contact-form']))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('ui.values.settings.toEmail', 'inbox@example.com')
            ->where('ui.nodes.0.control.path', ['settings', 'toEmail'])
            ->where('ui.nodes.0.control.mode', 'readOnly')
            ->where('ui.nodes.0.props.warning', 'This is being overridden by the `toEmail` config setting in your `config/craft/contact-form.php` config file.')
            ->where('ui.nodes.3.control.path', ['settings', 'allowAttachments'])
            ->where('ui.nodes.3.control.mode', 'editable')
            ->missing('ui.nodes.3.props.warning')
        );
});
