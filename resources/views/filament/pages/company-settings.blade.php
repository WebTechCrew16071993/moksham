@php /** @var \App\Filament\Pages\CompanySettings $this */ @endphp

<x-filament-panels::page>
    <form id="companySettingsForm" novalidate x-data x-on:submit.prevent="if ($('#companySettingsForm').valid()) { $wire.save() }" class="space-y-8">
        {{ $this->form }}

        <div class="flex items-center gap-3 mt-3">
            <x-filament::button type="submit" icon="heroicon-o-check-circle">
                Save Settings
            </x-filament::button>
            <x-filament::button id="resetSettingsBtn" color="gray" type="button" icon="heroicon-o-arrow-path" class="hidden">
                Reset
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>

@push('scripts')
    <script src="{{ asset('assets/js/jquery/jquery-3.7.1.min.js') }}" ></script>
    <script src="{{ asset('assets/js/jquery/jquery.validate.min.js') }}" ></script>
  
    <script>
        $(function () {
            const f = (name) => `data[${name}]`;

            $("#companySettingsForm").validate({
                ignore: ":hidden:not([name^='data'])",
                onkeyup: function(element) { $(element).valid(); },
                onfocusout: function(element) { $(element).valid(); },
                rules: {
                    // Company Information
                    [f('company_name')]: { 
                        required: true, 
                        minlength: 2,
                        maxlength: 255 
                    },
                    [f('company_address')]: { 
                        required: true, 
                        minlength: 5,
                        maxlength: 1000 
                    },
                    [f('company_city')]: { 
                        required: true, 
                        minlength: 2,
                        maxlength: 255,
                        pattern: /^[a-zA-Z\s.-]+$/
                    },
                    [f('company_state')]: { 
                        required: true, 
                        minlength: 2,
                        maxlength: 255,
                        pattern: /^[a-zA-Z\s.-]+$/
                    },
                    [f('company_zip')]: { 
                        required: true, 
                        minlength: 3,
                        maxlength: 20,
                        pattern: /^[0-9A-Za-z\s-]+$/
                    },
                    [f('company_country')]: { 
                        required: true, 
                        minlength: 2,
                        maxlength: 255,
                        pattern: /^[a-zA-Z\s.-]+$/
                    },
                    [f('company_phone')]: { 
                        required: true, 
                        minlength: 10,
                        maxlength: 30, 
                        pattern: /^[0-9+()\s-]+$/ 
                    },
                    [f('company_email')]: { 
                        required: true, 
                        email: true, 
                        maxlength: 255 
                    },
                    
                    // Bank Information
                    [f('bank_name')]: { 
                        required: true, 
                        minlength: 2,
                        maxlength: 255 
                    },
                    [f('bank_address')]: { 
                        required: true, 
                        minlength: 5,
                        maxlength: 1000 
                    },
                    [f('bank_city')]: { 
                        required: true, 
                        minlength: 2,
                        maxlength: 255,
                        pattern: /^[a-zA-Z\s.-]+$/
                    },
                    [f('bank_state')]: { 
                        required: true, 
                        minlength: 2,
                        maxlength: 255,
                        pattern: /^[a-zA-Z\s.-]+$/
                    },
                    [f('bank_zip')]: { 
                        required: true, 
                        minlength: 3,
                        maxlength: 20,
                        pattern: /^[0-9A-Za-z\s-]+$/
                    },
                    [f('bank_country')]: { 
                        required: true, 
                        minlength: 2,
                        maxlength: 255,
                        pattern: /^[a-zA-Z\s.-]+$/
                    },
                    [f('bank_account_number')]: { 
                        required: true, 
                        minlength: 8,
                        maxlength: 255,
                        pattern: /^[0-9A-Za-z-]+$/
                    },
                    [f('bank_swift_code')]: { 
                        minlength: 8,
                        maxlength: 11,
                        pattern: /^[A-Z]{6}[A-Z0-9]{2}([A-Z0-9]{3})?$/
                    },
                    [f('bank_routing_number')]: { 
                        minlength: 9,
                        maxlength: 255,
                        pattern: /^[0-9]+$/
                    },
                    
                    // Tax & Registration Information (optional fields)
                    [f('tax_iec_number')]: { 
                        minlength: 10,
                        maxlength: 255,
                        pattern: /^[A-Z0-9]+$/
                    },
                    [f('tax_gstin')]: { 
                        minlength: 15,
                        maxlength: 15,
                        pattern: /^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/
                    },
                    [f('tax_pan_number')]: { 
                        minlength: 10,
                        maxlength: 10,
                        pattern: /^[A-Z]{5}[0-9]{4}[A-Z]{1}$/
                    }
                },
                messages: {
                    // Company Information
                    [f('company_name')]: { 
                        required: 'Company Name is required',
                        minlength: 'Company Name must be at least 2 characters',
                        maxlength: 'Company Name cannot exceed 255 characters'
                    },
                    [f('company_address')]: { 
                        required: 'Company Address is required',
                        minlength: 'Address must be at least 5 characters',
                        maxlength: 'Address cannot exceed 1000 characters'
                    },
                    [f('company_city')]: { 
                        required: 'City is required',
                        minlength: 'City must be at least 2 characters',
                        maxlength: 'City cannot exceed 255 characters',
                        pattern: 'City can only contain letters, spaces, dots, and hyphens'
                    },
                    [f('company_state')]: { 
                        required: 'State is required',
                        minlength: 'State must be at least 2 characters',
                        maxlength: 'State cannot exceed 255 characters',
                        pattern: 'State can only contain letters, spaces, dots, and hyphens'
                    },
                    [f('company_zip')]: { 
                        required: 'Zip Code is required',
                        minlength: 'Zip Code must be at least 3 characters',
                        maxlength: 'Zip Code cannot exceed 20 characters',
                        pattern: 'Zip Code can only contain letters, numbers, spaces, and hyphens'
                    },
                    [f('company_country')]: { 
                        required: 'Country is required',
                        minlength: 'Country must be at least 2 characters',
                        maxlength: 'Country cannot exceed 255 characters',
                        pattern: 'Country can only contain letters, spaces, dots, and hyphens'
                    },
                    [f('company_phone')]: { 
                        required: 'Phone Number is required',
                        minlength: 'Phone Number must be at least 10 characters',
                        maxlength: 'Phone Number cannot exceed 30 characters',
                        pattern: 'Phone Number can only contain digits, spaces, +, -, and ()'
                    },
                    [f('company_email')]: { 
                        required: 'Email Address is required',
                        email: 'Please enter a valid email address',
                        maxlength: 'Email cannot exceed 255 characters'
                    },
                    
                    // Bank Information
                    [f('bank_name')]: { 
                        required: 'Bank Name is required',
                        minlength: 'Bank Name must be at least 2 characters',
                        maxlength: 'Bank Name cannot exceed 255 characters'
                    },
                    [f('bank_address')]: { 
                        required: 'Bank Address is required',
                        minlength: 'Bank Address must be at least 5 characters',
                        maxlength: 'Bank Address cannot exceed 1000 characters'
                    },
                    [f('bank_city')]: { 
                        required: 'Bank City is required',
                        minlength: 'City must be at least 2 characters',
                        maxlength: 'City cannot exceed 255 characters',
                        pattern: 'City can only contain letters, spaces, dots, and hyphens'
                    },
                    [f('bank_state')]: { 
                        required: 'Bank State is required',
                        minlength: 'State must be at least 2 characters',
                        maxlength: 'State cannot exceed 255 characters',
                        pattern: 'State can only contain letters, spaces, dots, and hyphens'
                    },
                    [f('bank_zip')]: { 
                        required: 'Bank Zip Code is required',
                        minlength: 'Zip Code must be at least 3 characters',
                        maxlength: 'Zip Code cannot exceed 20 characters',
                        pattern: 'Zip Code can only contain letters, numbers, spaces, and hyphens'
                    },
                    [f('bank_country')]: { 
                        required: 'Bank Country is required',
                        minlength: 'Country must be at least 2 characters',
                        maxlength: 'Country cannot exceed 255 characters',
                        pattern: 'Country can only contain letters, spaces, dots, and hyphens'
                    },
                    [f('bank_account_number')]: { 
                        required: 'Account Number is required',
                        minlength: 'Account Number must be at least 8 characters',
                        maxlength: 'Account Number cannot exceed 255 characters',
                        pattern: 'Account Number can only contain letters, numbers, and hyphens'
                    },
                    [f('bank_swift_code')]: { 
                        minlength: 'SWIFT Code must be 8 or 11 characters',
                        maxlength: 'SWIFT Code must be 8 or 11 characters',
                        pattern: 'Invalid SWIFT Code format (e.g., AAAAAABBCCC)'
                    },
                    [f('bank_routing_number')]: { 
                        minlength: 'Routing Number must be at least 9 digits',
                        maxlength: 'Routing Number cannot exceed 255 characters',
                        pattern: 'Routing Number can only contain digits'
                    },
                    
                    // Tax & Registration Information
                    [f('tax_iec_number')]: { 
                        minlength: 'IEC Number must be at least 10 characters',
                        maxlength: 'IEC Number cannot exceed 255 characters',
                        pattern: 'IEC Number can only contain uppercase letters and numbers'
                    },
                    [f('tax_gstin')]: { 
                        minlength: 'GSTIN must be exactly 15 characters',
                        maxlength: 'GSTIN must be exactly 15 characters',
                        pattern: 'Invalid GSTIN format (e.g., 22AAAAA0000A1Z5)'
                    },
                    [f('tax_pan_number')]: { 
                        minlength: 'PAN Number must be exactly 10 characters',
                        maxlength: 'PAN Number must be exactly 10 characters',
                        pattern: 'Invalid PAN format (e.g., ABCDE1234F)'
                    }
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
                    // Target Filament's field wrapper. In some contexts it's data-field-wrapper; fallback to .fi-fo-field-wrp
                    const wrap = element.closest('[data-field-wrapper], .fi-fo-field-wrp');
                    if (wrap.length) {
                        // Remove any existing custom or Filament error nodes to prevent duplicates
                        wrap.find('.fi-error-text, .fi-fo-field-error-message, [data-validation-error]').remove();
                        error.appendTo(wrap);
                    } else {
                        error.insertAfter(element);
                    }
                }
            });

            // Reset handler: restore saved values and clear validation states
            $('#resetSettingsBtn').on('click', function (e) {
                e.preventDefault();
                
                // Clear all validation errors immediately
                const $form = $('#companySettingsForm');
                const validator = $form.data('validator');
                if (validator) {
                    validator.resetForm();
                }
                $form.find('.fi-error-text').remove();
                $form.find('.fi-error-input').removeClass('fi-error-input');
                
                // Call Livewire to reset to saved values
                if (window.Livewire && $wire) {
                    $wire.resetToSaved();
                }
            });
        });
    </script>
    <style>
        .fi-error-text { color: #dc2626; font-size: 0.875rem; margin-top: 0.25rem; display: block; }
        .fi-error-input { border-color: #ef4444 !important; box-shadow: 0 0 0 1px #ef4444 inset; }
    </style>
@endpush
