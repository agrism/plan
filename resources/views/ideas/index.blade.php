@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- Mobile Segmented Tabs -->
    <div class="flex md:hidden p-1 bg-slate-200/80 rounded-2xl border border-slate-300">
        <button @click="mobileTab = 'ideas'"
                class="flex-1 py-2 rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5"
                :class="mobileTab === 'ideas' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900'">
            <span>💡 Uzdevumu Krātuve</span>
            <span class="px-1.5 py-0.5 rounded-full text-[10px]" :class="mobileTab === 'ideas' ? 'bg-amber-100 text-amber-900 font-extrabold' : 'bg-slate-300 text-slate-700'">{{ $backlogIdeas->count() }}</span>
        </button>
        <button @click="mobileTab = 'weekend'"
                class="flex-1 py-2 rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5"
                :class="mobileTab === 'weekend' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900'">
            <span>📅 Dienu Plāns</span>
            <span class="px-1.5 py-0.5 rounded-full text-[10px]" :class="mobileTab === 'weekend' ? 'bg-amber-100 text-amber-900 font-extrabold' : 'bg-slate-300 text-slate-700'">{{ $weekendTasksCount }}</span>
        </button>
    </div>

    <!-- Main 2-Column Grid -->
    <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-start">

        <!-- Left Column: Backlog Tasks -->
        <section class="md:col-span-6 lg:col-span-5 space-y-4"
                 :class="{ 'hidden md:block': mobileTab !== 'ideas' }">
            
            <!-- Backlog Header & Filters -->
            <div class="flex items-center justify-between gap-2">
                <div>
                    <h2 class="text-xl font-extrabold text-slate-900 flex items-center gap-2">
                        <span>💡</span>
                        <span>Uzdevumu Krātuve</span>
                        <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 border border-amber-300">
                            {{ $backlogIdeas->count() }}
                        </span>
                    </h2>
                    <p class="text-xs text-slate-500">Ienākošie uzdevumi, iniciatīvas un plāni</p>
                </div>
                
                <button @click="showAddModal = true"
                        class="hidden md:flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs shadow-sm shadow-amber-500/20 transition">
                    <span>➕</span> Pievienot
                </button>
            </div>

            <!-- Category Pills Filter & Dynamic Categories -->
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 text-xs no-scrollbar">
                <a href="{{ route('ideas.index', ['category' => 'all']) }}"
                   hx-get="{{ route('ideas.index', ['category' => 'all']) }}"
                   hx-target="#backlog-container"
                   class="px-2.5 py-1 rounded-xl whitespace-nowrap transition {{ !$categoryFilter || $categoryFilter === 'all' ? 'bg-slate-900 text-white font-bold' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-100 hover:text-slate-900' }}">
                    Visi
                </a>
                @foreach($categories as $cat)
                    <a href="{{ route('ideas.index', ['category' => $cat->slug]) }}"
                       hx-get="{{ route('ideas.index', ['category' => $cat->slug]) }}"
                       hx-target="#backlog-container"
                       class="px-2.5 py-1 rounded-xl whitespace-nowrap transition flex items-center gap-1 {{ $categoryFilter === $cat->slug || $categoryFilter == $cat->id ? 'bg-amber-500 text-slate-950 font-bold shadow-sm' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-100 hover:text-slate-900' }}">
                        <span>{{ $cat->emoji }}</span>
                        <span>{{ $cat->name }}</span>
                    </a>
                @endforeach

                <!-- Add New Category Button -->
                <button @click="showCategoryModal = true"
                        type="button"
                        class="px-2.5 py-1 rounded-xl whitespace-nowrap transition flex items-center gap-1 bg-slate-100 hover:bg-amber-100 border border-slate-200 hover:border-amber-300 text-slate-600 hover:text-amber-900 font-bold"
                        title="Izveidot jaunu kategoriju">
                    <span>➕</span> Jauna
                </button>
            </div>

            <!-- Backlog Cards Container -->
            <div id="backlog-container" class="space-y-3">
                @include('ideas.partials.backlog_list')
            </div>
        </section>

        <!-- Right Column: Focus & Scheduled Execution Planner -->
        <section class="md:col-span-6 lg:col-span-7 space-y-4"
                 :class="{ 'hidden md:block': mobileTab !== 'weekend' }">
            
            <!-- Schedule Header -->
            <div class="flex items-center justify-between gap-2">
                <div>
                    <h2 class="text-xl font-extrabold text-slate-900 flex items-center gap-2">
                        <span>📅</span>
                        <span>Dienu Plāns</span>
                    </h2>
                    <p class="text-xs text-slate-500">Ieplānotie uzdevumi konkrētajām dienām</p>
                </div>

                <!-- Progress Pill -->
                <div class="flex items-center gap-2 px-3 py-1 rounded-xl bg-white border border-slate-200 shadow-sm text-xs">
                    <span class="text-slate-500">Paveikts:</span>
                    <span class="font-bold text-amber-600">{{ $weekendCompletedCount }} / {{ $weekendTasksCount }}</span>
                </div>
            </div>

            <!-- Scheduled Days Container -->
            <div id="weekend-container" class="space-y-4">
                @include('ideas.partials.weekend_plan')
            </div>
        </section>

    </div>
</div>
@endsection
