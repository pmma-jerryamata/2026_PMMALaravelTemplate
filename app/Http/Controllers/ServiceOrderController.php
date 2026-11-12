<?php

namespace App\Http\Controllers;

use App\Models\ServiceCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ServiceOrderController extends Controller
{
    public function __invoke(Request $request, ServiceCategory $service)
    {
        $data = $request->validate(['direction' => ['required', Rule::in(['up', 'down'])]]);
        DB::transaction(function () use ($data, $service) {
            $ordered = ServiceCategory::orderBy('display_order')->orderBy('id')->lockForUpdate()->get();
            $index = $ordered->search(fn ($item) => $item->id === $service->id);
            $next = $index + ($data['direction'] === 'up' ? -1 : 1);
            if (! $ordered->has($next)) return;
            $items = $ordered->all(); [$items[$index], $items[$next]] = [$items[$next], $items[$index]];
            foreach ($items as $position => $item) $item->forceFill(['display_order' => $position + 1])->save();
        });
        Cache::forget('helpdesk.services');
        return redirect(route('dashboard.settings').'#services')->with('success', 'Service order updated.');
    }
}
