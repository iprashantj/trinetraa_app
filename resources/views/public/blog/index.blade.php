@extends('layouts.public')
@section('title', 'Eye Care Blog — Tips, Guides & Vision Health | Trinetraa Optician')
@section('meta_description', 'Read expert eye care tips, eyewear guides and vision health articles from the optometrists at Trinetraa Optician Nashik.')
@section('canonical', 'https://trinetraaoptician.com/blog')
@section('content')
<section style="background:linear-gradient(135deg,#0d1b2a 0%,#1a3a5c 100%);color:#fff;padding:5rem 0 3rem;text-align:center">
    <div class="pp-container">
        <div class="pp-section__eyebrow" style="color:#d4af37">Knowledge Centre</div>
        <h1 style="font-size:clamp(2rem,5vw,3rem);font-weight:800;margin-bottom:1rem">Eye Care Blog</h1>
        <p style="max-width:560px;margin:0 auto;opacity:0.85">Expert tips, guides, and the latest in eyewear fashion — keeping your vision and style sharp.</p>
    </div>
</section>

<section class="pp-section animate-on-scroll">
    <div class="pp-container">
        <div id="blogLoading" class="pp-loading"><div class="pp-spinner"></div></div>
        <div id="blogGrid" class="animate-stagger" style="display:none;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:1.5rem"></div>
    </div>
</section>
@endsection

@push('scripts')
<script>
@verbatim
(function () {
  var DEFAULT_POSTS = [
    { slug: null, title: "How to Choose the Right Eyeglass Frames", excerpt: "Frame selection depends on face shape, lifestyle, and personal style. Here's your complete guide.", image: null, published_at: "2025-01-15T00:00:00Z" },
    { slug: null, title: "Eye Care Tips for Computer Users", excerpt: "The 20-20-20 rule and other proven strategies to reduce digital eye strain at work.", image: null, published_at: "2025-02-01T00:00:00Z" },
    { slug: null, title: "Computer Vision Syndrome: Symptoms and Solutions", excerpt: "More than 60% of screen users experience CVS. Learn to recognise and treat it early.", image: null, published_at: "2025-02-20T00:00:00Z" },
    { slug: null, title: "Sunglasses Buying Guide 2025", excerpt: "UV protection, polarised lenses, lens tints — everything you need to know before buying sunglasses.", image: null, published_at: "2025-03-05T00:00:00Z" },
    { slug: null, title: "Children's Eye Health: What Parents Should Know", excerpt: "Regular eye checks are crucial for kids. Early detection of vision problems can prevent learning difficulties.", image: null, published_at: "2025-03-20T00:00:00Z" },
    { slug: null, title: "Contact Lens Care: Do's and Don'ts", excerpt: "Proper contact lens hygiene is essential. A complete guide to safe and comfortable lens wear.", image: null, published_at: "2025-04-01T00:00:00Z" },
    { slug: null, title: "Latest Eyewear Trends 2025", excerpt: "From oversized frames to transparent acetate — the eyewear styles dominating 2025.", image: null, published_at: "2025-04-15T00:00:00Z" },
  ];

  function formatDate(dateStr) {
    return new Date(dateStr).toLocaleDateString("en-IN", { day: "numeric", month: "long", year: "numeric" });
  }

  function esc(s) {
    return String(s == null ? "" : s).replace(/[&<>"']/g, function (c) {
      return { "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[c];
    });
  }

  function render(posts, usingDefault) {
    var loading = document.getElementById("blogLoading");
    var grid = document.getElementById("blogGrid");
    loading.style.display = "none";
    grid.style.display = "grid";
    grid.innerHTML = posts.map(function (post, i) {
      var media = post.image
        ? '<img src="' + esc(post.image) + '" alt="' + esc(post.title) + '" style="width:100%;height:200px;object-fit:cover">'
        : '<div style="height:160px;background:linear-gradient(135deg,#eef4fd,#d4e8ff);display:flex;align-items:center;justify-content:center;font-size:3rem">📖</div>';
      var excerpt = post.excerpt
        ? '<p style="color:#9FB1C7;font-size:0.875rem;line-height:1.7;margin-bottom:1rem">' + esc(post.excerpt) + '</p>'
        : '';
      var link = (post.slug && !usingDefault)
        ? '<a href="/blog/' + esc(post.slug) + '" style="color:#F8FAFC;font-weight:600;font-size:0.875rem">Read More →</a>'
        : '<span style="color:rgba(255,255,255,0.5);font-size:0.8rem">Coming soon</span>';
      return '<article class="animate-on-scroll" style="background:rgba(255,255,255,0.06);border-radius:1rem;overflow:hidden;box-shadow:0 2px 16px rgba(0,0,0,0.35);transition-delay:' + (i * 0.07) + 's">' +
        media +
        '<div style="padding:1.5rem">' +
        '<div style="color:rgba(255,255,255,0.5);font-size:0.8rem;margin-bottom:0.5rem">' + esc(formatDate(post.published_at || post.created_at)) + '</div>' +
        '<h2 style="font-weight:700;font-size:1rem;line-height:1.5;margin-bottom:0.75rem">' + esc(post.title) + '</h2>' +
        excerpt +
        link +
        '</div></article>';
    }).join("");
  }

  document.addEventListener("DOMContentLoaded", function () {
    fetch("/api/public/blog?per_page=12")
      .then(function (r) { return r.json(); })
      .then(function (d) {
        if (d.data && d.data.length > 0) { render(d.data, false); }
        else { render(DEFAULT_POSTS, true); }
      })
      .catch(function () { render(DEFAULT_POSTS, true); });
  });
})();
@endverbatim
</script>
@endpush
