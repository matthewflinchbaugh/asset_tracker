<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Category;
use App\Models\ChecklistTemplate;
use App\Models\Department;
use App\Models\Tag;
use App\Traits\WebhookSender;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;


class AssetController extends Controller
{
    use WebhookSender;

    /**
     * Hierarchy view of assets (top-level + children).
     */
    public function index(Request $request)
    {
        $user   = Auth::user();
        $search = $request->input('search');

        $query = Asset::with(['department', 'category', 'tags', 'children'])
            ->whereIn('status', ['active', 'pending_approval'])
            ->whereNull('parent_asset_id'); // only top-level assets

        // --- SEARCH (asset, dept, tags, children) ---
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('asset_tag_id', 'LIKE', "%{$search}%")
                    ->orWhere('location', 'LIKE', "%{$search}%")
                    ->orWhere('manufacturer', 'LIKE', "%{$search}%")
                    ->orWhere('model_number', 'LIKE', "%{$search}%")
                    ->orWhere('serial_number', 'LIKE', "%{$search}%")
                    ->orWhereHas('department', function ($deptQuery) use ($search) {
                        $deptQuery->where('name', 'LIKE', "%{$search}%");
                    })
                    ->orWhereHas('tags', function ($tagQuery) use ($search) {
                        $tagQuery->where('name', 'LIKE', "%{$search}%");
                    })
                    ->orWhereHas('children', function ($childQuery) use ($search) {
                        $childQuery->where('name', 'LIKE', "%{$search}%")
                            ->orWhere('asset_tag_id', 'LIKE', "%{$search}%")
                            ->orWhere('manufacturer', 'LIKE', "%{$search}%")
                            ->orWhere('model_number', 'LIKE', "%{$search}%")
                            ->orWhere('serial_number', 'LIKE', "%{$search}%")
                            ->orWhereHas('tags', function ($tagQuery) use ($search) {
                                $tagQuery->where('name', 'LIKE', "%{$search}%");
                            })
                            ->orWhereHas('department', function ($deptQuery) use ($search) {
                                $deptQuery->where('name', 'LIKE', "%{$search}%");
                            });
                    });
            });
        }

        // --- TECHNICIAN VISIBILITY (via visibleCategories) ---
        if ($user->role === 'technician') {
            if (!$user->relationLoaded('visibleCategories')) {
                $user->load('visibleCategories');
            }

            $allowedCategoryIds = $user->visibleCategories->pluck('id');

            if (!$allowedCategoryIds->isEmpty()) {
                $query->where(function ($q) use ($allowedCategoryIds) {
                    $q->whereIn('category_id', $allowedCategoryIds)
                        ->orWhereHas('tags', function ($subQuery) use ($allowedCategoryIds) {
                            $subQuery->whereIn('categories.id', $allowedCategoryIds);
                        });
                });
            } else {
                // tech has no visible categories → show nothing
                $query->whereRaw('1 = 0');
            }
        }

        $assets = $query->orderBy('name')->get();

        return view('admin.assets.index', compact('assets', 'search'));
    }

    /**
     * Kanban view grouped by department.
     */
    public function kanban()
    {
        $user = Auth::user();

        $query = Asset::with(['department', 'category', 'tags', 'maintenanceLogs'])
            ->whereIn('status', ['active', 'pending_approval']);

        // technician visibility
        if ($user->role === 'technician') {
            if (!$user->relationLoaded('visibleCategories')) {
                $user->load('visibleCategories');
            }
            $allowedCategoryIds = $user->visibleCategories->pluck('id');

            if (!$allowedCategoryIds->isEmpty()) {
                $query->where(function ($q) use ($allowedCategoryIds) {
                    $q->whereIn('category_id', $allowedCategoryIds)
                        ->orWhereHas('tags', function ($subQuery) use ($allowedCategoryIds) {
                            $subQuery->whereIn('categories.id', $allowedCategoryIds);
                        });
                });
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        $assets = $query->orderBy('name')->get();

        $assetsByGroup = $assets->groupBy(function ($asset) {
            return $asset->department->name ?? 'Unassigned';
        });

        return view('admin.assets.kanban', compact('assetsByGroup'));
    }

    /**
     * Flat, sortable list of assets (for /assets/list).
     */
    public function list(Request $request)
    {
        $user   = Auth::user();
        $search = $request->input('search');

        // Sorting params
        $sort_by = $request->input('sort_by', 'name');
        $order   = $request->input('order', 'asc');

        $validSorts = ['asset_tag_id', 'name', 'location', 'department', 'category'];
        if (!in_array($sort_by, $validSorts, true)) {
            $sort_by = 'name';
        }
        if (!in_array($order, ['asc', 'desc'], true)) {
            $order = 'asc';
        }

        $query = Asset::with(['department', 'category', 'tags'])
            ->whereIn('status', ['active', 'pending_approval']);

        // --- SEARCH ---
        if ($search) {
            $search = trim($search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('asset_tag_id', 'LIKE', "%{$search}%")
                    ->orWhere('location', 'LIKE', "%{$search}%")
                    ->orWhere('manufacturer', 'LIKE', "%{$search}%")
                    ->orWhere('model_number', 'LIKE', "%{$search}%")
                    ->orWhere('serial_number', 'LIKE', "%{$search}%")
                    ->orWhereHas('department', function ($deptQuery) use ($search) {
                        $deptQuery->where('name', 'LIKE', "%{$search}%");
                    })
                    ->orWhereHas('tags', function ($tagQuery) use ($search) {
                        $tagQuery->where('name', 'LIKE', "%{$search}%");
                    });
            });
        }

        // --- TECHNICIAN VISIBILITY (via visibleCategories) ---
        if ($user->role === 'technician') {
            if (!$user->relationLoaded('visibleCategories')) {
                $user->load('visibleCategories');
            }
            $allowedCategoryIds = $user->visibleCategories->pluck('id');

            if (!$allowedCategoryIds->isEmpty()) {
                $query->where(function ($q) use ($allowedCategoryIds) {
                    $q->whereIn('category_id', $allowedCategoryIds)
                        ->orWhereHas('tags', function ($subQuery) use ($allowedCategoryIds) {
                            $subQuery->whereIn('categories.id', $allowedCategoryIds);
                        });
                });
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        // --- SORTING ---
        if ($sort_by === 'department') {
            $query->leftJoin('departments', 'assets.department_id', '=', 'departments.id')
                ->orderBy('departments.name', $order)
                ->select('assets.*');
        } elseif ($sort_by === 'category') {
            $query->leftJoin('categories', 'assets.category_id', '=', 'categories.id')
                ->orderBy('categories.name', $order)
                ->select('assets.*');
        } else {
            $query->orderBy($sort_by, $order);
        }

        $assets = $query->paginate(25)->withQueryString();

        return view('admin.assets.list', [
            'assets'  => $assets,
            'search'  => $search,
            'sort_by' => $sort_by,
            'order'   => $order,
        ]);
    }

    /**
     * Show the form for creating a new asset.
     */
    public function create()
    {
        $departments = Department::orderBy('name')->get();
        $tags        = Tag::orderBy('name')->get();
        $allAssets   = Asset::where('status', 'active')->orderBy('name')->get();

        return view('admin.assets.create', compact('departments', 'tags', 'allAssets'));
    }

    /**
     * Store a newly created asset.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'                     => 'required|string|max:255',
	    'is_critical_infrastructure' => 'nullable|boolean',
            'department_id'            => 'required|exists:departments,id',
            'category_id'              => 'required|exists:categories,id',
            'parent_asset_id'          => 'nullable|exists:assets,id',
            'tag_ids'                  => 'nullable|array',
            'tag_ids.*'                => 'exists:categories,id',
            'location'                 => 'nullable|string|max:255',
            'manufacturer'             => 'nullable|string|max:255',
            'model_number'             => 'nullable|string|max:255',
            'serial_number'            => 'nullable|string|max:255',
            'purchase_cost'            => 'nullable|numeric|min:0',
            'purchase_date'            => 'nullable|date',
            'warranty_expiration_date' => 'nullable|date|after_or_equal:purchase_date',
            'pm_interval_value'        => 'nullable|integer|min:1',
            'pm_interval_unit'         => 'nullable|in:days,weeks,months,years',
            'pm_procedure_notes'       => 'nullable|string',
            'commissioning_notes'      => 'nullable|string',
        ]);

        // Generate asset_tag_id from department abbreviation
        $department = Department::findOrFail($validated['department_id']);
        $prefix     = $department->abbreviation;

        $latestAsset = Asset::where('asset_tag_id', 'LIKE', $prefix . '-%')
            ->whereNotNull('asset_tag_id')
            ->orderByRaw("CAST(SUBSTRING(asset_tag_id, LENGTH('$prefix-') + 1) AS UNSIGNED) DESC")
            ->first();

        $nextIdNumber = 1;
        if ($latestAsset) {
            $lastNumber   = (int) str_replace($prefix . '-', '', $latestAsset->asset_tag_id);
            $nextIdNumber = $lastNumber + 1;
        }

        $newAssetTagId                   = $prefix . '-' . Str::padLeft($nextIdNumber, 5, '0');
        $validated['asset_tag_id']       = $newAssetTagId;
        $validated['created_by_user_id'] = Auth::id();
	$validated['status']             = 'active';
	$validated['is_critical_infrastructure'] = $request->boolean('is_critical_infrastructure');


        $asset = Asset::create($validated);
        $asset->tags()->sync($request->input('tag_ids', []));

        $this->sendWebhooks('ASSET_CREATED', $asset);

        return redirect()
            ->route('assets.index')
            ->with('success', 'Asset created successfully with ID: ' . $newAssetTagId);
    }

    /**
     * Display a single asset.
     */
    public function show(Asset $asset)
    {
        $user = Auth::user();

        // technician security: must be in their visible categories via primary category or tags
        if ($user->role === 'technician') {
            if (!$user->relationLoaded('visibleCategories')) {
                $user->load('visibleCategories');
            }
            $allowedCategoryIds = $user->visibleCategories->pluck('id');

            $hasPrimary = $allowedCategoryIds->contains($asset->category_id);
            $hasTag     = $asset->tags->pluck('id')->intersect($allowedCategoryIds)->isNotEmpty();

            if (!$hasPrimary && !$hasTag) {
                return redirect()
                    ->route('assets.index')
                    ->with('error', 'You do not have permission to view this asset.');
            }
        }

        $asset->load([
            'department',
            'category',
            'creator',
            'maintenanceLogs.user',
            'tags',
            'parent',
            'children',
	    'children.maintenanceLogs.user',
            'checklistTemplates',
        ]);

        return view('admin.assets.show', compact('asset'));
    }

    /**
     * Show the form for editing an existing asset.
     */
    public function edit(Asset $asset)
    {
        $user = Auth::user();

        // Technicians: can ONLY edit their own pending assets
        if ($user->role === 'technician') {
            if ($asset->status !== 'pending_approval' || $asset->created_by_user_id !== $user->id) {
                abort(403, 'You can only edit pending assets that you submitted for approval.');
            }
        }

        $departments = Department::orderBy('name')->get();
        $tags        = Tag::orderBy('name')->get();
        $categories  = Category::orderBy('name')->get();

        $availableChildren = Asset::where('status', 'active')
            ->where('id', '!=', $asset->id)
            ->where(function ($query) use ($asset) {
                $query->whereNull('parent_asset_id')
                    ->orWhere('parent_asset_id', $asset->id);
            })
            ->orderBy('name')
            ->get();

        $asset->load(['children', 'tags', 'checklistTemplates']);

        $assignedTagIds   = $asset->tags->pluck('id')->toArray();
        $assignedChildIds = $asset->children->pluck('id')->toArray();

        $checklistTemplates = ChecklistTemplate::orderBy('name')->get();

        // Map current assignments for the edit blade
        $currentAssignments = $asset->checklistTemplates->map(function ($template) {
            return [
                'template_id'    => $template->id,
                'component_name' => $template->pivot->component_name,
            ];
        });

        return view('admin.assets.edit', [
            'asset'              => $asset,
            'departments'        => $departments,
            'tags'               => $tags,
            'categories'         => $categories,
            'availableChildren'  => $availableChildren,
            'assignedTagIds'     => $assignedTagIds,
            'assignedChildIds'   => $assignedChildIds,
            'checklistTemplates' => $checklistTemplates,
            'currentAssignments' => $currentAssignments,
        ]);
    }

    /**
     * Update an existing asset.
     *
     * Includes:
     *  - temporarily_out_of_service
     *  - next_pm_due_date
     *  - checklist assignments
     *  - child assets
     */
    public function update(Request $request, Asset $asset)
    {
        $user = Auth::user();

        // Technicians: can ONLY update their own pending assets
        if ($user->role === 'technician') {
            if ($asset->status !== 'pending_approval' || $asset->created_by_user_id !== $user->id) {
                abort(403, 'You can only update pending assets that you submitted for approval.');
            }
        }

        $validated = $request->validate([
            'name'                     => 'required|string|max:255',
	    'is_critical_infrastructure' => 'nullable|boolean',
            'department_id'            => 'required|exists:departments,id',
            'category_id'              => 'required|exists:categories,id',
            'parent_asset_id'          => [
                'nullable',
                'exists:assets,id',
                function ($attribute, $value, $fail) use ($asset) {
                    if ($value == $asset->id) {
                        $fail('An asset cannot be linked as its own parent.');
                    }
                },
            ],
            'tag_ids'                  => 'nullable|array',
            'tag_ids.*'                => 'exists:categories,id',
            'child_asset_ids'          => 'nullable|array',
            'child_asset_ids.*'        => 'exists:assets,id',
            'location'                 => 'nullable|string|max:255',
            'manufacturer'             => 'nullable|string|max:255',
            'model_number'             => 'nullable|string|max:255',
            'serial_number'            => 'nullable|string|max:255',
            'purchase_cost'            => 'nullable|numeric|min:0',
            'purchase_date'            => 'nullable|date',
            'warranty_expiration_date' => 'nullable|date|after_or_equal:purchase_date',
            'pm_interval_value'        => 'nullable|integer|min:1',
            'pm_interval_unit'         => 'nullable|in:days,weeks,months,years',
            'next_pm_due_date'         => 'nullable|date',
            'temporarily_out_of_service' => 'nullable|boolean',
            'pm_procedure_notes'       => 'nullable|string',
            'commissioning_notes'      => 'nullable|string',
            'checklist_assignments'                  => 'nullable|array',
            'checklist_assignments.*.template_id'    => 'exists:checklist_templates,id',
            'checklist_assignments.*.component_name' => 'nullable|string|max:255',
        ]);
	$validated['is_critical_infrastructure'] = $request->boolean('is_critical_infrastructure');


        $asset->update($validated);
        $asset->tags()->sync($request->input('tag_ids', []));

        // Sync child assets
        $newChildIds     = $request->input('child_asset_ids', []);
        $currentChildIds = $asset->children->pluck('id')->toArray();
        $assetsToUnlink  = array_diff($currentChildIds, $newChildIds);

        if (!empty($assetsToUnlink)) {
            Asset::whereIn('id', $assetsToUnlink)->update(['parent_asset_id' => null]);
        }
        if (!empty($newChildIds)) {
            Asset::whereIn('id', $newChildIds)->update(['parent_asset_id' => $asset->id]);
        }

        // If the next PM due date was changed, propagate to children
        if (array_key_exists('next_pm_due_date', $validated) && method_exists($asset, 'syncNextPmDueToChildren')) {
            $asset->syncNextPmDueToChildren();
        }

        $this->sendWebhooks('ASSET_UPDATED', $asset);

        // --- SYNC CHECKLIST TEMPLATES (allow multiples of same template) ---
        $assignments = $request->input('checklist_assignments', []);

        // Wipe existing rows for this asset and rebuild from the form
        \DB::table('asset_checklist_template')
            ->where('asset_id', $asset->id)
            ->delete();

        if (is_array($assignments)) {
            $now  = now();
            $rows = [];

            foreach ($assignments as $assignment) {
                if (empty($assignment['template_id'])) {
                    continue; // skip empty rows
                }

                $rows[] = [
                    'asset_id'              => $asset->id,
                    'checklist_template_id' => (int) $assignment['template_id'],
                    'component_name'        => $assignment['component_name'] ?? null,
                    'created_at'            => $now,
                    'updated_at'            => $now,
                ];
            }

            if (!empty($rows)) {
                \DB::table('asset_checklist_template')->insert($rows);
            }
        }
        // --- END SYNC CHECKLIST TEMPLATES ---

        return redirect()
            ->route('assets.show', $asset->id)
            ->with('success', 'Asset details updated successfully.');
    }

    /**
     * Archive an asset and all of its children (status = decommissioned).
     */
    public function archive(Asset $asset)
    {
        DB::transaction(function () use ($asset) {
            $idsToArchive = [];
            $queue        = [$asset->id];

            while (!empty($queue)) {
                $id            = array_shift($queue);
                $idsToArchive[] = $id;

                $childIds = Asset::where('parent_asset_id', $id)->pluck('id')->all();
                $queue    = array_merge($queue, $childIds);
            }

            Asset::whereIn('id', $idsToArchive)->update(['status' => 'decommissioned']);
        });

        return redirect()
            ->route('assets.show', $asset->id)
            ->with('success', 'Asset and its component assets have been archived (status set to "decommissioned").');
    }

    /**
     * Export an asset tree (asset + all descendants) as JSON.
     */
    public function export(Asset $asset)
    {
        // BFS collect all asset IDs in the tree
        $idsToExport = [];
        $queue       = [$asset->id];

        while (!empty($queue)) {
            $id = array_shift($queue);

            if (in_array($id, $idsToExport, true)) {
                continue;
            }

            $idsToExport[] = $id;

            $childIds = Asset::where('parent_asset_id', $id)->pluck('id')->all();
            $queue    = array_merge($queue, $childIds);
        }

        $assets = Asset::with(['department', 'category', 'tags'])
            ->whereIn('id', $idsToExport)
            ->get();

        $payload = [
            'exported_at' => now()->toIso8601String(),
            'root_asset'  => $asset->id,
            'assets'      => [],
        ];

        foreach ($assets as $a) {
            $payload['assets'][] = [
                'source_id'        => $a->id,
                'parent_source_id' => $a->parent_asset_id,

                'asset' => [
                    'asset_tag_id'             => $a->asset_tag_id,
                    'temp_asset_tag_id'        => $a->temp_asset_tag_id,
                    'name'                     => $a->name,
                    'status'                   => $a->status,
                    'manufacturer'             => $a->manufacturer,
                    'model_number'             => $a->model_number,
                    'serial_number'            => $a->serial_number,
                    'location'                 => $a->location,
                    'documentation_link'       => $a->documentation_link,
                    'purchase_cost'            => $a->purchase_cost,
                    'purchase_date'            => $a->purchase_date,
                    'warranty_expiration_date' => $a->warranty_expiration_date,
                    'pm_interval_value'        => $a->pm_interval_value,
                    'pm_interval_unit'         => $a->pm_interval_unit,
                    'pm_procedure_notes'       => $a->pm_procedure_notes,
                    'commissioning_notes'      => $a->commissioning_notes,
                ],

                'department' => $a->department ? $a->department->name : null,
                'category'   => $a->category ? $a->category->name : null,
                'tags'       => $a->tags->pluck('name')->all(),
            ];
        }

        $filename = 'asset_'
            . ($asset->asset_tag_id ?: $asset->id)
            . '_export_'
            . now()->format('Ymd_His')
            . '.json';

        return response()
            ->json($payload)
            ->withHeaders([
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
    }

    /**
     * Show simple upload form for JSON import.
     */
    public function showImportForm()
    {
        return view('admin.assets.import');
    }

    /**
     * Import an asset tree from a JSON export.
     */
    public function import(Request $request)
    {
        $request->validate([
            'asset_file' => 'required|file|mimes:json,txt',
        ]);

        $raw  = file_get_contents($request->file('asset_file')->getRealPath());
        $json = json_decode($raw, true);

        if (!$json || !isset($json['assets']) || !is_array($json['assets'])) {
            return back()
                ->withErrors(['asset_file' => 'Uploaded file is not a valid asset export.'])
                ->withInput();
        }

        $rootNewId        = null;
        $sourceIdToNewId  = [];
        $sourceIdToParent = [];

        DB::transaction(function () use (&$rootNewId, &$sourceIdToNewId, &$sourceIdToParent, $json) {
            // First pass: create assets
            foreach ($json['assets'] as $entry) {
                $sourceId  = $entry['source_id'] ?? null;
                $parentSrc = $entry['parent_source_id'] ?? null;
                $data      = $entry['asset'] ?? [];

                $deptName = $entry['department'] ?? null;
                $catName  = $entry['category'] ?? null;
                $tagNames = $entry['tags'] ?? [];

                $departmentId = null;
                $categoryId   = null;

                if ($deptName) {
                    $department = Department::firstOrCreate(
                        ['name' => $deptName],
                        ['abbreviation' => Str::upper(Str::substr($deptName, 0, 3))]
                    );
                    $departmentId = $department->id;
                }

                if ($catName) {
                    $category   = Category::firstOrCreate(['name' => $catName]);
                    $categoryId = $category->id;
                }

                $asset = new Asset();
                $asset->fill([
                    'department_id'             => $departmentId,
                    'category_id'               => $categoryId,
                    'parent_asset_id'           => null, // set in second pass
                    'asset_tag_id'              => $data['asset_tag_id'] ?? null,
                    'temp_asset_tag_id'         => $data['temp_asset_tag_id'] ?? null,
                    'name'                      => $data['name'] ?? null,
                    'status'                    => $data['status'] ?? 'active',
                    'manufacturer'              => $data['manufacturer'] ?? null,
                    'model_number'              => $data['model_number'] ?? null,
                    'serial_number'             => $data['serial_number'] ?? null,
                    'location'                  => $data['location'] ?? null,
                    'documentation_link'        => $data['documentation_link'] ?? null,
                    'purchase_cost'             => $data['purchase_cost'] ?? null,
                    'purchase_date'             => $data['purchase_date'] ?? null,
                    'warranty_expiration_date'  => $data['warranty_expiration_date'] ?? null,
                    'pm_interval_value'         => $data['pm_interval_value'] ?? null,
                    'pm_interval_unit'          => $data['pm_interval_unit'] ?? null,
                    'pm_procedure_notes'        => $data['pm_procedure_notes'] ?? null,
                    'commissioning_notes'       => $data['commissioning_notes'] ?? null,
                ]);

                $asset->created_by_user_id = auth()->id();
                $asset->save();

                // attach tags by name
                if (!empty($tagNames) && is_array($tagNames)) {
                    $tagIds = [];
                    foreach ($tagNames as $tagName) {
                        $tag      = Tag::firstOrCreate(['name' => $tagName]);
                        $tagIds[] = $tag->id;
                    }
                    $asset->tags()->sync($tagIds);
                }

                $sourceIdToNewId[$sourceId]  = $asset->id;
                $sourceIdToParent[$sourceId] = $parentSrc;
            }

            // Second pass: update parent_asset_id
            foreach ($sourceIdToNewId as $sourceId => $newId) {
                $parentSrc   = $sourceIdToParent[$sourceId];
                $parentNewId = $parentSrc ? ($sourceIdToNewId[$parentSrc] ?? null) : null;

                if ($parentNewId) {
                    Asset::where('id', $newId)->update([
                        'parent_asset_id' => $parentNewId,
                    ]);
                }
            }

            if (!empty($json['assets'][0]['source_id'])) {
                $firstSource = $json['assets'][0]['source_id'];
                $rootNewId   = $sourceIdToNewId[$firstSource] ?? null;
            }
        });

        if (!empty($rootNewId)) {
            return redirect()
                ->route('assets.show', $rootNewId)
                ->with('success', 'Asset import completed successfully.');
        }

        return redirect()
            ->route('assets.index')
            ->with('success', 'Asset import completed successfully.');
    }

        /**
     * Show bulk editor for Critical Infrastructure flag.
     */
    public function editCriticalBulk(Request $request)
    {
        $user = Auth::user();

        // Simple role guard; adjust roles as needed
        if (!in_array($user->role, ['admin', 'manager'])) {
            abort(403, 'You do not have permission to bulk edit critical infrastructure flags.');
        }

        // You can add filters later (by department, category, etc.)
        $assets = Asset::with(['department', 'category'])
            ->orderBy('name')
            ->get();

        return view('admin.assets.bulk_critical', compact('assets'));
    }

    /**
     * Handle bulk update for Critical Infrastructure flag.
     */
    public function updateCriticalBulk(Request $request)
    {
        $user = Auth::user();

        if (!in_array($user->role, ['admin', 'manager'])) {
            abort(403, 'You do not have permission to bulk edit critical infrastructure flags.');
        }

        $validated = $request->validate([
            'asset_ids'          => 'required|array',
            'asset_ids.*'        => 'integer|exists:assets,id',
            'critical_asset_ids' => 'nullable|array',
            'critical_asset_ids.*' => 'integer|exists:assets,id',
        ]);

        $allIds       = $validated['asset_ids'];
        $criticalIds  = $validated['critical_asset_ids'] ?? [];

        DB::transaction(function () use ($allIds, $criticalIds) {
            // Mark selected assets as critical
            if (!empty($criticalIds)) {
                Asset::whereIn('id', $criticalIds)
                    ->update(['is_critical_infrastructure' => true]);
            }

            // Mark all other assets (that were on the form) as NOT critical
            $nonCriticalIds = array_diff($allIds, $criticalIds);
            if (!empty($nonCriticalIds)) {
                Asset::whereIn('id', $nonCriticalIds)
                    ->update(['is_critical_infrastructure' => false]);
            }
        });

        return redirect()
            ->route('assets.bulk_critical.edit')
            ->with('success', 'Critical Infrastructure flags updated successfully.');
    }

    /**
     * Permanently delete an asset and all of its children.
     */
    public function destroy(Asset $asset)
    {
        DB::transaction(function () use ($asset) {
            $idsToDelete = [];
            $queue       = [$asset->id];

            while (!empty($queue)) {
                $id            = array_shift($queue);
                $idsToDelete[] = $id;

                $childIds = Asset::where('parent_asset_id', $id)->pluck('id')->all();
                $queue    = array_merge($queue, $childIds);
            }

            Asset::whereIn('id', $idsToDelete)->delete();
        });

        return redirect()
            ->route('assets.index')
            ->with('success', 'Asset and all of its component assets have been deleted. Any related logs and checklists were removed automatically.');
    }
}

