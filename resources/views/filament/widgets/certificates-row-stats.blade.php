<div class="px-2">
    <div class="grid grid-cols-5 gap-3">
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 shadow-sm p-4">
            <div class="text-sm text-gray-600 dark:text-gray-300">Total</div>
            <div class="mt-1 text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $this->stats['total'] ?? 0 }}</div>
            <div class="mt-1 text-xs text-orange-600">All Certificates</div>
        </div>
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 shadow-sm p-4">
            <div class="text-sm text-gray-600 dark:text-gray-300">Draft</div>
            <div class="mt-1 text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $this->stats['draft'] ?? 0 }}</div>
            <div class="mt-1 text-xs text-orange-600">Not submitted</div>
        </div>
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 shadow-sm p-4">
            <div class="text-sm text-gray-600 dark:text-gray-300">Owner</div>
            <div class="mt-1 text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $this->stats['owner'] ?? 0 }}</div>
            <div class="mt-1 text-xs text-emerald-600">Owner signed</div>
        </div>
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 shadow-sm p-4">
            <div class="text-sm text-gray-600 dark:text-gray-300">Chamber</div>
            <div class="mt-1 text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $this->stats['chamber'] ?? 0 }}</div>
            <div class="mt-1 text-xs text-sky-600">Chamber signed</div>
        </div>
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 shadow-sm p-4">
            <div class="text-sm text-gray-600 dark:text-gray-300">Done</div>
            <div class="mt-1 text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $this->stats['done'] ?? 0 }}</div>
            <div class="mt-1 text-xs text-emerald-600">Completed</div>
        </div>
    </div>
</div>
