@extends('layouts.app')

@section('judul', 'Detail Pengguna')

@section('konten')

    <div class="mx-auto max-w-2xl">
        <x-ui.kepala-halaman
            judul="Detail Pengguna"
            :kembali="route('pengguna.index')"
            kembali-label="Kembali ke daftar pengguna"/>

        <x-ui.kartu padat>
            <div class="flex flex-wrap items-center gap-4 border-b border-slate-100 px-5 py-5 sm:px-6">
                <x-ui.avatar :nama="$pengguna->name" ukuran="lg"
                             :warna="$pengguna->isAdmin() ? 'brand' : 'netral'"/>

                <div class="min-w-0 flex-1">
                    <h2 class="truncate text-lg font-semibold text-navy-900">{{ $pengguna->name }}</h2>
                    <p class="truncate text-sm text-slate-500">{{ $pengguna->email }}</p>

                    <div class="mt-2.5 flex flex-wrap items-center gap-2">
                        <x-ui.lencana-role :role="$pengguna->role"/>
                        <x-ui.lencana :warna="$pengguna->aktif ? 'hijau' : 'merah'">
                            {{ $pengguna->aktif ? 'Aktif' : 'Tidak Aktif' }}
                        </x-ui.lencana>
                        @if ($pengguna->harus_ganti_password)
                            <x-ui.lencana warna="kuning">Password sementara</x-ui.lencana>
                        @endif
                    </div>
                </div>
            </div>

            <dl class="divide-y divide-slate-100 text-sm">
                @foreach ([
                    'Username' => $pengguna->username,
                    'Email' => $pengguna->email,
                    'Role' => $pengguna->labelRole(),
                    'Status' => $pengguna->aktif ? 'Aktif' : 'Tidak Aktif',
                    'Terakhir login' => $pengguna->last_login_at?->translatedFormat('d F Y, H:i') ?? 'Belum pernah masuk',
                    'Dibuat pada' => $pengguna->created_at?->translatedFormat('d F Y, H:i') ?? '—',
                ] as $label => $nilai)
                    <div class="grid gap-1 px-5 py-3.5 sm:grid-cols-3 sm:px-6">
                        <dt class="text-slate-500">{{ $label }}</dt>
                        <dd class="font-medium text-navy-900 sm:col-span-2">{{ $nilai }}</dd>
                    </div>
                @endforeach
            </dl>

            <div class="flex flex-wrap items-center justify-end gap-3 border-t border-slate-100 bg-slate-50/60 px-5 py-4 sm:px-6">
                @can('resetPassword', $pengguna)
                    <x-ui.tombol varian="sekunder" :href="route('pengguna.reset-password', $pengguna)">
                        <x-ikon nama="kunci" class="size-4"/>
                        Reset Password
                    </x-ui.tombol>
                @endcan

                <x-ui.tombol :href="route('pengguna.edit', $pengguna)">
                    <x-ikon nama="pensil" class="size-4"/>
                    Edit Pengguna
                </x-ui.tombol>
            </div>
        </x-ui.kartu>
    </div>

@endsection
