<?php

namespace App\Http\Controllers;

use App\Models\Medcyto;
use App\Models\RegimenDetail;
use App\Models\RegimenHeader;
use App\Models\RegimenSupportiveMed;
use App\Models\Route;
use App\Models\Solution;
use App\Models\SupportiveMed;
use App\Models\Timing;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RegimenController extends Controller
{
    public function index()
    {
        $regimens = RegimenHeader::with([
            'details.medication',
            'details.unit',
            'details.solution',
            'details.route',
            'details.timing',
            'supportiveMeds.supportiveMed',
        ])
            ->orderBy('rh_id')
            ->get();

        $meds      = Medcyto::orderBy('dg_code')->get();
        $solutions = Solution::orderBy('so_code')->get();
        $routes    = Route::orderBy('rt_code')->get();
        $timings   = Timing::orderBy('tm_code')->get();
        $units     = Unit::orderBy('un_code')->get();

        $allSupportive = SupportiveMed::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $supportiveByCategory = [
            'pre_med_antiemetic'       => $allSupportive->where('category', 'pre_med_antiemetic')->values(),
            'pre_med_hypersensitivity' => $allSupportive->where('category', 'pre_med_hypersensitivity')->values(),
            'pre_hydration'            => $allSupportive->where('category', 'pre_hydration')->values(),
            'post_hydration'           => $allSupportive->where('category', 'post_hydration')->values(),
            'home_med'                 => $allSupportive->where('category', 'home_med')->values(),
        ];

        // เตรียม JSON สำหรับเปิด Modal แก้ไขยาสนับสนุนของสูตรยาเดิม
        $regimensSupportiveJson = $regimens->mapWithKeys(function ($reg) {
            return [
                $reg->rh_id => [
                    'rh_id'   => $reg->rh_id,
                    'rh_code' => $reg->rh_code,
                    'items'   => $reg->supportiveMeds->mapWithKeys(function ($sm) {
                        return [
                            $sm->sm_code => [
                                'override_dose'        => $sm->override_dose,
                                'override_route'       => $sm->override_route,
                                'override_instruction' => $sm->override_instruction,
                                'is_default'           => (bool) $sm->is_default,
                            ],
                        ];
                    }),
                ],
            ];
        });

        return view('regimens.index', compact(
            'regimens',
            'meds',
            'solutions',
            'routes',
            'timings',
            'units',
            'supportiveByCategory',
            'regimensSupportiveJson'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'rh_code'            => ['required', 'string', 'max:20'],
            'items'              => ['required', 'array', 'min:1'],
            'items.*.rd_orderno' => ['required', 'integer', 'min:1', 'max:99'],
            'items.*.rd_medcode' => ['required', 'string', 'max:8'],
            'items.*.rd_meddose' => ['required', 'numeric', 'min:0.01'],
            'items.*.rd_medunit' => ['required', 'string', 'max:2'],
            'items.*.rd_sol'     => ['required', 'string', 'max:8'],
            'items.*.rd_solvol'  => ['required', 'numeric', 'min:0'],
            'items.*.rd_medroute'=> ['required', 'string', 'max:8'],
            'items.*.rd_medtime' => ['required', 'string', 'max:3'],
            'items.*.rd_start'   => ['required', 'integer', 'min:1'],
            'items.*.rd_duration'=> ['required', 'integer', 'min:1'],
        ]);

        DB::transaction(function () use ($validated, $request) {
            $reg = RegimenHeader::create([
                'rh_code'    => $validated['rh_code'],
                'rh_used'    => 1,
                'rh_created' => now(),
            ]);

            $sortedItems = collect($validated['items'])
                ->sortBy(fn ($item, $idx) => (int) ($item['rd_orderno'] ?? ($idx + 1)))
                ->values();

            foreach ($sortedItems as $idx => $item) {
                RegimenDetail::create([
                    'rd_id'       => $reg->rh_id,
                    'rd_orderno'  => (int) ($item['rd_orderno'] ?? ($idx + 1)),
                    'rd_medcode'  => $item['rd_medcode'],
                    'rd_meddose'  => $item['rd_meddose'],
                    'rd_medunit'  => $item['rd_medunit'],
                    'rd_sol'      => $item['rd_sol'],
                    'rd_solvol'   => $item['rd_solvol'],
                    'rd_medroute' => $item['rd_medroute'],
                    'rd_medtime'  => $item['rd_medtime'],
                    'rd_start'    => $item['rd_start'],
                    'rd_duration' => $item['rd_duration'],
                ]);
            }

            $this->syncRegimenSupportiveMeds($reg->rh_id, $request);
        });

        return redirect()->route('regimens.index')->with('success', 'บันทึกสูตรยาเคมีบำบัด (Regimen) พร้อมลำดับการให้ยาและชุดยาสนับสนุนเรียบร้อยแล้ว');
    }

    /**
     * อัปเดตชุดยาสนับสนุนเริ่มต้น (Pre-med, Pre-hydration, Post-med MESNA, Post-hydration, Home-med) ของสูตรยาเดิม
     */
    public function updateSupportive(Request $request, $rhId)
    {
        $reg = RegimenHeader::findOrFail($rhId);

        DB::transaction(function () use ($reg, $request) {
            $this->syncRegimenSupportiveMeds($reg->rh_id, $request);
        });

        return redirect()
            ->route('regimens.index')
            ->with('success', "อัปเดตชุดยาสนับสนุน (Pre-med / Hydration / MESNA / Home-med) ของสูตร {$reg->rh_code} เรียบร้อยแล้ว");
    }

    /**
     * อัปเดตลำดับการให้ยา (rd_orderno) ของสูตรยา Regimen ที่มีอยู่แล้ว
     */
    public function updateSequence(Request $request, $rhId)
    {
        $reg = RegimenHeader::with('details')->findOrFail($rhId);

        $validated = $request->validate([
            'sequences'   => ['required', 'array'],
            'sequences.*' => ['required', 'integer', 'min:1', 'max:99'],
        ]);

        DB::transaction(function () use ($reg, $validated) {
            $existingItems = $reg->details->values();
            DB::table('reg_d')->where('rd_id', $reg->rh_id)->delete();

            foreach ($existingItems as $idx => $item) {
                $newSeq = (int) ($validated['sequences'][$idx] ?? $item->rd_orderno ?? ($idx + 1));

                RegimenDetail::create([
                    'rd_id'       => $reg->rh_id,
                    'rd_orderno'  => $newSeq,
                    'rd_medcode'  => $item->rd_medcode,
                    'rd_meddose'  => $item->rd_meddose,
                    'rd_medunit'  => $item->rd_medunit,
                    'rd_sol'      => $item->rd_sol,
                    'rd_solvol'   => $item->rd_solvol,
                    'rd_medroute' => $item->rd_medroute,
                    'rd_medtime'  => $item->rd_medtime,
                    'rd_start'    => $item->rd_start,
                    'rd_duration' => $item->rd_duration,
                ]);
            }
        });

        return redirect()->route('regimens.index')->with('success', "อัปเดตลำดับการให้ยาของสูตร {$reg->rh_code} เรียบร้อยแล้ว");
    }

    public function destroy($rhId)
    {
        DB::transaction(function () use ($rhId) {
            DB::table('regimen_supportive_meds')->where('rh_id', $rhId)->delete();
            DB::table('reg_d')->where('rd_id', $rhId)->delete();
            DB::table('reg_h')->where('rh_id', $rhId)->delete();
        });

        return redirect()->route('regimens.index')->with('success', 'ลบสูตรยาเคมีบำบัดเรียบร้อยแล้ว');
    }

    /**
     * บันทึก/ซิงค์รายการยาสนับสนุนทั้งหมดของ Regimen ลงตาราง regimen_supportive_meds
     */
    private function syncRegimenSupportiveMeds(int $rhId, Request $request): void
    {
        DB::table('regimen_supportive_meds')->where('rh_id', $rhId)->delete();

        $supportiveInputs = $request->input('supportive', []);
        if (is_array($supportiveInputs)) {
            foreach ($supportiveInputs as $smCode => $cfg) {
                if (empty($cfg['enabled'])) {
                    continue;
                }
                RegimenSupportiveMed::create([
                    'rh_id'                => $rhId,
                    'sm_code'              => (string) $smCode,
                    'override_dose'        => trim((string) ($cfg['dose'] ?? '')) ?: null,
                    'override_route'       => trim((string) ($cfg['route'] ?? '')) ?: null,
                    'override_instruction' => trim((string) ($cfg['instruction'] ?? '')) ?: null,
                    'is_default'           => true,
                ]);
            }
        }

        // บันทึกค่าตั้งต้นของ Post-medication (MESNA) หากเปิดใช้งานสำหรับสูตรนี้
        if ($request->boolean('mesna_enabled')) {
            $pct = max(20, min(200, (int) $request->input('mesna_pct', 60)));
            $hr0Mode = $request->input('mesna_hr0_mode') === 'separate' ? 'separate' : 'admix';
            $nssVol = trim((string) $request->input('mesna_nss_vol', '100 mL')) ?: '100 mL';
            $modeLabel = $hr0Mode === 'admix' ? 'ผสมลงขวดยาเคมีบำบัดเลย' : "แยกผสม NSS {$nssVol} ต่างหาก";

            RegimenSupportiveMed::create([
                'rh_id'                => $rhId,
                'sm_code'              => 'POST_MESNA',
                'override_dose'        => (string) $pct,
                'override_route'       => "{$hr0Mode}|{$nssVol}",
                'override_instruction' => "MESNA {$pct}% แบ่ง 3 ครั้ง (0-3-6 ชม.) | ชม.0: {$modeLabel}",
                'is_default'           => true,
            ]);
        }
    }
}
