@extends('layouts.app') {{-- Sesuaikan dengan nama layout utama Anda --}}

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Manajemen Event</h1>
        <a href="{{ route('admin.events.create') }}" class="btn btn-primary bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-2">
            <span>➕</span> Tambah Event
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg relative mb-6" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg relative mb-6" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 mb-6">
        <form method="GET" action="{{ route('admin.events.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Cari Event</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari judul atau lokasi..." class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Kategori</label>
                <select name="kategori_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua Kategori</option>
                    @foreach(\App\Models\Kategori::all() as $kat)
                        <option value="{{ $kat->id }}" {{ request('kategori_id') == $kat->id ? 'selected' : '' }}>{{ $kat->nama }}</option>
                    @endfontforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Urutan Tanggal</label>
                <select name="sort" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="asc" {{ request('sort', 'asc') == 'asc' ? 'selected' : '' }}>Terdekat (Ascending)</option>
                    <option value="desc" {{ request('sort') == 'desc' ? 'selected' : '' }}>Terlama (Descending)</option>
                </select>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white rounded-lg px-4 py-2 text-sm font-semibold transition-colors">
                    Filter
                </button>
                <a href="{{ route('admin.events.index') }}" class="flex-1 text-center bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg px-4 py-2 text-sm font-semibold transition-colors">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-6">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="p-4 text-xs font-bold text-gray-500 uppercase">Gambar</th>
                        <th class="p-4 text-xs font-bold text-gray-500 uppercase">Judul</th>
                        <th class="p-4 text-xs font-bold text-gray-500 uppercase">Kategori</th>
                        <th class="p-4 text-xs font-bold text-gray-500 uppercase">Tanggal & Waktu</th>
                        <th class="p-4 text-xs font-bold text-gray-500 uppercase">Lokasi</th>
                        <th class="p-4 text-xs font-bold text-gray-500 uppercase text-center">Status</th>
                        <th class="p-4 text-xs font-bold text-gray-500 uppercase text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm text-gray-600">
                    @forelse($events as $event)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="p-4">
                                <img src="{{ $event->image_url }}" alt="{{ $event->judul }}" class="w-16 h-16 object-cover rounded-lg border border-gray-100">
                            </td>
                            <td class="p-4 font-semibold text-gray-800">{{ $event->judul }}</td>
                            <td class="p-4">{{ $event->kategori->nama ?? '-' }}</td>
                            <td class="p-4">
                                {{ $event->tanggal_waktu ? $event->tanggal_waktu->format('d M Y, H:i') : '-' }} WIB
                            </td>
                            <td class="p-4 max-w-xs truncate">{{ $event->lokasi }}</td>
                            <td class="p-4 text-center">
                                @php
                                    $status = $event->status; // Memanggil Accessor getStatusAttribute()
                                    $badgeClass = match ($status) {
                                        'Upcoming' => 'bg-blue-50 text-blue-700 border border-blue-200',
                                        'Ongoing' => 'bg-green-50 text-green-700 border border-green-200',
                                        default => 'bg-gray-50 text-gray-500 border border-gray-200',
                                    };
                                @endphp
                                <span class="inline-block px-2.5 py-1 text-xs font-semibold rounded-full {{ $badgeClass }}">
                                    {{ $status }}
                                </span>
                            </td>
                            <td class="p-4">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('events.show', $event->id) }}" target="_blank" class="p-1.5 hover:bg-gray-100 rounded text-gray-500" title="View Public Page">👁️</a>
                                    <a href="{{ route('admin.events.edit', $event->id) }}" class="p-1.5 hover:bg-yellow-50 rounded text-yellow-600" title="Edit">✏️</a>
                                    <form action="{{ route('admin.events.destroy', $event->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus event ini? Semua tiket terkait juga akan terhapus.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 hover:bg-red-50 rounded text-red-600" title="Hapus">🗑️</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-gray-400">Belum ada event terdaftar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $events->appends(request()->except('page'))->links() }}
    </div>
</div>
@endsection