<x-app-layout>
    <x-slot name="slot">

        <div class="row justify-content-center mt-2">
            <div class="col-2"></div>

            <div class="col-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="font-weight-bold">Создать задачу</h5>
                    </div>

                    <div class="card-body">
                        <!--<div class="form-group">
                            <label for="file_todo">Изображение</label>
                            <input type="file" class="form-control-file" id="file_todo">
                        </div>-->

                        <div class="form-group">
                            <label class="input-file">
                                <input type="file" id="file_todo" accept="image/*">
                                <span>Выберите изображение</span>
                            </label>
                            <div class="input-file-list d-inline"></div>
                        </div>

                        <div class="form-group">
                            <label for="header_todo" class="mb-1">Название задачи</label>
                            <input id="header_todo" class="form-control form-control-sm" placeholder="Название задачи">
                            <small id="header_error" class="font-weight-bold text-danger d-none"></small>
                        </div>

                        <div class="form-group">
                            <label for="body_todo" class="mb-1">Описание задачи</label>
                            <textarea id="body_todo" class="form-control form-control-sm" rows="5" placeholder="Описание задачи"></textarea>
                            <small id="body_error" class="font-weight-bold text-danger d-none"></small>
                        </div>

                        <div class="form-group">
                            <label for="body_todo" class="mb-1">Теги</label>

                            <div class="input-group">
                                <input id="tag_todo" class="form-control form-control-sm" placeholder="Введите название тега и нажмите Enter">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary" id="btnAddTag">Добавить тег</button>
                                </div>
                            </div>

                            <div class="tags-list"></div>
                        </div>
                    </div>

                    <div class="card-footer text-center">
                        <button class="btn btn-success" id="todoSave">Сохранить</button>
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
                tags.push($(this).data('val'));
            });

            query.append('tags', JSON.stringify(tags));

            $('input,textarea').removeClass('border-danger')
            $('small').addClass('d-none');

            $.ajax({
                'url': '{{ route('todo.store') }}',
                method: 'POST',
                async: true,
                dataType: 'json',
                data: query,
                processData: false,
                contentType: false,
                cache: false,

                error: function(jqXHR, textStatus, errorThrown) {
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
                        var url = '{{ route('todo.edit', '@') }}';
                        url = url.replace('@', data.id);
                        window.location.href = url;
                    }
                }
            });
        });

        $('.card').on('click', '.input-file-list-remove', function() {
            files = null;
            $('.input-file-list').empty();
        });

        $('#tag_todo').keydown(function(e) {
            if(e.which == 13)
                addTag();
        });

        $('#btnAddTag').click(function() {
            addTag();
        });

        $('.card').on('click', '.btn-delete-tag', function() {
            $(this).closest('.badge').remove();
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