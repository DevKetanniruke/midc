@php
    $in = 'block w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-accent-500 focus:ring-accent-500';
    $readonly = ! auth()->user()->can('settings.edit');
    $thumb = 'h-20 w-20 rounded-lg border border-gray-200 bg-white object-contain p-1';
@endphp
<x-app-layout>
    <x-slot name="header">
        <h1 class="font-semibold text-lg text-gray-800">Settings</h1>
    </x-slot>

    <form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data" class="max-w-4xl mx-auto space-y-6"
          x-data="{ logo: @js($site->logo), favicon: @js(filled($values['favicon']) ? $site->favicon : null), removeLogo: false, removeFavicon: false,
                    preview(e, key) { const f = e.target.files[0]; if (f) { this[key] = URL.createObjectURL(f); this[key === 'logo' ? 'removeLogo' : 'removeFavicon'] = false } } }">
        @csrf @method('PUT')

        @if(session('status'))<div class="p-3 bg-green-50 border border-green-200 text-green-800 rounded-md text-sm">{{ session('status') }}</div>@endif
        @if($errors->any())<div class="rounded-md bg-red-50 border border-red-200 p-4 text-sm text-red-700">Please fix the highlighted fields below.</div>@endif
        @if($readonly)<div class="rounded-md bg-amber-50 border border-amber-200 p-3 text-sm text-amber-800">You can view the settings but not change them.</div>@endif

        <fieldset @disabled($readonly) class="space-y-6">
            <section class="bg-white rounded-lg shadow-sm border border-gray-200">
                <h2 class="px-6 py-4 border-b font-semibold text-gray-800">Branding</h2>
                <div class="p-6 grid gap-5 sm:grid-cols-2">
                    <x-form-field name="site_name" label="Site name" :required="true" :value="$values['site_name'] ?? $site->name"
                                  hint="Shown in the sidebar, the browser tab and the sign-in page." />
                    <x-form-field name="tagline" label="Tagline" :value="$values['tagline']" hint="Short line shown on the sign-in page." />

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Logo</label>
                        <div class="flex items-center gap-4">
                            <template x-if="logo && !removeLogo"><img :src="logo" alt="Logo" class="{{ $thumb }}"></template>
                            <div x-show="!logo || removeLogo" class="{{ $thumb }} grid place-items-center text-xs text-gray-400">No logo</div>
                            <div class="space-y-2 text-sm">
                                <input type="file" name="logo" accept="image/png,image/jpeg,image/webp,image/gif" @change="preview($event, 'logo')"
                                       class="block text-sm text-gray-600 file:mr-3 file:rounded-md file:border-0 file:bg-accent-50 file:px-3 file:py-1.5 file:text-accent-700 hover:file:bg-accent-100">
                                <label x-show="logo" class="inline-flex items-center gap-2 text-gray-600">
                                    <input type="checkbox" name="remove_logo" value="1" x-model="removeLogo" class="rounded border-gray-300 text-accent-600 focus:ring-accent-500"> Remove logo
                                </label>
                            </div>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">PNG, JPG, WebP or GIF, up to 2 MB. A square logo looks best in the sidebar.</p>
                        @error('logo')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Favicon <span class="font-normal text-gray-400">(browser tab icon)</span></label>
                        <div class="flex items-center gap-4">
                            <template x-if="favicon && !removeFavicon"><img :src="favicon" alt="Favicon" class="h-10 w-10 rounded border border-gray-200 bg-white object-contain p-1"></template>
                            <div class="space-y-2 text-sm">
                                <input type="file" name="favicon" accept="image/png,image/x-icon,image/vnd.microsoft.icon,image/jpeg,image/webp" @change="preview($event, 'favicon')"
                                       class="block text-sm text-gray-600 file:mr-3 file:rounded-md file:border-0 file:bg-accent-50 file:px-3 file:py-1.5 file:text-accent-700 hover:file:bg-accent-100">
                                @if(filled($values['favicon']))
                                    <label class="inline-flex items-center gap-2 text-gray-600">
                                        <input type="checkbox" name="remove_favicon" value="1" x-model="removeFavicon" class="rounded border-gray-300 text-accent-600 focus:ring-accent-500"> Remove favicon
                                    </label>
                                @endif
                            </div>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">PNG or ICO, up to 512 KB.</p>
                        @error('favicon')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>
            </section>

            <section class="bg-white rounded-lg shadow-sm border border-gray-200">
                <h2 class="px-6 py-4 border-b font-semibold text-gray-800">Company details <span class="font-normal text-sm text-gray-500">— printed at the top of gate passes and visitor passes</span></h2>
                <div class="p-6 grid gap-5 sm:grid-cols-2">
                    <x-form-field name="company_name" label="Company name" :value="$values['company_name']" class="sm:col-span-2"
                                  hint="Leave blank to keep each printed pass's current heading." />
                    <div class="sm:col-span-2">
                        <label for="address" class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                        <textarea id="address" name="address" rows="2" maxlength="300" class="{{ $in }}">{{ old('address', $values['address']) }}</textarea>
                        @error('address')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <x-form-field name="phone" label="Phone" type="tel" :value="$values['phone']" />
                    <x-form-field name="email" label="Email" type="email" :value="$values['email']" />
                    <x-form-field name="website" label="Website" :value="$values['website']" class="sm:col-span-2" />
                </div>
            </section>
        </fieldset>

        @can('settings.edit')
            <div class="flex justify-end">
                <button class="px-5 py-2 rounded-md bg-accent-600 text-white text-sm font-semibold hover:bg-accent-700">Save Settings</button>
            </div>
        @endcan
    </form>
</x-app-layout>
