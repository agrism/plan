<!DOCTYPE html>
<html lang="lv" class="h-full bg-slate-50 text-slate-800">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>Pieslēgties - Plānotājs</title>
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
<body class="min-h-full bg-slate-50 flex items-center justify-center p-4">
    <div class="w-full max-w-md bg-white border border-slate-200/90 rounded-3xl p-6 sm:p-8 shadow-xl space-y-6">
        
        <!-- Header -->
        <div class="text-center space-y-2">
            <span class="text-4xl">💼</span>
            <h1 class="text-2xl font-extrabold text-slate-900">Plānotājs</h1>
            <p class="text-xs text-slate-500">Pieslēdzies savai darbavietai vai komandai</p>
        </div>

        <!-- Quick One-Click Login for Demo/Team Members -->
        @if(isset($demoUsers) && $demoUsers->isNotEmpty())
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-2.5">
                <div class="text-[11px] font-bold uppercase tracking-wider text-amber-700 text-center">⚡ Ātrā Pieslēgšanās (Demo Komanda)</div>
                <div class="grid grid-cols-2 gap-2">
                    @foreach($demoUsers as $du)
                        <a href="{{ route('login.quick', $du->id) }}"
                           class="flex items-center gap-2 p-2.5 rounded-xl bg-white border border-slate-200 hover:border-amber-400 hover:bg-amber-50/50 text-xs font-bold text-slate-800 shadow-sm transition">
                            <span class="text-lg">{{ $du->avatar ?? '👤' }}</span>
                            <span class="truncate">{{ $du->name }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Standard Login Form -->
        <form action="{{ route('login') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">E-pasts</label>
                <input type="email" name="email" value="{{ old('email', 'janis@komanda.lv') }}" required
                       class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 text-sm focus:border-amber-500 focus:bg-white focus:ring-1 focus:ring-amber-500">
                @error('email') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Parole</label>
                <input type="password" name="password" value="password" required
                       class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 text-sm focus:border-amber-500 focus:bg-white focus:ring-1 focus:ring-amber-500">
            </div>

            <button type="submit" class="w-full py-3 bg-gradient-to-r from-amber-500 to-amber-400 hover:from-amber-400 hover:to-amber-300 text-slate-950 font-bold text-sm rounded-xl shadow-md shadow-amber-500/20 active:scale-[0.98] transition">
                Pieslēgties
            </button>
        </form>

        <div class="text-center pt-2 border-t border-slate-100">
            <p class="text-xs text-slate-500">Nav konta? <a href="{{ route('register') }}" class="text-amber-600 font-bold hover:underline">Reģistrē jaunu darbavietu</a></p>
        </div>
    </div>
</body>
</html>
