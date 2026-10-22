{{-- 
    Partial Empty State:
    Bisa dipanggil dengan:
    @include('partials.empty-state', [
        'title' => 'Judul Empty State',
        'description' => 'Deskripsi...',
        'colspan' => 6, // jika di dalam table <tbody>
        'actionUrl' => route('...'),
        'actionText' => 'Tambah Data',
        'actionIcon' => 'fas fa-plus',
    ])
--}}
<x-empty-state 
    :title="$title ?? 'Belum Ada Data'"
    :description="$description ?? 'Tidak ada data yang dapat ditampilkan saat ini.'"
    :image="$image ?? asset('images/empty-box.png')"
    :imageWidth="$imageWidth ?? '130px'"
    :actionUrl="$actionUrl ?? null"
    :actionText="$actionText ?? null"
    :actionIcon="$actionIcon ?? null"
    :actionOnClick="$actionOnClick ?? null"
    :colspan="$colspan ?? null"
    :compact="$compact ?? false"
>
    {!! $slot ?? '' !!}
</x-empty-state>
