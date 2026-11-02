<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Writes lighter siblings next to JPG/PNG images (needs PHP GD with WebP). Originals are never modified or deleted:
 *   photo.jpg  →  photo.webp            (max width --max, default 1600)
 *   logo.png   →  logo-pdf.png          (with --png=-pdf --max=420; DomPDF cannot read WebP)
 * Safe to re-run: up-to-date siblings are skipped unless --force.
 */
class OptimizeImages extends Command
{
    protected $signature = 'images:optimize
        {paths?* : Files or folders under public/ (default: public/uploads)}
        {--max=1600 : Maximum width in pixels of the generated copy}
        {--quality=78 : WebP/JPEG quality 1-100}
        {--png= : Also write a resized PNG sibling with this filename suffix (e.g. -pdf) instead of WebP}
        {--force : Rebuild even if the sibling is newer than the original}';

    protected $description = 'Create resized WebP (or small PNG) copies of uploaded images; originals are left untouched';

    public function handle(): int
    {
        if (! function_exists('imagewebp')) {
            $this->error('PHP GD with WebP support is required.');

            return self::FAILURE;
        }
        $targets = $this->argument('paths') ?: ['uploads'];
        $files = [];
        foreach ($targets as $t) {
            $abs = public_path(ltrim(str_replace('\\', '/', $t), '/'));
            if (is_dir($abs)) {
                $it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($abs, \FilesystemIterator::SKIP_DOTS));
                foreach ($it as $f) {
                    $files[] = $f->getPathname();
                }
            } elseif (is_file($abs)) {
                $files[] = $abs;
            } else {
                $this->warn("Skipping {$t}: not found under public/.");
            }
        }

        $max = max(100, (int) $this->option('max'));
        $q = max(1, min(100, (int) $this->option('quality')));
        $suffix = (string) $this->option('png');
        $before = $after = $done = 0;

        foreach ($files as $path) {
            if (! preg_match('/\.(jpe?g|png)$/i', $path) || preg_match('/(' . preg_quote($suffix ?: '-none-', '/') . ')\.png$/i', $path)) {
                continue;
            }
            $out = $suffix !== '' ? preg_replace('/\.png$/i', $suffix . '.png', $path) : preg_replace('/\.(jpe?g|png)$/i', '.webp', $path);
            if ($out === $path || (! $this->option('force') && is_file($out) && filemtime($out) >= filemtime($path))) {
                continue;
            }
            $info = @getimagesize($path);
            if (! $info) {
                continue;
            }
            $img = $info[2] === IMAGETYPE_PNG ? @imagecreatefrompng($path) : @imagecreatefromjpeg($path);
            if (! $img) {
                continue;
            }
            [$w, $h] = $info;
            if ($w > $max) {
                $img = imagescale($img, $max, (int) round($h * $max / $w), IMG_BICUBIC) ?: $img;
            }
            if ($suffix !== '') {
                imagesavealpha($img, true);
                imagepng($img, $out, 9);
            } else {
                if ($info[2] === IMAGETYPE_PNG) {
                    imagepalettetotruecolor($img);
                    imagealphablending($img, true);
                    imagesavealpha($img, true);
                }
                imagewebp($img, $out, $q);
            }
            imagedestroy($img);
            $before += filesize($path);
            $after += filesize($out);
            $done++;
            $this->line(sprintf('%s → %s  (%s KB → %s KB)', basename($path), basename($out), number_format(filesize($path) / 1024, 1), number_format(filesize($out) / 1024, 1)));
        }

        $this->info($done ? sprintf('%d image(s) written, %s KB saved.', $done, number_format(($before - $after) / 1024, 1)) : 'Nothing to do.');

        return self::SUCCESS;
    }
}
