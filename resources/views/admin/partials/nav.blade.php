@php
    $isAdmin = Auth::user()->isAdmin();
    $isStaff = Auth::user()->isStaff();
@endphp

<div class="mb-6 flex items-center gap-2 text-sm">
    <a href="{{ route('admin.dashboard') }}"
       class="px-4 py-2 rounded-lg font-medium {{ request()->routeIs('admin.dashboard') ? 'bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300' }}">
        Dashboard
    </a>
    @if ($isAdmin)
        <a href="{{ route('admin.audio.index') }}"
           class="px-4 py-2 rounded-lg font-medium {{ request()->routeIs('admin.audio.*') ? 'bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300' }}">
            Manajemen Audio
        </a>
    @endif
    <a href="{{ route('admin.schedules') }}"
       class="px-4 py-2 rounded-lg font-medium {{ request()->routeIs('admin.schedules*') ? 'bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300' }}">
        Jadwal Bell
    </a>
    <a href="{{ route('admin.school-days') }}"
       class="px-4 py-2 rounded-lg font-medium {{ request()->routeIs('admin.school-days') ? 'bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300' }}">
        Hari Sekolah
    </a>
    @if ($isAdmin)
        <a href="{{ route('admin.playlists.index') }}"
           class="px-4 py-2 rounded-lg font-medium {{ request()->routeIs('admin.playlists*') ? 'bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300' }}">
            Playlist
        </a>
        <a href="{{ route('admin.users.index') }}"
           class="px-4 py-2 rounded-lg font-medium {{ request()->routeIs('admin.users*') ? 'bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300' }}">
            User
        </a>
    @endif
</div>
