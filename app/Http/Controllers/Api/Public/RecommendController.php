<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\Eyewear;
use App\Support\FrameRecommendations;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RecommendController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'faceShape' => 'required|in:Oval,Round,Square,Heart,Oblong',
            'stylePref' => 'required|in:professional,casual,sporty,fashion-forward,classic',
            'usage' => 'required|in:office,outdoor,driving,everyday,reading',
            'budget' => 'required|in:under-500,500-2000,2000-5000,above-5000',
        ]);

        if ($validator->fails()) {
            return $this->message($validator->errors()->first(), 400);
        }

        $data = $validator->validated();
        $faceShape = $data['faceShape'];
        $stylePref = $data['stylePref'];
        $usage = $data['usage'];
        $budget = $data['budget'];

        $rec = FrameRecommendations::get([
            'faceShape' => $faceShape,
            'stylePref' => $stylePref,
            'usage' => $usage,
            'budget' => $budget,
        ]);

        $applyBudget = function ($q) use ($budget) {
            if ($budget === 'under-500') {
                $q->where('price', '<=', 500);
            } elseif ($budget === '500-2000') {
                $q->where('price', '>=', 500)->where('price', '<=', 2000);
            } elseif ($budget === '2000-5000') {
                $q->where('price', '>=', 2000)->where('price', '<=', 5000);
            } elseif ($budget === 'above-5000') {
                $q->where('price', '>=', 5000);
            }
        };

        $eyewears = Eyewear::query()
            ->where('is_active', true)
            ->where($applyBudget)
            ->where(function ($q) use ($rec) {
                foreach ($rec['recommendedTags'] as $tag) {
                    $q->orWhere('face_shape_tags', 'like', '%' . $tag . '%');
                }
            })
            ->take(8)
            ->with(['category:id,name'])
            ->get();

        if ($eyewears->count() < 4) {
            $excludeIds = $eyewears->pluck('id')->all();
            $extras = Eyewear::query()
                ->where('is_active', true)
                ->where($applyBudget)
                ->whereNotIn('id', $excludeIds)
                ->orderBy('sort_order')
                ->take(8 - $eyewears->count())
                ->with(['category:id,name'])
                ->get();
            $eyewears = $eyewears->concat($extras);
        }

        return response()->json([
            'recommendation' => $rec,
            'eyewears' => $eyewears,
        ]);
    }
}
