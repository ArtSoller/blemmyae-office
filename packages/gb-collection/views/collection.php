<?php

declare(strict_types=1);

use Timber\Timber;

if (!class_exists(Timber::class)) {
    return;
}

// Add subdirectories.
Timber::$dirname = ['templates', 'templates/macro', 'templates/layout'];

// Get context.
$context = Timber::context();

// Store field values.
$context['fields'] = get_fields();
// phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable
$context['block'] = $block ?? [];

// Render the block.
Timber::render('collection.twig', $context);
