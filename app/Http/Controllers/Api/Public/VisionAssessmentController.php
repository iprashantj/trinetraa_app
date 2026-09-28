<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\VisionAssessment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class VisionAssessmentController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|string|email|max:255',
            'phone' => 'nullable|string|max:20',
            'answers' => 'required|array',
            'answers.blurry_distance' => 'required|in:yes,no',
            'answers.blurry_near' => 'required|in:yes,no',
            'answers.headaches' => 'required|in:yes,no',
            'answers.night_driving' => 'required|in:yes,no',
            'answers.screen_strain' => 'required|in:yes,no',
            'answers.eye_pain' => 'required|in:yes,no',
            'answers.sudden_change' => 'required|in:yes,no',
            'answers.family_history' => 'required|in:yes,no',
            'answers.last_checkup' => 'required|in:within_6_months,6_to_12_months,1_to_2_years,over_2_years,never',
        ]);

        if ($validator->fails()) {
            return $this->message($validator->errors()->first(), 400);
        }

        $data = $validator->validated();
        $answers = $data['answers'];
        $analysis = $this->analyzeAnswers($answers);

        VisionAssessment::create([
            'name' => ($data['name'] ?? null) ?: null,
            'email' => ($data['email'] ?? null) ?: null,
            'phone' => ($data['phone'] ?? null) ?: null,
            'answers' => json_encode($answers),
            'result' => json_encode($analysis),
        ]);

        return response()->json(['analysis' => $analysis]);
    }

    private function analyzeAnswers(array $answers): array
    {
        $issues = [];
        $recommendations = [];

        if (($answers['blurry_distance'] ?? null) === 'yes') {
            $issues[] = 'Possible myopia (nearsightedness)';
            $recommendations[] = 'Consult for distance vision correction';
        }
        if (($answers['blurry_near'] ?? null) === 'yes') {
            $issues[] = 'Possible hyperopia (farsightedness) or presbyopia';
            $recommendations[] = 'Reading glasses or bifocals may help';
        }
        if (($answers['headaches'] ?? null) === 'yes') {
            $issues[] = 'Eye strain / headaches';
            $recommendations[] = 'Consider blue-light blocking lenses or anti-glare coatings';
        }
        if (($answers['night_driving'] ?? null) === 'yes') {
            $issues[] = 'Night vision difficulties';
            $recommendations[] = 'Anti-reflective coating lenses recommended';
        }
        if (($answers['screen_strain'] ?? null) === 'yes') {
            $issues[] = 'Digital eye strain';
            $recommendations[] = 'Blue-light blocking lenses and 20-20-20 rule';
        }
        if (($answers['last_checkup'] ?? null) === 'over_2_years' || ($answers['last_checkup'] ?? null) === 'never') {
            $recommendations[] = 'Overdue for an eye exam — book one soon';
        }
        if (($answers['family_history'] ?? null) === 'yes') {
            $recommendations[] = 'Regular monitoring recommended due to family history';
        }

        $urgency = 'routine';
        if (count($issues) >= 3) {
            $urgency = 'soon';
        }
        if (($answers['eye_pain'] ?? null) === 'yes' || ($answers['sudden_change'] ?? null) === 'yes') {
            $urgency = 'urgent';
        }

        return [
            'issues' => $issues,
            'recommendations' => $recommendations,
            'urgency' => $urgency,
        ];
    }
}
