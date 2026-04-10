<?php

namespace  App\Repositories;

use App\Models\Slider;
use App\Services\Contracts\SliderInterface;
use Illuminate\Http\Request;
use App\DataTables\Dashboard\Admin\SliderDataTable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
class SliderRepository implements SliderInterface
{
    private function forgetHomeSlidersCache(): void
    {
        $locales = array_keys((array) config('laravellocalization.supportedLocales', []));
        if (empty($locales)) {
            $locales = (array) config('translatable.locales', []);
        }
        if (empty($locales)) {
            $locales = ['ar', 'en'];
        }
        foreach ($locales as $locale) {
            Cache::forget("home.sliders.$locale");
        }
    }

    public function index(SliderDataTable $sliderDataTable)
    {
        return $sliderDataTable->render('dashboard.admin.sliders.index', ['pageTitle' => 'الصور المتحركه']);
    }

    public function create()
    {
        return view('dashboard.admin.sliders.create', ['pageTitle' => 'إضافة صوره']);
    }

    public function store(Request $request) {
        $request->validate([
            // support uploading one image or multiple images at once
            'slider' => 'nullable|file|mimes:jpg,jpeg,png,webp|max:5120',
            'sliders' => 'nullable|array',
            'sliders.*' => 'file|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        $files = [];
        if ($request->hasFile('sliders')) {
            $files = $request->file('sliders') ?: [];
        } elseif ($request->hasFile('slider')) {
            $files = [$request->file('slider')];
        }

        if (empty($files)) {
            return back()->with('error', 'يرجى اختيار صورة واحدة على الأقل.')->withInput();
        }

        $created = 0;
        foreach ($files as $file) {
            DB::beginTransaction();
            try {
                $slider = Slider::create();
                foreach ((array) config('laravellocalization.supportedLocales', []) as $locale => $lang) {
                    $slider->translateOrNew($locale)->name = $request[$locale]['name'] ?? '';
                    $slider->translateOrNew($locale)->description = $request[$locale]['description'] ?? '';
                }
                $slider->save();
                $slider->uploadSingleMedia('slider', $file, $slider, null, 'media', true, false, 'slider');
                DB::commit();
                $created++;
            } catch (\Exception $e) {
                DB::rollBack();
                return back()->with('error', 'حدث خطأ أثناء الحفظ: ' . $e->getMessage())->withInput();
            }
        }

        $this->forgetHomeSlidersCache();
        return redirect()->route('admin.sliders.index')->with('success', 'تم حفظ الصور بنجاح! (عدد: ' . $created . ')');
    }

    public function edit(Slider $slider) {
        $slider->load('media');
        return view('dashboard.admin.sliders.edit', [
            'pageTitle' => 'تعديل الصورة: ',
            'slider' => $slider,
        ]);
    }

    public function update(Request $request, Slider $slider) {
        $request->validate([
            'slider' => 'nullable|file|mimes:jpg,jpeg,png,webp|max:5120',
        ]);
        DB::beginTransaction();
        try {
            foreach (config('laravellocalization.supportedLocales') as $locale => $lang) {
                $slider->translateOrNew($locale)->name = $request[$locale]['name'] ?? '';
                $slider->translateOrNew($locale)->description = $request[$locale]['description'] ?? '';
            }
            $slider->save();
            if ($request->hasFile('slider')) {
                $slider->updateSingleMedia('slider', $request->file('slider'), $slider, null, 'media', true, false, 'slider');
            }
            DB::commit();
            $this->forgetHomeSlidersCache();

            return redirect()->route('admin.sliders.index')->with('success', 'تم حفظ بنجاح!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'حدث خطأ أثناء التحديث: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(Slider $slider) {
        $slider->deleteExistingMedia('slider', $slider, null, 'media', true, 'slider');
        $slider->delete();
        $this->forgetHomeSlidersCache();
        return redirect()->route('admin.sliders.index')->with('success', 'تم الحذف بنجاح!');
    }
}
