<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Kategori;
use App\Models\Ticket;
use App\Http\Requests\EventFormRequest; // Asumsi request validation dibuat terpisah
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class EventController extends Controller
{
    /**
     * Task 4.1: EventController - Index Method
     */
    public function index(Request $request)
    {
        // 1. Load events dengan relationship kategori dan tikets
        $query = Event::with(['kategori', 'tikets']);

        // 2. Filter by kategori_id jika parameter ada
        if ($request->has('kategori_id') && $request->kategori_id != '') {
            $query->where('kategori_id', $request->kategori_id);
        }

        // 3. Search by judul atau lokasi jika parameter search ada
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('judul', 'like', "%{$search}%")
                  ->orWhere('lokasi', 'like', "%{$search}%");
            });
        }

        // 4. Sort by tanggal_waktu (asc/desc) - Default: asc
        $sortOrder = $request->get('sort', 'asc');
        $query->orderBy('tanggal_waktu', $sortOrder);

        // 5. Paginate dengan 10 items per page
        $events = $query->paginate(10)->withQueryString();

        return view('pages.admin.events.index', compact('events'));
    }

    /**
     * Task 4.2: EventController - Create & Store
     */
    public function create()
    {
        // Ambil semua kategori
        $kategoris = Kategori::all();
        
        // Return view pages.admin.events.create
        return view('pages.admin.events.create', compact('kategoris'));
    }

    public function store(EventFormRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->validated();

            // Handle image upload
            if ($request->hasFile('gambar')) {
                // Simpan ke storage/app/public/events
                $data['gambar'] = $request->file('gambar')->store('events', 'public');
            } else {
                // Jika tidak, gunakan default konser.jpg
                $data['gambar'] = 'events/konser.jpg'; 
            }

            // Create event dengan data dari form
            $event = Event::create($data);

            // Create tickets (loop through $request->tikets)
            if ($request->has('tikets')) {
                foreach ($request->tikets as $tiketData) {
                    $event->tikets()->create([
                        'nama_tiket' => $tiketData['nama_tiket'],
                        'harga' => $tiketData['harga'],
                        'kuota' => $tiketData['kuota'],
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('admin.events.index')->with('success', 'Event berhasil ditambahkan!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Gagal menambahkan event: ' . $e->getMessage());
        }
    }

    /**
     * Task 4.3: EventController - Edit & Update
     */
    public function edit(Event $event)
    {
        // Load event dan kategoris
        $kategoris = Kategori::all();
        
        // Load tikets dari event (Eager loading jika belum ter-load)
        $event->load('tikets');
        
        // Cek $event->hasSales() dan pass ke view
        // Catatan: Pastikan method hasSales() sudah terdefinisi di Model Event Anda
        $hasSales = $event->hasSales();

        return view('pages.admin.events.edit', compact('event', 'kategoris', 'hasSales'));
    }

    public function update(EventFormRequest $request, Event $event)
    {
        DB::beginTransaction();
        try {
            $data = $request->validated();

            // Jika event sudah terjual (hasSales())
            if ($event->hasSales()) {
                // Tampilkan error jika tanggal_waktu berubah
                if ($request->tanggal_waktu != $event->tanggal_waktu) {
                    return redirect()->back()->withInput()->with('error', 'Tanggal & waktu tidak dapat diubah karena tiket sudah terjual!');
                }
            }

            // Handle image update (hapus old image jika ada dan bukan default)
            if ($request->hasFile('gambar')) {
                if ($event->gambar && $event->gambar !== 'events/konser.jpg') {
                    Storage::disk('public')->delete($event->gambar);
                }
                $data['gambar'] = $request->file('gambar')->store('events', 'public');
            }

            // Update event data
            $event->update($data);

            // Handle tickets
            if ($request->has('tikets')) {
                $submittedTicketIds = [];

                foreach ($request->tikets as $tiketData) {
                    if (isset($tiketData['id'])) {
                        // Update existing tickets
                        $ticket = Ticket::findOrFail($tiketData['id']);
                        $ticket->update([
                            'nama_tiket' => $tiketData['nama_tiket'],
                            'harga' => $tiketData['harga'],
                            'kuota' => $tiketData['kuota'],
                        ]);
                        $submittedTicketIds[] = $ticket->id;
                    } else {
                        // Create new tickets
                        $newTicket = $event->tikets()->create([
                            'nama_tiket' => $tiketData['nama_tiket'],
                            'harga' => $tiketData['harga'],
                            'kuota' => $tiketData['kuota'],
                        ]);
                        $submittedTicketIds[] = $newTicket->id;
                    }
                }

                // Delete removed tickets (hanya jika belum ada penjualan)
                if (!$event->hasSales()) {
                    $event->tikets()->whereNotIn('id', $submittedTicketIds)->delete();
                }
            }

            DB::commit();
            return redirect()->route('admin.events.index')->with('success', 'Event berhasil diperbarui!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui event: ' . $e->getMessage());
        }
    }

    /**
     * Task 4.4: EventController - Destroy
     */
    public function destroy(Event $event)
    {
        // Cek apakah event memiliki penjualan
        if ($event->hasSales()) {
            // Jika ya, return dengan error message
            return redirect()->back()->with('error', 'Event tidak dapat dihapus karena tiket sudah ada yang terjual!');
        }

        // Hapus image dari storage (jika bukan default)
        if ($event->gambar && $event->gambar !== 'events/konser.jpg') {
            Storage::disk('public')->delete($event->gambar);
        }

        // Delete event (tickets akan terhapus via cascade di database level)
        $event->delete();

        return redirect()->route('admin.events.index')->with('success', 'Event berhasil dihapus!');
    }

    /**
     * Task 4.5: EventController - Show (Public)
     */
    public function show(Event $event)
    {
        // 1. Detail event dengan relationships
        $event->load(['kategori', 'tikets']);

        // 2. Related events (kategori sama, tanggal > sekarang, max 4 events, kecualikan event saat ini)
        $relatedEvents = Event::where('kategori_id', $event->kategori_id)
            ->where('id', '!=', $event->id)
            ->where('tanggal_waktu', '>', now())
            ->orderBy('tanggal_waktu', 'asc')
            ->take(4)
            ->get();

        return view('pages.public.events.show', compact('event', 'relatedEvents'));
    }
}