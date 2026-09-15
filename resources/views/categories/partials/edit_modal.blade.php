<!-- Edit Category Modal -->
<div class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
     id="category-edit-modal-wrapper"
     x-data="{ 
         editEmoji: '{{ $category->emoji }}',
         editColor: '{{ $category->color }}'
     }">
    
    <div @click.outside="document.getElementById('category-edit-modal-wrapper')?.remove()"
         class="w-full max-w-md bg-white border border-slate-200 rounded-3xl p-6 shadow-2xl space-y-4">
        
        <div class="flex items-center justify-between pb-2 border-b border-slate-100">
            <div class="flex items-center gap-2">
                <span class="text-2xl" x-text="editEmoji"></span>
                <h3 class="text-lg font-bold text-slate-900">Labot kategoriju</h3>
            </div>
            <button type="button" @click="document.getElementById('category-edit-modal-wrapper')?.remove()" class="p-1.5 text-slate-400 hover:text-slate-600 rounded-full hover:bg-slate-100">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <form action="{{ route('categories.update', $category->id) }}" method="POST"
              hx-post="{{ route('categories.update', $category->id) }}"
              hx-target="body"
              @htmx:after-request="document.getElementById('category-edit-modal-wrapper')?.remove(); document.getElementById('settings-modal-wrapper')?.remove()"
              class="space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Kategorijas nosaukums</label>
                <input type="text" name="name" value="{{ $category->name }}" required
                       class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 text-sm focus:border-amber-500 font-semibold">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Ikona (Emoji)</label>
                <input type="hidden" name="emoji" :value="editEmoji">
                <div class="flex flex-wrap gap-2 text-base p-2 bg-slate-50 border border-slate-200 rounded-2xl max-h-28 overflow-y-auto">
                    <template x-for="em in ['💼', '⚡', '🚀', '👥', '📋', '🎯', '🎨', '📊', '🛠️', '💡', '💰', '🔒', '📦', '🏷️', '📢', '💻', '🍕', '✨', '📝', '🛒', '🔧', '📈', '🤝', '⚙️', '📂', '🧪', '📱', '🏆', '🔔']">
                        <button type="button" @click="editEmoji = em"
                                class="w-8 h-8 rounded-xl flex items-center justify-center transition text-base"
                                :class="editEmoji === em ? 'bg-amber-400 scale-110 shadow-sm' : 'hover:bg-slate-200'">
                            <span x-text="em"></span>
                        </button>
                    </template>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Krāsa</label>
                <input type="hidden" name="color" :value="editColor">
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
                        <button type="button" @click="editColor = c.id"
                                class="w-7 h-7 rounded-full transition flex items-center justify-center ring-offset-2"
                                :class="[c.bg, editColor === c.id ? 'ring-2 ring-slate-800 scale-110' : 'opacity-80 hover:opacity-100']">
                            <span x-show="editColor === c.id" class="text-white text-xs font-bold">✓</span>
                        </button>
                    </template>
                </div>
            </div>

            <div class="flex gap-2 justify-end pt-2 border-t border-slate-100">
                <button type="button" @click="document.getElementById('category-edit-modal-wrapper')?.remove()" class="px-4 py-2 text-xs font-semibold text-slate-500 hover:text-slate-800">
                    Atcelt
                </button>
                <button type="submit" class="px-5 py-2 text-xs font-bold bg-amber-500 text-slate-950 rounded-xl hover:bg-amber-400 shadow-sm active:scale-95 transition">
                    Saglabāt izmaiņas 💾
                </button>
            </div>
        </form>
    </div>
</div>
