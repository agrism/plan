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
            <div class="space-y-2">
                @foreach($fridayTasks as $task)
                    <div class="flex items-center justify-between gap-3 p-3 rounded-2xl bg-slate-50 border border-slate-200/80 hover:border-slate-300 hover:bg-white transition {{ $task->is_completed ? 'opacity-60 bg-slate-100/60' : '' }}">
                        
                        <!-- Checkbox & Title -->
                        <div class="flex items-center gap-3 flex-1 min-w-0">
                            <button hx-patch="{{ route('ideas.toggle', $task->id) }}"
                                    hx-target="body"
                                    type="button"
                                    class="w-5 h-5 rounded-lg border flex items-center justify-center transition {{ $task->is_completed ? 'bg-emerald-500 border-emerald-500 text-white font-bold' : 'border-slate-300 hover:border-amber-500 bg-white' }}">
                                @if($task->is_completed)
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                @endif
                            </button>

                            <div class="truncate">
                                <span class="text-xs sm:text-sm font-semibold {{ $task->is_completed ? 'line-through text-slate-400' : 'text-slate-800' }}">
                                    {{ $task->title }}
                                </span>
                                @if($task->scheduled_time_slot)
                                    <span class="text-[10px] text-amber-700 block font-bold">🕒 {{ $task->scheduled_time_slot }}</span>
                                @endif
                            </div>
                        </div>

                        <!-- Right: Category, Submitter & Unschedule -->
                        <div class="flex items-center gap-2">
                            <span class="text-xs hidden sm:inline" title="{{ $task->category_name }}">{{ $task->category_emoji }}</span>
                            <span class="text-xs" title="Izveidoja: {{ $task->creator->name }}">{{ $task->creator->avatar ?? '👤' }}</span>
                            
                            <!-- Move back to Backlog button -->
                            <form action="{{ route('ideas.schedule', $task->id) }}" method="POST"
                                  hx-patch="{{ route('ideas.schedule', $task->id) }}"
                                  hx-target="body">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="scheduled_date" value="null">
                                <button type="submit" title="Pārcelt atpakaļ uz Uzdevumu krātuvi" class="text-slate-400 hover:text-amber-600 p-1 text-xs transition">
                                    ↩️
                                </button>
                            </form>
                        </div>
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
            <div class="space-y-2">
                @foreach($saturdayTasks as $task)
                    <div class="flex items-center justify-between gap-3 p-3 rounded-2xl bg-slate-50 border border-slate-200/80 hover:border-slate-300 hover:bg-white transition {{ $task->is_completed ? 'opacity-60 bg-slate-100/60' : '' }}">
                        
                        <div class="flex items-center gap-3 flex-1 min-w-0">
                            <button hx-patch="{{ route('ideas.toggle', $task->id) }}"
                                    hx-target="body"
                                    type="button"
                                    class="w-5 h-5 rounded-lg border flex items-center justify-center transition {{ $task->is_completed ? 'bg-emerald-500 border-emerald-500 text-white font-bold' : 'border-slate-300 hover:border-amber-500 bg-white' }}">
                                @if($task->is_completed)
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                @endif
                            </button>

                            <div class="truncate">
                                <span class="text-xs sm:text-sm font-semibold {{ $task->is_completed ? 'line-through text-slate-400' : 'text-slate-800' }}">
                                    {{ $task->title }}
                                </span>
                                @if($task->scheduled_time_slot)
                                    <span class="text-[10px] text-amber-700 block font-bold">🕒 {{ $task->scheduled_time_slot }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <span class="text-xs hidden sm:inline" title="{{ $task->category_name }}">{{ $task->category_emoji }}</span>
                            <span class="text-xs" title="Izveidoja: {{ $task->creator->name }}">{{ $task->creator->avatar ?? '👤' }}</span>
                            
                            <form action="{{ route('ideas.schedule', $task->id) }}" method="POST"
                                  hx-patch="{{ route('ideas.schedule', $task->id) }}"
                                  hx-target="body">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="scheduled_date" value="null">
                                <button type="submit" title="Pārcelt atpakaļ uz Uzdevumu krātuvi" class="text-slate-400 hover:text-amber-600 p-1 text-xs transition">
                                    ↩️
                                </button>
                            </form>
                        </div>
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
            <div class="space-y-2">
                @foreach($sundayTasks as $task)
                    <div class="flex items-center justify-between gap-3 p-3 rounded-2xl bg-slate-50 border border-slate-200/80 hover:border-slate-300 hover:bg-white transition {{ $task->is_completed ? 'opacity-60 bg-slate-100/60' : '' }}">
                        
                        <div class="flex items-center gap-3 flex-1 min-w-0">
                            <button hx-patch="{{ route('ideas.toggle', $task->id) }}"
                                    hx-target="body"
                                    type="button"
                                    class="w-5 h-5 rounded-lg border flex items-center justify-center transition {{ $task->is_completed ? 'bg-emerald-500 border-emerald-500 text-white font-bold' : 'border-slate-300 hover:border-amber-500 bg-white' }}">
                                @if($task->is_completed)
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                @endif
                            </button>

                            <div class="truncate">
                                <span class="text-xs sm:text-sm font-semibold {{ $task->is_completed ? 'line-through text-slate-400' : 'text-slate-800' }}">
                                    {{ $task->title }}
                                </span>
                                @if($task->scheduled_time_slot)
                                    <span class="text-[10px] text-amber-700 block font-bold">🕒 {{ $task->scheduled_time_slot }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <span class="text-xs hidden sm:inline" title="{{ $task->category_name }}">{{ $task->category_emoji }}</span>
                            <span class="text-xs" title="Izveidoja: {{ $task->creator->name }}">{{ $task->creator->avatar ?? '👤' }}</span>
                            
                            <form action="{{ route('ideas.schedule', $task->id) }}" method="POST"
                                  hx-patch="{{ route('ideas.schedule', $task->id) }}"
                                  hx-target="body">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="scheduled_date" value="null">
                                <button type="submit" title="Pārcelt atpakaļ uz Uzdevumu krātuvi" class="text-slate-400 hover:text-amber-600 p-1 text-xs transition">
                                    ↩️
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

</div>
