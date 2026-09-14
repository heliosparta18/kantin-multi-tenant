<x-layouts.customer>
    <div class="p-4 space-y-4">
        <h1 class="text-xl font-bold">Katalog Menu Kantin</h1>
        
        <x-status-badge status="lunas" />
        <x-status-badge status="pending" />
        
        <x-input placeholder="Cari makanan..." />
        
        <x-button>Pesan Sekarang</x-button>
        
        <x-empty-state title="Menu Kosong" message="Belum ada menu yang tersedia saat ini." />
    </div>
</x-layouts.customer>