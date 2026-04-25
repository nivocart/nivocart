<?php
declare(strict_types=1);
use PhpCsFixer\Config;
use PhpCsFixer\Finder;

return (new Config())
    ->setRiskyAllowed(false)
    ->setIndent("\t")          // EXCLUSION 3: tabs throughout
    ->setRules([
        // ── Base: PSR-12 ────────────────────────────────────────
        '@PSR12' => true,

        // EXCLUSION 1 & 2: no strict_types enforcement
        'declare_strict_types'          => false,
        'blank_line_after_opening_tag'  => false,

        // EXCLUSION 4: no line length enforcement
        // (no PHP CS Fixer equivalent needed — not enforced by default)

        // EXCLUSION 5: single-line function signatures preferred
        'function_declaration' => [
            'closure_function_spacing'  => 'one',
            'trailing_comma_in_multiline' => false,
        ],
        'method_argument_space' => [
            'on_multiline' => 'ignore',
        ],

        // EXCLUSION 6: cast spacing left to developer preference
        'cast_spaces' => false,

        // ── Additional targeted rules ────────────────────────────

        // Enforce short array syntax [] over array()
        'array_syntax' => ['syntax' => 'short'],

        // No whitespace before semicolons
        'no_whitespace_before_semicolon' => true,   // ≈ SemicolonSpacing

        // No trailing whitespace
        'no_trailing_whitespace'         => true,
        'no_trailing_whitespace_in_comment' => true,

        // Consistent spacing inside array brackets
        'trim_array_spaces'              => true,

        // Disallow multiple statements on a single line
        'no_multiple_statements_per_line' => true,

        // Operator spacing — covers $v_result=1 style issues
        'binary_operator_spaces' => [
            'default' => 'single_space',
        ],

        // No space before colon in switch/match
        'switch_case_space'   => true,
        'switch_case_semicolon_to_colon' => true,

		// Keep TABs as indentation
		'indentation_type' => true,

        // ── Warnings in PHPCS — informational only in CS Fixer ──
        // (PHP CS Fixer has no warning level, these are just noted)
        // UnusedFunctionParameter — not available in CS Fixer
        // Todo/Fixme comments     — not available in CS Fixer
        // These remain PHPCS-only checks, keep phpcs.xml for those

        // ── General housekeeping ─────────────────────────────────
        'no_unused_imports'      => true,
        'no_extra_blank_lines'   => true,
        'single_quote'           => true,
		'curly_braces_position' => [
			'functions_opening_brace'               => 'same_line',
			'classes_opening_brace'                 => 'same_line',
			'control_structures_opening_brace'      => 'same_line',
			'anonymous_functions_opening_brace'     => 'same_line',
			'anonymous_classes_opening_brace'       => 'same_line',
		],
    ])
    ->setFinder(
        (new Finder())
            ->in([
                __DIR__ . '/admin',
                __DIR__ . '/catalog',
                __DIR__ . '/system',
            ])
            ->exclude([
                'vendor',
                'node_modules',
                'storage',
            ])
    )
;