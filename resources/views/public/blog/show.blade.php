@extends('layouts.public')
@php
    $post = \App\Models\BlogPost::where('slug', $slug)->where('is_published', true)->firstOrFail();
    $_su  = rtrim($site['siteUrl'] ?? 'https://trinetraaoptician.com', '/');
    $_bTitle = $post ? $post->title . ' — Trinetraa Optician Eye Care Blog' : 'Eye Care Blog — Trinetraa Optician';
    $_bDesc  = $post && $post->excerpt ? \Illuminate\Support\Str::limit(strip_tags($post->excerpt), 155)
              : ($post ? \Illuminate\Support\Str::limit(strip_tags($post->content ?? ''), 155) : 'Read eye care tips and eyewear guides from Trinetraa Optician Nashik.');
    $_bImg  = $post && $post->image ? $post->image : $_su . '/icons/icon-512.png';
    $_bUrl  = $_su . '/blog/' . $slug;
@endphp
@section('title', $_bTitle)
@section('meta_description', $_bDesc)
@section('og_image', $_bImg)
@section('og_type', 'article')
@section('canonical', $_bUrl)
@if($post)
@push('structured_data')
@php
    $at = '@';
    $articleJsonLd = json_encode([
        $at.'context'         => 'https://schema.org',
        $at.'type'            => 'Article',
        'headline'            => $post->title,
        'description'         => $_bDesc,
        'image'               => $_bImg,
        'url'                 => $_bUrl,
        'datePublished'       => optional($post->published_at ?? $post->created_at)->toIso8601String(),
        'dateModified'        => optional($post->updated_at)->toIso8601String(),
        'publisher'           => [
            $at.'type' => 'Organization',
            'name'     => 'Trinetraa Optician',
            'logo'     => [$at.'type' => 'ImageObject', 'url' => $_su . '/icons/icon-512.png'],
        ],
        'breadcrumb' => [
            $at.'type'        => 'BreadcrumbList',
            'itemListElement' => [
                [$at.'type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => $_su . '/'],
                [$at.'type' => 'ListItem', 'position' => 2, 'name' => 'Blog', 'item' => $_su . '/blog'],
                [$at.'type' => 'ListItem', 'position' => 3, 'name' => $post->title, 'item' => $_bUrl],
            ],
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
@endphp
<script type="application/ld+json">{!! $articleJsonLd !!}</script>
@endpush
@endif
@section('content')
<div id="postLoading" class="pp-loading" style="min-height:60vh"><div class="pp-spinner"></div></div>

<div id="postNotFound" style="display:none;text-align:center;padding:6rem 2rem">
    <div style="font-size:4rem;margin-bottom:1rem">📄</div>
    <h2>Post not found</h2>
    <a href="/blog" class="pp-btn-outline" style="margin-top:1rem;display:inline-block">← Back to Blog</a>
</div>

<div id="postContent" style="display:none"></div>
@endsection

@push('scripts')
<script>
var BLOG_SLUG = @json($slug);
</script>
<script>
@verbatim
(function () {
  function esc(s) {
    return String(s == null ? "" : s).replace(/[&<>"']/g, function (c) {
      return { "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[c];
    });
  }

  function render(post) {
    var loading = document.getElementById("postLoading");
    var container = document.getElementById("postContent");
    loading.style.display = "none";
    container.style.display = "block";

    var hero = post.image
      ? '<div style="width:100%;max-height:400px;overflow:hidden"><img src="' + esc(post.image) + '" alt="' + esc(post.title) + '" style="width:100%;height:400px;object-fit:cover"></div>'
      : '';

    var published = post.published_at
      ? '<div style="color:rgba(255,255,255,0.5);font-size:0.875rem;margin-bottom:2rem">Published ' +
        esc(new Date(post.published_at).toLocaleDateString("en-IN", { day: "numeric", month: "long", year: "numeric" })) + '</div>'
      : '';

    var excerpt = post.excerpt
      ? '<p style="font-size:1.1rem;color:#9FB1C7;line-height:1.8;margin-bottom:2rem;font-style:italic;border-left:4px solid #d4af37;padding-left:1rem">' + esc(post.excerpt) + '</p>'
      : '';

    var body = String(post.content || "").replace(/\n/g, "<br/>");

    container.innerHTML =
      hero +
      '<article style="max-width:800px;margin:0 auto;padding:3rem 1.5rem">' +
        '<div style="margin-bottom:0.5rem"><a href="/blog" style="color:rgba(255,255,255,0.5);font-size:0.875rem">← Blog</a></div>' +
        '<h1 style="font-size:clamp(1.75rem,4vw,2.5rem);font-weight:800;line-height:1.3;margin-bottom:1rem">' + esc(post.title) + '</h1>' +
        published +
        excerpt +
        '<div style="color:#E9EEF6;line-height:1.9;font-size:1.05rem">' + body + '</div>' +
      '</article>' +
      '<section style="background:rgba(255,255,255,0.04);padding:3rem 0;text-align:center">' +
        '<div class="pp-container">' +
          '<h3 style="font-weight:700;margin-bottom:0.5rem">Need personalised eye care advice?</h3>' +
          '<p style="color:#9FB1C7;margin-bottom:1.5rem">Book a free consultation with our certified optometrists.</p>' +
          '<a href="/appointment" class="pp-btn-primary">Book Free Eye Test →</a>' +
        '</div>' +
      '</section>';
  }

  function showNotFound() {
    document.getElementById("postLoading").style.display = "none";
    document.getElementById("postNotFound").style.display = "block";
  }

  document.addEventListener("DOMContentLoaded", function () {
    fetch("/api/public/blog/" + encodeURIComponent(BLOG_SLUG))
      .then(function (r) { if (!r.ok) { showNotFound(); return null; } return r.json(); })
      .then(function (d) { if (d && d.data) { render(d.data); } else if (d) { showNotFound(); } });
  });
})();
@endverbatim
</script>
@endpush
