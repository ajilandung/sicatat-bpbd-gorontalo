@extends($wajibGanti ? 'layouts.fokus' : 'layouts.app')

@section('judul', 'Ubah Password')

@section('konten')

    <div class="mx-auto max-w-xl">

        @if ($wajibGanti)
            <div class="mb-6">
                <div class="mb-3 h-1 w-9 rounded-full bg-air-600"></div>
                <h1 class="text-xl font-semibold tracking-tight text-navy-900 sm:text-2xl">Buat Password Baru</h1>
                <p class="mt-2 text-sm leading-relaxed text-slate-600">
                    Akun Anda masih memakai password sementara dari administrator.
                    Buat password baru terlebih dahulu untuk melanjutkan ke sistem.
                </p>
            </div>
        @else
            <x-ui.kepala-halaman
                judul="Ubah Password"
                deskripsi="Password diganti sendiri oleh pemilik akun. Administrator tidak dapat melihat password Anda."/>
        @endif

        <x-ui.ringkasan-galat class="mb-6"/>

        <x-ui.kartu>
            <form method="POST" action="{{ route('password.update') }}" class="space-y-5">
                @csrf
                @method('PUT')

                <x-ui.kolom nama="password_sekarang" label="Password saat ini" wajib>
                    <x-ui.password nama="password_sekarang" autocomplete="current-password" required autofocus/>
                </x-ui.kolom>

                <div class="border-t border-slate-100 pt-5">
                    <x-ui.kolom nama="password" label="Password baru" wajib
                                petunjuk="Minimal 8 karakter dan berbeda dari password saat ini.">
                        <x-ui.password nama="password" autocomplete="new-password" required/>
                    </x-ui.kolom>
                </div>

                <x-ui.kolom nama="password_confirmation" label="Konfirmasi password baru" wajib>
                    <x-ui.password nama="password_confirmation" autocomplete="new-password" required/>
                </x-ui.kolom>

                <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:justify-end">
                    @unless ($wajibGanti)
                        <x-ui.tombol varian="sekunder" :href="route(auth()->user()->routeDashboard())">Batal</x-ui.tombol>
                    @endunless
                    <x-ui.tombol>Simpan Password</x-ui.tombol>
                </div>
            </form>
        </x-ui.kartu>
    </div>

@endsection
