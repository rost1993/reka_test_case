<x-app-layout>
    <x-slot name="slot">

        <div class="row justify-content-center mt-2">
            <div class="col-2"></div>

            <div class="col-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="font-weight-bold">Задача № {{ $todo->id }}</h5>
                    </div>

                    <div class="card-body">
                        <div class="form-group">
                            <label class="input-file">
                                <input type="file" id="file_todo" accept="image/*" {{ $todo->path_to_file_preview ? 'disabled' : '' }}>
                                <span>Выберите изображение</span>
                            </label>

                            <div class="input-file-list d-inline">
                                @if($todo->path_to_file_preview)
                                    <div class="input-file-list-item">
                                        <img class="input-file-list-img" src="{{ Storage::url($todo->path_to_file_preview) }}" data-file="{{ Storage::url($todo->path_to_file) }}">
                                        <a href="#" class="input-file-list-remove" data-id="{{ $todo->id }}">x</a>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="header_todo" class="mb-1">Название</label>
                            <input id="header_todo" class="form-control" placeholder="Название задачи" value="{{ $todo->header }}">
                            <small id="header_error" class="font-weight-bold text-danger d-none"></small>
                        </div>

                        <div class="form-group">
                            <label for="body_todo" class="mb-1">Описание</label>
                            <textarea id="body_todo" class="form-control" rows="5" placeholder="Описание задачи">{{ $todo->body }}</textarea>
                            <small id="body_error" class="font-weight-bold text-danger d-none"></small>
                        </div>

                        <div class="form-group">
                            <label for="body_todo" class="mb-1">Теги</label>

                            <div class="input-group">
                                <input id="tag_todo" class="form-control" placeholder="Введите название тега и нажмите Enter">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary" id="btnAddTag">Добавить тег</button>
                                </div>
                            </div>

                            <div class="tags-list">
                                @foreach($todo->tags as $tag)
                                    <span class='badge badge-dark m-1' data-val='{{ $tag->name }}' data-id="{{ $tag->pivot->id }}">{{ $tag->name }}&nbsp;
                                    <button class='btn btn-sm btn-dark m-0 p-0 btn-delete-tag'>&times;</button></span>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="card-footer text-center">
                        <button class="btn btn-success" id="todoSave" data-id="{{ $todo->id }}" title="Сохранить">Сохранить</button>
                        <button class="btn btn-danger" id="todoDelete" data-id="{{ $todo->id }}" title="Удалить">Удалить</button>
                    </div>
                </div>
            </div>

            <div class="col-2"></div>
        </div>

    </x-slot>
</x-app-layout>

<script type="text/javascript">
    $(document).ready(function() {
        var files = null;

        $('.input-file input[type=file]').on('change', function() {
            var files_list = $(this).closest('.input-file').next();
            files_list.empty();

            var file = $(this).prop('files')[0];
            files = file;

            var reader = new FileReader();
            reader.readAsDataURL(file);
            reader.onloadend = function() {
                new_file_input =
                '<div class="input-file-list-item">' +
                    '<img class="input-file-list-img" src="' + reader.result + '">' +
                    '<span class="input-file-list-name">' + file.name + '</span>' +
                    '<a href="#" class="input-file-list-remove">x</a>' +
                '</div>';
                files_list.append(new_file_input); 
            }
        });

        $('#todoSave').click(function() {
            var query = new FormData();
            query.append('_token', $('meta[name="csrf-token"]').attr('content'));
            query.append('header', $('#header_todo').val());
            query.append('body', $('#body_todo').val());

            if(files !== null && files !== undefined)
                query.append('file_todo', files);

            var tags = [];

            $('.tags-list .badge').each(function() {
                if($(this).data('id') === undefined)
                    tags.push([-1, $(this).data('val')]);
                else
                    tags.push([$(this).data('id'), $(this).data('val')]);
            });

            query.append('tags', JSON.stringify(tags));

            $('input,textarea').removeClass('border-danger')
            $('small').addClass('d-none');

            var url = '{{ route('todo.update', '@') }}'
            url = url.replace('@', $(this).data('id'));

            $.ajax({
                'url': url,
                method: 'POST',
                async: true,
                dataType: 'json',
                data: query,
                processData: false,
                contentType: false,
                cache: false,

                error: function(jqXHR, textStatus, errorThrown) {
                    console.log(jqXHR);
                    try {
                        var errors = JSON.parse(jqXHR.responseText).errors;

                        for(field in errors) {
                            $('#' + field + '_error').removeClass('d-none').html(errors[field]);
                            $('#' + field + '_todo').addClass('border-danger');
                        }    
                    } catch {}
                },

                success: function(data, statusText, xhr) {
                    if(data.result == 'success') {
                        window.location.reload();
                    }
                }
            });
        });

        $('#todoDelete').click(function() {
            var query = {
                '_token' : $('meta[name="csrf-token"]').attr('content'),
            };

            var url = '{{ route('todo.destroy', '@') }}'
            url = url.replace('@', $(this).data('id'));

            $.ajax({
                'url': url,
                method: 'POST',
                async: true,
                dataType: 'json',
                data: query,

                error: function(jqXHR, textStatus, errorThrown) {
                    alert('При удалении произошла ошибка!');
                },

                success: function(data, statusText, xhr) {
                    if(data.result == 'success') {
                        window.location.href = '{{ route('todo') }}';
                    }
                }
            });
        });

        $('*').on('click', 'img', function() {
            window.open($(this).data('file'));
        });

        $('.card').on('click', '.input-file-list-remove', function() {
            if($(this).data('id') === undefined) {
                files = null;
                $('.input-file-list').empty();
                return;
            }

            var query = {
                '_token' : $('meta[name="csrf-token"]').attr('content'),
            };

            var url = '{{ route('todo.destroy_file', '@') }}'
            url = url.replace('@', $(this).data('id'));

            $.ajax({
                'url': url,
                method: 'POST',
                async: true,
                dataType: 'json',
                data: query,

                error: function(jqXHR, textStatus, errorThrown) {
                    alert('При удалении файла произошла ошибка!');
                },

                success: function(data, statusText, xhr) {
                    if(data.result == 'success') {
                        files = null;
                        $('.input-file-list').empty();
                        $('#file_todo').prop('disabled', false);
                    } else {
                        alert('При удалении файла произошла ошибка!');
                    }
                }
            });
        });

        $('#tag_todo').keydown(function(e) {
            if(e.which == 13)
                addTag();
        });

        $('#btnAddTag').click(function() {
            addTag();
        });

        $('.card').on('click', '.btn-delete-tag', function() {
            var item = $(this).closest('.badge');
            if($(item).data('id') === undefined) {
                $(item).remove();
                return;
            }

            var url = '{{ route('todo.destroy_tag', '@') }}'
            url = url.replace('@', $(item).data('id'));

            var query = {
                '_token' : $('meta[name="csrf-token"]').attr('content'),
            };

            $.ajax({
                'url': url,
                method: 'POST',
                async: true,
                dataType: 'json',
                data: query,

                error: function(jqXHR, textStatus, errorThrown) {
                    alert('При удалении тэга произошла ошибка!');
                },

                success: function(data, statusText, xhr) {
                    if(data.result == 'success') {
                        $(item).remove();
                    } else {
                        alert('При удалении тэга произошла ошибка!');
                    }
                }
            });
        });
    });

    function addTag() {
        var tag = String($('#tag_todo').val()).trim();

        if(tag.length == 0)
            return;

        if($('.tags-list .badge[data-val="' + tag + '"]').length > 0)
            return;

        var badge = "<span class='badge badge-dark m-1' data-val='" + tag + "'>"
            + tag + "&nbsp;"
            + "<button class='btn btn-sm btn-dark m-0 p-0 btn-delete-tag'>&times;</button></span>";
        $('.tags-list').append(badge);
        $('#tag_todo').val('');
    }

</script>