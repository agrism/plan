<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50 text-slate-800">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>{{ __('app.forgot_password_title') }} - {{ __('app.planner') }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['"Plus Jakarta Sans"', 'sans-serif'] },
                }
            }
        }
    </script>
</head>
<body class="min-h-full bg-slate-50 flex items-center justify-center p-4 relative font-sans">
    <!-- Language Switcher in Top Right -->
    <div class="absolute top-4 right-4 flex items-center bg-white/80 backdrop-blur-sm border border-slate-200 rounded-full p-0.5 shadow-sm">
        <a href="{{ route('locale.switch', 'lv') }}"
           class="px-2.5 py-1 rounded-full text-xs font-bold transition flex items-center gap-1 {{ app()->getLocale() === 'lv' ? 'bg-amber-500 text-slate-950 shadow-sm' : 'text-slate-500 hover:text-slate-900' }}"
           title="Latviešu">
            <span>🇱🇻</span>
            <span>LV</span>
        </a>
        <a href="{{ route('locale.switch', 'en') }}"
           class="px-2.5 py-1 rounded-full text-xs font-bold transition flex items-center gap-1 {{ app()->getLocale() === 'en' ? 'bg-amber-500 text-slate-950 shadow-sm' : 'text-slate-500 hover:text-slate-900' }}"
           title="English">
            <span>🇬🇧</span>
            <span>EN</span>
        </a>
    </div>

    <div class="w-full max-w-md bg-white border border-slate-200/90 rounded-3xl p-6 sm:p-8 shadow-xl space-y-6">
        <!-- Header -->
        <div class="text-center space-y-2">
            <span class="text-4xl">🔑</span>
            <h1 class="text-2xl font-extrabold text-slate-900">{{ __('app.forgot_password_title') }}</h1>
            <p class="text-xs text-slate-500">{{ __('app.forgot_password_subtitle') }}</p>
        </div>

        <!-- Success Message -->
        @if (session('status'))
            <div class="p-3.5 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold rounded-2xl flex items-center gap-2">
                <span>✓</span>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        <!-- Form -->
        <form action="{{ route('password.email') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('app.email') }}</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus
                       placeholder="{{ __('app.email_placeholder') }}"
                       class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 text-sm focus:border-amber-500 focus:bg-white focus:ring-1 focus:ring-amber-500">
                @error('email') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <button type="submit" class="w-full py-3 bg-gradient-to-r from-amber-500 to-amber-400 hover:from-amber-400 hover:to-amber-300 text-slate-950 font-bold text-sm rounded-xl shadow-md shadow-amber-500/20 active:scale-[0.98] transition">
                {{ __('app.send_reset_link') }}
            </button>
        </form>

        <div class="text-center pt-2 border-t border-slate-100">
            <a href="{{ route('login') }}" class="text-xs font-bold text-amber-600 hover:underline flex items-center justify-center gap-1">
                <span>←</span>
                <span>{{ __('app.back_to_login') }}</span>
            </a>
        </div>
    </div>
</body>
</html>
