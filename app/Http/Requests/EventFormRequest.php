<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EventFormRequest extends FormRequest
{
    /**
     * Task 3.4: Authorize Method
     * Hanya mengizinkan user dengan role 'admin' yang bisa mengakses form ini.
     */
    public function authorize(): bool
    {
        // Memastikan user sudah login dan memiliki kolom role bernilai 'admin'
        return auth()->check() && auth()->user()->role === 'admin';
    }

    /**
     * Task 3.2: Define Validation Rules
     * Aturan validasi untuk data Event dan data Tiket (array).
     */
    public function rules(): array
    {
        return [
            // Rules untuk Event
            'judul'         => 'required|string|max:255',
            'deskripsi'     => 'required|string',
            'lokasi'        => 'required|string|max:255',
            'kategori_id'   => 'required|exists:kategoris,id',
            'tanggal_waktu' => 'required|date|after:now',
            'gambar'        => 'nullable|image|mimes:jpg,jpeg,png|max:2048',

            // Rules untuk Tiket (Nested Array)
            'tikets'                => 'required|array|min:1',
            // 🔄 DIUBAH: Mengubah 'tipe' menjadi 'nama_tiket' sesuai dengan input name di blade dan controller
            'tikets.*.nama_tiket'   => 'required|in:reguler,premium', 
            'tikets.*.harga'        => 'required|numeric|min:0',
            // 🔄 DIUBAH: Mengubah 'stok' menjadi 'kuota' sesuai dengan nama kolom database dan input name
            'tikets.*.kuota'        => 'required|integer|min:0', 
            'tikets.*.id'           => 'nullable|exists:tikets,id', 
        ];
    }

    /**
     * Task 3.3: Define Custom Messages
     * Kustomisasi pesan kesalahan dalam Bahasa Indonesia.
     */
    public function messages(): array
    {
        return [
            // Pesan Error untuk Event
            'judul.required'         => 'Judul event wajib diisi.',
            'judul.string'           => 'Judul event harus berupa teks.',
            'judul.max'              => 'Judul event tidak boleh lebih dari 255 karakter.',
            'deskripsi.required'     => 'Deskripsi event wajib diisi.',
            'lokasi.required'        => 'Lokasi event wajib diisi.',
            'lokasi.max'             => 'Lokasi event tidak boleh lebih dari 255 karakter.',
            'kategori_id.required'   => 'Kategori event wajib dipilih.',
            'kategori_id.exists'     => 'Kategori yang dipilih tidak valid.',
            'tanggal_waktu.required' => 'Tanggal & waktu event wajib diisi.',
            'tanggal_waktu.date'     => 'Format tanggal & waktu tidak valid.',
            'tanggal_waktu.after'    => 'Tanggal & waktu event harus di masa depan (setelah waktu sekarang).',
            'gambar.image'           => 'Berkas harus berupa gambar.',
            'gambar.mimes'           => 'Format gambar yang diperbolehkan hanya JPG, JPEG, atau PNG.',
            'gambar.max'             => 'Ukuran gambar maksimal adalah 2MB.',

            // Pesan Error untuk Array Tiket
            'tikets.required'        => 'Minimal harus ada satu tiket yang ditambahkan.',
            'tikets.array'           => 'Format data tiket tidak valid.',
            'tikets.min'             => 'Minimal harus menambahkan 1 jenis tiket.',
            // 🔄 DIUBAH: Menyesuaikan pesan error ke field 'nama_tiket'
            'tikets.*.nama_tiket.required' => 'Tipe tiket wajib dipilih.',
            'tikets.*.nama_tiket.in'       => 'Tipe tiket harus berupa reguler atau premium.',
            'tikets.*.harga.required'=> 'Harga tiket wajib diisi.',
            'tikets.*.harga.numeric' => 'Harga tiket harus berupa angka.',
            'tikets.*.harga.min'     => 'Harga tiket tidak boleh kurang dari 0.',
            // 🔄 DIUBAH: Menyesuaikan pesan error ke field 'kuota'
            'tikets.*.kuota.required' => 'Stok tiket wajib diisi.',
            'tikets.*.kuota.integer'  => 'Stok tiket harus berupa bilangan bulat.',
            'tikets.*.kuota.min'      => 'Stok tiket tidak boleh kurang dari 0.',
            'tikets.*.id.exists'     => 'ID tiket yang akan diperbarui tidak valid.',
        ];
    }
}