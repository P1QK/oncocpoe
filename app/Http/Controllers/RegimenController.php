<?php

namespace App\Http\Controllers;

use App\Models\Medcyto;
use App\Models\RegimenDetail;
use App\Models\RegimenHeader;
use App\Models\Route;
use App\Models\Solution;
use App\Models\Timing;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RegimenController extends Controller
{
    public function index()
    {
        $regimens = RegimenHeader::with(['details.medication', 'details.unit', 'details.solution', 'details.route', 'details.timing'])
            ->orderBy('rh_id')
            ->get();

        $meds      = Medcyto::orderBy('dg_code')->get();
        $solutions = Solution::orderBy('so_code')->get();
        $routes    = Route::orderBy('rt_code')->get();
        $timings   = Timing::orderBy('tm_code')->get();
        $units     = Unit::orderBy('un_code')->get();

        return view('regimens.index', compact('regimens', 'meds', 'solutions', 'routes', 'timings', 'units'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'rh_code'            => ['required', 'string', 'max:20'],
            'items'              => ['required', 'array', 'min:1'],
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

        DB::transaction(function () use ($validated) {
            $reg = RegimenHeader::create([
                'rh_code'    => $validated['rh_code'],
                'rh_used'    => 1,
                'rh_created' => now(),
            ]);

            foreach ($validated['items'] as $item) {
                RegimenDetail::create([
                    'rd_id'       => $reg->rh_id,
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
        });

        return redirect()->route('regimens.index')->with('success', 'บันทึกสูตรยาเคมีบำบัด (Regimen) ใหม่เรียบร้อยแล้ว');
    }

    public function destroy($rhId)
    {
        DB::transaction(function () use ($rhId) {
            DB::table('reg_d')->where('rd_id', $rhId)->delete();
            DB::table('reg_h')->where('rh_id', $rhId)->delete();
        });

        return redirect()->route('regimens.index')->with('success', 'ลบสูตรยาเคมีบำบัดเรียบร้อยแล้ว');
    }
}
