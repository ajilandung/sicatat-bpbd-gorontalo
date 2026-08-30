@props(['role', 'label' => null])

@php
    use App\Models\User;

    // Warna peran dipetakan sekali di sini supaya sama di tabel, detail, dan topbar.
    [$gaya, $ikon] = match ($role) {
        User::ROLE_ADMIN => ['bg-navy-100 text-navy-800 ring-navy-700/25', 'perisai'],
        User::ROLE_PIMPINAN => ['bg-air-50 text-air-800 ring-air-600/20', 'mata'],
        default => ['bg-slate-100 text-slate-700 ring-slate-500/20', 'users'],
    };

    $teks = $label ?? (User::daftarRole()[$role] ?? 'Petugas');
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium ring-1 ring-inset '.$gaya]) }}>
    <x-ikon :nama="$ikon" class="size-3.5"/>
    {{ $teks }}
</span>
