<style>
/* Hide user name next to avatar in topbar trigger */
.fi-user-menu-trigger .fi-user-name,
.fi-topbar-item-user .fi-user-name,
.fi-topbar-item-user [data-filament-user-name],
[data-header-avatar] + span,
.fi-user-avatar + span {
    display: none !important;
}

/* Hide theme toggle buttons/items in user dropdown */
.fi-dropdown [data-theme-toggle],
.fi-dropdown .fi-theme-switcher,
.fi-dropdown-item[data-theme],
button[aria-label^="Switch theme"],
button[title^="Switch theme"],
.fi-dropdown .fi-color-theme-switch,
.fi-dropdown .fi-theme-toggle {
    display: none !important;
}
</style>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const removeThemeBlock = () => {
            document.querySelectorAll('.fi-theme-switcher').forEach((el) => {
                const list = el.closest('.fi-dropdown-list');
                if (list) list.remove();
            });
        };
        removeThemeBlock();
        // Also run after Livewire renders
        window.addEventListener('livewire:load', () => {
            if (window.Livewire) {
                window.Livewire.hook('message.processed', () => removeThemeBlock());
            }
        });
    });
    </script>
<style>
/* User dropdown tweaks */
/* Hide the icon shown next to the name in the FIRST dropdown row */
.fi-dropdown .fi-dropdown-panel .fi-dropdown-list > li:first-child svg,
.fi-dropdown .fi-dropdown-panel .fi-dropdown-list > li:first-child [data-slot="icon"] {
    display: none !important;
}

/* Remove extra divider so only one line shows after the first row */
.fi-dropdown .fi-dropdown-panel .fi-dropdown-list > li:first-child {
    border-bottom: 0 !important;
}
/* Ensure the next separator still shows */
.fi-dropdown .fi-dropdown-panel .fi-dropdown-list > li:first-child + li {
    border-top: 1px solid rgba(0,0,0,0.08) !important;
}
.fi-dropdown-header-icon {
    display: none !important;
}

/* Remove the entire theme switcher container to avoid empty spacing */
.fi-dropdown .fi-dropdown-panel .fi-dropdown-list:has(.fi-theme-switcher) {
    display: none !important;
}
</style>
