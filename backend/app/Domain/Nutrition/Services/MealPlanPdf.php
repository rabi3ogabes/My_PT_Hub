<?php

namespace App\Domain\Nutrition\Services;

use App\Domain\Nutrition\Models\MealPlan;
use Illuminate\Support\Facades\File;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;

/** Printable meal plan PDF (mPDF). Arabic is rendered RTL with automatic script/font selection. */
final class MealPlanPdf
{
    public function __construct(private readonly MealPlanService $plans) {}

    public function render(MealPlan $plan, string $locale): string
    {
        $rtl = $locale === 'ar';
        $tmp = storage_path('framework/cache/mpdf');
        File::ensureDirectoryExists($tmp);

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'tempDir' => $tmp,
            'margin_top' => 14,
            'margin_bottom' => 14,
            'margin_left' => 12,
            'margin_right' => 12,
            'default_font' => 'dejavusans',
        ]);
        $mpdf->autoScriptToLang = true;
        $mpdf->autoLangToFont = true;
        $mpdf->SetDirectionality($rtl ? 'rtl' : 'ltr');
        $mpdf->SetTitle($plan->name);
        $mpdf->SetAuthor((string) config('brand.name', config('app.name')));

        $days = [];
        foreach ($plan->days->sortBy('day_number') as $day) {
            $date = $plan->start_date
                ? $plan->start_date->copy()->addDays(((int) $day->day_number) - 1)->toDateString()
                : now()->toDateString();
            $meals = [];
            foreach ($this->plans->mealsFor($plan, $day, $date) as $row) {
                $meals[] = [
                    'name' => $row['meal']->name ?: __('nutrition.slots.'.$row['slot']),
                    'time' => $row['time'],
                    'items' => $this->plans->plannedItems($row['meal']),
                ];
            }
            $days[] = ['day' => $day, 'meals' => $meals, 'totals' => $this->plans->dayTotals($day)];
        }

        $html = view('pdf.meal-plan', [
            'plan' => $plan,
            'days' => $days,
            'rtl' => $rtl,
            'locale' => $locale,
            'grocery' => $this->plans->groceryList($plan),
        ])->render();

        $mpdf->WriteHTML($html);

        return $mpdf->Output('', Destination::STRING_RETURN);
    }
}
