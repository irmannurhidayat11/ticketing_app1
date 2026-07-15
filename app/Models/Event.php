<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

class Event extends Model
{
    use HasFactory;

    // 2.1.1: Define Fillable Fields
    protected $fillable = [
        'user_id',
        'kategori_id',
        'judul',
        'deskripsi',
        'lokasi',
        'gambar',
        'tanggal_waktu',
    ];

    // Menginstruksikan Laravel untuk memperlakukan kolom ini sebagai objek Carbon (Datetime)
    protected $casts = [
        'tanggal_waktu' => 'datetime',
    ];

    // 2.1.2: Define Relationships
    
    /**
     * Relasi One-to-Many ke model Tiket (Satu event memiliki banyak tiket)
     */
    public function tikets(): HasMany
    {
        return $this->hasMany(Tiket::class);
    }

    /**
     * Relasi Many-to-One ke model Kategori (Satu event dimiliki oleh satu kategori)
     */
    public function kategori(): BelongsTo
    {
        return $this->belongsTo(Kategori::class, 'kategori_id');
    }

    /**
     * Relasi Many-to-One ke model User/Pembuat (Satu event dimiliki oleh satu user)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi One-to-Many ke model Order (Satu event memiliki banyak transaksi/order)
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    // 2.1.3: Add Status Attribute (Accessor)
    // Mengakses status dengan memanggil: $event->status
    public function getStatusAttribute(): string
    {
        $waktuEvent = $this->tanggal_waktu;
        $sekarang = Carbon::now();

        if ($waktuEvent > $sekarang) {
            return 'Upcoming';
        }

        // Ongoing jika sedang berlangsung (dalam rentang waktu 3 jam sejak mulai)
        if ($waktuEvent <= $sekarang && $waktuEvent->copy()->addHours(3) >= $sekarang) {
            return 'Ongoing';
        }

        return 'Completed';
    }

    // 2.1.4: Add Helper Methods
    // Mengecek apakah event ini sudah memiliki penjualan tiket
    public function hasSales(): bool
    {
        return $this->orders()->exists();
    }

    // 2.1.5: Add Query Scopes
    // Digunakan untuk filter query database, contoh: Event::upcoming()->get()

    public function scopeUpcoming($query)
    {
        return $query->where('tanggal_waktu', '>', Carbon::now());
    }

    public function scopeOngoing($query)
    {
        $sekarang = Carbon::now();
        return $query->where('tanggal_waktu', '<=', $sekarang)
                     ->where('tanggal_waktu', '>=', $sekarang->copy()->subHours(3));
    }

    public function scopeCompleted($query)
    {
        return $query->where('tanggal_waktu', '<', Carbon::now()->subHours(3));
    }

    // 2.1.6: Add Image URL Accessor
    // Mengakses URL gambar dengan memanggil: $event->image_url
    public function getImageUrlAttribute(): string
    {
        $gambar = $this->gambar;

        // Jika gambarnya adalah URL eksternal yang valid (misal dari internet)
        if (filter_var($gambar, FILTER_VALIDATE_URL)) {
            return $gambar;
        }

        // Jika gambarnya ada di dalam folder local storage proyek
        if ($gambar && Storage::disk('public')->exists($gambar)) {
            return Storage::url($gambar);
        }

        // Fallback jika tidak ada gambar yang cocok
        return asset('images/konser.jpg');
    }
}