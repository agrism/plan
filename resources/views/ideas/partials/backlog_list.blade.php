@if($backlogIdeas->isEmpty())
    <div class="p-8 text-center rounded-xl bg-white border border-dashed border-slate-300 text-slate-500 shadow-sm">
        <span class="text-3xl block mb-2">📋</span>
        <p class="text-sm font-bold text-slate-800">{{ __('app.backlog_empty_title') }}</p>
        <p class="text-xs text-slate-500 mt-1">{{ __('app.backlog_empty_desc') }}</p>
    </div>
@else
    @foreach($backlogIdeas as $idea)
        @php
            $hasDetails = !empty($idea->description) || !empty($idea->links) || !empty($idea->image_url) || $idea->comments->isNotEmpty();
            $hasYouTube = false;
            if (!empty($idea->processed_links)) {
                foreach ($idea->processed_links as $pl) {
                    if (!empty($pl['embed_url'])) {
                        $hasYouTube = true;
                        break;
                    }
                }
            }
        @endphp

        <div class="group relative rounded-xl bg-white border border-slate-200/90 hover:border-slate-300 transition p-4 shadow-sm hover:shadow-md flex flex-col gap-2.5"
             x-data="{ expanded: false }">
            
            <!-- Card Header: Category, Author Badge, Edit & Delete Buttons -->
            <div class="flex items-center justify-between gap-2">
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-xs font-bold border {{ $idea->category_badge_class }}">
                    <span>{{ $idea->category_emoji }}</span>
                    <span>{{ $idea->category_name }}</span>
                </span>

                <div class="flex items-center gap-2">
                    <div class="flex items-center gap-1.5 text-xs text-slate-500 font-medium" title="{{ __('app.created_by') }}: {{ $idea->creator->name }}">
                        <span class="text-sm">{{ $idea->creator->avatar ?? '👤' }}</span>
                        <span class="font-bold text-slate-700">{{ explode(' ', $idea->creator->name)[0] }}</span>
                    </div>

                    <!-- Edit Button -->
                    <button hx-get="{{ route('ideas.edit', $idea->id) }}"
                            hx-target="#edit-modal-slot"
                            hx-swap="innerHTML"
                            @click.stop
                            type="button"
                            title="{{ __('app.edit_task') }}"
                            class="p-1 rounded-md text-slate-400 hover:text-amber-600 hover:bg-amber-50 transition text-xs">
                        ✏️
                    </button>

                    <!-- Delete with Math Confirmation -->
                    <button type="button"
                            @click.stop="window.openMathDeleteConfirm('{{ addslashes($idea->title) }}', '{{ route('ideas.destroy', $idea->id) }}')"
                            title="{{ __('app.delete') }}"
                            class="p-1 rounded-md text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition text-xs">
                        🗑️
                    </button>
                </div>
            </div>

            <!-- Clickable Title & Expand Trigger -->
            <div @click="{{ $hasDetails ? 'expanded = !expanded' : '' }}"
                 class="flex items-start justify-between gap-2 {{ $hasDetails ? 'cursor-pointer select-none' : '' }}">
                <div class="space-y-1 flex-1">
                    <h3 class="text-sm font-bold text-slate-900 leading-snug group-hover:text-amber-900 transition">
                        {{ $idea->title }}
                    </h3>

                    <!-- Indicators if task has description, links, or video -->
                    @if($hasDetails)
                        <div class="flex items-center gap-1.5 flex-wrap pt-0.5 text-[11px] text-slate-400">
                            @if($idea->description)
                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md bg-slate-100 text-slate-600 font-medium">
                                    <span>📝</span> {{ __('app.description') }}
                                </span>
                            @endif
                            @if(!empty($idea->links) && count($idea->links) > 0)
                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md bg-slate-100 text-slate-600 font-medium">
                                    <span>{{ $hasYouTube ? '▶️' : '🔗' }}</span>
                                    <span>{{ count($idea->links) }} {{ count($idea->links) === 1 ? __('app.link') : __('app.links') }}</span>
                                </span>
                            @endif
                            @if($idea->image_url)
                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md bg-slate-100 text-slate-600 font-medium">
                                    <span>🖼️</span> {{ __('app.image') }}
                                </span>
                            @endif
                        </div>
                    @endif
                </div>

                @if($hasDetails)
                    <button type="button" @click.stop="expanded = !expanded"
                            title="{{ __('app.description') }}"
                            class="p-1 text-slate-400 hover:text-slate-600 rounded-md hover:bg-slate-100 transition flex-shrink-0 mt-0.5">
                        <svg class="w-4 h-4 transition-transform duration-200" :class="expanded ? 'rotate-180 text-amber-600' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                @endif
            </div>

            <!-- Slide-Down Expandable Details Section (Description, Links, YouTube Player, Image) -->
            @if($hasDetails)
                <div x-show="expanded" x-collapse x-cloak class="space-y-3 pt-2 border-t border-slate-100/90" @click.stop>
                    
                    <!-- Optional Image -->
                    @if($idea->image_url)
                        <div class="w-full h-40 rounded-lg overflow-hidden bg-slate-100 border border-slate-100">
                            <img src="{{ $idea->image_url }}" alt="{{ $idea->title }}" class="w-full h-full object-cover">
                        </div>
                    @endif

                    <!-- Optional Description -->
                    @if($idea->description)
                        <div class="bg-slate-50 p-3 rounded-lg border border-slate-100 text-xs text-slate-700 leading-relaxed whitespace-pre-line">
                            {{ $idea->description }}
                        </div>
                    @endif

                    <!-- Dynamic Links & Embedded YouTube Video Players -->
                    @if(!empty($idea->processed_links))
                        <div class="space-y-2 pt-0.5">
                            @foreach($idea->processed_links as $linkItem)
                                <!-- YouTube player embed above link if it's a YouTube URL -->
                                @if($linkItem['embed_url'])
                                    <div class="rounded-lg overflow-hidden border border-slate-200 bg-black aspect-video w-full shadow-sm">
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
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 hover:text-slate-900 font-medium truncate max-w-full transition">
                                        <span>{{ $linkItem['youtube_id'] ? '▶️' : '🔗' }}</span>
                                        <span class="truncate">{{ $linkItem['domain'] }}</span>
                                        <svg class="w-3 h-3 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <!-- Task Comments Section -->
                    <div class="border-t border-slate-200/80 pt-2 mt-2">
                        @php $task = $idea; @endphp
                        @include('ideas.partials.comments_section')
                    </div>

                </div>
            @endif

            <!-- Card Actions (Reactions, Comments Count & Schedule Button) -->
            <div class="flex items-center justify-between pt-2 border-t border-slate-100 mt-0.5" @click.stop>
                
                <div class="flex items-center gap-1.5">
                    <!-- Thumbs Up Reaction -->
                    <button hx-post="{{ route('ideas.react', $idea->id) }}"
                            hx-target="body"
                            type="button"
                            class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold transition {{ $idea->reactions->where('user_id', auth()->id())->isNotEmpty() ? 'bg-amber-100 text-amber-900 border border-amber-300 shadow-sm' : 'bg-slate-100 text-slate-600 hover:text-slate-900 hover:bg-slate-200' }}">
                        <span>👍</span>
                        <span>{{ $idea->reactions->count() }}</span>
                    </button>

                    <!-- Comments Badge / Trigger -->
                    <button @click.stop="expanded = !expanded"
                            type="button"
                            title="{{ __('app.comments') }}"
                            class="flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold transition {{ $idea->comments->isNotEmpty() ? 'bg-slate-100 text-slate-800 border border-slate-200 shadow-sm' : 'text-slate-400 hover:text-slate-700 hover:bg-slate-100' }}">
                        <span>💬</span>
                        <span>{{ $idea->comments->count() }}</span>
                    </button>
                </div>

                <!-- Schedule Calendar Modal Trigger -->
                <button @click.stop="window.openScheduleModal({{ $idea->id }}, '{{ addslashes($idea->title) }}', '{{ $idea->scheduled_date ? $idea->scheduled_date->toDateString() : '' }}', '{{ route('ideas.schedule', $idea->id) }}', false)"
                        type="button"
                        class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-amber-50 hover:bg-amber-100 border border-amber-200 text-amber-800 text-xs font-bold transition">
                    <span>📅</span>
                    <span>{{ __('app.schedule') }}</span>
                </button>

            </div>
        </div>
    @endforeach
@endif
