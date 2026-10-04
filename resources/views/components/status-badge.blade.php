<div>
  @props(['status' => 'default'])

@php
    $classes = match(strtolower((string) $status)) {
        'success', 'lunas', 'active', 'aktif' => 'bg-green-100 text-green-800',
        'pending' => 'bg-yellow-100 text-yellow-800',
        'failed', 'batal', 'inactive', 'nonaktif' => 'bg-red-100 text-red-800',
        default => 'bg-gray-100 text-gray-800',
    };
    $slotContent = trim((string) $slot);
@endphp

<span class="px-2.5 py-0.5 rounded-full text-xs font-medium {{ $classes }}">
    {{ $slotContent !== '' ? $slot : ucfirst($status) }}
</span>
</div>