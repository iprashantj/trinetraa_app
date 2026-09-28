<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Service;
use App\Models\Eyewear;
use App\Models\Review;
use App\Models\GalleryItem;
use App\Models\Offer;
use App\Models\BlogPost;
use App\Models\Appointment;
use App\Models\ServiceablePincode;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DatabaseSeeder extends Seeder
{
    private function discountPct($price, $discountPrice): int
    {
        if (!$discountPrice) {
            return 0;
        }
        return (int) round((($price - $discountPrice) / $price) * 100);
    }

    public function run(): void
    {
        // ── 1. Admin User ────────────────────────────────────────────────────
        // No weak default password: if an admin user already exists, leave its
        // password untouched; otherwise ADMIN_PASSWORD must be set explicitly.
        $email = env('ADMIN_EMAIL', 'admin@trinetraa.com');

        if (User::where('email', $email)->exists()) {
            $this->command->info("Admin already exists, skipping: {$email}");
        } else {
            $password = env('ADMIN_PASSWORD');
            if (! $password) {
                throw new \RuntimeException('ADMIN_PASSWORD env var must be set to seed the initial admin user.');
            }

            $admin = User::create([
                'email' => $email,
                'name' => 'Admin',
                'password_hash' => password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]),
                'role' => 'owner',
                'is_active' => true,
            ]);
            $this->command->info("Admin seeded: {$admin->email}");
        }

        // ── 2. Categories ────────────────────────────────────────────────────
        $categoryData = [
            ['name' => 'Sunglasses', 'slug' => 'sunglasses', 'description' => 'Premium UV-protection sunglasses for every style and occasion.', 'sort_order' => 1],
            ['name' => 'Eyeglasses', 'slug' => 'eyeglasses', 'description' => 'Prescription and non-prescription eyeglasses in the latest styles.', 'sort_order' => 2],
            ['name' => 'Contact Lenses', 'slug' => 'contact-lenses', 'description' => 'Daily, monthly and toric contact lenses from leading brands.', 'sort_order' => 3],
            ['name' => 'Kids Eyewear', 'slug' => 'kids-eyewear', 'description' => 'Durable, comfortable and colourful eyewear designed for children.', 'sort_order' => 4],
            ['name' => 'Sports Eyewear', 'slug' => 'sports-eyewear', 'description' => 'Impact-resistant, high-performance eyewear for athletes and outdoor enthusiasts.', 'sort_order' => 5],
        ];
        foreach ($categoryData as $cat) {
            Category::updateOrCreate(
                ['slug' => $cat['slug']],
                ['name' => $cat['name'], 'description' => $cat['description'], 'sort_order' => $cat['sort_order'], 'is_active' => true]
            );
        }
        $this->command->info('Categories seeded: ' . count($categoryData));

        // ── 3. Brands ────────────────────────────────────────────────────────
        $brandData = [
            ['name' => 'Ray-Ban', 'slug' => 'ray-ban', 'website' => 'https://www.rayban.com', 'story' => "Founded in 1937 for American aviators, Ray-Ban is the world's most iconic eyewear brand. Known for the timeless Aviator and Wayfarer, Ray-Ban frames combine Italian craftsmanship with bold American spirit.", 'description' => "The world's most iconic eyewear brand since 1937.", 'sort_order' => 1],
            ['name' => 'Oakley', 'slug' => 'oakley', 'website' => 'https://www.oakley.com', 'story' => 'Born in a California garage in 1975, Oakley has redefined performance eyewear. Their proprietary PRIZM lens technology and O-Matter frame material set the gold standard for sports optics worldwide.', 'description' => 'High-performance sports eyewear engineered for athletes.', 'sort_order' => 2],
            ['name' => 'Carrera', 'slug' => 'carrera', 'website' => 'https://www.carrera.com', 'story' => 'Launched in Austria in 1956, Carrera has been synonymous with motorsport and daring design. Bold lines, wraparound silhouettes, and racing-inspired aesthetics define every Carrera frame.', 'description' => 'Racing-inspired bold eyewear from Austria since 1956.', 'sort_order' => 3],
            ['name' => 'Titan', 'slug' => 'titan', 'website' => 'https://www.titaneyeplus.com', 'story' => 'A proud Indian brand from the Tata Group stable, Titan Eye+ brings world-class eyewear to every corner of India. With over 900 stores, Titan combines international quality with deeply rooted Indian values of trust and service.', 'description' => "India's most trusted homegrown eyewear brand by Tata Group.", 'sort_order' => 4],
            ['name' => 'Fastrack', 'slug' => 'fastrack', 'website' => 'https://www.fastrack.in', 'story' => "Fastrack is India's leading youth accessories brand, part of the Tata Group. With edgy designs, vibrant colours and pocket-friendly pricing, Fastrack has become the go-to eyewear choice for India's young generation.", 'description' => "India's coolest youth eyewear brand with edgy, affordable styles.", 'sort_order' => 5],
            ['name' => 'VDGE', 'slug' => 'vdge', 'website' => 'https://www.vdge.in', 'story' => "VDGE (pronounced 'edge') is a contemporary Indian designer eyewear label celebrating minimalism and precision engineering. Each frame is a statement of quiet confidence crafted from premium acetate and titanium alloys.", 'description' => 'Minimal, architect-grade designer eyewear made in India.', 'sort_order' => 6],
            ['name' => 'Eye Plus', 'slug' => 'eye-plus', 'website' => 'https://www.eyeplus.in', 'story' => 'Eye Plus delivers premium quality eyewear at accessible price points for Indian consumers. From flexible kids frames to silicone hydrogel contact lenses, Eye Plus makes good vision care affordable for every family.', 'description' => 'Quality eyewear and contact lenses at value prices for every family.', 'sort_order' => 7],
            ['name' => 'Vogue Eyewear', 'slug' => 'vogue-eyewear', 'website' => 'https://www.vogueeyewear.com', 'story' => 'Vogue Eyewear was created in Italy in 1973 as an accessible fashion eyewear brand. With a commitment to keeping up with trends, Vogue Eyewear collections reflect the latest runway aesthetics adapted for everyday wear.', 'description' => 'Italian fashion-forward eyewear that follows the runway.', 'sort_order' => 8],
        ];
        foreach ($brandData as $brand) {
            Brand::updateOrCreate(
                ['slug' => $brand['slug']],
                [
                    'name' => $brand['name'],
                    'website' => $brand['website'],
                    'story' => $brand['story'],
                    'description' => $brand['description'],
                    'sort_order' => $brand['sort_order'],
                    'is_active' => true,
                ]
            );
        }
        $this->command->info('Brands seeded: ' . count($brandData));

        // ── 4. Services ──────────────────────────────────────────────────────
        $serviceData = [
            ['title' => 'Comprehensive Eye Examination', 'slug' => 'eye-examination', 'description' => 'A complete assessment of your visual acuity and eye health by our licensed optometrist. Includes refraction test, intraocular pressure check, and slit-lamp examination. Early detection of conditions like glaucoma, cataracts, and diabetic retinopathy.', 'icon' => '🔍', 'price' => '₹200', 'duration' => '30 min', 'sort_order' => 1],
            ['title' => 'Prescription Glasses', 'slug' => 'prescription-glasses', 'description' => 'Get your custom prescription glasses fitted and dispensed same day in most cases. We offer a wide selection of frames paired with single vision, bifocal, progressive, and anti-reflective coated lenses from trusted optical labs.', 'icon' => '👓', 'price' => null, 'duration' => '45 min', 'sort_order' => 2],
            ['title' => 'Contact Lens Fitting', 'slug' => 'contact-lens-fitting', 'description' => 'Expert fitting of soft, rigid gas-permeable, toric, and multifocal contact lenses. Our optometrist evaluates your corneal curvature and tear film to recommend the ideal lens for comfort and vision correction.', 'icon' => '👁️', 'price' => '₹300', 'duration' => '30 min', 'sort_order' => 3],
            ['title' => 'Pediatric Eye Care', 'slug' => 'pediatric-eye-care', 'description' => 'Specialised eye care for children aged 3 and above. We screen for lazy eye (amblyopia), squint (strabismus), and refractive errors. Early diagnosis and treatment are key to healthy visual development.', 'icon' => '🧒', 'price' => '₹200', 'duration' => '45 min', 'sort_order' => 4],
            ['title' => 'Retinal Screening', 'slug' => 'retinal-screening', 'description' => 'Non-invasive digital retinal imaging to detect and monitor conditions such as diabetic retinopathy, macular degeneration, and hypertensive retinopathy. Recommended annually for patients over 40 or those with diabetes.', 'icon' => '🔬', 'price' => '₹500', 'duration' => '20 min', 'sort_order' => 5],
            ['title' => 'Low Vision Aid', 'slug' => 'low-vision-aid', 'description' => 'Consultation and fitting of low vision devices for patients with significant visual impairment that cannot be fully corrected with standard glasses or contact lenses. Includes magnifiers, telescopic lenses, and electronic aids.', 'icon' => '🕶️', 'price' => '₹400', 'duration' => '60 min', 'sort_order' => 6],
        ];
        foreach ($serviceData as $svc) {
            Service::updateOrCreate(
                ['slug' => $svc['slug']],
                [
                    'title' => $svc['title'],
                    'description' => $svc['description'],
                    'icon' => $svc['icon'],
                    'price' => $svc['price'],
                    'duration' => $svc['duration'],
                    'sort_order' => $svc['sort_order'],
                    'is_active' => true,
                ]
            );
        }
        $this->command->info('Services seeded: ' . count($serviceData));

        // ── 5. Eyewear Products ──────────────────────────────────────────────
        $catMap = Category::pluck('id', 'name')->all();

        $eyewearData = [
            // Sunglasses
            ['name' => 'Classic Aviator Gold', 'slug' => 'classic-aviator-gold', 'category' => 'Sunglasses', 'brand' => 'Ray-Ban', 'price' => 3500, 'discountPrice' => null, 'description' => 'Iconic pilot-inspired aviator frames crafted from lightweight metal alloy. Features classic teardrop lenses with UV400 protection, adjustable nose pads, and a timeless gold finish that works from the beach to the boardroom.', 'image' => 'https://images.unsplash.com/photo-1511499767150-a48a237f0083?w=600&h=600&fit=crop', 'faceShapeTags' => ['oval', 'heart'], 'sort_order' => 1],
            ['name' => 'Wayfarer Original Black', 'slug' => 'ray-ban-wayfarer', 'category' => 'Sunglasses', 'brand' => 'Ray-Ban', 'price' => 4200, 'discountPrice' => 3500, 'description' => 'Timeless wayfarer design in classic matte black acetate. The most recognisable frame silhouette in eyewear history — worn by icons from the 1950s to today. Polarised lenses reduce glare on bright days.', 'image' => 'https://images.unsplash.com/photo-1509695507497-903c140c43b0?w=600&h=600&fit=crop', 'faceShapeTags' => ['oval', 'square', 'round'], 'sort_order' => 2],
            ['name' => 'Carrera 1001/S', 'slug' => 'carrera-1001s', 'category' => 'Sunglasses', 'brand' => 'Carrera', 'price' => 5500, 'discountPrice' => 4800, 'description' => "Racing-inspired bold wraparound frames from Carrera's heritage motorsport collection. Injected polycarbonate chassis with rubber grip temples for secure fit at speed. Full UV400 shield lenses.", 'image' => 'https://images.unsplash.com/photo-1509695507497-903c140c43b0?w=600&h=600&fit=crop', 'faceShapeTags' => ['oval', 'oblong'], 'sort_order' => 3],
            // Eyeglasses
            ['name' => 'Square Blue Light Block', 'slug' => 'titan-square-blue', 'category' => 'Eyeglasses', 'brand' => 'Titan', 'price' => 1800, 'discountPrice' => null, 'description' => 'Anti-blue-light lenses for screen users who spend long hours on computers and smartphones. Slim square TR-90 frame in matte navy. Reduces digital eye strain and filters 40% of harmful high-energy visible light.', 'image' => 'https://images.unsplash.com/photo-1574258495973-f010dfbb5371?w=600&h=600&fit=crop', 'faceShapeTags' => ['oval', 'heart', 'oblong'], 'sort_order' => 4],
            ['name' => 'Round Metal Frame', 'slug' => 'round-metal', 'category' => 'Eyeglasses', 'brand' => 'Vogue Eyewear', 'price' => 2200, 'discountPrice' => 1800, 'description' => 'Vintage-inspired round metal frames reminiscent of 1960s intellectual chic. Ultra-thin stainless steel rims with spring hinges for an all-day comfortable fit. Available with single-vision or progressive lenses.', 'image' => 'https://images.unsplash.com/photo-1577803645773-f96470509666?w=600&h=600&fit=crop', 'faceShapeTags' => ['square', 'oblong', 'diamond'], 'sort_order' => 5],
            ['name' => 'Cat Eye Gold', 'slug' => 'cat-eye-gold', 'category' => 'Eyeglasses', 'brand' => 'Vogue Eyewear', 'price' => 2500, 'discountPrice' => null, 'description' => 'Bold cat-eye frames in shiny gold-tone metal with a subtle crystal detail on the temple. A statement piece that flatters round and oval faces. Pairs beautifully with anti-reflective and photo-chromatic lenses.', 'image' => 'https://images.unsplash.com/photo-1582142306909-195724d33ffc?w=600&h=600&fit=crop', 'faceShapeTags' => ['round', 'oval', 'square'], 'sort_order' => 6],
            ['name' => 'Thin Rimless Titanium', 'slug' => 'rimless-titanium', 'category' => 'Eyeglasses', 'brand' => 'Carrera', 'price' => 3800, 'discountPrice' => 3200, 'description' => 'Ultra-lightweight rimless titanium frame that weighs just 8 grams. Near-invisible design lets your face take centre stage. Suitable for high-prescription lenses thanks to drill-mount technology. Anti-corrosion coating included.', 'image' => 'https://images.unsplash.com/photo-1574258495973-f010dfbb5371?w=600&h=600&fit=crop', 'faceShapeTags' => ['oval', 'oblong', 'diamond'], 'sort_order' => 7],
            ['name' => 'Fastrack Urban Blue', 'slug' => 'fastrack-urban', 'category' => 'Eyeglasses', 'brand' => 'Fastrack', 'price' => 1200, 'discountPrice' => null, 'description' => 'Trendy rectangular frames in matte blue TR-90 plastic. Lightweight, flexible, and designed for the young urban commuter. Scratch-resistant polycarbonate lenses with anti-glare coating included at this price.', 'image' => 'https://images.unsplash.com/photo-1577803645773-f96470509666?w=600&h=600&fit=crop', 'faceShapeTags' => ['oval', 'heart'], 'sort_order' => 8],
            ['name' => 'VDGE Minimal Black', 'slug' => 'vdge-minimal', 'category' => 'Eyeglasses', 'brand' => 'VDGE', 'price' => 2800, 'discountPrice' => 2400, 'description' => 'Architect-grade minimalist black frames precision-cut from a single sheet of premium Italian acetate. Clean lines, no unnecessary detail — just pure form and function. Each pair is hand-finished and numbered.', 'image' => 'https://images.unsplash.com/photo-1582142306909-195724d33ffc?w=600&h=600&fit=crop', 'faceShapeTags' => ['oval', 'oblong'], 'sort_order' => 9],
            // Kids Eyewear
            ['name' => 'Kids Flex Purple', 'slug' => 'kids-flex-purple', 'category' => 'Kids Eyewear', 'brand' => 'Eye Plus', 'price' => 900, 'discountPrice' => null, 'description' => 'Flexible memory titanium kids frame in cheerful purple that returns to its original shape even after rough play. Soft silicone nose pads and temple tips for all-day comfort. Suitable for ages 4 to 10. Prescription-ready.', 'image' => 'https://images.unsplash.com/photo-1503454537195-1dcabb73ffb9?w=600&h=600&fit=crop', 'faceShapeTags' => ['oval', 'round', 'heart'], 'sort_order' => 10],
            ['name' => 'Kids Sports Shield', 'slug' => 'kids-sports', 'category' => 'Kids Eyewear', 'brand' => 'Eye Plus', 'price' => 1100, 'discountPrice' => null, 'description' => 'Impact-resistant polycarbonate lens frame designed for active children. Meets ANSI Z87.1 safety standards. Wraparound design protects from dust and debris during outdoor sports and cycling. Ages 6 to 14.', 'image' => 'https://images.unsplash.com/photo-1503454537195-1dcabb73ffb9?w=600&h=600&fit=crop', 'faceShapeTags' => ['oval', 'square'], 'sort_order' => 11],
            // Sports Eyewear
            ['name' => 'Oakley Flak 2.0', 'slug' => 'oakley-flak', 'category' => 'Sports Eyewear', 'brand' => 'Oakley', 'price' => 6500, 'discountPrice' => 5800, 'description' => "High-performance sports wrap with Oakley's proprietary PRIZM Road lens technology that enhances colour contrast for better road visibility. O-Matter lightweight frame, Unobtainium ear socks for grip in wet conditions. Ideal for cycling and running.", 'image' => 'https://images.unsplash.com/photo-1503454537195-1dcabb73ffb9?w=600&h=600&fit=crop', 'faceShapeTags' => ['oval', 'oblong'], 'sort_order' => 12],
            ['name' => 'Performance Goggle', 'slug' => 'performance-goggle', 'category' => 'Sports Eyewear', 'brand' => 'Oakley', 'price' => 4500, 'discountPrice' => null, 'description' => 'UV400 polycarbonate sports shield with 180-degree peripheral vision for cricket, football, and multi-sport use. Rubberised anti-slip temple grips and adjustable nose pad. Meets CE EN 166 safety certification.', 'image' => 'https://images.unsplash.com/photo-1503454537195-1dcabb73ffb9?w=600&h=600&fit=crop', 'faceShapeTags' => ['oval', 'square', 'oblong'], 'sort_order' => 13],
            // Contact Lenses
            ['name' => 'Monthly Contact Lenses', 'slug' => 'monthly-contact', 'category' => 'Contact Lenses', 'brand' => 'Eye Plus', 'price' => 800, 'discountPrice' => null, 'description' => 'Silicone hydrogel monthly disposables with high oxygen transmissibility for all-day comfort. 55% water content ensures eyes stay moist throughout the day. Available in powers from -0.50 to -12.00 and +0.50 to +6.00. Pack of 6 lenses.', 'image' => 'https://images.unsplash.com/photo-1576091160550-2173dba999ef?w=600&h=600&fit=crop', 'faceShapeTags' => [], 'sort_order' => 14],
            ['name' => 'Daily Disposable Contact', 'slug' => 'daily-contact', 'category' => 'Contact Lenses', 'brand' => 'Eye Plus', 'price' => 1200, 'discountPrice' => null, 'description' => 'Ultra-thin daily disposables for maximum convenience and hygiene. No cleaning required — simply wear and discard each day. Ideal for occasional wear, travel, or allergy sufferers. UV blocking Type 1 protection. Box of 30 lenses.', 'image' => 'https://images.unsplash.com/photo-1576091160550-2173dba999ef?w=600&h=600&fit=crop', 'faceShapeTags' => [], 'sort_order' => 15],
            ['name' => 'Toric Contact Lens', 'slug' => 'toric-contact', 'category' => 'Contact Lenses', 'brand' => 'Eye Plus', 'price' => 1500, 'discountPrice' => null, 'description' => 'Astigmatism-correcting toric lenses with a stabilisation system that keeps the lens correctly oriented for clear vision throughout the day. Monthly replacement, silicone hydrogel material for 16 hours of comfortable wear. Available in a wide range of cylinder and axis powers.', 'image' => 'https://images.unsplash.com/photo-1576091160550-2173dba999ef?w=600&h=600&fit=crop', 'faceShapeTags' => [], 'sort_order' => 16],
        ];
        foreach ($eyewearData as $ew) {
            $categoryId = $catMap[$ew['category']] ?? null;
            if (!$categoryId) {
                $this->command->warn("Category not found for: {$ew['name']} ({$ew['category']})");
                continue;
            }
            $pct = $this->discountPct($ew['price'], $ew['discountPrice']);
            $tags = count($ew['faceShapeTags']) > 0 ? json_encode($ew['faceShapeTags']) : null;
            Eyewear::updateOrCreate(
                ['slug' => $ew['slug']],
                [
                    'name' => $ew['name'],
                    'category_id' => $categoryId,
                    'brand' => $ew['brand'],
                    'price' => $ew['price'],
                    'discount_percentage' => $pct,
                    'description' => $ew['description'],
                    'image' => $ew['image'],
                    'face_shape_tags' => $tags,
                    'sort_order' => $ew['sort_order'],
                    'is_active' => true,
                    'availability' => 'in_store',
                ]
            );
        }
        $this->command->info('Eyewear products seeded: ' . count($eyewearData));

        // ── 6. Reviews ───────────────────────────────────────────────────────
        $reviewData = [
            ['user_name' => 'Priya Deshmukh', 'user_email' => 'priya.deshmukh@demo.trinetraa.com', 'rating' => 5, 'review_text' => 'Absolutely love my new glasses from Trinetraa! The staff was incredibly patient in helping me choose the right frame for my face shape. The eye test was thorough and the doctor explained everything clearly. Will definitely come back.'],
            ['user_name' => 'Rahul Nair', 'user_email' => 'rahul.nair@demo.trinetraa.com', 'rating' => 5, 'review_text' => "Best optical store in Nashik, hands down. I've been getting my glasses here for three years now. The collection is always up to date with the latest styles. Got the Ray-Ban Wayfarer last month — superb quality and fair pricing."],
            ['user_name' => 'Sunita Borse', 'user_email' => 'sunita.borse@demo.trinetraa.com', 'rating' => 4, 'review_text' => 'Very good experience overall. The frame selection is excellent and covers all budgets. Slight wait during peak hours but the staff handled it gracefully. My progressive lenses were ready within 2 days as promised.'],
            ['user_name' => 'Amit Kulkarni', 'user_email' => 'amit.kulkarni@demo.trinetraa.com', 'rating' => 5, 'review_text' => 'Came in for contact lens fitting and left as a very happy customer. The optometrist was highly professional and recommended the right toric lenses for my astigmatism. No discomfort at all — wearing them all day comfortably now.'],
            ['user_name' => 'Kavya Rao', 'user_email' => 'kavya.rao@demo.trinetraa.com', 'rating' => 5, 'review_text' => "Got my daughter's glasses here — she has a strong prescription and the staff found her a perfect Kids Flex frame that she actually loves wearing. The flexible frame survived two months of school so far with zero damage. Highly recommend!"],
            ['user_name' => 'Mahesh Patil', 'user_email' => 'mahesh.patil@demo.trinetraa.com', 'rating' => 4, 'review_text' => "Good store with knowledgeable staff. I appreciated that they didn't try to oversell me on features I didn't need. My anti-blue-light lenses have made a noticeable difference in screen fatigue. Prices are competitive with the big chains."],
            ['user_name' => 'Deepa Joshi', 'user_email' => 'deepa.joshi@demo.trinetraa.com', 'rating' => 5, 'review_text' => 'The retinal screening service is a fantastic addition. I had my scan done and the doctor detected an early-stage concern that I would have otherwise missed. Truly grateful for the thoroughness and care shown by the whole team.'],
            ['user_name' => 'Nikhil Tawde', 'user_email' => 'nikhil.tawde@demo.trinetraa.com', 'rating' => 5, 'review_text' => 'Picked up the Oakley Flak 2.0 for cycling. The in-store team helped me understand the PRIZM lens difference and it was worth every rupee. Great advice, fast service, and the carry case they included was a nice bonus.'],
            ['user_name' => 'Anita Sharma', 'user_email' => 'anita.sharma@demo.trinetraa.com', 'rating' => 4, 'review_text' => 'Clean, well-organised store with a calm atmosphere — very different from the usual chaotic optical shops. My husband and I both got our eye exams done together. Reasonable prices and a wonderful doctor. Four stars only because parking can be tricky.'],
            ['user_name' => 'Suresh Mahadik', 'user_email' => 'suresh.mahadik@demo.trinetraa.com', 'rating' => 5, 'review_text' => 'I specifically chose Trinetraa after reading the reviews and I was not disappointed. The VDGE Minimal Black glasses I ordered look exactly as advertised. Staff is courteous, follow-up was prompt, and the quality of the lenses is outstanding.'],
        ];
        foreach ($reviewData as $rev) {
            Review::updateOrCreate(
                ['user_email' => $rev['user_email']],
                [
                    'user_name' => $rev['user_name'],
                    'rating' => $rev['rating'],
                    'review_text' => $rev['review_text'],
                    'is_approved' => true,
                ]
            );
        }
        $this->command->info('Reviews seeded: ' . count($reviewData));

        // ── 7. Gallery ───────────────────────────────────────────────────────
        $galleryData = [
            ['type' => 'image', 'title' => 'Our Store Interior', 'description' => 'A warm welcome awaits you at Trinetraa Optician, Nashik — over 500 frames on display.', 'image' => 'https://images.unsplash.com/photo-1586281380349-632531db7ed4?w=800&h=600&fit=crop', 'sort_order' => 1],
            ['type' => 'image', 'title' => 'Comprehensive Eye Examination', 'description' => 'Our licensed optometrists use the latest diagnostic equipment for precise results.', 'image' => 'https://images.unsplash.com/photo-1559757148-5c350d0d3c56?w=800&h=600&fit=crop', 'sort_order' => 2],
            ['type' => 'image', 'title' => 'Premium Frame Collection', 'description' => 'Curated displays of frames from Ray-Ban, Oakley, Carrera, Titan and more.', 'image' => 'https://images.unsplash.com/photo-1574258495973-f010dfbb5371?w=800&h=600&fit=crop', 'sort_order' => 3],
            ['type' => 'image', 'title' => 'Happy Customer', 'description' => 'Nothing makes us happier than seeing a customer leave with their perfect pair.', 'image' => 'https://images.unsplash.com/photo-1508214751196-bcfd4ca60f91?w=800&h=600&fit=crop', 'sort_order' => 4],
            ['type' => 'image', 'title' => 'Slit-Lamp Examination', 'description' => 'Advanced slit-lamp biomicroscopy for detailed assessment of the anterior and posterior eye.', 'image' => 'https://images.unsplash.com/photo-1576091160550-2173dba999ef?w=800&h=600&fit=crop', 'sort_order' => 5],
            ['type' => 'image', 'title' => 'Sunglasses Display', 'description' => "Seasonal sunglasses from the world's top brands — UV400 certified for your protection.", 'image' => 'https://images.unsplash.com/photo-1511499767150-a48a237f0083?w=800&h=600&fit=crop', 'sort_order' => 6],
            ['type' => 'image', 'title' => 'Kids Eyewear Corner', 'description' => "A dedicated section for children's eyewear — fun, durable, and prescription-ready.", 'image' => 'https://images.unsplash.com/photo-1503454537195-1dcabb73ffb9?w=800&h=600&fit=crop', 'sort_order' => 7],
            ['type' => 'image', 'title' => 'Contact Lens Consultation', 'description' => 'Expert fitting and aftercare guidance for first-time and experienced contact lens wearers.', 'image' => 'https://images.unsplash.com/photo-1576091160550-2173dba999ef?w=800&h=600&fit=crop', 'sort_order' => 8],
        ];
        foreach ($galleryData as $item) {
            $existing = GalleryItem::where('title', $item['title'])->first();
            $payload = array_merge($item, ['is_active' => true]);
            if ($existing) {
                $existing->update($payload);
            } else {
                GalleryItem::create($payload);
            }
        }
        $this->command->info('Gallery seeded: ' . count($galleryData));

        // ── 8. Offers ────────────────────────────────────────────────────────
        $offerData = [
            ['title' => 'Monsoon Frame Fest 2025', 'description' => 'Celebrate the season with 20% off on all frames across our entire collection. Valid on Ray-Ban, Oakley, Carrera, Titan, Fastrack, VDGE, Eye Plus and Vogue Eyewear. Offer applicable on in-store purchases only. Cannot be combined with other discounts.', 'badge' => 'LIMITED TIME', 'valid_from' => '2025-06-01', 'valid_to' => '2025-08-31', 'sort_order' => 1, 'is_active' => true, 'image' => 'https://images.unsplash.com/photo-1511499767150-a48a237f0083?w=800&h=400&fit=crop'],
            ['title' => 'Eye Test + Free Frames', 'description' => 'Book a Comprehensive Eye Examination and receive a complimentary basic frame (valued up to ₹1,000) with your prescription. Choose from our curated selection of Eye Plus and Fastrack frames. A complete vision care solution at an unbeatable value.', 'badge' => 'COMBO DEAL', 'valid_from' => '2025-06-01', 'valid_to' => '2025-12-31', 'sort_order' => 2, 'is_active' => true, 'image' => 'https://images.unsplash.com/photo-1574258495973-f010dfbb5371?w=800&h=400&fit=crop'],
            ['title' => 'Kids Eyewear Special', 'description' => 'Buy any 2 kids frames from our collection and get the 3rd absolutely free! Mix and match from Kids Flex, Kids Sports Shield, and the full Eye Plus kids range. The best value offer for families. Valid while stocks last.', 'badge' => 'KIDS SPECIAL', 'valid_from' => '2025-06-01', 'valid_to' => '2025-09-30', 'sort_order' => 3, 'is_active' => true, 'image' => 'https://images.unsplash.com/photo-1503454537195-1dcabb73ffb9?w=800&h=400&fit=crop'],
        ];
        foreach ($offerData as $offer) {
            $existing = Offer::where('title', $offer['title'])->first();
            if ($existing) {
                $existing->update($offer);
            } else {
                Offer::create($offer);
            }
        }
        $this->command->info('Offers seeded: ' . count($offerData));

        // ── 9. Blog Posts ────────────────────────────────────────────────────
        $blogData = [
            [
                'title' => 'How Often Should You Get an Eye Exam?',
                'slug' => 'how-often-eye-exam',
                'excerpt' => "Most adults should have a comprehensive eye exam every 1–2 years, but the right frequency depends on your age, health, and risk factors. Here's what our optometrist recommends.",
                'content' => "Regular eye examinations are one of the most important — and most overlooked — aspects of preventive healthcare. Unlike dental check-ups, many people only visit an optometrist when something feels wrong. But by that point, conditions like glaucoma or macular degeneration may already be progressing silently.\n\n**General guidelines by age group:**\n\n- **Children (age 3–5):** At least one eye exam before starting school to detect amblyopia (lazy eye), strabismus, or significant refractive errors.\n- **School-age children (6–17):** Annually, especially since uncorrected vision problems are a common cause of learning difficulties.\n- **Adults (18–39):** Every two years if no risk factors; annually if you wear glasses or contacts.\n- **Adults (40–64):** Every 1–2 years. The risk of presbyopia, glaucoma, and cataracts begins to rise after 40.\n- **Seniors (65+):** Every year. Age-related macular degeneration and diabetic retinopathy require close monitoring.\n\n**Who needs more frequent exams?**\n\nIf you have diabetes, a family history of eye disease, high blood pressure, or a previous eye injury, your optometrist may recommend more frequent visits regardless of age.\n\n**What happens during a comprehensive eye exam at Trinetraa?**\n\nOur 30-minute examination covers visual acuity testing, refraction (to determine your exact prescription), slit-lamp examination, intraocular pressure measurement, and — if required — dilated fundus examination. We use digital records so your prescription history is always on file.\n\nDon't wait for symptoms. Book your eye exam at Trinetraa Optician today.",
                'is_published' => true,
                'published_at' => '2025-05-10',
                'meta_title' => 'How Often Should You Get an Eye Exam? | Trinetraa Optician',
                'meta_description' => 'Learn how often you should get an eye exam based on your age and health. Expert advice from Trinetraa Optician, Nashik.',
                'image' => 'https://images.unsplash.com/photo-1559757148-5c350d0d3c56?w=800&h=450&fit=crop',
            ],
            [
                'title' => 'Do Blue Light Glasses Really Work?',
                'slug' => 'blue-light-glasses',
                'excerpt' => 'With screen time at an all-time high, blue light glasses have become popular. But does the science back them up? We separate fact from marketing.',
                'content' => "If you spend hours in front of a computer, smartphone, or tablet, you've probably heard about blue light glasses. Retailers claim they reduce eye strain, improve sleep, and protect your eyes from long-term damage. But what does the research actually say?\n\n**What is blue light?**\n\nBlue light is part of the visible light spectrum with wavelengths between 380–500 nanometres. It has the highest energy of all visible light. While it occurs naturally in sunlight and helps regulate our circadian rhythm, digital screens also emit significant amounts — though far less than the sun.\n\n**The claims vs the evidence:**\n\n*Claim: Blue light causes digital eye strain*\nThe scientific consensus is that digital eye strain (tired, dry, or irritated eyes after prolonged screen use) is primarily caused by reduced blinking and poor ergonomics — not blue light wavelengths specifically. A 2021 Cochrane review found insufficient evidence that blue light filtering reduces eye strain.\n\n*Claim: Blue light damages the retina*\nCurrent evidence suggests the levels emitted by screens are not high enough to cause retinal damage. However, excessive night-time screen use can disrupt sleep by suppressing melatonin.\n\n*Where blue light glasses do help:*\n- Reducing glare from screens (the anti-reflective coating, not the filter itself)\n- Evening screen use to protect sleep quality\n- Patients who report subjective improvement in comfort\n\n**Our recommendation:**\n\nAt Trinetraa, we recommend blue light filtering lenses as part of a broader screen hygiene strategy — not as a standalone solution. Combine them with the 20-20-20 rule (every 20 minutes, look at something 20 feet away for 20 seconds), proper monitor positioning, and adequate room lighting.\n\nWe stock a range of anti-blue-light lenses from Titan Eye+ that can be fitted to any prescription.",
                'is_published' => true,
                'published_at' => '2025-05-22',
                'meta_title' => 'Do Blue Light Glasses Really Work? | Trinetraa Optician',
                'meta_description' => "Exploring the science behind blue light glasses — what works, what doesn't, and what our optometrist recommends for screen users in Nashik.",
                'image' => 'https://images.unsplash.com/photo-1574258495973-f010dfbb5371?w=800&h=450&fit=crop',
            ],
            [
                'title' => '7 Signs You Might Need Glasses',
                'slug' => 'signs-need-glasses',
                'excerpt' => "Blurry vision is the obvious sign — but there are 6 others that many people miss. Read on to find out if it's time to visit an optometrist.",
                'content' => "Many people live with uncorrected vision problems for years without realising it. The human brain is remarkably good at compensating for visual deficits, which means by the time symptoms feel obvious, your eyesight may have been declining for some time. Here are seven signs that it's time to book an eye exam.\n\n**1. Frequent headaches**\nEyestrain from uncorrected near- or farsightedness forces your eye muscles to work overtime, often resulting in tension headaches — particularly around the temples and forehead. If you get headaches during or after reading or screen use, your vision could be the cause.\n\n**2. Squinting at signs or screens**\nSquinting temporarily improves focus by reducing the amount of light entering the eye. If you catch yourself doing it often, your eyes are struggling.\n\n**3. Difficulty seeing at night**\nNight myopia — difficulty focusing in low light — is a common and correctable condition. If driving after dark feels increasingly stressful, a prescription update could transform your experience.\n\n**4. Holding your phone too close or too far**\nPresbyopia (age-related near-vision loss) typically starts in the early 40s. If you find yourself stretching your arm to read a menu, reading glasses or progressive lenses can help.\n\n**5. Eye fatigue by mid-afternoon**\nTired, burning or aching eyes after a few hours of work often indicate uncorrected astigmatism. The eyes work extra hard to compensate for the uneven corneal curvature, causing fatigue.\n\n**6. Double vision or blurring when tired**\nOccasional double vision can indicate problems with eye muscle coordination (convergence insufficiency) that respond well to prism lenses or vision therapy.\n\n**7. A family history of eye disease**\nConditions like glaucoma, macular degeneration, and keratoconus have strong genetic components. If a close family member has been diagnosed, proactive screening is essential.\n\nIf you recognise two or more of these signs, book a Comprehensive Eye Examination at Trinetraa Optician, Nashik. Early detection is always the best protection.",
                'is_published' => true,
                'published_at' => '2025-06-01',
                'meta_title' => '7 Signs You Might Need Glasses | Trinetraa Optician Nashik',
                'meta_description' => 'Headaches, squinting, and night vision trouble are signs you might need glasses. Learn all 7 from our optometrists at Trinetraa, Nashik.',
                'image' => 'https://images.unsplash.com/photo-1577803645773-f96470509666?w=800&h=450&fit=crop',
            ],
            [
                'title' => 'Complete Guide to Contact Lens Care',
                'slug' => 'contact-lens-care',
                'excerpt' => 'Proper contact lens hygiene is critical to eye health. Follow this step-by-step guide from our optometrists to keep your lenses — and your eyes — safe.',
                'content' => "Contact lenses are a convenient and effective vision correction option for millions of people. But improper care remains the leading cause of contact-lens-related eye infections, some of which can cause permanent vision loss. This guide covers everything you need to know.\n\n**Hand hygiene — the non-negotiable first step**\n\nAlways wash your hands with soap and water and dry them thoroughly with a lint-free towel before touching your lenses. This single step prevents the vast majority of contact-related infections.\n\n**The correct cleaning routine:**\n\n1. **Remove lenses** one at a time and place in your palm.\n2. **Rub and rinse** with fresh multipurpose solution for at least 5 seconds per side — even if the packaging says \"no-rub.\"\n3. **Place in a clean case** filled with fresh solution. Never top-up old solution.\n4. **Soak** for the minimum time specified on the solution bottle (usually 4–6 hours).\n\n**Case hygiene:**\n\n- Rinse your case with **fresh solution** (never water) after each use.\n- Allow it to air dry face down on a clean tissue.\n- Replace your lens case **every 1–3 months**.\n\n**Never do these things:**\n\n- Wear lenses while swimming, showering, or in a hot tub (Acanthamoeba infection risk).\n- Sleep in lenses not prescribed for overnight wear.\n- Use tap water, saliva, or homemade saline to rinse lenses.\n- Extend monthly lenses beyond 30 days of wear.\n\n**When to remove lenses immediately:**\n\nRedness, pain, unusual discharge, sensitivity to light, or sudden blurring are red-flag symptoms. Remove your lenses and visit Trinetraa Optician or an ophthalmologist the same day.\n\n**Annual check-up:**\n\nEven if your prescription hasn't changed, an annual contact lens check ensures the fit is still correct, your corneas are healthy, and your lens parameters suit your current needs.\n\nFor personalised advice on the right lens for your prescription and lifestyle, book a Contact Lens Fitting appointment at Trinetraa.",
                'is_published' => true,
                'published_at' => '2025-06-10',
                'meta_title' => 'Complete Guide to Contact Lens Care | Trinetraa Optician',
                'meta_description' => 'Step-by-step guide to safe contact lens care from Trinetraa Optician Nashik — cleaning, case hygiene, and red-flag warning signs.',
                'image' => 'https://images.unsplash.com/photo-1576091160550-2173dba999ef?w=800&h=450&fit=crop',
            ],
            [
                'title' => "Children's Eye Health: A Parent's Guide",
                'slug' => 'childrens-eye-health',
                'excerpt' => "Vision problems in children often go undetected because kids don't know what 'normal' vision looks like. Here's what every parent should watch for.",
                'content' => "Good vision is fundamental to a child's development, learning, and quality of life. Yet the Indian National Blindness and Visual Impairment Survey found that refractive errors in school-age children are significantly under-diagnosed — largely because children rarely complain about vision problems they've always had.\n\n**When should my child first see an optometrist?**\n\nWe recommend a first eye exam by age 3–4, before starting school. Many developmental milestones depend on vision, and early detection of conditions like amblyopia (lazy eye) is critical — treatment is most effective before age 7.\n\n**Warning signs to watch for:**\n\n- Sitting very close to the TV or holding books close to the face\n- Tilting or turning the head to look at objects\n- Frequent eye rubbing not related to tiredness\n- One eye turning in or out (squint)\n- Complaints of headaches after school\n- Poor performance in subjects that require reading or board work\n- Avoiding activities that need close work (reading, drawing)\n\n**Common childhood eye conditions:**\n\n**Amblyopia (Lazy Eye):** Reduced vision in one eye that hasn't developed normally. Treatment involves patching the stronger eye to force the weaker eye to work. Success rate is high when treated early.\n\n**Strabismus (Squint):** Misalignment of the eyes. Can be treated with glasses, patches, prism lenses, or surgery depending on severity.\n\n**Myopia (Short-sightedness):** The most common refractive error in children — and it's increasing. Research shows outdoor activity (at least 2 hours per day) significantly slows myopia progression. Myopia control lenses and orthokeratology are now available options.\n\n**Choosing kids' frames:**\n\nFor children, we recommend impact-resistant polycarbonate lenses and flexible TR-90 or memory titanium frames that survive the rigours of school life. Our Kids Flex and Kids Sports Shield ranges are built specifically for active children.\n\nBook a Pediatric Eye Care appointment at Trinetraa. We make the experience comfortable and engaging for children from the very first visit.",
                'is_published' => true,
                'published_at' => '2025-06-18',
                'meta_title' => "Children's Eye Health: A Parent's Guide | Trinetraa Optician",
                'meta_description' => "Warning signs, common conditions, and when to book your child's first eye exam. Expert paediatric eye care advice from Trinetraa Optician, Nashik.",
                'image' => 'https://images.unsplash.com/photo-1503454537195-1dcabb73ffb9?w=800&h=450&fit=crop',
            ],
        ];
        foreach ($blogData as $post) {
            BlogPost::updateOrCreate(
                ['slug' => $post['slug']],
                [
                    'title' => $post['title'],
                    'excerpt' => $post['excerpt'],
                    'content' => $post['content'],
                    'image' => $post['image'],
                    'is_published' => $post['is_published'],
                    'published_at' => $post['published_at'],
                    'meta_title' => $post['meta_title'],
                    'meta_description' => $post['meta_description'],
                ]
            );
        }
        $this->command->info('Blog posts seeded: ' . count($blogData));

        // ── 10. Appointments ─────────────────────────────────────────────────
        $appointmentData = [
            ['name' => 'Sanjay Pawar', 'email' => 'sanjay.pawar@demo.trinetraa.com', 'phone' => '9823100001', 'service' => 'Comprehensive Eye Examination', 'appt_date' => '2025-06-25', 'time_slot' => '10:00 AM', 'notes' => 'First visit. Experiencing headaches after long work hours.', 'status' => 'confirmed'],
            ['name' => 'Rekha Gaikwad', 'email' => 'rekha.gaikwad@demo.trinetraa.com', 'phone' => '9823100002', 'service' => 'Comprehensive Eye Examination', 'appt_date' => '2025-06-26', 'time_slot' => '11:30 AM', 'notes' => 'Existing patient. Annual check-up due.', 'status' => 'confirmed'],
            ['name' => 'Vinayak Shinde', 'email' => 'vinayak.shinde@demo.trinetraa.com', 'phone' => '9823100003', 'service' => 'Comprehensive Eye Examination', 'appt_date' => '2025-07-02', 'time_slot' => '09:30 AM', 'notes' => 'Referred by Dr. Patil. Possible early glaucoma.', 'status' => 'confirmed'],
            ['name' => 'Pooja Deore', 'email' => 'pooja.deore@demo.trinetraa.com', 'phone' => '9823100004', 'service' => 'Comprehensive Eye Examination', 'appt_date' => '2025-07-05', 'time_slot' => '02:00 PM', 'notes' => 'Child patient, age 7. School noticed trouble reading the board.', 'status' => 'confirmed'],
            ['name' => 'Rajan Mhase', 'email' => 'rajan.mhase@demo.trinetraa.com', 'phone' => '9823100005', 'service' => 'Comprehensive Eye Examination', 'appt_date' => '2025-07-08', 'time_slot' => '12:00 PM', 'notes' => 'Diabetic patient. Annual retinal screening included.', 'status' => 'confirmed'],
        ];
        foreach ($appointmentData as $appt) {
            $existing = Appointment::where('email', $appt['email'])->first();
            if (!$existing) {
                Appointment::create($appt);
            }
        }
        $this->command->info('Appointments seeded: ' . count($appointmentData));

        // ── 11. Serviceable Pincodes ─────────────────────────────────────────
        $pincodes = [
            ['pincode' => '422001', 'area' => 'Nashik City'],
            ['pincode' => '422002', 'area' => 'Nashik Road'],
            ['pincode' => '422003', 'area' => 'Deolali'],
            ['pincode' => '422004', 'area' => 'Satpur'],
            ['pincode' => '422005', 'area' => 'Panchavati'],
            ['pincode' => '422006', 'area' => 'Sinhagad Road'],
            ['pincode' => '422007', 'area' => 'Cidco'],
            ['pincode' => '422008', 'area' => 'Gangapur Road'],
            ['pincode' => '422009', 'area' => 'Ambad'],
            ['pincode' => '422010', 'area' => 'Trimbak Road'],
        ];
        foreach ($pincodes as $p) {
            ServiceablePincode::updateOrCreate(
                ['pincode' => $p['pincode']],
                ['area' => $p['area'], 'is_active' => true]
            );
        }
        $this->command->info('Pincodes seeded: ' . count($pincodes));

        $this->command->info('All seed data inserted successfully.');
    }
}
