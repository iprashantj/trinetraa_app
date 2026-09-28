<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\Brand;
use App\Models\Eyewear;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    private string $base;

    public function __construct()
    {
        $this->base = rtrim(config('app.url', 'https://trinetraaoptician.com'), '/');
    }

    // ── /sitemap.xml ── index pointing to sub-sitemaps ───────────────────────
    public function index(): Response
    {
        $sitemaps = [
            ['loc' => $this->base . '/sitemap-pages.xml',    'lastmod' => now()->toDateString()],
            ['loc' => $this->base . '/sitemap-eyewears.xml', 'lastmod' => now()->toDateString()],
            ['loc' => $this->base . '/sitemap-brands.xml',   'lastmod' => now()->toDateString()],
            ['loc' => $this->base . '/sitemap-blog.xml',     'lastmod' => now()->toDateString()],
        ];

        $xml = $this->wrap('sitemapindex', 'xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"',
            implode('', array_map(fn ($s) =>
                '<sitemap><loc>' . e($s['loc']) . '</loc><lastmod>' . $s['lastmod'] . '</lastmod></sitemap>',
            $sitemaps))
        );

        return $this->response($xml);
    }

    // ── /sitemap-pages.xml ────────────────────────────────────────────────────
    public function pages(): Response
    {
        $pages = [
            ['/', '1.0', 'daily'],
            ['/eyewears', '0.9', 'daily'],
            ['/brands', '0.8', 'weekly'],
            ['/services', '0.8', 'monthly'],
            ['/offers', '0.8', 'daily'],
            ['/blog', '0.8', 'daily'],
            ['/gallery', '0.7', 'weekly'],
            ['/about', '0.7', 'monthly'],
            ['/contact', '0.7', 'monthly'],
            ['/try-on', '0.6', 'monthly'],
            ['/recommend', '0.6', 'monthly'],
            ['/appointment', '0.7', 'monthly'],
        ];

        $xml = $this->urlset(array_map(fn ($p) => $this->url(
            $this->base . $p[0], now()->toDateString(), $p[2], $p[1]
        ), $pages));

        return $this->response($xml);
    }

    // ── /sitemap-eyewears.xml ─────────────────────────────────────────────────
    public function eyewears(): Response
    {
        $items = Eyewear::where('is_active', true)
            ->orderByDesc('updated_at')
            ->get(['slug', 'image', 'updated_at']);

        $urls = $items->map(fn ($ew) => $this->url(
            $this->base . '/eyewears/' . $ew->slug,
            $ew->updated_at->toDateString(),
            'weekly',
            '0.8',
            $ew->image ? [['loc' => $ew->image, 'title' => $ew->slug]] : []
        ))->all();

        return $this->response($this->urlset($urls));
    }

    // ── /sitemap-brands.xml ───────────────────────────────────────────────────
    public function brands(): Response
    {
        $items = Brand::where('is_active', true)
            ->orderByDesc('updated_at')
            ->get(['slug', 'updated_at']);

        $urls = $items->map(fn ($b) => $this->url(
            $this->base . '/brands/' . $b->slug,
            $b->updated_at->toDateString(),
            'monthly',
            '0.6'
        ))->all();

        return $this->response($this->urlset($urls));
    }

    // ── /sitemap-blog.xml ─────────────────────────────────────────────────────
    public function blog(): Response
    {
        $items = BlogPost::where('is_published', true)
            ->orderByDesc('updated_at')
            ->get(['slug', 'title', 'image', 'updated_at']);

        $urls = $items->map(fn ($p) => $this->url(
            $this->base . '/blog/' . $p->slug,
            $p->updated_at->toDateString(),
            'monthly',
            '0.7',
            $p->image ? [['loc' => $p->image, 'title' => $p->title]] : []
        ))->all();

        return $this->response($this->urlset($urls));
    }

    // ── Helpers ───────────────────────────────────────────────────────────────
    private function url(string $loc, string $lastmod, string $changefreq, string $priority, array $images = []): string
    {
        $imgXml = implode('', array_map(fn ($img) =>
            '<image:image><image:loc>' . e($img['loc']) . '</image:loc>'
            . '<image:title>' . e($img['title']) . '</image:title></image:image>',
        $images));

        return '<url>'
            . '<loc>' . e($loc) . '</loc>'
            . '<lastmod>' . $lastmod . '</lastmod>'
            . '<changefreq>' . $changefreq . '</changefreq>'
            . '<priority>' . $priority . '</priority>'
            . $imgXml
            . '</url>';
    }

    private function urlset(array $urls): string
    {
        return $this->wrap('urlset',
            'xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"',
            implode('', $urls)
        );
    }

    private function wrap(string $root, string $attrs, string $body): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
            . '<' . $root . ' ' . $attrs . '>' . "\n"
            . $body . "\n"
            . '</' . $root . '>';
    }

    private function response(string $xml): Response
    {
        return response($xml, 200, [
            'Content-Type'  => 'application/xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}
