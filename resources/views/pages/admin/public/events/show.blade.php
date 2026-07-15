<div class="mt-12 border-t border-gray-100 pt-10">
    <div class="mb-6">
        <h3 class="text-xl font-bold text-gray-800">Event Terkait</h3>
        <p class="text-sm text-gray-500">Event menarik lainnya di kategori yang sama.</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
        @forelse($relatedEvents as $related)
            <x-event-card :event="$related" />
        @empty
            <div class="col-span-full py-6 text-center text-gray-400 text-sm">
                Tidak ada event terkait lainnya saat ini.
            </div>
        @endforelse
    </div>
</div>