@if ($errors->any())
    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
        <div class="flex items-start gap-2">
            <i class="bi bi-exclamation-triangle mt-0.5"></i>
            <div>
                <p class="font-semibold">{{ __('Please review the highlighted fields.') }}</p>
                <ul class="mt-1 list-disc pl-5 space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endif
