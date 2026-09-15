<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50 text-slate-800">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="theme-color" content="#ffffff">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="{{ config('app.name', 'Plan') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Plan') }}</title>

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

    <!-- HTMX 2.0, Alpine Collapse & Alpine.js -->
    <script src="https://unpkg.com/htmx.org@2.0.4"></script>
    <script defer src="https://unpkg.com/@alpinejs/collapse@3.14.8/dist/cdn.min.js"></script>
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
      x-data="{ mobileTab: 'ideas', showAddModal: false, showTenantModal: false, showCategoryModal: false }">

    <!-- Top Navigation Bar -->
    <header class="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-slate-200/90 px-4 py-3 sm:px-6 shadow-sm">
        <div class="max-w-7xl mx-auto flex items-center justify-between gap-4">
            
            <!-- Left: Workspace Switcher Dropdown & Settings Button -->
            <div class="flex items-center gap-2">
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" type="button" class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200/80 border border-slate-200 transition text-sm font-bold text-slate-800">
                        <span class="text-base">💼</span>
                        <span class="truncate max-w-[140px] sm:max-w-[200px] text-amber-700">{{ $tenant->name ?? __('app.workspace') }}</span>
                        <svg class="w-4 h-4 text-slate-500 transition" :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>

                    <!-- Dropdown Menu -->
                    <div x-show="open" @click.outside="open = false" x-cloak
                         class="absolute left-0 mt-2 w-72 bg-white border border-slate-200 rounded-2xl shadow-xl p-2 z-50">
                        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400 px-3 py-1.5">{{ __('app.your_workspaces') }}</div>
                        @if(auth()->check())
                            @foreach(auth()->user()->tenants as $t)
                                <form action="{{ route('tenants.switch', $t->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="w-full text-left flex items-center justify-between px-3 py-2 rounded-xl text-sm transition {{ $tenant->id === $t->id ? 'bg-amber-50 text-amber-900 font-bold border border-amber-200' : 'text-slate-700 hover:bg-slate-100' }}">
                                        <span class="truncate">{{ $t->name }}</span>
                                        @if($tenant->id === $t->id)
                                            <span class="text-amber-600 text-xs">✓ {{ __('app.active') }}</span>
                                        @endif
                                    </button>
                                </form>
                            @endforeach
                        @endif
                        <div class="border-t border-slate-100 my-1"></div>
                        <button hx-get="{{ route('tenants.settings') }}"
                                hx-target="#settings-modal-slot"
                                hx-swap="innerHTML"
                                @click="open = false"
                                type="button"
                                class="w-full text-left flex items-center gap-2 px-3 py-2 text-xs font-bold text-slate-700 hover:bg-amber-50 hover:text-amber-900 rounded-xl transition">
                            <span>⚙️</span> {{ __('app.workspace_and_categories') }}
                        </button>
                        <button @click="open = false; showTenantModal = true" class="w-full text-left flex items-center gap-2 px-3 py-2 text-xs font-bold text-amber-600 hover:bg-amber-50 rounded-xl transition">
                            <span>➕</span> {{ __('app.create_workspace') }}
                        </button>
                    </div>
                </div>

                <!-- Settings Quick Button -->
                <button hx-get="{{ route('tenants.settings') }}"
                        hx-target="#settings-modal-slot"
                        hx-swap="innerHTML"
                        type="button"
                        title="{{ __('app.workspace_and_categories') }}"
                        class="p-2 rounded-xl bg-slate-100 hover:bg-amber-100 hover:text-amber-900 text-slate-600 border border-slate-200 transition text-xs font-bold flex items-center gap-1.5">
                    <span>⚙️</span>
                    <span class="hidden lg:inline">{{ __('app.settings') }}</span>
                </button>
            </div>

            <!-- Center: Team Member Avatars & Quick Switcher -->
            <div class="hidden sm:flex items-center gap-1.5 bg-slate-100 p-1.5 rounded-2xl border border-slate-200">
                <span class="text-xs text-slate-500 font-medium pl-2 pr-1">{{ __('app.team') }}:</span>
                @if(isset($tenant))
                    @foreach($tenant->users as $member)
                        <a href="{{ route('login.quick', $member->id) }}"
                           title="{{ __('app.switch_user') }}: {{ $member->name }}"
                           class="flex items-center gap-1.5 px-2.5 py-1 rounded-xl text-xs font-medium transition {{ auth()->id() === $member->id ? 'bg-amber-500 text-slate-950 font-bold shadow-sm' : 'text-slate-700 hover:bg-slate-200' }}">
                            <span>{{ $member->avatar ?? '👤' }}</span>
                            <span>{{ explode(' ', $member->name)[0] }}</span>
                        </a>
                    @endforeach
                @endif
            </div>

            <!-- Right: Language Switcher, User & Logout -->
            <div class="flex items-center gap-2.5 sm:gap-3">
                
                <!-- Language Switcher (LV / EN) -->
                <div class="flex items-center bg-slate-100 p-0.5 rounded-xl border border-slate-200 text-xs font-bold">
                    <a href="{{ route('locale.switch', 'lv') }}"
                       title="Latviešu valoda"
                       class="px-2 py-1 rounded-lg transition flex items-center gap-1 {{ app()->getLocale() === 'lv' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-800' }}">
                        <span>🇱🇻</span>
                        <span class="text-[11px]">LV</span>
                    </a>
                    <a href="{{ route('locale.switch', 'en') }}"
                       title="English"
                       class="px-2 py-1 rounded-lg transition flex items-center gap-1 {{ app()->getLocale() === 'en' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-800' }}">
                        <span>🇬🇧</span>
                        <span class="text-[11px]">EN</span>
                    </a>
                </div>

                <div class="flex items-center gap-2 text-right">
                    <span class="text-xl">{{ auth()->user()->avatar ?? '👤' }}</span>
                    <span class="hidden md:inline text-xs font-bold text-slate-800">{{ auth()->user()->name }}</span>
                </div>
                
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" title="{{ __('app.logout') }}" class="p-2 rounded-xl bg-slate-100 hover:bg-rose-50 hover:text-rose-600 text-slate-500 border border-slate-200 transition text-xs">
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
            <span class="text-[10px]">{{ __('app.task_backlog') }}</span>
        </button>

        <!-- Tab 2: Plan -->
        <button @click="mobileTab = 'weekend'"
                class="flex flex-col items-center gap-1 py-1 px-3 rounded-xl tap-highlight-transparent transition"
                :class="mobileTab === 'weekend' ? 'text-amber-600 font-bold' : 'text-slate-500 hover:text-slate-800'">
            <span class="text-xl">📅</span>
            <span class="text-[10px]">{{ __('app.daily_plan') }}</span>
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
                 class="absolute bottom-14 right-0 w-52 bg-white border border-slate-200 rounded-2xl shadow-xl p-2 z-50 space-y-1">
                <div class="text-[10px] font-bold uppercase text-slate-400 px-2 py-1">{{ __('app.switch_user') }}:</div>
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
                    <h3 class="text-lg font-bold text-slate-900">{{ __('app.add_new_task') }}</h3>
                </div>
                <button @click="showAddModal = false" class="p-1.5 text-slate-400 hover:text-slate-600 rounded-full hover:bg-slate-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <form action="{{ route('ideas.store') }}" method="POST"
                  enctype="multipart/form-data"
                  hx-encoding="multipart/form-data"
                  hx-post="{{ route('ideas.store') }}"
                  hx-target="body"
                  @htmx:after-request="showAddModal = false"
                  class="space-y-4"
                  x-data="{ formLinks: [''] }">
                @csrf

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('app.task_title') }}</label>
                    <input type="text" name="title" required autofocus placeholder="{{ __('app.task_title_placeholder') }}"
                           class="w-full px-4 py-3 bg-slate-50 border border-slate-300 focus:border-amber-500 focus:bg-white focus:ring-1 focus:ring-amber-500 rounded-xl text-slate-900 placeholder-slate-400 text-sm">
                </div>

                <!-- Optional Description -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('app.description') }} ({{ __('app.optional') }})</label>
                    <textarea name="description" rows="2" placeholder="{{ __('app.description_placeholder') }}"
                              class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 focus:border-amber-500 focus:bg-white rounded-xl text-slate-900 text-xs resize-none"></textarea>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-bold text-slate-700">{{ __('app.category') }}</label>
                        <button type="button" @click="showCategoryModal = true" class="text-[11px] font-bold text-amber-700 hover:text-amber-800 flex items-center gap-1">
                            <span>➕</span> {{ __('app.new_category') }}
                        </button>
                    </div>
                    <div class="grid grid-cols-3 gap-2 text-xs max-h-36 overflow-y-auto p-0.5">
                        @if(isset($tenant) && $tenant->categories)
                            @foreach($tenant->categories as $idx => $cat)
                                <label class="flex items-center gap-1.5 p-2 rounded-xl bg-slate-50 border border-slate-200 cursor-pointer hover:border-amber-400 has-[:checked]:border-amber-500 has-[:checked]:bg-amber-50 has-[:checked]:text-amber-950 font-semibold truncate transition">
                                    <input type="radio" name="category_id" value="{{ $cat->id }}" {{ $idx === 0 ? 'checked' : '' }} class="hidden">
                                    <span class="text-sm flex-shrink-0">{{ $cat->emoji }}</span>
                                    <span class="truncate">{{ $cat->name }}</span>
                                </label>
                            @endforeach
                        @else
                            <label class="flex items-center gap-1.5 p-2 rounded-xl bg-slate-50 border border-slate-200 cursor-pointer hover:border-amber-400 has-[:checked]:border-amber-500 has-[:checked]:bg-amber-50 has-[:checked]:text-amber-950 font-semibold truncate transition">
                                <input type="radio" name="category" value="citi" checked class="hidden">
                                <span class="text-sm flex-shrink-0">✨</span>
                                <span class="truncate">{{ __('app.all') }}</span>
                            </label>
                        @endif
                    </div>
                </div>

                <!-- Dynamic Links & YouTube Video Input -->
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-bold text-slate-700">{{ __('app.links_and_youtube') }}</label>
                        <span class="text-[11px] text-slate-400">{{ __('app.links_hint') }}</span>
                    </div>
                    
                    <div class="space-y-2">
                        <template x-for="(link, index) in formLinks" :key="index">
                            <div class="flex items-center gap-2">
                                <span class="text-slate-400 text-xs">🔗</span>
                                <input type="text" :name="'links[' + index + ']'" x-model="formLinks[index]" placeholder="{{ __('app.links_placeholder') }}"
                                       class="flex-1 px-3 py-2 bg-slate-50 border border-slate-300 focus:border-amber-500 focus:bg-white rounded-xl text-slate-900 text-xs">
                                <button type="button" @click="formLinks.splice(index, 1)" class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-rose-50 text-xs" title="{{ __('app.remove_image') }}">✕</button>
                            </div>
                        </template>
                    </div>

                    <button type="button" @click="formLinks.push('')"
                            class="text-xs font-bold text-amber-700 hover:text-amber-800 flex items-center gap-1 py-0.5">
                        <span>➕</span> {{ __('app.add_another_link') }}
                    </button>
                </div>

                <!-- Optional Date -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('app.schedule_date_optional') }}</label>
                    <input type="date" name="scheduled_date"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 focus:border-amber-500 focus:bg-white rounded-xl text-slate-900 text-xs">
                </div>

                <!-- Optional Image Upload -->
                <div x-data="{ imagePreview: null }">
                    <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('app.add_image') }}</label>
                    <div class="flex items-center gap-3">
                        <label class="flex-1 flex items-center justify-center gap-2 px-4 py-2 bg-slate-50 border border-dashed border-slate-300 hover:border-amber-500 rounded-xl cursor-pointer text-xs font-semibold text-slate-600 hover:text-slate-900 transition">
                            <span>📷</span>
                            <span x-text="imagePreview ? '{{ __('app.change_image') }}' : '{{ __('app.upload_image') }}'"></span>
                            <input type="file" name="image_file" accept="image/*" class="hidden"
                                   @change="const file = $event.target.files[0]; if (file) { const reader = new FileReader(); reader.onload = (e) => { imagePreview = e.target.result; }; reader.readAsDataURL(file); }">
                        </label>
                        <template x-if="imagePreview">
                            <div class="relative w-12 h-12 rounded-xl overflow-hidden border border-slate-200 shadow-sm flex-shrink-0">
                                <img :src="imagePreview" class="w-full h-full object-cover">
                                <button type="button" @click="imagePreview = null"
                                        class="absolute inset-0 bg-black/40 text-white flex items-center justify-center text-xs font-bold">✕</button>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full py-3.5 bg-gradient-to-r from-amber-500 to-amber-400 hover:from-amber-400 hover:to-amber-300 text-slate-950 font-bold text-sm rounded-xl shadow-md shadow-amber-500/20 active:scale-[0.98] transition">
                        {{ __('app.add_to_backlog_btn') }} 💡
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Create Workspace Modal -->
    <div x-show="showTenantModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm">
        <div @click.outside="showTenantModal = false" class="w-full max-w-md bg-white border border-slate-200 rounded-3xl p-6 shadow-2xl">
            <h3 class="text-lg font-bold text-slate-900 mb-4">{{ __('app.create_workspace') }}</h3>
            <form action="{{ route('tenants.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('app.workspace_name') }}</label>
                    <input type="text" name="name" required placeholder="Piem., Marketing Team 🚀"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 text-sm focus:border-amber-500">
                </div>
                <div class="flex gap-2 justify-end">
                    <button type="button" @click="showTenantModal = false" class="px-4 py-2 text-xs font-semibold text-slate-500 hover:text-slate-800">{{ __('app.cancel') }}</button>
                    <button type="submit" class="px-4 py-2 text-xs font-bold bg-amber-500 text-slate-950 rounded-xl hover:bg-amber-400">{{ __('app.create') }}</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Create Category Modal -->
    <div x-show="showCategoryModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
        <div @click.outside="showCategoryModal = false" class="w-full max-w-md bg-white border border-slate-200 rounded-3xl p-6 shadow-2xl space-y-4"
             x-data="{ selectedEmoji: '📁', selectedColor: 'blue' }">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <span class="text-2xl" x-text="selectedEmoji"></span>
                    <h3 class="text-lg font-bold text-slate-900">{{ __('app.create_new_category') }}</h3>
                </div>
                <button type="button" @click="showCategoryModal = false" class="p-1.5 text-slate-400 hover:text-slate-600 rounded-full hover:bg-slate-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <form action="{{ route('categories.store') }}" method="POST"
                  hx-post="{{ route('categories.store') }}"
                  hx-target="body"
                  @htmx:after-request="showCategoryModal = false"
                  class="space-y-4">
                @csrf
                
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('app.category_name') }}</label>
                    <input type="text" name="name" required placeholder="{{ __('app.category_name_placeholder') }}"
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 text-sm focus:border-amber-500 font-semibold">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">{{ __('app.icon_emoji') }}</label>
                    <input type="hidden" name="emoji" :value="selectedEmoji">
                    <div class="flex flex-wrap gap-2 text-base p-2 bg-slate-50 border border-slate-200 rounded-2xl max-h-28 overflow-y-auto">
                        <template x-for="em in ['💼', '⚡', '🚀', '👥', '📋', '🎯', '🎨', '📊', '🛠️', '💡', '💰', '🔒', '📦', '🏷️', '📢', '💻', '🍕', '✨', '📝', '🛒', '🔧', '📈', '🤝', '⚙️']">
                            <button type="button" @click="selectedEmoji = em"
                                    class="w-8 h-8 rounded-xl flex items-center justify-center transition text-base"
                                    :class="selectedEmoji === em ? 'bg-amber-400 scale-110 shadow-sm' : 'hover:bg-slate-200'">
                                <span x-text="em"></span>
                            </button>
                        </template>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">{{ __('app.color') }}</label>
                    <input type="hidden" name="color" :value="selectedColor">
                    <div class="flex flex-wrap gap-2.5 p-2 bg-slate-50 border border-slate-200 rounded-2xl">
                        <template x-for="c in [
                            { id: 'blue', bg: 'bg-blue-500' },
                            { id: 'rose', bg: 'bg-rose-500' },
                            { id: 'purple', bg: 'bg-purple-500' },
                            { id: 'amber', bg: 'bg-amber-500' },
                            { id: 'emerald', bg: 'bg-emerald-500' },
                            { id: 'indigo', bg: 'bg-indigo-500' },
                            { id: 'cyan', bg: 'bg-cyan-500' },
                            { id: 'slate', bg: 'bg-slate-500' }
                        ]">
                            <button type="button" @click="selectedColor = c.id"
                                    class="w-7 h-7 rounded-full transition flex items-center justify-center ring-offset-2"
                                    :class="[c.bg, selectedColor === c.id ? 'ring-2 ring-slate-800 scale-110' : 'opacity-80 hover:opacity-100']">
                                <span x-show="selectedColor === c.id" class="text-white text-xs font-bold">✓</span>
                            </button>
                        </template>
                    </div>
                </div>

                <div class="flex gap-2 justify-end pt-2 border-t border-slate-100">
                    <button type="button" @click="showCategoryModal = false" class="px-4 py-2 text-xs font-semibold text-slate-500 hover:text-slate-800">{{ __('app.cancel') }}</button>
                    <button type="submit" class="px-5 py-2 text-xs font-bold bg-amber-500 text-slate-950 rounded-xl hover:bg-amber-400 shadow-sm active:scale-95 transition">{{ __('app.add_category_btn') }}</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Modal Target Slot for HTMX -->
    <div id="edit-modal-slot"></div>

    <!-- Workspace & Category Settings Modal Target Slot for HTMX -->
    <div id="settings-modal-slot"></div>

    <!-- Dynamic Math Delete Confirmation Modal -->
    <div x-data="{
        showModal: false,
        itemTitle: '',
        actionUrl: '',
        num1: 1,
        num2: 2,
        userAnswer: '',
        get isCorrect() {
            return parseInt(this.userAnswer, 10) === (this.num1 + this.num2);
        },
        generateMath() {
            this.num1 = Math.floor(Math.random() * 7) + 1; // 1 to 7
            const maxNum2 = 9 - this.num1; // ensures sum <= 9
            this.num2 = Math.floor(Math.random() * maxNum2) + 1; // 1 to maxNum2
            this.userAnswer = '';
        },
        init() {
            window.addEventListener('open-math-delete-confirm', (e) => {
                this.itemTitle = e.detail?.title || '{{ __('app.task_title') }}';
                this.actionUrl = e.detail?.actionUrl || '';
                this.generateMath();
                this.showModal = true;
                this.$nextTick(() => {
                    this.$refs.mathInput?.focus();
                });
            });
            window.openMathDeleteConfirm = (title, actionUrl) => {
                window.dispatchEvent(new CustomEvent('open-math-delete-confirm', { detail: { title, actionUrl } }));
            };
        },
        submitDelete() {
            if (!this.isCorrect) return;
            htmx.ajax('DELETE', this.actionUrl, { target: 'body' });
            this.showModal = false;
            document.getElementById('edit-modal-wrapper')?.remove();
        }
    }">
        <div x-show="showModal" x-cloak
             class="fixed inset-0 z-[70] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            
            <div @click.outside="showModal = false"
                 x-show="showModal"
                 x-transition:enter="transition ease-out duration-200 transform"
                 x-transition:enter-start="scale-95 opacity-0"
                 x-transition:enter-end="scale-100 opacity-100"
                 x-transition:leave="transition ease-in duration-150 transform"
                 x-transition:leave-start="scale-100 opacity-100"
                 x-transition:leave-end="scale-95 opacity-0"
                 class="w-full max-w-md bg-white border border-rose-100 rounded-3xl p-6 shadow-2xl space-y-5">
                
                <!-- Icon & Header -->
                <div class="flex items-start gap-3.5">
                    <div class="w-12 h-12 rounded-2xl bg-rose-50 border border-rose-200 flex items-center justify-center text-2xl flex-shrink-0">
                        🗑️
                    </div>
                    <div class="flex-1">
                        <h3 class="text-base font-extrabold text-slate-900">{{ __('app.delete_confirmation') }}</h3>
                        <p class="text-xs text-slate-500 mt-0.5">
                            {{ __('app.delete_confirm_text', ['title' => '']) }} <span class="font-bold text-slate-800" x-text="'«' + itemTitle + '»'"></span>?
                        </p>
                    </div>
                </div>

                <!-- Math Challenge Box -->
                <div class="p-4 rounded-2xl bg-amber-50/70 border border-amber-200/80 space-y-2.5">
                    <div class="flex items-center justify-between">
                        <label class="text-xs font-bold text-amber-950 flex items-center gap-1.5">
                            <span>🧮</span>
                            <span>{{ __('app.security_math_calc') }}</span>
                        </label>
                        <button type="button" @click="generateMath(); $nextTick(() => $refs.mathInput?.focus())" class="text-[11px] font-semibold text-amber-700 hover:text-amber-900 hover:underline flex items-center gap-1">
                            <span>🔄</span> {{ __('app.another_example') }}
                        </button>
                    </div>
                    
                    <p class="text-[11px] text-amber-800">
                        {{ __('app.math_hint') }}
                    </p>

                    <form @submit.prevent="submitDelete()">
                        <div class="flex items-center gap-3 pt-1">
                            <div class="px-4 py-2 bg-white rounded-xl border border-amber-300 font-extrabold text-base sm:text-lg text-slate-800 shadow-inner flex items-center justify-center min-w-[90px] select-none tracking-wider">
                                <span x-text="num1"></span>
                                <span class="mx-1 text-amber-600">+</span>
                                <span x-text="num2"></span>
                                <span class="mx-1 text-slate-400">=</span>
                            </div>

                            <input type="text"
                                   inputmode="numeric"
                                   pattern="[0-9]*"
                                   maxlength="1"
                                   x-ref="mathInput"
                                   x-model="userAnswer"
                                   placeholder="?"
                                   autofocus
                                   required
                                   class="w-20 px-3 py-2 text-center text-xl font-bold bg-white border rounded-xl focus:outline-none transition shadow-sm"
                                   :class="{
                                       'border-emerald-500 ring-2 ring-emerald-400 text-emerald-700 bg-emerald-50/40': isCorrect,
                                       'border-rose-400 text-rose-700 ring-2 ring-rose-200': userAnswer !== '' && !isCorrect,
                                       'border-amber-300 focus:border-amber-500 text-slate-900': userAnswer === ''
                                   }">

                            <div class="flex-1 text-xs">
                                <template x-if="isCorrect">
                                    <span class="inline-flex items-center gap-1 text-emerald-600 font-bold">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                        {{ __('app.correct') }}
                                    </span>
                                </template>
                                <template x-if="userAnswer !== '' && !isCorrect">
                                    <span class="text-[11px] font-semibold text-rose-600 leading-tight block">{{ __('app.incorrect_digit') }}</span>
                                </template>
                                <template x-if="userAnswer === ''">
                                    <span class="text-[11px] text-amber-700/80 leading-tight block">{{ __('app.enter_1_digit') }}</span>
                                </template>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Footer Buttons -->
                <div class="flex items-center justify-end gap-2.5 pt-1">
                    <button type="button" @click="showModal = false"
                            class="px-4 py-2.5 text-xs font-bold text-slate-600 hover:text-slate-800 hover:bg-slate-100 rounded-xl transition">
                        {{ __('app.cancel') }}
                    </button>
                    
                    <button type="button"
                            @click="submitDelete()"
                            :disabled="!isCorrect"
                            class="px-5 py-2.5 rounded-xl font-bold text-xs shadow-sm transition flex items-center gap-1.5"
                            :class="isCorrect ? 'bg-rose-600 hover:bg-rose-700 text-white shadow-rose-500/30 cursor-pointer active:scale-95' : 'bg-slate-100 text-slate-400 border border-slate-200 cursor-not-allowed'">
                        <span>🗑️</span>
                        <span>{{ __('app.delete_task_btn') }}</span>
                    </button>
                </div>

            </div>
        </div>
    </div>

    <!-- Configure HTMX CSRF & Calendar Picker -->
    <script>
        document.body.addEventListener('htmx:configRequest', (event) => {
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            event.detail.headers['X-CSRF-TOKEN'] = token;
        });

        function calendarPicker(initialDate = null) {
            let initD = initialDate ? new Date(initialDate) : new Date();
            if (isNaN(initD.getTime())) initD = new Date();
            
            const isLv = '{{ app()->getLocale() }}' === 'lv';
            
            return {
                currentMonth: initD.getMonth(),
                currentYear: initD.getFullYear(),
                selectedDate: initialDate,
                monthNames: isLv 
                    ? ['Janvāris', 'Februāris', 'Marts', 'Aprīlis', 'Maijs', 'Jūnijs', 'Jūlijs', 'Augusts', 'Septembris', 'Oktobris', 'Novembris', 'Decembris']
                    : ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
                dayNames: isLv
                    ? ['P', 'O', 'T', 'C', 'Pk', 'S', 'Sv']
                    : ['M', 'Tu', 'W', 'Th', 'F', 'Sa', 'Su'],
                
                get monthYearString() {
                    return this.monthNames[this.currentMonth] + ' ' + this.currentYear;
                },
                
                prevMonth() {
                    if (this.currentMonth === 0) {
                        this.currentMonth = 11;
                        this.currentYear--;
                    } else {
                        this.currentMonth--;
                    }
                },
                
                nextMonth() {
                    if (this.currentMonth === 11) {
                        this.currentMonth = 0;
                        this.currentYear++;
                    } else {
                        this.currentMonth++;
                    }
                },
                
                get daysInMonth() {
                    const days = [];
                    const firstDay = new Date(this.currentYear, this.currentMonth, 1).getDay();
                    const startOffset = (firstDay === 0 ? 6 : firstDay - 1);
                    
                    const prevMonthDays = new Date(this.currentYear, this.currentMonth, 0).getDate();
                    for (let i = startOffset - 1; i >= 0; i--) {
                        days.push({ day: prevMonthDays - i, isCurrentMonth: false, dateString: null });
                    }
                    
                    const totalDays = new Date(this.currentYear, this.currentMonth + 1, 0).getDate();
                    const today = new Date();
                    const todayStr = today.getFullYear() + '-' + String(today.getMonth() + 1).padStart(2, '0') + '-' + String(today.getDate()).padStart(2, '0');
                    
                    for (let i = 1; i <= totalDays; i++) {
                        const m = String(this.currentMonth + 1).padStart(2, '0');
                        const d = String(i).padStart(2, '0');
                        const dStr = `${this.currentYear}-${m}-${d}`;
                        days.push({
                            day: i,
                            isCurrentMonth: true,
                            dateString: dStr,
                            isToday: dStr === todayStr,
                            isSelected: dStr === this.selectedDate
                        });
                    }
                    
                    const remaining = (7 - (days.length % 7)) % 7;
                    for (let i = 1; i <= remaining; i++) {
                        days.push({ day: i, isCurrentMonth: false, dateString: null });
                    }
                    
                    return days;
                }
            };
        }
    </script>
</body>
</html>
