<x-app-layout>
    <div class="container mx-auto px-4 py-8 max-w-6xl">
        <a href="{{ route('admin.events.index') }}" class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-900 mb-6 font-medium text-sm transition-colors">
            ⬅️ Kembali ke Daftar Event
        </a>

        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-800 p-4 rounded-xl mb-6 shadow-sm text-sm font-medium">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-50 border border-red-200 text-red-800 p-4 rounded-xl mb-6 shadow-sm text-sm font-medium">
                {{ session('error') }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <img src="{{ asset('storage/' . $event->gambar) }}" alt="{{ $event->judul }}" class="w-full h-96 object-cover">
                </div>

                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 space-y-4">
                    <div class="flex items-center gap-3">
                        <span class="bg-blue-50 text-blue-700 px-3 py-1 rounded-full text-xs font-semibold uppercase tracking-wider">
                            {{ $event->kategori->nama ?? 'Umum' }}
                        </span>
                        <span class="text-sm font-medium {{ \Carbon\Carbon::parse($event->tanggal_waktu)->isFuture() ? 'text-green-600 bg-green-50' : 'text-gray-500 bg-gray-100' }} px-3 py-1 rounded-full text-xs">
                            {{ \Carbon\Carbon::parse($event->tanggal_waktu)->isFuture() ? '🟢 Upcoming' : '🔴 Completed' }}
                        </span>
                    </div>

                    <h1 class="text-3xl font-bold text-gray-900 tracking-tight">{{ $event->judul }}</h1>
                    
                    <hr class="border-gray-100">

                    <div class="space-y-2">
                        <h3 class="text-lg font-bold text-gray-800">Deskripsi Event</h3>
                        <p class="text-gray-600 leading-relaxed text-sm whitespace-pre-line">
                            {{ $event->deskripsi }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 space-y-4">
                    <h3 class="text-md font-bold text-gray-800 border-b border-gray-50 pb-2">Informasi Pelaksanaan</h3>
                    
                    <div class="space-y-3">
                        <div class="flex items-start gap-3">
                            <span class="text-xl">📅</span>
                            <div>
                                <p class="text-xs font-semibold text-gray-400 uppercase">Tanggal & Waktu</p>
                                <p class="text-sm font-medium text-gray-800">
                                    {{ \Carbon\Carbon::parse($event->tanggal_waktu)->translatedFormat('d F Y, H:i') }} WIB
                                </p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <span class="text-xl">📍</span>
                            <div>
                                <p class="text-xs font-semibold text-gray-400 uppercase">Lokasi</p>
                                <p class="text-sm font-medium text-gray-800">{{ $event->lokasi }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 space-y-4">
                    <h3 class="text-md font-bold text-gray-800 border-b border-gray-50 pb-2">Tiket Tersedia</h3>
                    
                    <div class="space-y-3">
                        @forelse($event->tikets as $tiket)
                            <div class="border border-gray-200 rounded-xl p-4 bg-gray-50 flex justify-between items-center transition-all hover:border-gray-300">
                                <div>
                                    <p class="text-sm font-bold text-gray-800 capitalize">{{ $tiket->nama_tiket }}</p>
                                    <p class="text-xs text-gray-400">Stok sisa: <span class="font-semibold text-gray-600">{{ $tiket->stok }}</span></p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-extrabold text-indigo-600">
                                        {{ $tiket->harga == 0 ? 'Gratis' : 'IDR ' . number_format($tiket->harga, 0, ',', '.') }}
                                    </p>
                                </div>
                            </div>
                        @empty
                            <div class="py-4 text-center text-gray-400 text-xs">
                                Tidak ada tiket yang terdaftar untuk event ini.
                            </div>
                        @endforelse
                    </div>

                    @if(\Carbon\Carbon::parse($event->tanggal_waktu)->isFuture() && $event->tikets->sum('stok') > 0)
                        <button class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded-xl shadow-sm transition-colors text-center text-sm mt-2">
                            🎟️ Pesan Tiket Sekarang
                        </button>
                    @else
                        <button disabled class="w-full bg-gray-200 text-gray-400 font-bold py-3 px-4 rounded-xl text-center text-sm mt-2 cursor-not-allowed">
                            🔒 Penjualan Ditutup
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <div class="mt-12 border-t border-gray-100 pt-10">
            <div class="mb-6">
                <h3 class="text-xl font-bold text-gray-800">Event Terkait</h3>
                <p class="text-sm text-gray-500">Event menarik lainnya di kategori yang sama.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
                @forelse($relatedEvents as $related)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden flex flex-col transition-all hover:shadow-md">
                        <img src="{{ asset('storage/' . $related->gambar) }}" alt="{{ $related->judul }}" class="w-full h-40 object-cover">
                        <div class="p-4 flex flex-col flex-grow space-y-2">
                            <span class="text-xs font-semibold text-blue-600 uppercase">{{ $related->kategori->nama ?? 'Umum' }}</span>
                            <h4 class="font-bold text-sm text-gray-800 line-clamp-2 flex-grow">{{ $related->judul }}</h4>
                            <p class="text-xs text-gray-400 flex items-center gap-1">📍 {{ $related->lokasi }}</p>
                            <a href="{{ route('events.show', $related->id) }}" class="text-xs font-bold text-center text-indigo-600 bg-indigo-50 hover:bg-indigo-100 py-2 rounded-lg transition-colors mt-2">
                                Lihat Detail
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full py-6 text-center text-gray-400 text-sm">
                        Tidak ada event terkait lainnya saat ini.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
