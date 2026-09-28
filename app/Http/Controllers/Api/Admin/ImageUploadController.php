<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ImageUploadController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'image'  => 'required|file|image|mimes:jpeg,jpg,png,webp,gif|max:8192',
            'folder' => 'nullable|string|in:eyewears,brands,gallery,blog,offers,services,misc',
        ]);

        $allowedExtensions = ['jpeg', 'jpg', 'png', 'webp', 'gif'];

        $folder = $request->input('folder', 'misc');
        $file   = $request->file('image');

        // Derive the extension from the detected MIME type rather than the
        // client-supplied filename/extension to prevent double-extension
        // (e.g. "evil.php.jpg") or path-traversal filename tricks.
        $ext = match ($file->getMimeType()) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
            default => null,
        };

        if (! $ext || ! in_array($ext, $allowedExtensions, true)) {
            return response()->json(['message' => 'Unsupported image type.'], 422);
        }

        $filename = Str::random(32) . '.' . $ext;
        $destDir  = public_path('uploads/' . $folder . '/' . date('Y-m'));

        if (!file_exists($destDir)) {
            mkdir($destDir, 0755, true);
        }

        $file->move($destDir, $filename);

        return response()->json([
            'url' => '/uploads/' . $folder . '/' . date('Y-m') . '/' . $filename,
        ], 201);
    }
}
