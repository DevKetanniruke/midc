<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

class SettingController extends Controller implements HasMiddleware
{
    /** Uploaded logos live under public/ so no storage symlink is needed. */
    private const UPLOAD_DIR = 'uploads/settings';

    public static function middleware(): array
    {
        return [
            new Middleware('permission:settings.view', only: ['edit']),
            new Middleware('permission:settings.edit', only: ['update']),
        ];
    }

    public function edit(): View
    {
        return view('settings.edit', ['values' => Setting::values()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'site_name' => ['required', 'string', 'max:100'],
            'company_name' => ['nullable', 'string', 'max:150'],
            'tagline' => ['nullable', 'string', 'max:200'],
            'address' => ['nullable', 'string', 'max:300'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:150'],
            'website' => ['nullable', 'string', 'max:150'],
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp,gif', 'max:2048'],
            'favicon' => ['nullable', 'file', 'mimes:png,ico,jpg,jpeg,webp', 'max:512'],
            'remove_logo' => ['nullable', 'boolean'],
            'remove_favicon' => ['nullable', 'boolean'],
        ]);

        $current = Setting::values();
        $save = collect($data)->only(['site_name', 'company_name', 'tagline', 'address', 'phone', 'email', 'website'])->all();

        foreach (['logo', 'favicon'] as $field) {
            if ($request->file($field)) {
                $save[$field] = $this->store($request->file($field), $field);
                $this->forget($current[$field]);
            } elseif ($request->boolean("remove_$field")) {
                $save[$field] = null;
                $this->forget($current[$field]);
            }
        }

        Setting::put($save);

        return redirect()->route('settings.edit')->with('status', 'Settings saved.');
    }

    /** Move the upload into public/uploads/settings and return its path relative to public/. */
    private function store(UploadedFile $file, string $prefix): string
    {
        $name = $prefix.'-'.time().'.'.$file->extension();
        $file->move(public_path(self::UPLOAD_DIR), $name);

        return self::UPLOAD_DIR.'/'.$name;
    }

    /** Delete a previously uploaded file (only ever inside the settings upload folder). */
    private function forget(?string $path): void
    {
        if ($path && str_starts_with($path, self::UPLOAD_DIR.'/') && ! str_contains($path, '..')) {
            @unlink(public_path($path));
        }
    }
}
