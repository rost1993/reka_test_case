<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

use Illuminate\Support\Facades\Auth;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;

use App\Models\Tag;
use App\Models\Todo;
use App\Models\TodoTag;

class TodoController extends Controller
{
    /*
        Построение списков
    */
    public function index(Request $request): View {
        $query = Todo::orderBy(app(Todo::class)->getTable() . '.updated_at', 'desc');

        // Фильтр по заголовку
        if($request->header_todo) {
            $query->where('header', 'LIKE', '%' . $request->header_todo . '%');
        }

        // Фильтр по телу
        if($request->body_todo) {
            $query->where('body', 'LIKE', '%' . $request->body_todo . '%');
        }

        // Добавляем фильтр по тегам
        if($request->tags_todo) {
            $tags = json_decode($request->tags_todo);

            if(count($tags) > 0 ) {
                $query->whereHas('tags', function($q) use ($tags) {
                    $q->whereIn('name', $tags);
                });
            }
        }

        $todos = $query->paginate(10);

        return view('todo.index', [
            'todos' => $todos,
            'header_todo' => $request->header_todo,
            'body_todo' => $request->body_todo,
            'tags_todo' => $request->tags_todo,
        ]);
    }

    /*
        Открытие формы нового элемента списка
    */
    public function create(Request $request): View {
        return view('todo.create', []);
    }

    /*
        Добавление нового элемента списка
    */
    public function store(Request $request) {
        $validated = $request->validate([
            'header' => 'required|max:500',
            'body' => 'required|max:1000',
        ]);

        $path = $path_preview = null;

        // Определяем есть ли файл
        if($request->hasFile('file_todo')) {
            $image = $request->file('file_todo');
            $path = $image->store('public/images');

            $temp_arr = explode('/', $path);
            $image_name = $temp_arr[count($temp_arr) - 1];

            // Формируем превью
            Image::make($request->file('file_todo'))
                ->resize(150, 150, function ($constraint) {
                    $constraint->aspectRatio();
                })
                ->save(storage_path('app/public/images/') . '/preview_' . $image_name);

            $path_preview = 'public/images/preview_' . $image_name;
        }

        // Сохраняем элемент списка
        $todo = new Todo();
        $todo->header = $request->header;
        $todo->body = $request->body;
        $todo->user_id = Auth::user()->id;
        $todo->path_to_file = $path;
        $todo->path_to_file_preview = $path_preview;
        $todo->save();

        // Определяем есть ли связанные теги
        if($request->tags) {

            foreach(json_decode($request->tags) as $i => $tag_name) {
                $tag = Tag::where('name', '=', $tag_name)->first();

                if(!$tag) {
                    $tag = new Tag();
                    $tag->name = $tag_name;
                    $tag->save();
                }

                $todo_tag = new TodoTag();
                $todo_tag->todo_id = $todo->id;
                $todo_tag->tag_id = $tag->id;
                $todo_tag->save();
            }
        }

        return response()->json(['result' => 'success', 'id' => $todo->id ]);
    }

    /*
        Обновление элемента списка
    */
    public function update(Request $request, int $id) {
        $validated = $request->validate([
            'header' => 'required|max:500',
            'body' => 'required|max:1000',
        ]);

        $todo = Todo::find($id);

        $path = $path_preview = null;

        // Работаем с файлами
        if($request->hasFile('file_todo')) {
            $image = $request->file('file_todo');
            $path = $image->store('public/images');

            $temp_arr = explode('/', $path);
            $image_name = $temp_arr[count($temp_arr) - 1];

            Image::make($request->file('file_todo'))
                ->resize(150, 150, function ($constraint) {
                    $constraint->aspectRatio();
                })
                ->save(storage_path('app/public/images/') . 'preview_' . $image_name);

            $path_preview = 'public/images/preview_' . $image_name;

            $todo->path_to_file = $path;
            $todo->path_to_file_preview = $path_preview;
        }
        
        $todo->header = $request->header;
        $todo->body = $request->body;
        $todo->user_id = Auth::user()->id;
        $todo->save();

        // Определяем есть ли связанные теги
        if($request->tags) {
            foreach(json_decode($request->tags) as $i => $tag_arr) {
                if($tag_arr[0] != -1)
                    continue;

                $tag = Tag::where('name', '=', $tag_arr[1])->first();

                if(!$tag) {
                    $tag = new Tag();
                    $tag->name = $tag_arr[1];
                    $tag->save();
                }

                $todo_tag = new TodoTag();
                $todo_tag->todo_id = $todo->id;
                $todo_tag->tag_id = $tag->id;
                $todo_tag->save();
            }
        }

        return response()->json(['result' => 'success']);
    }

    /*
        Редакктирование элемента списка
    */
    public function edit(Request $request) {
        $todo = Todo::find($request->id);

        return view('todo.edit', [
            'todo' => $todo
        ]);
    }

    /*
        Удаление элемента списка
    */
    public function destroy(Request $request, int $id) {
        $todo = Todo::find($id);

        // Удаляем файл с изображением
        if($todo->path_to_file)
            Storage::delete($todo->path_to_file);

        // Удаляем файл с первью
        if($todo->path_to_file_preview)
            Storage::delete($todo->path_to_file_preview);

        // Удаляем связи с тегами
        $todo_tag = TodoTag::where('todo_id', '=', $todo->id)->delete();

        $todo->delete();

        return response()->json(['result' => 'success']);
    }

    /*
        Удаление изображений
    */
    public function destroy_file(Request $request, int $id) {
        $todo = Todo::find($id);

        Storage::delete($todo->path_to_file);
        Storage::delete($todo->path_to_file_preview);

        $todo->path_to_file = null;
        $todo->path_to_file_preview = null;

        $todo->save();

        return response()->json(['result' => 'success']);
    }

    /*
        Удаление тега
    */
    public function destroy_tag(Request $request, int $id_todo_tag) {
        $todo_tag = TodoTag::find($id_todo_tag);
        $todo_tag->delete();

        return response()->json(['result' => 'success']);
    }
}
