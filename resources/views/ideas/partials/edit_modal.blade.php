<!-- Edit Task Modal -->
<div class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4 bg-slate-900/50 backdrop-blur-sm"
     id="edit-modal-wrapper"
     x-data="{ 
         editLinks: {{ json_encode($task->links && count($task->links) > 0 ? $task->links : ['']) }},
         hasImage: {{ $task->image_url ? 'true' : 'false' }},
         newImagePreview: null,
         removeImage: false
     }">
    
    <div @click.outside="document.getElementById('edit-modal-wrapper')?.remove()"
         class="w-full max-w-xl max-h-[90vh] overflow-y-auto bg-white border border-slate-200 rounded-t-3xl sm:rounded-3xl p-6 shadow-2xl space-y-5">
        
        <!-- Header -->
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div class="flex items-center gap-2">
                <span class="text-2xl">✏️</span>
                <div>
                    <h3 class="text-lg font-bold text-slate-900">Labot uzdevumu</h3>
                    <p class="text-xs text-slate-500">Izveidoja: {{ $task->creator->name }} ({{ $task->created_at->translatedFormat('j. M H:i') }})</p>
                </div>
            </div>
            <button type="button" @click="document.getElementById('edit-modal-wrapper')?.remove()" class="p-1.5 text-slate-400 hover:text-slate-600 rounded-full hover:bg-slate-100">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <form action="{{ route('ideas.update', $task->id) }}" method="POST"
              enctype="multipart/form-data"
              hx-encoding="multipart/form-data"
              hx-post="{{ route('ideas.update', $task->id) }}"
              hx-target="body"
              @htmx:after-request="document.getElementById('edit-modal-wrapper')?.remove()"
              class="space-y-4">
            @csrf

            <!-- Title -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Nosaukums</label>
                <input type="text" name="title" value="{{ $task->title }}" required
                       class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 focus:border-amber-500 focus:bg-white rounded-xl text-slate-900 font-semibold text-sm">
            </div>

            <!-- Optional Description -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Apraksts (Pēc izvēles)</label>
                <textarea name="description" rows="3" placeholder="Pievieno papildu piezīmes, aprakstu vai detaļas..."
                          class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 focus:border-amber-500 focus:bg-white rounded-xl text-slate-900 text-xs resize-none">{{ $task->description }}</textarea>
            </div>

            <!-- Category -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Kategorija</label>
                <div class="grid grid-cols-3 gap-2 text-xs max-h-36 overflow-y-auto p-0.5">
                    @if(isset($categories) && count($categories) > 0)
                        @foreach($categories as $cat)
                            <label class="flex items-center gap-1.5 p-2 rounded-xl bg-slate-50 border border-slate-200 cursor-pointer hover:border-amber-400 has-[:checked]:border-amber-500 has-[:checked]:bg-amber-50 has-[:checked]:text-amber-950 font-semibold truncate transition">
                                <input type="radio" name="category_id" value="{{ $cat->id }}" {{ ($task->category_id == $cat->id || $task->category === $cat->slug) ? 'checked' : '' }} class="hidden">
                                <span class="text-sm flex-shrink-0">{{ $cat->emoji }}</span>
                                <span class="truncate">{{ $cat->name }}</span>
                            </label>
                        @endforeach
                    @else
                        <label class="flex items-center gap-1.5 p-2 rounded-xl bg-slate-50 border border-slate-200 cursor-pointer hover:border-amber-400 has-[:checked]:border-amber-500 has-[:checked]:bg-amber-50 has-[:checked]:text-amber-950 font-semibold truncate transition">
                            <input type="radio" name="category" value="{{ $task->category ?? 'citi' }}" checked class="hidden">
                            <span class="text-sm flex-shrink-0">{{ $task->category_emoji }}</span>
                            <span class="truncate">{{ $task->category_name }}</span>
                        </label>
                    @endif
                </div>
            </div>

            <!-- Dynamic Links & YouTube Video Input (Infinitely addable) -->
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <label class="block text-xs font-bold text-slate-700">Saites & YouTube video</label>
                    <span class="text-[11px] text-slate-400">YouTube video tiks automātiski atskaņoti</span>
                </div>
                
                <div class="space-y-2">
                    <template x-for="(link, index) in editLinks" :key="index">
                        <div class="flex items-center gap-2">
                            <span class="text-slate-400 text-xs">🔗</span>
                            <input type="text" :name="'links[' + index + ']'" x-model="editLinks[index]" placeholder="https://youtube.com/watch?v=... vai tīmekļa saite"
                                   class="flex-1 px-3 py-2 bg-slate-50 border border-slate-300 focus:border-amber-500 focus:bg-white rounded-xl text-slate-900 text-xs">
                            <button type="button" @click="editLinks.splice(index, 1)" class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-rose-50 text-xs" title="Noņemt">✕</button>
                        </div>
                    </template>
                </div>

                <button type="button" @click="editLinks.push('')"
                        class="text-xs font-bold text-amber-700 hover:text-amber-800 flex items-center gap-1 py-1">
                    <span>➕</span> Pievienot vēl vienu saiti
                </button>
            </div>

            <!-- Scheduled Date (Optional) -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Ieplānotais datums (Kalendārs)</label>
                <input type="date" name="scheduled_date" value="{{ $task->scheduled_date ? $task->scheduled_date->toDateString() : '' }}"
                       class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 focus:border-amber-500 focus:bg-white rounded-xl text-slate-900 text-xs">
                <p class="text-[10px] text-slate-400 mt-1">Atstāj tukšu, ja vēlies saglabāt to uzdevumu krātuvē.</p>
            </div>

            <!-- Image Management -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Attēls</label>
                
                @if($task->image_url)
                    <div x-show="!removeImage" class="relative rounded-2xl overflow-hidden bg-slate-100 border border-slate-200 mb-2 p-2 flex items-center justify-between gap-3">
                        <img src="{{ $task->image_url }}" alt="Attēls" class="w-16 h-12 object-cover rounded-xl">
                        <div class="flex-1 text-xs text-slate-500 truncate">Esošais attēls</div>
                        <button type="button" @click="removeImage = true" class="px-2.5 py-1 bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-bold rounded-lg transition">
                            🗑️ Noņemt
                        </button>
                        <input type="hidden" name="remove_image" :value="removeImage ? 1 : 0">
                    </div>
                @endif

                <div class="flex items-center gap-3">
                    <label class="flex-1 flex items-center justify-center gap-2 px-4 py-2 bg-slate-50 border border-dashed border-slate-300 hover:border-amber-500 rounded-xl cursor-pointer text-xs font-semibold text-slate-600 hover:text-slate-900 transition">
                        <span>📷</span>
                        <span x-text="newImagePreview ? 'Mainīt jauno attēlu' : 'Augšupielādēt jaunu attēlu'"></span>
                        <input type="file" name="image_file" accept="image/*" class="hidden"
                               @change="const file = $event.target.files[0]; if (file) { const reader = new FileReader(); reader.onload = (e) => { newImagePreview = e.target.result; removeImage = false; }; reader.readAsDataURL(file); }">
                    </label>
                    <template x-if="newImagePreview">
                        <div class="relative w-12 h-10 rounded-xl overflow-hidden border border-slate-200 shadow-sm flex-shrink-0">
                            <img :src="newImagePreview" class="w-full h-full object-cover">
                            <button type="button" @click="newImagePreview = null" class="absolute inset-0 bg-black/40 text-white flex items-center justify-center text-xs">✕</button>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Footer Buttons -->
            <div class="flex items-center justify-between pt-3 border-t border-slate-100">
                <button type="button" @click="document.getElementById('edit-modal-wrapper')?.remove()" class="px-4 py-2 text-xs font-bold text-slate-500 hover:text-slate-800">
                    Atcelt
                </button>
                <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-amber-500 to-amber-400 hover:from-amber-400 text-slate-950 font-bold text-xs rounded-xl shadow-md shadow-amber-500/20 active:scale-95 transition">
                    Saglabāt Izmaiņas 💾
                </button>
            </div>
        </form>

    </div>
</div>
