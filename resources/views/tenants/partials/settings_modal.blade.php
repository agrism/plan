<!-- Workspace & Category Settings Modal -->
<div class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4 bg-slate-900/50 backdrop-blur-sm"
     id="settings-modal-wrapper"
     x-data="{ 
         activeTab: 'categories',
         newEmoji: '📁',
         newColor: 'blue'
     }">
    
    <div @click.outside="document.getElementById('settings-modal-wrapper')?.remove()"
         class="w-full max-w-2xl max-h-[92vh] overflow-y-auto bg-white border border-slate-200 rounded-t-3xl sm:rounded-3xl p-6 shadow-2xl space-y-5">
        
        <!-- Header -->
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div class="flex items-center gap-2.5">
                <div class="w-10 h-10 rounded-2xl bg-amber-50 border border-amber-200 flex items-center justify-center text-xl">
                    ⚙️
                </div>
                <div>
                    <h3 class="text-lg font-extrabold text-slate-900">{{ __('app.workspace_and_categories') }}</h3>
                    <p class="text-xs text-slate-500 font-medium">{{ $tenant->name }}</p>
                </div>
            </div>
            <button type="button" @click="document.getElementById('settings-modal-wrapper')?.remove()" class="p-1.5 text-slate-400 hover:text-slate-600 rounded-full hover:bg-slate-100">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <!-- Segmented Navigation Tabs -->
        <div class="flex p-1 bg-slate-100 rounded-2xl border border-slate-200">
            <button type="button"
                    @click="activeTab = 'categories'"
                    class="flex-1 py-2 rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5"
                    :class="activeTab === 'categories' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900'">
                <span>🏷️ {{ __('app.categories_management') }}</span>
                <span class="px-1.5 py-0.5 rounded-full text-[10px] bg-amber-100 text-amber-900 font-extrabold">{{ $categories->count() }}</span>
            </button>
            <button type="button"
                    @click="activeTab = 'workspace'"
                    class="flex-1 py-2 rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5"
                    :class="activeTab === 'workspace' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900'">
                <span>🏢 {{ __('app.workspace_settings') }}</span>
            </button>
        </div>

        <!-- Tab 1: Categories Management -->
        <div x-show="activeTab === 'categories'" class="space-y-6">
            
            <!-- Existing Categories List -->
            <div class="space-y-2.5">
                <div class="flex items-center justify-between">
                    <h4 class="text-xs font-extrabold uppercase text-slate-400 tracking-wider">{{ __('app.existing_categories') }}</h4>
                    <span class="text-[11px] text-slate-500 font-medium">{{ __('app.total_count', ['count' => $categories->count()]) }}</span>
                </div>

                <div class="divide-y divide-slate-100 border border-slate-200 rounded-2xl overflow-hidden bg-slate-50/50">
                    @forelse($categories as $cat)
                        <div class="p-3 sm:p-3.5 bg-white flex items-center justify-between gap-3 hover:bg-slate-50/80 transition">
                            <div class="flex items-center gap-3 min-w-0">
                                <span class="w-8 h-8 rounded-xl bg-slate-100 border border-slate-200 flex items-center justify-center text-base flex-shrink-0">
                                    {{ $cat->emoji }}
                                </span>
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-bold text-slate-900 truncate">{{ $cat->name }}</span>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold border {{ $cat->badge_class }}">
                                            {{ $cat->color }}
                                        </span>
                                    </div>
                                    <p class="text-[10px] text-slate-400 font-medium">
                                        {{ $cat->tasks_count === 1 ? __('app.assigned_tasks_single', ['count' => $cat->tasks_count]) : __('app.assigned_tasks', ['count' => $cat->tasks_count]) }}
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center gap-1.5 flex-shrink-0">
                                <!-- Edit Category Button -->
                                <button hx-get="{{ route('categories.edit', $cat->id) }}"
                                        hx-target="#category-edit-slot"
                                        hx-swap="innerHTML"
                                        type="button"
                                        title="{{ __('app.edit_category') }}"
                                        class="px-2.5 py-1.5 rounded-xl bg-slate-100 hover:bg-amber-100 hover:text-amber-900 text-slate-700 text-xs font-semibold transition flex items-center gap-1">
                                    <span>✏️</span>
                                    <span class="hidden sm:inline">{{ __('app.edit') }}</span>
                                </button>

                                <!-- Delete Category with Single Digit Math Confirmation -->
                                <button type="button"
                                        @click="window.openMathDeleteConfirm('{{ __('app.category_prefix', ['name' => addslashes($cat->name)]) }}', '{{ route('categories.destroy', $cat->id) }}')"
                                        title="{{ __('app.delete') }}"
                                        class="px-2.5 py-1.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-semibold transition flex items-center gap-1">
                                    <span>🗑️</span>
                                    <span class="hidden sm:inline">{{ __('app.delete') }}</span>
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="p-6 text-center text-xs text-slate-400 italic">{{ __('app.no_categories') }}</div>
                    @endforelse
                </div>
            </div>

            <!-- Create New Category Form -->
            <div class="p-4 rounded-2xl bg-amber-50/50 border border-amber-200/80 space-y-3">
                <div class="flex items-center gap-2">
                    <span class="text-lg">➕</span>
                    <h4 class="text-xs font-extrabold text-slate-900">{{ __('app.create_new_category') }}</h4>
                </div>

                <form action="{{ route('categories.store') }}" method="POST"
                      hx-post="{{ route('categories.store') }}"
                      hx-target="body"
                      @htmx:after-request="document.getElementById('settings-modal-wrapper')?.remove()"
                      class="space-y-3">
                    @csrf

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('app.category_name') }}</label>
                        <input type="text" name="name" required placeholder="{{ __('app.category_name_placeholder') }}"
                               class="w-full px-3.5 py-2 bg-white border border-slate-300 focus:border-amber-500 rounded-xl text-slate-900 text-xs font-semibold">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('app.icon_emoji') }}</label>
                        <input type="hidden" name="emoji" :value="newEmoji">
                        <div class="flex flex-wrap gap-1.5 p-2 bg-white border border-slate-200 rounded-xl max-h-24 overflow-y-auto">
                            <template x-for="em in ['💼', '⚡', '🚀', '👥', '📋', '🎯', '🎨', '📊', '🛠️', '💡', '💰', '🔒', '📦', '🏷️', '📢', '💻', '🍕', '✨', '📝', '🛒', '🔧', '📈', '🤝', '⚙️', '📂', '🧪', '📱', '🏆', '🔔']">
                                <button type="button" @click="newEmoji = em"
                                        class="w-7 h-7 rounded-lg flex items-center justify-center transition text-sm"
                                        :class="newEmoji === em ? 'bg-amber-400 scale-110 shadow-sm' : 'hover:bg-slate-100'">
                                    <span x-text="em"></span>
                                </button>
                            </template>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('app.color') }}</label>
                        <input type="hidden" name="color" :value="newColor">
                        <div class="flex flex-wrap gap-2 p-2 bg-white border border-slate-200 rounded-xl">
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
                                <button type="button" @click="newColor = c.id"
                                        class="w-6 h-6 rounded-full transition flex items-center justify-center ring-offset-2"
                                        :class="[c.bg, newColor === c.id ? 'ring-2 ring-slate-800 scale-110' : 'opacity-80 hover:opacity-100']">
                                    <span x-show="newColor === c.id" class="text-white text-[10px] font-bold">✓</span>
                                </button>
                            </template>
                        </div>
                    </div>

                    <div class="pt-1">
                        <button type="submit" class="w-full py-2.5 bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs rounded-xl shadow-sm active:scale-95 transition">
                            {{ __('app.add_category_btn') }}
                        </button>
                    </div>
                </form>
            </div>

        </div>

        <!-- Tab 2: Workspace Settings -->
        <div x-show="activeTab === 'workspace'" class="space-y-6">
            
            <!-- Edit Workspace Name Form -->
            <form action="{{ route('tenants.update', $tenant->id) }}" method="POST"
                  hx-post="{{ route('tenants.update', $tenant->id) }}"
                  hx-target="body"
                  @htmx:after-request="document.getElementById('settings-modal-wrapper')?.remove()"
                  class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-3">
                @csrf

                <div class="flex items-center gap-2">
                    <span class="text-lg">🏢</span>
                    <h4 class="text-xs font-extrabold text-slate-900">{{ __('app.change_workspace_name') }}</h4>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('app.workspace_name') }}</label>
                    <div class="flex gap-2">
                        <input type="text" name="name" value="{{ $tenant->name }}" required
                               class="flex-1 px-3.5 py-2.5 bg-white border border-slate-300 focus:border-amber-500 rounded-xl text-slate-900 text-xs font-bold">
                        <button type="submit" class="px-5 py-2.5 bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs rounded-xl shadow-sm active:scale-95 transition">
                            {{ __('app.save') }} 💾
                        </button>
                    </div>
                </div>
            </form>

            <!-- Team Invite Code -->
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-2.5"
                 x-data="{ copiedCode: false, copiedLink: false }">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="text-lg">🔑</span>
                        <h4 class="text-xs font-extrabold text-slate-900">{{ __('app.team_invite_code') }}</h4>
                    </div>
                    <span class="text-[10px] text-slate-400 font-medium">{{ __('app.invite_code_hint') }}</span>
                </div>

                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                    <input type="text" readonly value="{{ $tenant->invite_code }}"
                           class="flex-1 px-3.5 py-2 bg-white border border-slate-300 rounded-xl text-xs font-mono font-bold text-slate-800 select-all">
                    <div class="flex items-center gap-1.5">
                        <button type="button"
                                @click="navigator.clipboard.writeText('{{ $tenant->invite_code }}'); copiedCode = true; setTimeout(() => copiedCode = false, 2000)"
                                class="flex-1 sm:flex-initial px-3.5 py-2 bg-slate-200 hover:bg-slate-300 text-slate-800 text-xs font-bold rounded-xl transition flex items-center justify-center gap-1.5">
                            <span x-text="copiedCode ? '✓ {{ __('app.copied') }}' : '{{ __('app.copy_code') }}'"></span>
                        </button>
                        <button type="button"
                                @click="navigator.clipboard.writeText('{{ url('/join/' . $tenant->invite_code) }}'); copiedLink = true; setTimeout(() => copiedLink = false, 2000)"
                                class="flex-1 sm:flex-initial px-3.5 py-2 bg-amber-500 hover:bg-amber-400 text-slate-950 text-xs font-bold rounded-xl transition flex items-center justify-center gap-1.5 shadow-sm">
                            <span x-text="copiedLink ? '✓ {{ __('app.copied') }}' : '🔗 {{ __('app.copy_invite_link') }}'"></span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Team Members List -->
            <div class="space-y-2">
                <h4 class="text-xs font-extrabold uppercase text-slate-400 tracking-wider">{{ __('app.workspace_members', ['count' => $tenant->users->count()]) }}</h4>
                <div class="divide-y divide-slate-100 border border-slate-200 rounded-2xl overflow-hidden bg-white">
                    @foreach($tenant->users as $member)
                        <div class="p-3 flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <span class="text-xl">{{ $member->avatar ?? '👤' }}</span>
                                <div>
                                    <p class="text-xs font-bold text-slate-900">{{ $member->name }}</p>
                                    <p class="text-[10px] text-slate-500">{{ $member->email }}</p>
                                </div>
                            </div>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-slate-100 text-slate-600">
                                {{ $member->pivot->role ?? 'member' }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>

    </div>
</div>

<!-- Slot for Category Edit Modal -->
<div id="category-edit-slot"></div>
