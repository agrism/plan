<div class="space-y-5">
    @forelse($scheduledDates as $dateStr)
        @php
            $tasksForDay = $scheduledTasksByDate->get($dateStr, collect());
            $carbonDate = \Carbon\Carbon::parse($dateStr);
            $isToday = ($dateStr === ($today ?? \Carbon\Carbon::now()->toDateString()));
            $isTomorrow = ($dateStr === ($tomorrow ?? \Carbon\Carbon::tomorrow()->toDateString()));
            $completedCount = $tasksForDay->where('is_completed', true)->count();
            $totalCount = $tasksForDay->count();
        @endphp

        <div class="rounded-3xl bg-white border border-slate-200/90 p-4 sm:p-5 shadow-sm">
            <!-- Day Header -->
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-2.5">
                    <span class="text-xl">📅</span>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-sm sm:text-base font-extrabold text-slate-900 capitalize">
                                {{ $carbonDate->translatedFormat('l') }}
                            </h3>
                            @if($isToday)
                                <span class="px-2 py-0.5 rounded-md bg-amber-100 text-amber-900 font-extrabold text-[10px] uppercase tracking-wider border border-amber-300">Šodien</span>
                            @elseif($isTomorrow)
                                <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 font-bold text-[10px] uppercase tracking-wider border border-slate-200">Rīt</span>
                            @endif
                        </div>
                        <p class="text-[11px] text-slate-500 font-medium">{{ $carbonDate->translatedFormat('j. F') }}</p>
                    </div>
                </div>

                <span class="text-xs font-bold text-slate-600 bg-slate-100 px-2.5 py-1 rounded-xl">
                    {{ $completedCount }} / {{ $totalCount }}
                </span>
            </div>

            <!-- Day Tasks List -->
            @if($tasksForDay->isEmpty())
                <p class="text-xs text-slate-400 italic py-2">Nav ieplānotu uzdevumu šai dienai.</p>
            @else
                <div class="space-y-2.5">
                    @foreach($tasksForDay as $task)
                        @php
                            $hasDetails = !empty($task->description) || !empty($task->links) || !empty($task->image_url);
                            $hasYouTube = false;
                            if (!empty($task->processed_links)) {
                                foreach ($task->processed_links as $pl) {
                                    if (!empty($pl['embed_url'])) {
                                        $hasYouTube = true;
                                        break;
                                    }
                                }
                            }
                        @endphp

                        <div class="p-3.5 rounded-2xl bg-slate-50/80 border border-slate-200/80 hover:border-slate-300 hover:bg-white transition space-y-2.5 {{ $task->is_completed ? 'opacity-65 bg-slate-100/60' : '' }}"
                             x-data="{ expanded: false }">
                            
                            <!-- Top row: Checkbox, Title, Badges, Calendar Reschedule, Edit & Unschedule -->
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-start gap-2.5 flex-1 min-w-0">
                                    <button hx-patch="{{ route('ideas.toggle', $task->id) }}"
                                            hx-target="body"
                                            @click.stop
                                            type="button"
                                            class="mt-0.5 w-5 h-5 rounded-lg border flex-shrink-0 flex items-center justify-center transition {{ $task->is_completed ? 'bg-emerald-500 border-emerald-500 text-white font-bold' : 'border-slate-300 hover:border-amber-500 bg-white' }}">
                                        @if($task->is_completed)
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                        @endif
                                    </button>

                                    <div @click="{{ $hasDetails ? 'expanded = !expanded' : '' }}"
                                         class="min-w-0 flex-1 space-y-1 {{ $hasDetails ? 'cursor-pointer select-none' : '' }}">
                                        <span class="text-xs sm:text-sm font-semibold {{ $task->is_completed ? 'line-through text-slate-400' : 'text-slate-900' }} leading-snug block">
                                            {{ $task->title }}
                                        </span>
                                        
                                        @if($task->scheduled_time_slot)
                                            <span class="text-[10px] text-amber-700 block font-bold">🕒 {{ $task->scheduled_time_slot }}</span>
                                        @endif

                                        <!-- Quick indicators for details -->
                                        @if($hasDetails)
                                            <div class="flex items-center gap-1.5 flex-wrap text-[10px] text-slate-400 pt-0.5">
                                                @if($task->description)
                                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded bg-white text-slate-600 border border-slate-200">📝 Apraksts</span>
                                                @endif
                                                @if(!empty($task->links) && count($task->links) > 0)
                                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded bg-white text-slate-600 border border-slate-200">
                                                        <span>{{ $hasYouTube ? '▶️' : '🔗' }}</span>
                                                        <span>{{ count($task->links) }}</span>
                                                    </span>
                                                @endif
                                                @if($task->image_url)
                                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded bg-white text-slate-600 border border-slate-200">🖼️</span>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <div class="flex items-center gap-1.5 flex-shrink-0" @click.stop>
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

                                    <!-- Reschedule Calendar Picker Dropdown -->
                                    <div class="relative" x-data="{ schedMenu: false }">
                                        <button @click="schedMenu = !schedMenu"
                                                type="button"
                                                title="Pārcelt uz citu datumu"
                                                class="p-1 rounded-lg text-slate-400 hover:text-amber-600 hover:bg-amber-50 transition text-xs">
                                            📅
                                        </button>

                                        <!-- Calendar Popup -->
                                        <div x-show="schedMenu" @click.outside="schedMenu = false" x-cloak
                                             x-data="calendarPicker('{{ $task->scheduled_date ? $task->scheduled_date->toDateString() : '' }}')"
                                             class="absolute right-0 top-full mt-1 w-72 sm:w-80 bg-white border border-slate-200 rounded-3xl shadow-2xl p-3.5 z-50 space-y-3">
                                            
                                            <!-- Header / Month navigation -->
                                            <div class="flex items-center justify-between px-1">
                                                <h4 class="text-xs font-extrabold text-slate-900 capitalize" x-text="monthYearString"></h4>
                                                <div class="flex items-center gap-1">
                                                    <button type="button" @click="prevMonth()" class="p-1 rounded-lg text-slate-500 hover:bg-slate-100 hover:text-slate-800 text-xs font-bold">◀</button>
                                                    <button type="button" @click="nextMonth()" class="p-1 rounded-lg text-slate-500 hover:bg-slate-100 hover:text-slate-800 text-xs font-bold">▶</button>
                                                </div>
                                            </div>

                                            <!-- Weekdays row -->
                                            <div class="grid grid-cols-7 gap-1 text-center text-[10px] font-bold text-slate-400">
                                                <template x-for="dn in dayNames">
                                                    <div x-text="dn" class="py-0.5"></div>
                                                </template>
                                            </div>

                                            <!-- Days Grid -->
                                            <div class="grid grid-cols-7 gap-1 text-center text-xs">
                                                <template x-for="dayObj in daysInMonth">
                                                    <div>
                                                        <template x-if="dayObj.isCurrentMonth">
                                                            <form action="{{ route('ideas.schedule', $task->id) }}" method="POST"
                                                                  hx-patch="{{ route('ideas.schedule', $task->id) }}"
                                                                  hx-target="body">
                                                                @csrf
                                                                @method('PATCH')
                                                                <input type="hidden" name="scheduled_date" :value="dayObj.dateString">
                                                                <button type="submit"
                                                                        class="w-full aspect-square rounded-xl text-xs font-semibold flex items-center justify-center transition"
                                                                        :class="{
                                                                            'bg-amber-500 text-slate-950 font-bold shadow-sm ring-2 ring-amber-400': dayObj.isSelected,
                                                                            'border border-amber-300 font-bold text-amber-900 bg-amber-50': dayObj.isToday && !dayObj.isSelected,
                                                                            'text-slate-700 hover:bg-amber-100 hover:text-amber-900': !dayObj.isSelected && !dayObj.isToday
                                                                        }">
                                                                    <span x-text="dayObj.day"></span>
                                                                </button>
                                                            </form>
                                                        </template>
                                                        <template x-if="!dayObj.isCurrentMonth">
                                                            <span class="w-full aspect-square rounded-xl text-xs text-slate-300 flex items-center justify-center select-none" x-text="dayObj.day"></span>
                                                        </template>
                                                    </div>
                                                </template>
                                            </div>

                                            <!-- Quick Shortcuts -->
                                            <div class="pt-2 border-t border-slate-100 space-y-1">
                                                <div class="text-[10px] font-bold uppercase text-slate-400 px-1">Ātrās izvēles:</div>
                                                <div class="grid grid-cols-3 gap-1.5 text-xs">
                                                    <form action="{{ route('ideas.schedule', $task->id) }}" method="POST"
                                                          hx-patch="{{ route('ideas.schedule', $task->id) }}"
                                                          hx-target="body">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="hidden" name="scheduled_date" value="{{ $today ?? \Carbon\Carbon::now()->toDateString() }}">
                                                        <button type="submit" class="w-full py-1 text-center rounded-lg bg-slate-100 hover:bg-amber-100 hover:text-amber-900 text-slate-700 font-medium text-[11px] transition">
                                                            Šodien
                                                        </button>
                                                    </form>

                                                    <form action="{{ route('ideas.schedule', $task->id) }}" method="POST"
                                                          hx-patch="{{ route('ideas.schedule', $task->id) }}"
                                                          hx-target="body">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="hidden" name="scheduled_date" value="{{ $tomorrow ?? \Carbon\Carbon::tomorrow()->toDateString() }}">
                                                        <button type="submit" class="w-full py-1 text-center rounded-lg bg-slate-100 hover:bg-amber-100 hover:text-amber-900 text-slate-700 font-medium text-[11px] transition">
                                                            Rīt
                                                        </button>
                                                    </form>

                                                    <form action="{{ route('ideas.schedule', $task->id) }}" method="POST"
                                                          hx-patch="{{ route('ideas.schedule', $task->id) }}"
                                                          hx-target="body">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="hidden" name="scheduled_date" value="{{ $friday }}">
                                                        <button type="submit" class="w-full py-1 text-center rounded-lg bg-slate-100 hover:bg-amber-100 hover:text-amber-900 text-slate-700 font-medium text-[11px] transition">
                                                            Piektdiena
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>

                                            <!-- Move back to Backlog from modal -->
                                            <div class="border-t border-slate-100 pt-2 flex items-center justify-between text-xs">
                                                <form action="{{ route('ideas.schedule', $task->id) }}" method="POST"
                                                      hx-patch="{{ route('ideas.schedule', $task->id) }}"
                                                      hx-target="body">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="scheduled_date" value="null">
                                                    <button type="submit" class="text-[11px] font-semibold text-slate-600 hover:text-amber-700 flex items-center gap-1">
                                                        <span>↩️</span> Uz krātuvi
                                                    </button>
                                                </form>

                                                <button type="button" @click="schedMenu = false" class="px-2 py-1 text-slate-400 hover:text-slate-600 text-[11px]">
                                                    Aizvērt
                                                </button>
                                            </div>

                                        </div>
                                    </div>

                                    <!-- Quick Move back to Backlog button -->
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

                                    <!-- Expand arrow button if details exist -->
                                    @if($hasDetails)
                                        <button type="button" @click.stop="expanded = !expanded"
                                                title="Rādīt detaļas"
                                                class="p-1 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100 transition">
                                            <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="expanded ? 'rotate-180 text-amber-600' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                            </svg>
                                        </button>
                                    @endif
                                </div>
                            </div>

                            <!-- Slide-Down Expandable Details (Description, Links, YouTube Player, Image) -->
                            @if($hasDetails)
                                <div x-show="expanded" x-collapse x-cloak class="space-y-2.5 pt-2 border-t border-slate-200/60" @click.stop>
                                    <!-- Optional Image -->
                                    @if($task->image_url)
                                        <div class="w-full h-36 rounded-xl overflow-hidden bg-slate-100 border border-slate-100">
                                            <img src="{{ $task->image_url }}" alt="{{ $task->title }}" class="w-full h-full object-cover">
                                        </div>
                                    @endif

                                    <!-- Optional Description -->
                                    @if($task->description)
                                        <div class="text-xs text-slate-600 leading-relaxed whitespace-pre-line bg-white p-3 rounded-xl border border-slate-200/80">
                                            {{ $task->description }}
                                        </div>
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
                            @endif

                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @empty
        <div class="p-8 text-center rounded-3xl bg-white border border-dashed border-slate-300 text-slate-500 shadow-sm">
            <span class="text-3xl block mb-2">📅</span>
            <p class="text-sm font-bold text-slate-800">Nav ieplānotu uzdevumu!</p>
            <p class="text-xs text-slate-500 mt-1">Izvēlies uzdevumu no uzdevumu krātuves un ieplāno to ar kalendāru.</p>
        </div>
    @endforelse
</div>
