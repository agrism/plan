@if($backlogIdeas->isEmpty())
    <div class="p-8 text-center rounded-3xl bg-white border border-dashed border-slate-300 text-slate-500 shadow-sm">
        <span class="text-3xl block mb-2">📋</span>
        <p class="text-sm font-bold text-slate-800">Uzdevumu krātuve pašlaik ir tukša!</p>
        <p class="text-xs text-slate-500 mt-1">Piespied pogu „Pievienot”, lai ierakstītu jaunu darāmo darbu.</p>
    </div>
@else
    @foreach($backlogIdeas as $idea)
        <div class="group relative rounded-2xl bg-white border border-slate-200/90 hover:border-slate-300 transition p-4 shadow-sm hover:shadow-md flex flex-col gap-3">
            
            <!-- Card Header: Category, Author Badge & Edit Button -->
            <div class="flex items-center justify-between gap-2">
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg text-xs font-bold border {{ $idea->category_badge_class }}">
                    <span>{{ $idea->category_emoji }}</span>
                    <span>{{ $idea->category_name }}</span>
                </span>

                <div class="flex items-center gap-2">
                    <div class="flex items-center gap-1.5 text-xs text-slate-500 font-medium" title="Izveidoja: {{ $idea->creator->name }}">
                        <span class="text-sm">{{ $idea->creator->avatar ?? '👤' }}</span>
                        <span class="font-bold text-slate-700">{{ explode(' ', $idea->creator->name)[0] }}</span>
                    </div>

                    <!-- Edit Button -->
                    <button hx-get="{{ route('ideas.edit', $idea->id) }}"
                            hx-target="#edit-modal-slot"
                            hx-swap="innerHTML"
                            type="button"
                            title="Labot uzdevumu"
                            class="p-1 rounded-lg text-slate-400 hover:text-amber-600 hover:bg-amber-50 transition text-xs">
                        ✏️
                    </button>
                </div>
            </div>

            <!-- Optional Image -->
            @if($idea->image_url)
                <div class="w-full h-36 rounded-xl overflow-hidden bg-slate-100 border border-slate-100">
                    <img src="{{ $idea->image_url }}" alt="{{ $idea->title }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                </div>
            @endif

            <!-- Title -->
            <h3 class="text-sm font-bold text-slate-900 leading-snug">
                {{ $idea->title }}
            </h3>

            <!-- Optional Description -->
            @if($idea->description)
                <p class="text-xs text-slate-600 leading-relaxed whitespace-pre-line bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                    {{ $idea->description }}
                </p>
            @endif

            <!-- Dynamic Links & YouTube Video Embeds -->
            @if(!empty($idea->processed_links))
                <div class="space-y-2 pt-1">
                    @foreach($idea->processed_links as $linkItem)
                        <!-- If it's a YouTube video, render responsive embed player above the link -->
                        @if($linkItem['embed_url'])
                            <div class="rounded-xl overflow-hidden border border-slate-200 bg-black aspect-video w-full shadow-sm">
                                <iframe src="{{ $linkItem['embed_url'] }}"
                                        title="YouTube video"
                                        class="w-full h-full"
                                        frameborder="0"
                                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                        allowfullscreen>
                                </iframe>
                            </div>
                        @endif

                        <!-- Clickable Link Badge -->
                        <div class="flex items-center gap-1.5 text-xs">
                            <a href="{{ $linkItem['url'] }}" target="_blank" rel="noopener noreferrer"
                               class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 hover:text-slate-900 font-medium truncate max-w-full transition">
                                <span>{{ $linkItem['youtube_id'] ? '▶️' : '🔗' }}</span>
                                <span class="truncate">{{ $linkItem['domain'] }}</span>
                                <svg class="w-3 h-3 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                            </a>
                        </div>
                    @endforeach
                </div>
            @endif

            <!-- Card Actions (Reactions & Schedule Button) -->
            <div class="flex items-center justify-between pt-2 border-t border-slate-100 mt-1">
                
                <!-- Thumbs Up Reaction -->
                <button hx-post="{{ route('ideas.react', $idea->id) }}"
                        hx-target="body"
                        type="button"
                        class="flex items-center gap-1.5 px-2.5 py-1 rounded-xl text-xs font-bold transition {{ $idea->reactions->where('user_id', auth()->id())->isNotEmpty() ? 'bg-amber-100 text-amber-900 border border-amber-300 shadow-sm' : 'bg-slate-100 text-slate-600 hover:text-slate-900 hover:bg-slate-200' }}">
                    <span>👍</span>
                    <span>{{ $idea->reactions->count() }}</span>
                </button>

                <!-- Schedule Popup Menu with Alpine -->
                <div class="relative" x-data="{ schedMenu: false }">
                    <button @click="schedMenu = !schedMenu"
                            type="button"
                            class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-amber-50 hover:bg-amber-100 border border-amber-200 text-amber-800 text-xs font-bold transition">
                        <span>📅</span>
                        <span>Ieplānot</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>

                    <!-- Schedule Dropdown Options -->
                    <div x-show="schedMenu" @click.outside="schedMenu = false" x-cloak
                         class="absolute right-0 bottom-full mb-2 w-48 bg-white border border-slate-200 rounded-2xl shadow-2xl p-1.5 z-50">
                        <div class="text-[10px] font-bold uppercase text-slate-400 px-2.5 py-1">Pārcelt uz dienu:</div>
                        
                        <!-- Friday -->
                        <form action="{{ route('ideas.schedule', $idea->id) }}" method="POST"
                              hx-patch="{{ route('ideas.schedule', $idea->id) }}"
                              hx-target="body">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="scheduled_date" value="{{ $friday }}">
                            <button type="submit" class="w-full text-left px-2.5 py-1.5 text-xs text-slate-700 hover:bg-slate-100 rounded-xl transition flex items-center justify-between">
                                <span>📅 Piektdiena</span>
                            </button>
                        </form>

                        <!-- Saturday -->
                        <form action="{{ route('ideas.schedule', $idea->id) }}" method="POST"
                              hx-patch="{{ route('ideas.schedule', $idea->id) }}"
                              hx-target="body">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="scheduled_date" value="{{ $saturday }}">
                            <button type="submit" class="w-full text-left px-2.5 py-1.5 text-xs text-slate-700 hover:bg-slate-100 rounded-xl transition flex items-center justify-between">
                                <span>📅 Sestdiena</span>
                            </button>
                        </form>

                        <!-- Sunday -->
                        <form action="{{ route('ideas.schedule', $idea->id) }}" method="POST"
                              hx-patch="{{ route('ideas.schedule', $idea->id) }}"
                              hx-target="body">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="scheduled_date" value="{{ $sunday }}">
                            <button type="submit" class="w-full text-left px-2.5 py-1.5 text-xs text-slate-700 hover:bg-slate-100 rounded-xl transition flex items-center justify-between">
                                <span>📅 Svētdiena</span>
                            </button>
                        </form>

                        <div class="border-t border-slate-100 my-1"></div>

                        <!-- Delete button -->
                        <form action="{{ route('ideas.destroy', $idea->id) }}" method="POST"
                              hx-delete="{{ route('ideas.destroy', $idea->id) }}"
                              hx-target="body">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full text-left px-2.5 py-1.5 text-xs text-rose-600 hover:bg-rose-50 rounded-xl transition">
                                🗑️ Dzēst uzdevumu
                            </button>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    @endforeach
@endif
