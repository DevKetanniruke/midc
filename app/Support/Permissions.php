<?php

namespace App\Support;

/** Registry of the permissions the app checks. Names are "{module}.{action}", e.g. "gate-passes.view". */
class Permissions
{
    public const ACTIONS = ['view' => 'View', 'create' => 'Create', 'edit' => 'Edit', 'delete' => 'Delete'];

    /** module => [label, actions] — each module is a sidebar menu (or pair of menus). */
    public const MODULES = [
        'dashboard' => ['Dashboard', ['view']],
        'labels' => ['Barcode & QR Labels', ['view', 'create', 'edit', 'delete']],
        'gate-passes' => ['Gate Passes', ['view', 'create', 'edit', 'delete']],
        'visitor-passes' => ['Visitor Passes', ['view', 'create', 'edit', 'delete']],
        'users' => ['Users', ['view', 'create', 'edit', 'delete']],
        'roles' => ['Roles & Permissions', ['view', 'create', 'edit', 'delete']],
        'settings' => ['Settings', ['view', 'edit']],
    ];

    /** Built-in roles. The gate pass approval chain relies on dept_head, hr and security, so they cannot be renamed or deleted. */
    public const SYSTEM_ROLES = [
        'admin' => 'Administrator',
        'dept_head' => 'Head of Dept.',
        'hr' => 'HR',
        'security' => 'Security',
    ];

    /** Permissions given to the built-in roles the first time they are seeded (admins can change them afterwards). */
    public const DEFAULTS = [
        'dept_head' => ['dashboard.view', 'gate-passes.view', 'gate-passes.create', 'gate-passes.edit'],
        'hr' => ['dashboard.view', 'gate-passes.view', 'gate-passes.create', 'gate-passes.edit'],
        'security' => [
            'dashboard.view', 'gate-passes.view', 'gate-passes.create', 'gate-passes.edit',
            'visitor-passes.view', 'visitor-passes.create', 'visitor-passes.edit',
        ],
    ];

    /** Where to send someone who cannot open the dashboard: first permission they hold => route. */
    public const LANDING = [
        'labels.view' => 'labels.index',
        'gate-passes.view' => 'gate-passes.index',
        'visitor-passes.view' => 'visitor-passes.index',
        'users.view' => 'users.index',
        'roles.view' => 'roles.index',
        'settings.view' => 'settings.edit',
    ];

    /** @return list<string> every permission name */
    public static function all(): array
    {
        $names = [];
        foreach (self::MODULES as $module => [, $actions]) {
            foreach ($actions as $action) {
                $names[] = "$module.$action";
            }
        }

        return $names;
    }

    public static function roleLabel(string $name): string
    {
        return self::SYSTEM_ROLES[$name] ?? $name;
    }

    public static function isSystemRole(string $name): bool
    {
        return array_key_exists($name, self::SYSTEM_ROLES);
    }
}
