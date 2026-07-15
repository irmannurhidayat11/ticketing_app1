<x-app-layout>
    <div class="container mx-auto px-4 py-6 max-w-4xl">
        <a href="{{ route('admin.events.index') }}" class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-900 mb-6 font-medium text-sm">
            ⬅️ Kembali ke list Event
        </a>

        @if($hasSales)
            <div class="bg-amber-50 border border-amber-300 text-amber-800 p-4 rounded-xl flex items-start gap-3 mb-6 shadow-sm">
                <span class="text-lg">⚠️</span>
                <div>
                    <p class="font-bold text-sm">Peringatan:</p>
                    <p class="text-xs leading-relaxed">Event ini sudah memiliki penjualan tiket. Beberapa data yang sensitif seperti Tanggal & Waktu tidak dapat diubah, serta tiket yang terjual tidak dapat dihapus.</p>
                </div>
            </div>
        @endif

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="bg-gray-50 border-b border-gray-100 p-6">
                <h2 class="text-xl font-bold text-gray-800">Edit Event: {{ $event->judul }}</h2>
            </div>

            <form action="{{ route('admin.events.update', $event->id) }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-8">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="block">
                            <span class="text-sm font-medium text-gray-700">Judul Event</span>
                            <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="judul" value="{{ old('judul', $event->judul) }}" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('judul') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-2">
                        <label class="block">
                            <span class="text-sm font-medium text-gray-700">Kategori</span>
                            <span class="text-red-500">*</span>
                        </label>
                        <select name="kategori_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @foreach($kategoris as $kat)
                                <option value="{{ $kat->id }}" {{ old('kategori_id', $event->kategori_id) == $kat->id ? 'selected' : '' }}>{{ $kat->nama }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label class="block">
                            <span class="text-sm font-medium text-gray-700">Lokasi</span>
                            <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="lokasi" value="{{ old('lokasi', $event->lokasi) }}" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div class="space-y-2">
                        <label class="block">
                            <span class="text-sm font-medium text-gray-700">Tanggal & Waktu</span>
                            @if($hasSales)
                                <span class="text-amber-500 text-xs">(🔒 Lock - Sudah Terjual)</span>
                            @else
                                <span class="text-red-500">*</span>
                            @endif
                        </label>
                        <input type="datetime-local" name="tanggal_waktu" 
                               value="{{ old('tanggal_waktu', $event->tanggal_waktu ? $event->tanggal_waktu->format('Y-m-d\TH:i') : '') }}" 
                               required 
                               {{ $hasSales ? 'readonly' : '' }}
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 {{ $hasSales ? 'bg-gray-100' : '' }}">
                    </div>

                    <div class="space-y-2 md:col-span-2">
                        <label class="block">
                            <span class="text-sm font-medium text-gray-700">Upload Gambar</span>
                            <span class="text-gray-400 text-xs">(Kosongkan jika tidak ingin mengubah gambar)</span>
                        </label>
                        
                        <div class="mb-4 p-2 bg-gray-50 rounded-lg border border-gray-200 w-fit">
                            <p class="text-xs text-gray-500 mb-2">Gambar Saat Ini:</p>
                            <img src="{{ $event->image_url }}" alt="Current Image" class="max-h-36 rounded object-cover">
                        </div>

                        <input type="file" id="gambarInput" name="gambar" accept="image/*" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">

                        <div id="imagePreviewContainer" class="hidden mt-4 p-2 bg-gray-50 rounded-lg border border-gray-200 w-fit">
                            <p class="text-xs text-gray-500 mb-2">Preview Gambar Baru:</p>
                            <img id="imagePreview" src="#" alt="Preview" class="max-h-48 rounded object-cover">
                        </div>
                    </div>

                    <div class="space-y-2 md:col-span-2">
                        <label class="block">
                            <span class="text-sm font-medium text-gray-700">Deskripsi</span>
                            <span class="text-red-500">*</span>
                        </label>
                        <textarea name="deskripsi" rows="5" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('deskripsi', $event->deskripsi) }}</textarea>
                    </div>
                </div>

                <hr class="border-gray-100">

                <div class="space-y-6">
                    <div class="flex justify-between items-center">
                        <div>
                            <h3 class="text-lg font-bold text-gray-800">Tiket Event</h3>
                            <p class="text-xs text-gray-500">Kelola tiket yang tersedia.</p>
                        </div>
                        @if(!$hasSales)
                            <button type="button" id="btnAddTicket" class="bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-bold py-2 px-4 rounded-lg transition-colors">
                                ➕ Tambah Tiket
                            </button>
                        @endif
                    </div>

                    <div id="ticketsContainer" class="space-y-4"></div>
                </div>

                <div class="pt-4">
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg shadow-sm transition-colors text-center">
                        Simpan Perubahan Event
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // --- Image Preview Logic ---
            const gambarInput = document.getElementById('gambarInput');
            const previewContainer = document.getElementById('imagePreviewContainer');
            const previewImg = document.getElementById('imagePreview');

            gambarInput.addEventListener('change', function () {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        previewImg.src = e.target.result;
                        previewContainer.classList.remove('hidden');
                    };
                    reader.readAsDataURL(file);
                } else {
                    previewContainer.classList.add('hidden');
                }
            });

            // --- Ticket Pre-population & Dynamic Logic ---
            const ticketsContainer = document.getElementById('ticketsContainer');
            const btnAddTicket = document.getElementById('btnAddTicket');
            
            const hasSales = JSON.parse('{!! json_encode($hasSales) !!}');
            const existingTickets = JSON.parse('{!! json_encode($event->tikets) !!}');
            let ticketIndex = 0;

            function createTicketCard(ticket = null) {
                const index = ticketIndex;
                const card = document.createElement('div');
                card.className = 'ticket-card bg-gray-50 border border-gray-200 rounded-lg p-5 relative';
                card.id = `ticket-card-${index}`;

                const isExisting = ticket !== null;
                const ticketIdInput = isExisting ? `<input type="hidden" name="tikets[${index}][id]" value="${ticket.id}">` : '';
                
                const isReadonly = hasSales ? 'readonly bg-gray-100' : '';
                const isSelectDisabled = hasSales ? 'disabled' : '';

                let actionHeader = '';
                if (hasSales && isExisting) {
                    actionHeader = `<span class="bg-green-100 text-green-700 text-xs font-semibold px-2.5 py-1 rounded-full border border-green-200">🏷️ Sudah Terjual</span>`;
                } else if (!hasSales) {
                    actionHeader = `
                        <button type="button" class="btn-remove-ticket text-red-500 hover:text-red-700 text-xs font-semibold" data-index="${index}">
                            🗑️ Hapus
                        </button>
                    `;
                }

                card.innerHTML = `
                    ${ticketIdInput}
                    <div class="flex justify-between items-center mb-4">
                        <h4 class="text-sm font-bold text-gray-700">Tiket #${index + 1}</h4>
                        ${actionHeader}
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="space-y-2">
                            <label class="block">
                                <span class="text-xs font-medium text-gray-600">Tipe Tiket</span>
                                <span class="text-red-500">*</span>
                            </label>
                            ${hasSales && isExisting ? `<input type="hidden" name="tikets[${index}][nama_tiket]" value="${ticket.nama_tiket}">` : ''}
                            <select name="tikets[${index}][nama_tiket]" required ${isSelectDisabled} class="w-full border border-gray-300 rounded-lg px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="reguler" ${(ticket && ticket.nama_tiket === 'reguler') ? 'selected' : ''}>Reguler</option>
                                <option value="premium" ${(ticket && ticket.nama_tiket === 'premium') ? 'selected' : ''}>Premium</option>
                            </select>
                        </div>

                        <div class="space-y-2">
                            <label class="block">
                                <span class="text-xs font-medium text-gray-600">Harga (IDR)</span>
                                <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="tikets[${index}][harga]" min="0" required value="${ticket ? ticket.harga : ''}" ${isReadonly} placeholder="Contoh: 150000" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div class="space-y-2">
                            <label class="block">
                                <span class="text-xs font-medium text-gray-600">Kuota Stok</span>
                                <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="tikets[${index}][kuota]" min="1" required value="${ticket ? ticket.kuota : ''}" ${isReadonly} placeholder="Stok tiket" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                `;
                
                ticketIndex++;
                return card;
            }

            if (existingTickets.length > 0) {
                existingTickets.forEach(ticket => {
                    ticketsContainer.appendChild(createTicketCard(ticket));
                });
            } else {
                if (!hasSales) btnAddTicket.click();
            }

            if (btnAddTicket) {
                btnAddTicket.addEventListener('click', function () {
                    ticketsContainer.appendChild(createTicketCard());
                });
            }

            ticketsContainer.addEventListener('click', function (e) {
                if (e.target.classList.contains('btn-remove-ticket')) {
                    const indexToRemove = e.target.getAttribute('data-index');
                    const card = document.getElementById(`ticket-card-${indexToRemove}`);
                    card.remove();
                    
                    const cards = ticketsContainer.querySelectorAll('.ticket-card h4');
                    cards.forEach((h4, i) => {
                        h4.innerText = `Tiket #${i + 1}`;
                    });
                }
            });
        });
    </script>
</x-app-layout>