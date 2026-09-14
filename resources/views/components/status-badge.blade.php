<div>
  @props(['status'])

@php
    $classes = match($status) {
        'success', 'lunas' => 'bg-green-100 text-green-800',
        'pending' => 'bg-yellow-100 text-yellow-800',
        'failed', 'batal' => 'bg-red-100 text-red-800',
        default => 'bg-gray-100 text-gray-800',
    };
@endphp

<span class="px-2.5 py-0.5 rounded-full text-xs font-medium {{ $classes }}">
    {{ ucfirst($status) }}
</span>
</div>