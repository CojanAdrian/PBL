<?php

namespace App\Http\Controllers;

use App\Models\Content;
use App\Models\Image;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Saves edits made by clicking on texts and images in the admin's Visual editor.
 */
class VisualEditorController extends Controller
{
    public function text(Request $request): JsonResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $data = $request->validate([
            'key' => ['required', 'string', 'max:255'],
            'locale' => ['required', 'string', 'exists:langs,code'],
            'text' => ['nullable', 'string', 'max:20000'],
            'page' => ['nullable', 'integer', 'exists:pages,id'],
        ]);

        $content = Content::firstOrCreate(['key' => $data['key']], ['page_id' => $data['page'] ?? null]);
        $content->text()->updateOrCreate(
            ['lang' => $data['locale']],
            ['text' => str_replace("\r\n", "\n", $data['text'] ?? '')],
        );

        return response()->json(['saved' => true]);
    }

    public function image(Request $request): JsonResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $data = $request->validate([
            'key' => ['required', 'string', 'max:255'],
            'image' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,gif,avif', 'max:8192'],
            'page' => ['nullable', 'integer', 'exists:pages,id'],
        ]);

        $image = Image::firstOrNew(['key' => $data['key']], ['page_id' => $data['page'] ?? null]);
        $previous = $image->img;

        $image->img = $request->file('image')->store('', 'image');
        $image->save();

        if (filled($previous) && $previous !== $image->img && ! Image::where('img', $previous)->exists()) {
            Storage::disk('image')->delete($previous);
        }

        return response()->json(['url' => Storage::disk('image')->url($image->img)]);
    }
}
