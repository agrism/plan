@extends('layouts.app')

@section('content')
<div class="max-w-md mx-auto space-y-6">
    <!-- Create New Workspace Card -->
    <div class="bg-white border border-slate-200/90 rounded-md p-6 sm:p-8 shadow-xl space-y-6">
        <div class="text-center space-y-2">
            <span class="text-4xl">💼</span>
            <h1 class="text-2xl font-extrabold text-slate-900">{{ __('app.create_workspace') }}</h1>
            <p class="text-xs text-slate-500">{{ __('app.create_workspace_subtitle') }}</p>
        </div>

        <form action="{{ route('tenants.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('app.workspace_name') }}</label>
                <input type="text" name="name" value="{{ old('name') }}" required placeholder="{{ __('app.workspace_name_placeholder') }}"
                       class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-md text-slate-900 text-sm focus:border-amber-500 focus:bg-white focus:ring-1 focus:ring-amber-500">
                @error('name') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <button type="submit" class="w-full py-3 bg-gradient-to-r from-amber-500 to-amber-400 hover:from-amber-400 hover:to-amber-300 text-slate-950 font-bold text-sm rounded-md shadow-md shadow-amber-500/20 active:scale-[0.98] transition">
                {{ __('app.create_workspace') }}
            </button>
        </form>
    </div>

    <!-- Join Existing Workspace Card -->
    <div class="bg-white border border-slate-200/90 rounded-md p-6 sm:p-8 shadow-xl space-y-5">
        <div class="flex items-center gap-3">
            <span class="text-3xl">🔑</span>
            <div>
                <h2 class="text-base font-bold text-slate-900">{{ __('app.join_workspace_title') }}</h2>
                <p class="text-xs text-slate-500">{{ __('app.join_workspace_subtitle') }}</p>
            </div>
        </div>

        <form action="{{ route('tenants.join') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('app.enter_invite_code') }}</label>
                <input type="text" name="invite_code" required placeholder="{{ __('app.invite_code_placeholder') }}"
                       class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-md text-slate-900 font-mono font-bold text-sm focus:border-amber-500 focus:bg-white focus:ring-1 focus:ring-amber-500">
                @error('invite_code') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <button type="submit" class="w-full py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-md transition shadow-sm active:scale-[0.98]">
                {{ __('app.join_btn') }}
            </button>
        </form>
    </div>
</div>
@endsection
