<!DOCTYPE html>
<html lang="lv" class="h-full bg-slate-50 text-slate-800">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="theme-color" content="#ffffff">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Plānotājs">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Plānotājs') }}</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#fffbeb',
                            100: '#fef3c7',
                            400: '#fbbf24',
                            500: '#f59e0b',
                            600: '#d97706',
                        }
                    }
                }
            }
        }
    </script>

    <!-- HTMX 2.0 & Alpine.js -->
    <script src="https://unpkg.com/htmx.org@2.0.4"></script>
    <script defer src="https://unpkg.com/alpinejs@3.14.8/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 9999px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        .tap-highlight-transparent { -webkit-tap-highlight-color: transparent; }
    </style>
</head>
<body class="h-full font-sans antialiased flex flex-col bg-slate-50 text-slate-800 selection:bg-amber-400 selection:text-slate-950 pb-20 md:pb-0"
      x-data="{ mobileTab: 'ideas', showAddModal: false, showTenantModal: false }">

    <!-- Top Navigation Bar -->
    <header class="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-slate-200/90 px-4 py-3 sm:px-6 shadow-sm">
        <div class="max-w-7xl mx-auto flex items-center justify-between gap-4">
            
            <!-- Left: Workspace Switcher Dropdown -->
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" type="button" class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200/80 border border-slate-200 transition text-sm font-bold text-slate-800">
                    <span class="text-base">💼</span>
                    <span class="truncate max-w-[140px] sm:max-w-[200px] text-amber-700">{{ $tenant->name ?? 'Darbavieta' }}</span>
                    <svg class="w-4 h-4 text-slate-500 transition" :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>

                <!-- Dropdown Menu -->
                <div x-show="open" @click.outside="open = false" x-cloak
                     class="absolute left-0 mt-2 w-64 bg-white border border-slate-200 rounded-2xl shadow-xl p-2 z-50">
                    <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400 px-3 py-1.5">Tavas Darbavietas</div>
                    @if(auth()->check())
                        @foreach(auth()->user()->tenants as $t)
                            <form action="{{ route('tenants.switch', $t->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full text-left flex items-center justify-between px-3 py-2 rounded-xl text-sm transition {{ $tenant->id === $t->id ? 'bg-amber-50 text-amber-900 font-bold border border-amber-200' : 'text-slate-700 hover:bg-slate-100' }}">
                                    <span class="truncate">{{ $t->name }}</span>
                                    @if($tenant->id === $t->id)
                                        <span class="text-amber-600 text-xs">✓ Aktīva</span>
                                    @endif
                                </button>
                            </form>
                        @endforeach
                    @endif
                    <div class="border-t border-slate-100 my-1"></div>
                    <button @click="open = false; showTenantModal = true" class="w-full text-left flex items-center gap-2 px-3 py-2 text-xs font-bold text-amber-600 hover:bg-amber-50 rounded-xl transition">
                        <span>➕</span> Izveidot jaunu darbavietu
                    </button>
                </div>
            </div>

            <!-- Center: Team Member Avatars & Quick Switcher -->
            <div class="hidden sm:flex items-center gap-1.5 bg-slate-100 p-1.5 rounded-2xl border border-slate-200">
                <span class="text-xs text-slate-500 font-medium pl-2 pr-1">Komanda:</span>
                @if(isset($tenant))
                    @foreach($tenant->users as $member)
                        <a href="{{ route('login.quick', $member->id) }}"
                           title="Pārslēgties uz {{ $member->name }}"
                           class="flex items-center gap-1.5 px-2.5 py-1 rounded-xl text-xs font-medium transition {{ auth()->id() === $member->id ? 'bg-amber-500 text-slate-950 font-bold shadow-sm' : 'text-slate-700 hover:bg-slate-200' }}">
                            <span>{{ $member->avatar ?? '👤' }}</span>
                            <span>{{ explode(' ', $member->name)[0] }}</span>
                        </a>
                    @endforeach
                @endif
            </div>

            <!-- Right: Current User & Logout -->
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-2 text-right">
                    <span class="text-xl">{{ auth()->user()->avatar ?? '👤' }}</span>
                    <span class="hidden md:inline text-xs font-bold text-slate-800">{{ auth()->user()->name }}</span>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" title="Iziet" class="p-2 rounded-xl bg-slate-100 hover:bg-rose-50 hover:text-rose-600 text-slate-500 border border-slate-200 transition text-xs">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    </button>
                </form>
            </div>
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="flex-1 max-w-7xl w-full mx-auto p-4 sm:p-6 lg:p-8">
        @yield('content')
    </main>

    <!-- Mobile Bottom Navigation Bar -->
    <nav class="md:hidden fixed bottom-0 left-0 right-0 z-40 bg-white/95 backdrop-blur-lg border-t border-slate-200 px-4 py-2 flex items-center justify-around shadow-2xl">
        <!-- Tab 1: Tasks -->
        <button @click="mobileTab = 'ideas'"
                class="flex flex-col items-center gap-1 py-1 px-3 rounded-xl tap-highlight-transparent transition"
                :class="mobileTab === 'ideas' ? 'text-amber-600 font-bold' : 'text-slate-500 hover:text-slate-800'">
            <span class="text-xl">💡</span>
            <span class="text-[10px]">Uzdevumi</span>
        </button>

        <!-- Tab 2: Plan -->
        <button @click="mobileTab = 'weekend'"
                class="flex flex-col items-center gap-1 py-1 px-3 rounded-xl tap-highlight-transparent transition"
                :class="mobileTab === 'weekend' ? 'text-amber-600 font-bold' : 'text-slate-500 hover:text-slate-800'">
            <span class="text-xl">📅</span>
            <span class="text-[10px]">Plāns</span>
        </button>

        <!-- Big Central Add Button -->
        <button @click="showAddModal = true"
                class="flex items-center justify-center w-12 h-12 -mt-5 rounded-full bg-gradient-to-tr from-amber-500 to-amber-400 text-slate-950 font-extrabold shadow-lg shadow-amber-500/30 active:scale-95 transition tap-highlight-transparent">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
        </button>

        <!-- Tab 3: Quick Switch Member -->
        <div class="relative" x-data="{ memberMenu: false }">
            <button @click="memberMenu = !memberMenu"
                    class="flex flex-col items-center gap-1 py-1 px-3 rounded-xl tap-highlight-transparent text-slate-500 hover:text-slate-800">
                <span class="text-xl">{{ auth()->user()->avatar ?? '👥' }}</span>
                <span class="text-[10px] font-semibold">{{ explode(' ', auth()->user()->name)[0] }}</span>
            </button>

            <!-- Member Switcher Popup -->
            <div x-show="memberMenu" @click.outside="memberMenu = false" x-cloak
                 class="absolute bottom-14 right-0 w-48 bg-white border border-slate-200 rounded-2xl shadow-xl p-2 z-50">
                <div class="text-[10px] font-bold uppercase text-slate-400 px-2 py-1">Pārslēgt lietotāju:</div>
                @if(isset($tenant))
                    @foreach($tenant->users as $member)
                        <a href="{{ route('login.quick', $member->id) }}" class="flex items-center gap-2 px-2.5 py-1.5 text-xs rounded-xl text-slate-700 hover:bg-slate-100 transition {{ auth()->id() === $member->id ? 'bg-amber-50 text-amber-800 font-bold' : '' }}">
                            <span>{{ $member->avatar }}</span>
                            <span>{{ $member->name }}</span>
                        </a>
                    @endforeach
                @endif
            </div>
        </div>
    </nav>

    <!-- Slide-Up Bottom Sheet Modal for Adding Tasks -->
    <div x-show="showAddModal" x-cloak class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4 bg-slate-900/40 backdrop-blur-sm">
        <div @click.outside="showAddModal = false"
             x-show="showAddModal"
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="translate-y-full sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="translate-y-0 sm:scale-100"
             x-transition:leave="transition ease-in duration-200 transform"
             x-transition:leave-start="translate-y-0 sm:scale-100"
             x-transition:leave-end="translate-y-full sm:translate-y-0 sm:scale-95"
             class="w-full max-w-lg bg-white border border-slate-200 rounded-t-3xl sm:rounded-3xl p-6 shadow-2xl">
            
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <span class="text-2xl">✨</span>
                    <h3 class="text-lg font-bold text-slate-900">Pievienot jaunu uzdevumu</h3>
                </div>
                <button @click="showAddModal = false" class="p-1.5 text-slate-400 hover:text-slate-600 rounded-full hover:bg-slate-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <form action="{{ route('ideas.store') }}" method="POST"
                  hx-post="{{ route('ideas.store') }}"
                  hx-target="body"
                  @htmx:after-request="showAddModal = false"
                  class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Uzdevuma nosaukums</label>
                    <input type="text" name="title" required autofocus placeholder="Piem., Sagatavot atskaiti, Servera atjauninājumi..."
                           class="w-full px-4 py-3 bg-slate-50 border border-slate-300 focus:border-amber-500 focus:bg-white focus:ring-1 focus:ring-amber-500 rounded-xl text-slate-900 placeholder-slate-400 text-sm">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Kategorija</label>
                    <div class="grid grid-cols-3 gap-2 text-xs">
                        <label class="flex items-center gap-1.5 p-2 rounded-xl bg-slate-50 border border-slate-200 cursor-pointer hover:border-blue-500/50 has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50 has-[:checked]:text-blue-900">
                            <input type="radio" name="category" value="projekti" checked class="hidden">
                            <span>💼</span> <span class="font-semibold">Projekti</span>
                        </label>
                        <label class="flex items-center gap-1.5 p-2 rounded-xl bg-slate-50 border border-slate-200 cursor-pointer hover:border-rose-500/50 has-[:checked]:border-rose-500 has-[:checked]:bg-rose-50 has-[:checked]:text-rose-900">
                            <input type="radio" name="category" value="steidzami" class="hidden">
                            <span>⚡</span> <span class="font-semibold">Steidzami</span>
                        </label>
                        <label class="flex items-center gap-1.5 p-2 rounded-xl bg-slate-50 border border-slate-200 cursor-pointer hover:border-purple-500/50 has-[:checked]:border-purple-500 has-[:checked]:bg-purple-50 has-[:checked]:text-purple-900">
                            <input type="radio" name="category" value="attistiba" class="hidden">
                            <span>🚀</span> <span class="font-semibold">Attīstība</span>
                        </label>
                        <label class="flex items-center gap-1.5 p-2 rounded-xl bg-slate-50 border border-slate-200 cursor-pointer hover:border-amber-500/50 has-[:checked]:border-amber-500 has-[:checked]:bg-amber-50 has-[:checked]:text-amber-900">
                            <input type="radio" name="category" value="sanaksmes" class="hidden">
                            <span>👥</span> <span class="font-semibold">Sanāksmes</span>
                        </label>
                        <label class="flex items-center gap-1.5 p-2 rounded-xl bg-slate-50 border border-slate-200 cursor-pointer hover:border-emerald-500/50 has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50 has-[:checked]:text-emerald-900">
                            <input type="radio" name="category" value="ikdienas" class="hidden">
                            <span>📋</span> <span class="font-semibold">Ikdienas</span>
                        </label>
                        <label class="flex items-center gap-1.5 p-2 rounded-xl bg-slate-50 border border-slate-200 cursor-pointer hover:border-slate-500/50 has-[:checked]:border-slate-600 has-[:checked]:bg-slate-100 has-[:checked]:text-slate-900">
                            <input type="radio" name="category" value="citi" class="hidden">
                            <span>✨</span> <span class="font-semibold">Citi</span>
                        </label>
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full py-3.5 bg-gradient-to-r from-amber-500 to-amber-400 hover:from-amber-400 hover:to-amber-300 text-slate-950 font-bold text-sm rounded-xl shadow-md shadow-amber-500/20 active:scale-[0.98] transition">
                        Pievienot Uzdevumu Krātuvei 💡
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Create Workspace Modal -->
    <div x-show="showTenantModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm">
        <div @click.outside="showTenantModal = false" class="w-full max-w-md bg-white border border-slate-200 rounded-3xl p-6 shadow-2xl">
            <h3 class="text-lg font-bold text-slate-900 mb-4">Izveidot jaunu darbavietu</h3>
            <form action="{{ route('tenants.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nosaukums</label>
                    <input type="text" name="name" required placeholder="Piem., Mārketinga Komanda 🚀"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 text-sm focus:border-amber-500">
                </div>
                <div class="flex gap-2 justify-end">
                    <button type="button" @click="showTenantModal = false" class="px-4 py-2 text-xs font-semibold text-slate-500 hover:text-slate-800">Atcelt</button>
                    <button type="submit" class="px-4 py-2 text-xs font-bold bg-amber-500 text-slate-950 rounded-xl hover:bg-amber-400">Izveidot</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Configure HTMX CSRF -->
    <script>
        document.body.addEventListener('htmx:configRequest', (event) => {
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            event.detail.headers['X-CSRF-TOKEN'] = token;
        });
    </script>
</body>
</html>
