@php /** @var \App\Filament\Pages\Settings $this */ @endphp

<x-filament-panels::page>
    <div class="space-y-8">
        <x-filament::section class="mb-4 white-stg-card">
            <form id="profileForm" x-data x-on:submit.prevent="if ($('#profileForm').valid()) { $wire.saveProfile() }" class="space-y-6">
                {{ $this->profileForm }}
                <div class="flex items-center gap-3">
                    <x-filament::button type="submit" icon="heroicon-o-check-circle">
                        Save Profile
                    </x-filament::button>
                </div>
            </form>
        </x-filament::section>

        <x-filament::section class="m-0 white-stg-card">
            <form id="passwordForm" x-data x-on:submit.prevent="if ($('#passwordForm').valid()) { $wire.changePassword() }" class="space-y-6">
                {{ $this->passwordForm }}
                <div class="flex items-center gap-3">
                    <x-filament::button type="submit" icon="heroicon-o-key">
                        Update Password
                    </x-filament::button>
                </div>
            </form>
        </x-filament::section>
    </div>
</x-filament-panels::page>

@push('scripts')
<script src="{{ asset('assets/js/jquery/jquery-3.7.1.min.js') }}" ></script>
<script src="{{ asset('assets/js/jquery/jquery.validate.min.js') }}" ></script>

    <script>
        $(function () {
            // Helper: get input name with statePath prefixes used by Filament
            const pf = (field) => `profileData[${field}]`;
            const pw = (field) => `passwordData[${field}]`;

            // PROFILE FORM VALIDATION
            $("#profileForm").validate({
                ignore: ":hidden:not([name^='profileData'])", // ignore hidden fields not belonging to this form state
                onkeyup: function(element) { $(element).valid(); },
                onfocusout: function(element) { $(element).valid(); },
                rules: {
                    [pf('first_name')]: { required: true },
                    [pf('last_name')]: { required: true },
                    [pf('email')]: { required: true, email: true, maxlength: 255 },
                    [pf('phone_number')]: {
                        required: true,
                        minlength: 10,
                        maxlength: 20,
                        digits: true,
                    },
                    [pf('profile_photo')]: { required: true },
                },
                messages: {
                    [pf('first_name')]: { required: 'First Name is required' },
                    [pf('last_name')]: { required: 'Last Name is required' },
                    [pf('email')]: { required: 'Email is required', email: 'Enter a valid email' },
                    [pf('phone_number')]: { required: 'Phone Number is required' },
                    [pf('profile_photo')]: { required: 'Profile Photo is required' },
                },
                errorClass: 'fi-error-text',
                errorElement: 'div',
                wrapper: 'div',
                highlight: function (element) {
                    $(element).addClass('fi-error-input');
                },
                unhighlight: function (element) {
                    $(element).removeClass('fi-error-input');
                },
                errorPlacement: function(error, element) {
                    const wrap = element.closest('.fi-fo-field-wrp');
                    if (wrap.length) {
                        // Avoid duplicate messages: remove any existing .fi-error-text siblings
                        wrap.find('.fi-error-text').remove();
                        error.appendTo(wrap);
                    } else {
                        error.insertAfter(element);
                    }
                }
            });

            // PASSWORD FORM VALIDATION
            $("#passwordForm").validate({
                ignore: ":hidden:not([name^='passwordData'])",
                onkeyup: function(element) { $(element).valid(); },
                onfocusout: function(element) { $(element).valid(); },
                rules: {
                    [pw('current_password')]: { required: true },
                    [pw('new_password')]: { required: true, minlength: 8 },
                    [pw('new_password_confirmation')]: { required: true, equalTo: `[name="${pw('new_password')}"]` },
                },
                messages: {
                    [pw('current_password')]: { required: 'Current Password is required' },
                    [pw('new_password')]: { required: 'New Password is required', minlength: 'Use at least 8 characters' },
                    [pw('new_password_confirmation')]: { required: 'Please confirm the new password', equalTo: 'Passwords must match' },
                },
                errorClass: 'fi-error-text',
                errorElement: 'div',
                wrapper: 'div',
                highlight: function (element) {
                    $(element).addClass('fi-error-input');
                },
                unhighlight: function (element) {
                    $(element).removeClass('fi-error-input');
                },
                errorPlacement: function(error, element) {
                    const wrap = element.closest('.fi-fo-field-wrp');
                    if (wrap.length) {
                        wrap.find('.fi-error-text').remove();
                        error.appendTo(wrap);
                    } else {
                        error.insertAfter(element);
                    }
                }
            });
            // Refresh header avatar after Livewire profile update
            const refreshAvatar = () => {
                const selectors = [
                    '[data-header-avatar] img',
                    '.fi-user-avatar img',
                    '.filament-user-avatar img',
                    '.fi-dropdown-user-avatar img',
                ];
                const imgs = document.querySelectorAll(selectors.join(','));
                imgs.forEach(img => {
                    const base = img.src.split('?')[0];
                    img.src = `${base}?t=${Date.now()}`;
                });
            };

            window.addEventListener('livewire:load', () => {
                if (window.Livewire && typeof window.Livewire.on === 'function') {
                    window.Livewire.on('profile-updated', () => {
                        setTimeout(refreshAvatar, 300);
                    });
                }
            });

            // Enforce digits-only while typing in phone number field
            const phoneSelector = `[name="${pf('phone_number')}"]`;
            const phoneInput = document.querySelector(phoneSelector);
            if (phoneInput) {
                phoneInput.addEventListener('input', (e) => {
                    const digits = e.target.value.replace(/\D+/g, '');
                    if (e.target.value !== digits) {
                        e.target.value = digits;
                    }
                });
            }
        });
    </script>
    <style>
        /* Red error text */
        .fi-error-text { color: #dc2626; font-size: 0.875rem; margin-top: 0.25rem; display: block; }
        /* Highlight invalid inputs */
        .fi-error-input { border-color: #ef4444 !important; box-shadow: 0 0 0 1px #ef4444 inset; }
        /* Hide number input spinners (phone field) */
        input[type=number]::-webkit-outer-spin-button,
        input[type=number]::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
        input[type=number] { -moz-appearance: textfield; }
    </style>
@endpush
