<?php

namespace App\Http\Controllers;

use App\Models\Medcyto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class MedcytoController extends Controller
{
    /**
     * แสดงรายการข้อมูลยาเคมีบำบัดทั้งหมดจากตาราง medcyto พร้อมค้นหาและสถิติ
     */
    public function index(Request $request)
    {
        $query = Medcyto::query()->orderBy('dg_code');

        if ($request->filled('search')) {
            $kw = trim($request->input('search'));
            $query->where(function ($q) use ($kw) {
                $q->where('dg_code', 'like', "%{$kw}%")
                  ->orWhere('dg_name', 'like', "%{$kw}%")
                  ->orWhere('dg_brand', 'like', "%{$kw}%")
                  ->orWhere('dg_fullname', 'like', "%{$kw}%")
                  ->orWhere('dg_precaution', 'like', "%{$kw}%");
            });
        }

        $meds = $query->get();

        // ตรวจสอบรหัสยาที่ถูกใช้งานอยู่ในสูตรยา (reg_d) หรือใบสั่งยา (order_d)
        $usedInRegimens = DB::table('reg_d')
            ->select('rd_medcode', DB::raw('COUNT(*) as total'))
            ->groupBy('rd_medcode')
            ->pluck('total', 'rd_medcode');

        $usedInOrders = DB::table('order_d')
            ->select('or_medcode', DB::raw('COUNT(*) as total'))
            ->groupBy('or_medcode')
            ->pluck('total', 'or_medcode');

        $stats = [
            'total_meds'      => Medcyto::count(),
            'with_precaution' => Medcyto::whereNotNull('dg_precaution')->where('dg_precaution', '!=', '')->count(),
            'in_regimens'     => $usedInRegimens->count(),
            'in_orders'       => $usedInOrders->count(),
        ];

        return view('medcyto', compact('meds', 'stats', 'usedInRegimens', 'usedInOrders'));
    }

    /**
     * เพิ่มข้อมูลยาเคมีบำบัดใหม่ลงในตาราง medcyto (Create)
     */
    public function store(Request $request)
    {
        $request->merge([
            'dg_code' => strtoupper(trim((string) $request->input('dg_code'))),
        ]);

        $validated = $request->validate([
            'dg_code'       => ['required', 'string', 'max:8', 'alpha_num', Rule::unique('medcyto', 'dg_code')],
            'dg_name'       => ['required', 'string', 'max:150'],
            'dg_brand'      => ['nullable', 'string', 'max:150'],
            'dg_fullname'   => ['nullable', 'string', 'max:250'],
            'dg_conc'       => ['required', 'numeric', 'min:0.01', 'max:99999'],
            'dg_vol'        => ['nullable', 'numeric', 'min:0', 'max:99999'],
            'dg_price'      => ['nullable', 'numeric', 'min:0', 'max:9999999'],
            'dg_saleprc'    => ['nullable', 'numeric', 'min:0', 'max:9999999'],
            'dg_recon'      => ['nullable', 'string', 'max:500'],
            'dg_precaution' => ['nullable', 'string', 'max:500'],
            'dg_exprecon'   => ['nullable', 'integer', 'min:0', 'max:9999'],
            'dg_expdilute'  => ['nullable', 'integer', 'min:0', 'max:9999'],
            'dg_time'       => ['nullable', 'string', 'max:50'],
        ], [
            'dg_code.required'  => 'กรุณาระบุรหัสยาเคมีบำบัด (dg_code)',
            'dg_code.unique'    => 'รหัสยาเคมีบำบัดนี้มีอยู่ในระบบแล้ว กรุณาใช้รหัสอื่น',
            'dg_code.max'       => 'รหัสยาเคมีบำบัดต้องไม่เกิน 8 ตัวอักษร',
            'dg_code.alpha_num' => 'รหัสยาเคมีบำบัดต้องเป็นตัวอักษรภาษาอังกฤษหรือตัวเลขเท่านั้น',
            'dg_name.required'  => 'กรุณาระบุชื่อสามัญทางยา (Generic Name)',
            'dg_conc.required'  => 'กรุณาระบุความเข้มข้นของยา (mg/mL)',
            'dg_conc.min'       => 'ความเข้มข้นของยาต้องมากกว่า 0 mg/mL',
        ]);

        Medcyto::create([
            'dg_code'       => $validated['dg_code'],
            'dg_name'       => $validated['dg_name'],
            'dg_brand'      => $validated['dg_brand'] ?? '',
            'dg_fullname'   => $validated['dg_fullname'] ?: ($validated['dg_name'] . ' (' . $validated['dg_conc'] . ' mg/mL)'),
            'dg_conc'       => (float) $validated['dg_conc'],
            'dg_vol'        => (float) ($validated['dg_vol'] ?? 0),
            'dg_price'      => (float) ($validated['dg_price'] ?? 0),
            'dg_saleprc'    => (float) ($validated['dg_saleprc'] ?? 0),
            'dg_recon'      => $validated['dg_recon'] ?? '',
            'dg_precaution' => $validated['dg_precaution'] ?? '',
            'dg_exprecon'   => (int) ($validated['dg_exprecon'] ?? 24),
            'dg_expdilute'  => (int) ($validated['dg_expdilute'] ?? 24),
            'dg_time'       => $validated['dg_time'] ?: 'ชั่วโมง',
        ]);

        return redirect()
            ->route('medcyto.index')
            ->with('success', "เพิ่มข้อมูลยาเคมีบำบัด [{$validated['dg_code']}] {$validated['dg_name']} เรียบร้อยแล้ว");
    }

    /**
     * แก้ไขข้อมูลยาเคมีบำบัดในตาราง medcyto (Update)
     */
    public function update(Request $request, string $dgCode)
    {
        $med = Medcyto::findOrFail($dgCode);

        $validated = $request->validate([
            'dg_name'       => ['required', 'string', 'max:150'],
            'dg_brand'      => ['nullable', 'string', 'max:150'],
            'dg_fullname'   => ['nullable', 'string', 'max:250'],
            'dg_conc'       => ['required', 'numeric', 'min:0.01', 'max:99999'],
            'dg_vol'        => ['nullable', 'numeric', 'min:0', 'max:99999'],
            'dg_price'      => ['nullable', 'numeric', 'min:0', 'max:9999999'],
            'dg_saleprc'    => ['nullable', 'numeric', 'min:0', 'max:9999999'],
            'dg_recon'      => ['nullable', 'string', 'max:500'],
            'dg_precaution' => ['nullable', 'string', 'max:500'],
            'dg_exprecon'   => ['nullable', 'integer', 'min:0', 'max:9999'],
            'dg_expdilute'  => ['nullable', 'integer', 'min:0', 'max:9999'],
            'dg_time'       => ['nullable', 'string', 'max:50'],
        ], [
            'dg_name.required' => 'กรุณาระบุชื่อสามัญทางยา (Generic Name)',
            'dg_conc.required' => 'กรุณาระบุความเข้มข้นของยา (mg/mL)',
            'dg_conc.min'      => 'ความเข้มข้นของยาต้องมากกว่า 0 mg/mL',
        ]);

        $med->update([
            'dg_name'       => $validated['dg_name'],
            'dg_brand'      => $validated['dg_brand'] ?? '',
            'dg_fullname'   => $validated['dg_fullname'] ?: ($validated['dg_name'] . ' (' . $validated['dg_conc'] . ' mg/mL)'),
            'dg_conc'       => (float) $validated['dg_conc'],
            'dg_vol'        => (float) ($validated['dg_vol'] ?? 0),
            'dg_price'      => (float) ($validated['dg_price'] ?? 0),
            'dg_saleprc'    => (float) ($validated['dg_saleprc'] ?? 0),
            'dg_recon'      => $validated['dg_recon'] ?? '',
            'dg_precaution' => $validated['dg_precaution'] ?? '',
            'dg_exprecon'   => (int) ($validated['dg_exprecon'] ?? 24),
            'dg_expdilute'  => (int) ($validated['dg_expdilute'] ?? 24),
            'dg_time'       => $validated['dg_time'] ?: 'ชั่วโมง',
        ]);

        return redirect()
            ->route('medcyto.index')
            ->with('success', "อัปเดตข้อมูลยาเคมีบำบัด [{$med->dg_code}] {$med->dg_name} เรียบร้อยแล้ว");
    }

    /**
     * ลบข้อมูลยาเคมีบำบัดออกจากตาราง medcyto (Delete) พร้อมตรวจสอบ Referential Integrity
     */
    public function destroy(string $dgCode)
    {
        $med = Medcyto::findOrFail($dgCode);

        $inRegimen = DB::table('reg_d')->where('rd_medcode', $dgCode)->exists();
        $inOrder   = DB::table('order_d')->where('or_medcode', $dgCode)->exists();
        $inDayRec  = DB::table('onc_dayrecord')->where('or_medcode', $dgCode)->exists();

        if ($inRegimen || $inOrder || $inDayRec) {
            return redirect()
                ->route('medcyto.index')
                ->with('error', "ไม่สามารถลบยา [{$med->dg_code}] {$med->dg_name} ได้ เนื่องจากรหัสยานี้ถูกอ้างอิงอยู่ในสูตรยา (Regimen) หรือประวัติใบสั่งยาแล้ว");
        }

        $med->delete();

        return redirect()
            ->route('medcyto.index')
            ->with('success', "ลบรายการยาเคมีบำบัด [{$dgCode}] {$med->dg_name} เรียบร้อยแล้ว");
    }
}
