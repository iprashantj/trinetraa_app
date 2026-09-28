<?php

namespace App\Support;

/**
 * Rule-based frame recommendation engine — ported from lib/frameRecommendations.js
 */
class FrameRecommendations
{
    const FACE_SHAPE_FRAMES = [
        'Oval' => [
            'desc' => 'Oval faces are well-balanced and suit most frame styles.',
            'suitable' => ['rectangular', 'square', 'aviator', 'round', 'cat-eye'],
            'avoid' => [],
            'tip' => 'Almost any frame works for you! Try bold styles like thick-rimmed rectangulars or retro rounds.',
        ],
        'Round' => [
            'desc' => 'Round faces benefit from frames that add angles and length.',
            'suitable' => ['rectangular', 'square', 'geometric', 'browline', 'angular'],
            'avoid' => ['round', 'oval', 'small'],
            'tip' => 'Go for angular and rectangular frames that add definition. Avoid round or oval shapes.',
        ],
        'Square' => [
            'desc' => 'Square faces suit frames that soften the jawline.',
            'suitable' => ['round', 'oval', 'cat-eye', 'rimless', 'semi-rimless'],
            'avoid' => ['square', 'geometric', 'angular'],
            'tip' => 'Round and oval frames beautifully soften square features. Rimless frames also look great.',
        ],
        'Heart' => [
            'desc' => 'Heart-shaped faces suit frames wider at the bottom.',
            'suitable' => ['oval', 'round', 'rimless', 'light-colored', 'aviator'],
            'avoid' => ['cat-eye', 'top-heavy', 'decorative-top'],
            'tip' => 'Look for frames wider at the bottom to balance a broader forehead. Light or rimless frames work well.',
        ],
        'Oblong' => [
            'desc' => 'Oblong faces suit frames that add width.',
            'suitable' => ['oversized', 'round', 'square', 'decorative', 'colorful'],
            'avoid' => ['narrow', 'rectangular', 'small'],
            'tip' => 'Wider, taller frames add width to elongated faces. Try oversized or decorative styles.',
        ],
    ];

    const STYLE_PREF_MAP = [
        'professional' => ['rectangular', 'browline', 'semi-rimless'],
        'casual' => ['round', 'oval', 'wayfarers'],
        'sporty' => ['wraparound', 'semi-rimless', 'shield'],
        'fashion-forward' => ['cat-eye', 'oversized', 'geometric', 'colored'],
        'classic' => ['aviator', 'rectangular', 'browline'],
    ];

    const USAGE_MAP = [
        'office' => ['rectangular', 'browline', 'rimless'],
        'outdoor' => ['wraparound', 'aviator', 'shield', 'sport'],
        'driving' => ['aviator', 'polarized', 'wraparound'],
        'everyday' => ['oval', 'rectangular', 'round'],
        'reading' => ['rimless', 'semi-rimless', 'small'],
    ];

    public static function get(array $args): array
    {
        $faceShape = $args['faceShape'] ?? null;
        $stylePref = $args['stylePref'] ?? null;
        $usage = $args['usage'] ?? null;
        $budget = $args['budget'] ?? null;

        $faceInfo = self::FACE_SHAPE_FRAMES[$faceShape] ?? self::FACE_SHAPE_FRAMES['Oval'];
        $styleFrames = self::STYLE_PREF_MAP[$stylePref] ?? [];
        $usageFrames = self::USAGE_MAP[$usage] ?? [];

        $allSuitable = array_values(array_unique(array_merge($faceInfo['suitable'], $styleFrames, $usageFrames)));
        $prioritized = array_values(array_filter($faceInfo['suitable'], function ($f) use ($styleFrames, $usageFrames) {
            return in_array($f, $styleFrames) || in_array($f, $usageFrames);
        }));

        return [
            'faceShape' => $faceShape,
            'faceInfo' => $faceInfo,
            'recommendedTags' => count($prioritized) ? $prioritized : array_slice($faceInfo['suitable'], 0, 3),
            'allTags' => $allSuitable,
            'tip' => $faceInfo['tip'],
            'budgetRange' => $budget,
        ];
    }

    const QUIZ_QUESTIONS = [
        [
            'id' => 'faceShape',
            'question' => 'What is your face shape?',
            'icon' => '😊',
            'options' => [
                ['value' => 'Oval', 'label' => 'Oval', 'desc' => 'Balanced, slightly wider cheekbones'],
                ['value' => 'Round', 'label' => 'Round', 'desc' => 'Soft curves, similar width & length'],
                ['value' => 'Square', 'label' => 'Square', 'desc' => 'Strong jawline, similar width & length'],
                ['value' => 'Heart', 'label' => 'Heart', 'desc' => 'Wider forehead, narrower chin'],
                ['value' => 'Oblong', 'label' => 'Oblong', 'desc' => 'Long and narrow face'],
            ],
        ],
        [
            'id' => 'stylePref',
            'question' => 'What is your preferred style?',
            'icon' => '👗',
            'options' => [
                ['value' => 'professional', 'label' => 'Professional', 'desc' => 'Clean, office-ready look'],
                ['value' => 'casual', 'label' => 'Casual', 'desc' => 'Relaxed everyday wear'],
                ['value' => 'sporty', 'label' => 'Sporty', 'desc' => 'Active and athletic'],
                ['value' => 'fashion-forward', 'label' => 'Fashion Forward', 'desc' => 'Bold and trendy'],
                ['value' => 'classic', 'label' => 'Classic', 'desc' => 'Timeless and elegant'],
            ],
        ],
        [
            'id' => 'usage',
            'question' => 'Primary use case?',
            'icon' => '🎯',
            'options' => [
                ['value' => 'office', 'label' => 'Office / Work', 'desc' => 'Daily professional use'],
                ['value' => 'outdoor', 'label' => 'Outdoor / Sun', 'desc' => 'Protection outside'],
                ['value' => 'driving', 'label' => 'Driving', 'desc' => 'Road clarity and safety'],
                ['value' => 'everyday', 'label' => 'Everyday Casual', 'desc' => 'General all-day use'],
                ['value' => 'reading', 'label' => 'Reading / Screen', 'desc' => 'Close-up work and screens'],
            ],
        ],
        [
            'id' => 'budget',
            'question' => "What's your budget?",
            'icon' => '💰',
            'options' => [
                ['value' => 'under-500', 'label' => 'Under ₹500', 'desc' => 'Budget-friendly'],
                ['value' => '500-2000', 'label' => '₹500–₹2,000', 'desc' => 'Mid-range'],
                ['value' => '2000-5000', 'label' => '₹2,000–₹5,000', 'desc' => 'Premium'],
                ['value' => 'above-5000', 'label' => 'Above ₹5,000', 'desc' => 'Luxury'],
            ],
        ],
    ];
}
