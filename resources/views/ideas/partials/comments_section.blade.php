<div id="task-comments-container-{{ $task->id }}" class="space-y-3 pt-2">
    <!-- Comments Header -->
    <div class="flex items-center justify-between">
        <h4 class="text-xs font-bold text-slate-800 flex items-center gap-1.5">
            <span>💬</span>
            <span>{{ __('app.comments') }}</span>
            <span class="px-1.5 py-0.5 rounded bg-slate-100 border border-slate-200 text-[10px] font-bold text-slate-600">
                {{ $task->comments->count() }}
            </span>
        </h4>
    </div>

    <!-- Comments List -->
    <div class="space-y-2 max-h-56 overflow-y-auto pr-1">
        @if($task->comments->isEmpty())
            <div class="p-3 text-center bg-slate-50 border border-dashed border-slate-200 rounded-md text-slate-400 text-xs">
                <span class="text-base block mb-0.5">💭</span>
                <p>{{ __('app.no_comments') }}</p>
            </div>
        @else
            @foreach($task->comments as $comment)
                <div class="p-2.5 rounded-md bg-slate-50 border border-slate-200/90 text-xs space-y-1.5 hover:bg-white transition group">
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-1.5">
                            <span class="text-sm">{{ $comment->user->avatar ?? '👤' }}</span>
                            <span class="font-bold text-slate-900">{{ $comment->user->name }}</span>
                            <span class="text-[10px] text-slate-400">• {{ $comment->created_at->diffForHumans() }}</span>
                        </div>

                        @if(auth()->check() && (auth()->id() === $comment->user_id || auth()->user()->currentTenant()->owner_id === auth()->id()))
                            <button type="button"
                                    hx-delete="{{ route('ideas.comments.destroy', [$task->id, $comment->id]) }}"
                                    hx-target="#task-comments-container-{{ $task->id }}"
                                    hx-swap="outerHTML"
                                    hx-confirm="{{ __('app.delete_comment_confirm') }}"
                                    class="text-slate-400 hover:text-rose-600 transition p-1 rounded hover:bg-rose-50 opacity-0 group-hover:opacity-100"
                                    title="{{ __('app.delete_comment') }}">
                                🗑️
                            </button>
                        @endif
                    </div>
                    <div class="text-slate-700 leading-relaxed pl-5 whitespace-pre-line text-xs font-normal">
                        {!! nl2br(e($comment->content)) !!}
                    </div>
                </div>
            @endforeach
        @endif
    </div>

    <!-- New Comment Form -->
    <form hx-post="{{ route('ideas.comments.store', $task->id) }}"
          hx-target="#task-comments-container-{{ $task->id }}"
          hx-swap="outerHTML"
          class="flex items-start gap-2 pt-1"
          x-data="{ commentText: '' }"
          @htmx:after-request="commentText = ''">
        @csrf
        <div class="flex-1">
            <textarea name="content"
                      x-model="commentText"
                      rows="2"
                      required
                      placeholder="{{ __('app.write_comment_placeholder') }}"
                      class="w-full px-3 py-2 bg-slate-50 border border-slate-300 focus:border-amber-500 focus:bg-white rounded-md text-slate-900 text-xs resize-none placeholder:text-slate-400"></textarea>
        </div>
        <button type="submit"
                class="px-3 py-2 bg-amber-500 hover:bg-amber-400 active:scale-95 text-slate-950 font-bold text-xs rounded-md shadow-sm transition flex-shrink-0 flex items-center gap-1">
            <span>{{ __('app.post_comment_btn') }}</span>
        </button>
    </form>
</div>
