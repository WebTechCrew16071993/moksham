@php /** @var \App\Filament\Resources\ImportCompanyResource\Pages\CreateImportCompany $this */ @endphp

<x-filament-panels::page>
    <div id="companyFormRoot" class="space-y-6">
        <form id="companyForm" novalidate>
            {{ $this->form }}
        </form>

        <div class="flex items-center gap-3">
            <x-filament::button id="submitCompanyBtn" type="button" icon="heroicon-o-check-circle">
                Create Company
            </x-filament::button>
            <x-filament::button color="gray" type="reset" icon="heroicon-o-arrow-path" class="hidden">
                Reset
            </x-filament::button>
        </div>
    </div>
</x-filament-panels::page>

@push('scripts')
    <script src="{{ asset('assets/js/jquery/jquery-3.7.1.min.js') }}" ></script>
    <script src="{{ asset('assets/js/jquery/jquery.validate.min.js') }}" ></script>

    <script>
        (function initCompanyValidation() {
            const f = (name) => `data[${name}]`;

            const $form = $("#companyForm");

            $form.attr('novalidate', true);
            $form.validate({
                ignore: [],
                onkeyup: function(element) { $(element).valid(); },
                onfocusout: function(element) { $(element).valid(); },
                rules: {
                    [f('name')]: { required: true, maxlength: 255 },
                    [f('person_name')]: { required: true, maxlength: 255 },
                    [f('address')]: { required: true, maxlength: 2000 },
                    [f('city')]: { required: true, maxlength: 255 },
                    [f('state')]: { required: true, maxlength: 255 },
                    [f('zip')]: { required: true, maxlength: 20 },
                    [f('country')]: { required: true, maxlength: 255 },
                    [f('email')]: { required: true, email: true, maxlength: 255 },
                    [f('phone_number')]: { required: true, maxlength: 30, pattern: /^[0-9+()\s-]+$/ },

                    [f('bank_name')]: { required: true, maxlength: 255 },
                    [f('bank_address')]: { required: true, maxlength: 2000 },
                    [f('bank_city')]: { required: true, maxlength: 255 },
                    [f('bank_state')]: { required: true, maxlength: 255 },
                    [f('bank_zip')]: { required: true, maxlength: 20 },
                    [f('bank_country')]: { required: true, maxlength: 255 },
                    [f('bank_account_number')]: { required: true, maxlength: 255 },
                    [f('bank_swift_code')]: { maxlength: 255 },
                    [f('bank_ifsc_code')]: { maxlength: 255 },
                },
                messages: {
                    [f('name')]: { required: 'Company Name is required' },
                    [f('person_name')]: { required: 'Contact Person is required' },
                    [f('address')]: { required: 'Address is required' },
                    [f('city')]: { required: 'City is required' },
                    [f('state')]: { required: 'State is required' },
                    [f('zip')]: { required: 'Zip Code is required' },
                    [f('country')]: { required: 'Country is required' },
                    [f('email')]: { required: 'Email is required', email: 'Enter a valid email' },
                    [f('phone_number')]: { required: 'Phone is required', pattern: 'Use digits, spaces, +, -, or ()' },

                    [f('bank_name')]: { required: 'Bank Name is required' },
                    [f('bank_address')]: { required: 'Bank Address is required' },
                    [f('bank_city')]: { required: 'City is required' },
                    [f('bank_state')]: { required: 'State is required' },
                    [f('bank_zip')]: { required: 'Zip Code is required' },
                    [f('bank_country')]: { required: 'Country is required' },
                    [f('bank_account_number')]: { required: 'Account Number is required' },
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
                    const wrap = element.closest('[data-field-wrapper], .fi-fo-field-wrp');
                    if (wrap.length) {
                        wrap.find('.fi-error-text').remove();
                        error.appendTo(wrap);
                    } else {
                        error.insertAfter(element);
                    }
                }
            });

            // Submit button click
            document.getElementById('submitCompanyBtn')?.addEventListener('click', function () {
                const valid = $form.validate().form();
                if (!valid) {
                    const $first = $form.find('.fi-error-text').first().closest('[data-field-wrapper], .fi-fo-field-wrp');
                    if ($first.length) {
                        window.scrollTo({ top: $first.offset().top - 120, behavior: 'smooth' });
                    }
                    return;
                }
                if (window.Livewire) {
                    $wire.create();
                }
            });

            // Enter key submits via validation as well
            $form.on('keypress', function (e) {
                if (e.which === 13 && !$(e.target).is('textarea')) {
                    e.preventDefault();
                    document.getElementById('submitCompanyBtn')?.click();
                }
            });
        })();
    </script>
@endpush

@push('styles')
    <style>
        .fi-error-text { color: #dc2626; font-size: 0.875rem; margin-top: 0.25rem; display: block; }
        .fi-error-input { border-color: #ef4444 !important; box-shadow: 0 0 0 1px #ef4444 inset; }
    </style>
@endpush
