 <x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            <label>{{ __('Список задач') }}</label>
        </h2>

        <form>
            <div class="form-group">
                <div class="input-group input-group-sm">
                    <input class="form-control form-control-sm" name="header_todo" placeholder="Название задачи" value="{{ $header_todo }}">
                    <input class="form-control form-control-sm" name="body_todo" placeholder="Описание задачи" value="{{ $body_todo }}">
                    <input class="form-control form-control-sm" id="tag_todo" placeholder="Тег" value="">
                    <input type="hidden" name="tags_todo" value="{{ $tags_todo }}">

                    <div class="input-group-append">
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="btnAddTag">Добавить тег</button>
                    </div>
                </div>
                <div class="tags-list"></div>
            </div>    

            <button type="submit" class="btn btn-sm btn-primary" id="btnSearch">Поиск</button>
            <a class="btn btn-sm btn-success" href="{{ route('todo.create') }}">Добавить задачу</a>
        </form>
    </x-slot>

    <x-slot name="slot">
        <div class="container-fluid">
            @foreach($todos as $todo)
                <div class="media m-3" style="border: 1px solid black; border-radius: 5px;">
                    @if($todo->path_to_file_preview)
                        <img src="{{ Storage::url($todo->path_to_file_preview) }}" data-url="{{ Storage::url($todo->path_to_file) }}" height="150px" class="rounded align-self-center input-file-list-img mr-3" alt="...">
                    @endif

                    <div class="media-body">
                        <h5 class="mt-0 font-weight-bold">{{ $todo->header }}
                            @if($todo->user_id == Auth::user()->id)
                                <a href="{{ route('todo.edit', $todo->id) }}" style="font-size: 13px;">(редактировать)</a>
                            @endif
                        </h5>
                        <p>{{ $todo->body }}</p>

                        <p>
                            Теги:
                            @if($todo->tags)
                                @foreach($todo->tags as $tag)
                                    <span class='badge badge-dark mr-1'>{{ $tag->name }}</span>
                                @endforeach
                            @endif
                        </p>
                    </div>
                </div>
            @endforeach

            {{ $todos->links() }}
        </div>
    </x-slot>
</x-app-layout>

<script type="text/javascript">
    $(document).ready(function() {
        $('#tag_todo').keydown(function(e) {
            if(e.which == 13)
                addTag();
        });

        $('#btnAddTag').click(function() {
            addTag();
        });

        $('.form-group').on('click', '.btn-delete-tag', function() {
            $(this).closest('.badge').remove();
            var tags = [];

            $('.tags-list .badge').each(function() {
                tags.push($(this).data('val'));
            });

            $("[name='tags_todo']").val(JSON.stringify(tags));
        });

        $('*').on('click', 'img', function() {
            window.open($(this).data('url'));
        });

        renderTags();
    });

    function addTag() {
        var tag = String($('#tag_todo').val()).trim();

        if(tag.length == 0)
            return;

        if($('.tags-list .badge[data-val="' + tag + '"]').length > 0)
            return;

        renderTag(tag);
        $('#tag_todo').val('');

        var tags = [];

        $('.tags-list .badge').each(function() {
            tags.push($(this).data('val'));
        });

        $("[name='tags_todo']").val(JSON.stringify(tags));
    }

    //
    function renderTags() {
        if($("[name='tags_todo']").val() === undefined || $("[name='tags_todo']").val().length == 0)
            return;

        var tags = JSON.parse($("[name='tags_todo']").val());

        tags.forEach(function(tag) {
            renderTag(tag);
        });
    }

    function renderTag(tag) {
        var badge = "<span class='badge badge-dark m-1' data-val='" + tag + "'>"
            + tag + "&nbsp;"
            + "<button class='btn btn-sm btn-dark m-0 p-0 btn-delete-tag'>&times;</button></span>";
        $('.tags-list').append(badge);
    }
</script>