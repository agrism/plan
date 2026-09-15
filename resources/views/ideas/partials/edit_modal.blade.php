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
         class="w-full max-w-xl max-h-[90vh] overflow-y-auto bg-white border border-slate-200 rounded-t-2xl sm:rounded-2xl p-6 shadow-2xl space-y-5">
        
        <!-- Header -->
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div class="flex items-center gap-2">
                <span class="text-2xl">✏️</span>
                <div>
                    <h3 class="text-lg font-bold text-slate-900">{{ __('app.edit_task') }}</h3>
                    <p class="text-xs text-slate-500">{{ __('app.created_by') }}: {{ $task->creator->name }} ({{ $task->created_at->translatedFormat('j. M H:i') }})</p>
                </div>
            </div>
            <button type="button" @click="document.getElementById('edit-modal-wrapper')?.remove()" class="p-1.5 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100">
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
                <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('app.task_title') }}</label>
                <input type="text" name="title" value="{{ $task->title }}" required
                       class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 focus:border-amber-500 focus:bg-white rounded-lg text-slate-900 font-semibold text-sm">
            </div>

            <!-- Optional Description -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('app.description') }} ({{ __('app.optional') }})</label>
                <textarea name="description" rows="3" placeholder="{{ __('app.description_placeholder') }}"
                          class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 focus:border-amber-500 focus:bg-white rounded-lg text-slate-900 text-xs resize-none">{{ $task->description }}</textarea>
            </div>

            <!-- Category -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">{{ __('app.category') }}</label>
                <div class="grid grid-cols-3 gap-2 text-xs max-h-36 overflow-y-auto p-0.5">
                    @if(isset($categories) && count($categories) > 0)
                        @foreach($categories as $cat)
                            <label class="flex items-center gap-1.5 p-2 rounded-lg bg-slate-50 border border-slate-200 cursor-pointer hover:border-amber-400 has-[:checked]:border-amber-500 has-[:checked]:bg-amber-50 has-[:checked]:text-amber-950 font-semibold truncate transition">
                                <input type="radio" name="category_id" value="{{ $cat->id }}" {{ ($task->category_id == $cat->id || $task->category === $cat->slug) ? 'checked' : '' }} class="hidden">
                                <span class="text-sm flex-shrink-0">{{ $cat->emoji }}</span>
                                <span class="truncate">{{ $cat->name }}</span>
                            </label>
                        @endforeach
                    @else
                        <label class="flex items-center gap-1.5 p-2 rounded-lg bg-slate-50 border border-slate-200 cursor-pointer hover:border-amber-400 has-[:checked]:border-amber-500 has-[:checked]:bg-amber-50 has-[:checked]:text-amber-950 font-semibold truncate transition">
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
                    <label class="block text-xs font-bold text-slate-700">{{ __('app.links_and_youtube') }}</label>
                    <span class="text-[11px] text-slate-400">{{ __('app.links_hint') }}</span>
                </div>
                
                <div class="space-y-2">
                    <template x-for="(link, index) in editLinks" :key="index">
                        <div class="flex items-center gap-2">
                            <span class="text-slate-400 text-xs">🔗</span>
                            <input type="text" :name="'links[' + index + ']'" x-model="editLinks[index]" placeholder="{{ __('app.links_placeholder') }}"
                                   class="flex-1 px-3 py-2 bg-slate-50 border border-slate-300 focus:border-amber-500 focus:bg-white rounded-lg text-slate-900 text-xs">
                            <button type="button" @click="editLinks.splice(index, 1)" class="p-1.5 text-slate-400 hover:text-rose-600 rounded-md hover:bg-rose-50 text-xs" title="{{ __('app.delete') }}">✕</button>
                        </div>
                    </template>
                </div>

                <button type="button" @click="editLinks.push('')"
                        class="text-xs font-bold text-amber-700 hover:text-amber-800 flex items-center gap-1 py-1">
                    <span>➕</span> {{ __('app.add_another_link') }}
                </button>
            </div>

            <!-- Scheduled Date (Optional) -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('app.schedule_date') }}</label>
                <input type="date" name="scheduled_date" value="{{ $task->scheduled_date ? $task->scheduled_date->toDateString() : '' }}"
                       class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 focus:border-amber-500 focus:bg-white rounded-lg text-slate-900 text-xs">
                <p class="text-[10px] text-slate-400 mt-1">{{ __('app.schedule_date_hint') }}</p>
            </div>

            <!-- Image Management -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">{{ __('app.image') }}</label>
                
                @if($task->image_url)
                    <div x-show="!removeImage" class="relative rounded-lg overflow-hidden bg-slate-100 border border-slate-200 mb-2 p-2 flex items-center justify-between gap-3">
                        <img src="{{ $task->image_url }}" alt="{{ __('app.image') }}" class="w-16 h-12 object-cover rounded-md">
                        <div class="flex-1 text-xs text-slate-500 truncate">{{ __('app.existing_image') }}</div>
                        <button type="button" @click="removeImage = true" class="px-2.5 py-1 bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-bold rounded-md transition">
                            🗑️ {{ __('app.remove_image') }}
                        </button>
                        <input type="hidden" name="remove_image" :value="removeImage ? 1 : 0">
                    </div>
                @endif

                <div class="flex items-center gap-3">
                    <label class="flex-1 flex items-center justify-center gap-2 px-4 py-2 bg-slate-50 border border-dashed border-slate-300 hover:border-amber-500 rounded-lg cursor-pointer text-xs font-semibold text-slate-600 hover:text-slate-900 transition">
                        <span>📷</span>
                        <span x-text="newImagePreview ? '{{ __('app.change_image') }}' : '{{ __('app.upload_new_image') }}'"></span>
                        <input type="file" name="image_file" accept="image/*" class="hidden"
                               @change="const file = $event.target.files[0]; if (file) { const reader = new FileReader(); reader.onload = (e) => { newImagePreview = e.target.result; removeImage = false; }; reader.readAsDataURL(file); }">
                    </label>
                    <template x-if="newImagePreview">
                        <div class="relative w-12 h-10 rounded-md overflow-hidden border border-slate-200 shadow-sm flex-shrink-0">
                            <img :src="newImagePreview" class="w-full h-full object-cover">
                            <button type="button" @click="newImagePreview = null" class="absolute inset-0 bg-black/40 text-white flex items-center justify-center text-xs">✕</button>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Footer Buttons -->
            <div class="flex items-center justify-between pt-3 border-t border-slate-100">
                <button type="button"
                        @click="window.openMathDeleteConfirm('{{ addslashes($task->title) }}', '{{ route('ideas.destroy', $task->id) }}')"
                        class="px-3 py-2 text-rose-600 hover:bg-rose-50 rounded-lg text-xs font-bold transition flex items-center gap-1.5">
                    <span>🗑️</span> {{ __('app.delete') }}
                </button>

                <div class="flex items-center gap-2">
                    <button type="button" @click="document.getElementById('edit-modal-wrapper')?.remove()" class="px-4 py-2 text-xs font-bold text-slate-500 hover:text-slate-800">
                        {{ __('app.cancel') }}
                    </button>
                    <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-amber-500 to-amber-400 hover:from-amber-400 text-slate-950 font-bold text-xs rounded-lg shadow-md shadow-amber-500/20 active:scale-95 transition">
                        {{ __('app.save_changes') }} 💾
                    </button>
                </div>
            </div>
        </form>

        <!-- Task Discussion / Comments Section -->
        <div class="border-t border-slate-200 pt-4 mt-2">
            @include('ideas.partials.comments_section')
        </div>

    </div>
</div>
