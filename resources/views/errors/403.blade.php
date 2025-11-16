@extends('layouts.admin')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-gray-50 to-gray-100">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-xl p-8 text-center">
        <div class="mb-6">
            <div class="w-24 h-24 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-shield-alt text-red-600 text-4xl"></i>
            </div>
            <h1 class="text-4xl font-bold text-gray-900 mb-2">403</h1>
            <h2 class="text-2xl font-semibold text-gray-700 mb-4">Erişim Reddedildi</h2>
        </div>

        <div class="mb-6">
            <p class="text-gray-600 text-lg mb-2">
                {{ $message ?? 'Bu işlem için yetkiniz bulunmuyor.' }}
            </p>
            <p class="text-gray-500 text-sm">
                Bu sayfaya veya işleme erişim yetkiniz bulunmamaktadır.
            </p>
        </div>

        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ url()->previous() }}"
               class="px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium transition-colors duration-200">
                <i class="fas fa-arrow-left mr-2"></i>
                Geri Dön
            </a>
            <a href="{{ route('dashboard') }}"
               class="px-6 py-3 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white rounded-lg font-medium transition-all duration-200 shadow-lg hover:shadow-xl">
                <i class="fas fa-home mr-2"></i>
                Ana Sayfaya Dön
            </a>
        </div>

        <div class="mt-8 pt-6 border-t border-gray-200">
            <p class="text-xs text-gray-400">
                Eğer bu hatayı sürekli görüyorsanız, lütfen sistem yöneticinizle iletişime geçin.
            </p>
        </div>
    </div>
</div>
@endsection

