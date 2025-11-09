<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\Category;
use App\Models\Department;
use App\Models\Tag;
use App\Models\Asset;
use App\Models\ChecklistTemplate;

class AssetEditViewComposer extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Provide variables for admin.assets.edit without forcing controller changes.
        View::composer('admin.assets.edit', function ($view) {
            // Only inject if the view didn't already get them from the controller.
            if (! $view->offsetExists('categories')) {
                $view->with('categories', Category::orderBy('name')->get());
            }
            if (! $view->offsetExists('departments')) {
                $view->with('departments', Department::orderBy('name')->get());
            }
            if (! $view->offsetExists('tags')) {
                $view->with('tags', Tag::orderBy('name')->get());
            }
            if (! $view->offsetExists('availableChildren')) {
                $asset = $view->offsetExists('asset') ? $view['asset'] : null;
                $children = Asset::query()
                    ->when($asset, fn ($q) => $q->where('id', '!=', $asset->id))
                    ->where('status', 'active')
                    ->where(function ($q) use ($asset) {
                        if ($asset) {
                            $q->whereNull('parent_asset_id')
                              ->orWhere('parent_asset_id', $asset->id);
                        } else {
                            $q->whereNull('parent_asset_id');
                        }
                    })
                    ->orderBy('name')
                    ->get();
                $view->with('availableChildren', $children);
            }
            if (! $view->offsetExists('checklistTemplates')) {
                $view->with('checklistTemplates', ChecklistTemplate::orderBy('name')->get());
            }
        });
    }
}
