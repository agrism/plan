<div class="space-y-5">

    <!-- Day 1: Piektdiena -->
    <div class="rounded-3xl bg-white border border-slate-200/90 p-4 sm:p-5 shadow-sm">
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-2">
                <span class="text-xl">📅</span>
                <div>
                    <h3 class="text-sm sm:text-base font-extrabold text-slate-900">Piektdiena</h3>
                    <p class="text-[11px] text-slate-500 font-medium">{{ \Carbon\Carbon::parse($friday)->translatedFormat('j. F') }}</p>
                </div>
            </div>
            <span class="text-xs font-bold text-slate-600 bg-slate-100 px-2.5 py-1 rounded-xl">
                {{ $fridayTasks->where('is_completed', true)->count() }} / {{ $fridayTasks->count() }}
            </span>
        </div>

        @if($fridayTasks->isEmpty())
            <p class="text-xs text-slate-400 italic py-2">Nav ieplānotu uzdevumu piektdienai.</p>
        @else
            <div class="space-y-3">
                @foreach($fridayTasks as $task)
                    <div class="p-3.5 rounded-2xl bg-slate-50/80 border border-slate-200/80 hover:border-slate-300 hover:bg-white transition space-y-2.5 {{ $task->is_completed ? 'opacity-65 bg-slate-100/60' : '' }}">
                        
                        <!-- Top row: Checkbox, Title, Badges, Edit & Unschedule -->
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-start gap-2.5 flex-1 min-w-0">
                                <button hx-patch="{{ route('ideas.toggle', $task->id) }}"
                                        hx-target="body"
                                        type="button"
                                        class="mt-0.5 w-5 h-5 rounded-lg border flex-shrink-0 flex items-center justify-center transition {{ $task->is_completed ? 'bg-emerald-500 border-emerald-500 text-white font-bold' : 'border-slate-300 hover:border-amber-500 bg-white' }}">
                                    @if($task->is_completed)
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                    @endif
                                </button>

                                <div class="min-w-0 flex-1">
                                    <span class="text-xs sm:text-sm font-semibold {{ $task->is_completed ? 'line-through text-slate-400' : 'text-slate-900' }} leading-snug">
                                        {{ $task->title }}
                                    </span>
                                    @if($task->scheduled_time_slot)
                                        <span class="text-[10px] text-amber-700 block font-bold mt-0.5">🕒 {{ $task->scheduled_time_slot }}</span>
                                    @endif
                                </div>
                            </div>

                            <div class="flex items-center gap-1.5 flex-shrink-0">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold border {{ $task->category_badge_class }}">
                                    <span>{{ $task->category_emoji }}</span>
                                    <span class="hidden sm:inline">{{ $task->category_name }}</span>
                                </span>

                                <span class="text-xs" title="Izveidoja: {{ $task->creator->name }}">{{ $task->creator->avatar ?? '👤' }}</span>

                                <!-- Edit Button -->
                                <button hx-get="{{ route('ideas.edit', $task->id) }}"
                                        hx-target="#edit-modal-slot"
                                        hx-swap="innerHTML"
                                        type="button"
                                        title="Labot uzdevumu"
                                        class="p-1 rounded-lg text-slate-400 hover:text-amber-600 hover:bg-amber-50 transition text-xs">
                                    ✏️
                                </button>

                                <!-- Move back to Backlog button -->
                                <form action="{{ route('ideas.schedule', $task->id) }}" method="POST"
                                      hx-patch="{{ route('ideas.schedule', $task->id) }}"
                                      hx-target="body">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="scheduled_date" value="null">
                                    <button type="submit" title="Pārcelt atpakaļ uz Uzdevumu krātuvi" class="text-slate-400 hover:text-amber-600 p-1 text-xs transition rounded-lg hover:bg-slate-100">
                                        ↩️
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Optional Image -->
                        @if($task->image_url)
                            <div class="w-full h-32 rounded-xl overflow-hidden bg-slate-100 border border-slate-100">
                                <img src="{{ $task->image_url }}" alt="{{ $task->title }}" class="w-full h-full object-cover">
                            </div>
                        @endif

                        <!-- Optional Description -->
                        @if($task->description)
                            <p class="text-xs text-slate-600 leading-relaxed whitespace-pre-line bg-white/70 p-2.5 rounded-xl border border-slate-200/60">
                                {{ $task->description }}
                            </p>
                        @endif

                        <!-- Dynamic Links & YouTube Video Embeds -->
                        @if(!empty($task->processed_links))
                            <div class="space-y-2 pt-0.5">
                                @foreach($task->processed_links as $linkItem)
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

                                    <div class="flex items-center gap-1.5 text-xs">
                                        <a href="{{ $linkItem['url'] }}" target="_blank" rel="noopener noreferrer"
                                           class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-white hover:bg-slate-100 text-slate-700 hover:text-slate-900 border border-slate-200 font-medium truncate max-w-full transition">
                                            <span>{{ $linkItem['youtube_id'] ? '▶️' : '🔗' }}</span>
                                            <span class="truncate">{{ $linkItem['domain'] }}</span>
                                            <svg class="w-3 h-3 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Day 2: Sestdiena -->
    <div class="rounded-3xl bg-white border border-slate-200/90 p-4 sm:p-5 shadow-sm">
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-2">
                <span class="text-xl">📅</span>
                <div>
                    <h3 class="text-sm sm:text-base font-extrabold text-slate-900">Sestdiena</h3>
                    <p class="text-[11px] text-slate-500 font-medium">{{ \Carbon\Carbon::parse($saturday)->translatedFormat('j. F') }}</p>
                </div>
            </div>
            <span class="text-xs font-bold text-slate-600 bg-slate-100 px-2.5 py-1 rounded-xl">
                {{ $saturdayTasks->where('is_completed', true)->count() }} / {{ $saturdayTasks->count() }}
            </span>
        </div>

        @if($saturdayTasks->isEmpty())
            <p class="text-xs text-slate-400 italic py-2">Nav ieplānotu uzdevumu sestdienai.</p>
        @else
            <div class="space-y-3">
                @foreach($saturdayTasks as $task)
                    <div class="p-3.5 rounded-2xl bg-slate-50/80 border border-slate-200/80 hover:border-slate-300 hover:bg-white transition space-y-2.5 {{ $task->is_completed ? 'opacity-65 bg-slate-100/60' : '' }}">
                        
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-start gap-2.5 flex-1 min-w-0">
                                <button hx-patch="{{ route('ideas.toggle', $task->id) }}"
                                        hx-target="body"
                                        type="button"
                                        class="mt-0.5 w-5 h-5 rounded-lg border flex-shrink-0 flex items-center justify-center transition {{ $task->is_completed ? 'bg-emerald-500 border-emerald-500 text-white font-bold' : 'border-slate-300 hover:border-amber-500 bg-white' }}">
                                    @if($task->is_completed)
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                    @endif
                                </button>

                                <div class="min-w-0 flex-1">
                                    <span class="text-xs sm:text-sm font-semibold {{ $task->is_completed ? 'line-through text-slate-400' : 'text-slate-900' }} leading-snug">
                                        {{ $task->title }}
                                    </span>
                                    @if($task->scheduled_time_slot)
                                        <span class="text-[10px] text-amber-700 block font-bold mt-0.5">🕒 {{ $task->scheduled_time_slot }}</span>
                                    @endif
                                </div>
                            </div>

                            <div class="flex items-center gap-1.5 flex-shrink-0">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold border {{ $task->category_badge_class }}">
                                    <span>{{ $task->category_emoji }}</span>
                                    <span class="hidden sm:inline">{{ $task->category_name }}</span>
                                </span>

                                <span class="text-xs" title="Izveidoja: {{ $task->creator->name }}">{{ $task->creator->avatar ?? '👤' }}</span>

                                <!-- Edit Button -->
                                <button hx-get="{{ route('ideas.edit', $task->id) }}"
                                        hx-target="#edit-modal-slot"
                                        hx-swap="innerHTML"
                                        type="button"
                                        title="Labot uzdevumu"
                                        class="p-1 rounded-lg text-slate-400 hover:text-amber-600 hover:bg-amber-50 transition text-xs">
                                    ✏️
                                </button>

                                <!-- Move back to Backlog button -->
                                <form action="{{ route('ideas.schedule', $task->id) }}" method="POST"
                                      hx-patch="{{ route('ideas.schedule', $task->id) }}"
                                      hx-target="body">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="scheduled_date" value="null">
                                    <button type="submit" title="Pārcelt atpakaļ uz Uzdevumu krātuvi" class="text-slate-400 hover:text-amber-600 p-1 text-xs transition rounded-lg hover:bg-slate-100">
                                        ↩️
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Optional Image -->
                        @if($task->image_url)
                            <div class="w-full h-32 rounded-xl overflow-hidden bg-slate-100 border border-slate-100">
                                <img src="{{ $task->image_url }}" alt="{{ $task->title }}" class="w-full h-full object-cover">
                            </div>
                        @endif

                        <!-- Optional Description -->
                        @if($task->description)
                            <p class="text-xs text-slate-600 leading-relaxed whitespace-pre-line bg-white/70 p-2.5 rounded-xl border border-slate-200/60">
                                {{ $task->description }}
                            </p>
                        @endif

                        <!-- Dynamic Links & YouTube Video Embeds -->
                        @if(!empty($task->processed_links))
                            <div class="space-y-2 pt-0.5">
                                @foreach($task->processed_links as $linkItem)
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

                                    <div class="flex items-center gap-1.5 text-xs">
                                        <a href="{{ $linkItem['url'] }}" target="_blank" rel="noopener noreferrer"
                                           class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-white hover:bg-slate-100 text-slate-700 hover:text-slate-900 border border-slate-200 font-medium truncate max-w-full transition">
                                            <span>{{ $linkItem['youtube_id'] ? '▶️' : '🔗' }}</span>
                                            <span class="truncate">{{ $linkItem['domain'] }}</span>
                                            <svg class="w-3 h-3 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Day 3: Svētdiena -->
    <div class="rounded-3xl bg-white border border-slate-200/90 p-4 sm:p-5 shadow-sm">
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-2">
                <span class="text-xl">📅</span>
                <div>
                    <h3 class="text-sm sm:text-base font-extrabold text-slate-900">Svētdiena</h3>
                    <p class="text-[11px] text-slate-500 font-medium">{{ \Carbon\Carbon::parse($sunday)->translatedFormat('j. F') }}</p>
                </div>
            </div>
            <span class="text-xs font-bold text-slate-600 bg-slate-100 px-2.5 py-1 rounded-xl">
                {{ $sundayTasks->where('is_completed', true)->count() }} / {{ $sundayTasks->count() }}
            </span>
        </div>

        @if($sundayTasks->isEmpty())
            <p class="text-xs text-slate-400 italic py-2">Nav ieplānotu uzdevumu svētdienai.</p>
        @else
            <div class="space-y-3">
                @foreach($sundayTasks as $task)
                    <div class="p-3.5 rounded-2xl bg-slate-50/80 border border-slate-200/80 hover:border-slate-300 hover:bg-white transition space-y-2.5 {{ $task->is_completed ? 'opacity-65 bg-slate-100/60' : '' }}">
                        
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-start gap-2.5 flex-1 min-w-0">
                                <button hx-patch="{{ route('ideas.toggle', $task->id) }}"
                                        hx-target="body"
                                        type="button"
                                        class="mt-0.5 w-5 h-5 rounded-lg border flex-shrink-0 flex items-center justify-center transition {{ $task->is_completed ? 'bg-emerald-500 border-emerald-500 text-white font-bold' : 'border-slate-300 hover:border-amber-500 bg-white' }}">
                                    @if($task->is_completed)
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                    @endif
                                </button>

                                <div class="min-w-0 flex-1">
                                    <span class="text-xs sm:text-sm font-semibold {{ $task->is_completed ? 'line-through text-slate-400' : 'text-slate-900' }} leading-snug">
                                        {{ $task->title }}
                                    </span>
                                    @if($task->scheduled_time_slot)
                                        <span class="text-[10px] text-amber-700 block font-bold mt-0.5">🕒 {{ $task->scheduled_time_slot }}</span>
                                    @endif
                                </div>
                            </div>

                            <div class="flex items-center gap-1.5 flex-shrink-0">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold border {{ $task->category_badge_class }}">
                                    <span>{{ $task->category_emoji }}</span>
                                    <span class="hidden sm:inline">{{ $task->category_name }}</span>
                                </span>

                                <span class="text-xs" title="Izveidoja: {{ $task->creator->name }}">{{ $task->creator->avatar ?? '👤' }}</span>

                                <!-- Edit Button -->
                                <button hx-get="{{ route('ideas.edit', $task->id) }}"
                                        hx-target="#edit-modal-slot"
                                        hx-swap="innerHTML"
                                        type="button"
                                        title="Labot uzdevumu"
                                        class="p-1 rounded-lg text-slate-400 hover:text-amber-600 hover:bg-amber-50 transition text-xs">
                                    ✏️
                                </button>

                                <!-- Move back to Backlog button -->
                                <form action="{{ route('ideas.schedule', $task->id) }}" method="POST"
                                      hx-patch="{{ route('ideas.schedule', $task->id) }}"
                                      hx-target="body">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="scheduled_date" value="null">
                                    <button type="submit" title="Pārcelt atpakaļ uz Uzdevumu krātuvi" class="text-slate-400 hover:text-amber-600 p-1 text-xs transition rounded-lg hover:bg-slate-100">
                                        ↩️
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Optional Image -->
                        @if($task->image_url)
                            <div class="w-full h-32 rounded-xl overflow-hidden bg-slate-100 border border-slate-100">
                                <img src="{{ $task->image_url }}" alt="{{ $task->title }}" class="w-full h-full object-cover">
                            </div>
                        @endif

                        <!-- Optional Description -->
                        @if($task->description)
                            <p class="text-xs text-slate-600 leading-relaxed whitespace-pre-line bg-white/70 p-2.5 rounded-xl border border-slate-200/60">
                                {{ $task->description }}
                            </p>
                        @endif

                        <!-- Dynamic Links & YouTube Video Embeds -->
                        @if(!empty($task->processed_links))
                            <div class="space-y-2 pt-0.5">
                                @foreach($task->processed_links as $linkItem)
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

                                    <div class="flex items-center gap-1.5 text-xs">
                                        <a href="{{ $linkItem['url'] }}" target="_blank" rel="noopener noreferrer"
                                           class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-white hover:bg-slate-100 text-slate-700 hover:text-slate-900 border border-slate-200 font-medium truncate max-w-full transition">
                                            <span>{{ $linkItem['youtube_id'] ? '▶️' : '🔗' }}</span>
                                            <span class="truncate">{{ $linkItem['domain'] }}</span>
                                            <svg class="w-3 h-3 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                    </div>
                @endforeach
            </div>
        @endif
    </div>

</div>
