<!-- Profile Settings Modal -->
<div class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4 bg-slate-900/50 backdrop-blur-sm"
     id="profile-modal-wrapper"
     x-data="{ 
         selectedAvatar: '{{ $user->avatar ?? '👤' }}',
         showPasswordFields: false
     }">
    
    <div @click.outside="document.getElementById('profile-modal-wrapper')?.remove()"
         class="w-full max-w-lg max-h-[92vh] overflow-y-auto bg-white border border-slate-200 rounded-t-3xl sm:rounded-3xl p-6 shadow-2xl space-y-5">
        
        <!-- Header -->
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div class="flex items-center gap-2.5">
                <div class="w-10 h-10 rounded-2xl bg-amber-50 border border-amber-200 flex items-center justify-center text-xl">
                    <span x-text="selectedAvatar"></span>
                </div>
                <div>
                    <h3 class="text-lg font-extrabold text-slate-900">{{ __('app.profile_settings') }}</h3>
                    <p class="text-xs text-slate-500 font-medium">{{ $user->email }}</p>
                </div>
            </div>
            <button type="button" @click="document.getElementById('profile-modal-wrapper')?.remove()" class="p-1.5 text-slate-400 hover:text-slate-600 rounded-full hover:bg-slate-100">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        @if (session('status'))
            <div class="p-3.5 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold rounded-2xl flex items-center gap-2">
                <span>✓</span>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="p-3.5 bg-rose-50 border border-rose-200 text-rose-800 text-xs font-bold rounded-2xl space-y-1">
                @foreach ($errors->all() as $error)
                    <p>• {{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form action="{{ route('profile.update') }}" method="POST"
              hx-post="{{ route('profile.update') }}"
              hx-target="body"
              @htmx:after-request="document.getElementById('profile-modal-wrapper')?.remove()"
              class="space-y-4">
            @csrf

            <!-- Name -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('app.name') }}</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                       placeholder="{{ __('app.name_placeholder') }}"
                       class="w-full px-3.5 py-2.5 bg-white border border-slate-300 focus:border-amber-500 rounded-xl text-slate-900 text-xs font-bold">
            </div>

            <!-- Email & Verification Status -->
            <div>
                <div class="flex items-center justify-between mb-1">
                    <label class="block text-xs font-bold text-slate-700">{{ __('app.email') }}</label>
                    @if($user->hasVerifiedEmail())
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            ✓ {{ __('app.verify_email_badge') }}
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                            ⚠️ {{ __('app.unverified_email_badge') }}
                        </span>
                    @endif
                </div>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                       placeholder="{{ __('app.email_placeholder') }}"
                       class="w-full px-3.5 py-2.5 bg-white border border-slate-300 focus:border-amber-500 rounded-xl text-slate-900 text-xs font-bold">
                
                @if(!$user->hasVerifiedEmail())
                    <div class="mt-2 p-2.5 bg-amber-50/80 border border-amber-200/80 rounded-xl flex items-center justify-between gap-2">
                        <div class="text-[11px] font-medium text-amber-900">
                            <span>{{ __('app.email_unverified') }}</span>
                        </div>
                        <button type="button"
                                hx-post="{{ route('verification.send') }}"
                                hx-target="#profile-modal-slot"
                                hx-swap="innerHTML"
                                class="px-2.5 py-1 bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-[11px] rounded-lg shadow-sm transition active:scale-95 whitespace-nowrap">
                            {{ __('app.send_verification_link') }}
                        </button>
                    </div>
                @endif
            </div>

            <!-- Avatar Emoji Selector -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">{{ __('app.avatar') }}</label>
                <input type="hidden" name="avatar" :value="selectedAvatar">
                <div class="flex flex-wrap gap-2 text-base p-2 bg-slate-50 border border-slate-200 rounded-2xl max-h-28 overflow-y-auto">
                    <template x-for="em in ['👤', '👩', '👨', '🧑', '👩‍💻', '👨‍💻', '🚀', '🦊', '🦁', '🐱', '🐶', '🦄', '🐼', '🐨', '🐯', '⚡', '✨', '🎯', '💡', '👑', '🦸‍♂️', '🦸‍♀️']">
                        <button type="button" @click="selectedAvatar = em"
                                class="w-8 h-8 rounded-xl flex items-center justify-center transition text-base"
                                :class="selectedAvatar === em ? 'bg-amber-400 scale-110 shadow-sm' : 'hover:bg-slate-200'">
                            <span x-text="em"></span>
                        </button>
                    </template>
                </div>
            </div>

            <!-- Change Password Toggle Section -->
            <div class="pt-2 border-t border-slate-100">
                <div class="flex items-center justify-between cursor-pointer py-2" @click="showPasswordFields = !showPasswordFields">
                    <div class="flex items-center gap-2">
                        <span class="text-base">🔒</span>
                        <span class="text-xs font-extrabold text-slate-900">{{ __('app.change_password_section') }}</span>
                    </div>
                    <span class="text-xs text-slate-400 font-bold" x-text="showPasswordFields ? '▲' : '▼'"></span>
                </div>

                <div x-show="showPasswordFields" x-collapse class="space-y-3 pt-2">
                    <p class="text-[11px] text-slate-500">{{ __('app.current_password_hint') }}</p>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('app.current_password') }}</label>
                        <input type="password" name="current_password" placeholder="•••••"
                               class="w-full px-3.5 py-2.5 bg-white border border-slate-300 focus:border-amber-500 rounded-xl text-slate-900 text-xs font-bold">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('app.new_password') }}</label>
                            <input type="password" name="password" minlength="5" placeholder="•••••"
                                   class="w-full px-3.5 py-2.5 bg-white border border-slate-300 focus:border-amber-500 rounded-xl text-slate-900 text-xs font-bold">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('app.confirm_new_password') }}</label>
                            <input type="password" name="password_confirmation" minlength="5" placeholder="•••••"
                                   class="w-full px-3.5 py-2.5 bg-white border border-slate-300 focus:border-amber-500 rounded-xl text-slate-900 text-xs font-bold">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2.5">
                <button type="button" @click="document.getElementById('profile-modal-wrapper')?.remove()"
                        class="px-4 py-2.5 text-xs font-bold text-slate-600 hover:text-slate-800 hover:bg-slate-100 rounded-xl transition">
                    {{ __('app.cancel') }}
                </button>
                <button type="submit"
                        class="px-5 py-2.5 bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs rounded-xl shadow-sm active:scale-95 transition">
                    {{ __('app.save') }} 💾
                </button>
            </div>
        </form>
    </div>
</div>
