<?php

namespace App\Http\Controllers;

use App\Models\ChecklistTemplate;
use App\Models\ChecklistTemplateField;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ChecklistTemplateController extends Controller
{
    /**
     * Display a listing of the templates.
     */
    public function index()
    {
        $templates = ChecklistTemplate::withCount('fields')->get();
        return view('admin.checklists.index', compact('templates'));
    }

    /**
     * Show the form for creating a new template.
     */
    public function create()
    {
        return view('admin.checklists.create');
    }

    /**
     * Store a newly created template.
     */
    public function store(Request $request)
    {
        $validated = $request->validate(['name' => 'required|string|max:255|unique:checklist_templates']);
        $template = ChecklistTemplate::create($validated);
        return redirect()->route('checklist-templates.edit', $template->id)
                         ->with('success', 'Template created. Now add fields.');
    }

    /**
     * Show the form for editing the template and its fields.
     */
    public function edit(ChecklistTemplate $checklistTemplate)
    {
        $checklistTemplate->load('fields');
        $fieldTypes = ['numeric' => 'Numeric', 'text' => 'Text', 'pass_fail' => 'Pass/Fail', 'checkbox' => 'Checkbox'];
        return view('admin.checklists.edit', compact('checklistTemplate', 'fieldTypes'));
    }

    /**
     * Update the template's name.
     */
    public function update(Request $request, ChecklistTemplate $checklistTemplate)
    {
        $validated = $request->validate(['name' => 'required|string|max:255|unique:checklist_templates,name,' . $checklistTemplate->id]);
        $checklistTemplate->update($validated);
        return redirect()->route('checklist-templates.edit', $checklistTemplate->id)
                         ->with('success', 'Template name updated.');
    }

    /**
     * Remove the template.
     */
    public function destroy(ChecklistTemplate $checklistTemplate)
    {
        // Note: Assets linked to this will have their template ID set to null by the DB.
        $checklistTemplate->delete();
        return redirect()->route('checklist-templates.index')
                         ->with('success', 'Checklist template deleted.');
    }

    /**
     * Store a new field for a template.
     */
    public function storeField(Request $request, ChecklistTemplate $checklistTemplate)
    {
        $validated = $request->validate([
            'label' => 'required|string|max:255',
            'field_type' => ['required', Rule::in(['numeric', 'text', 'pass_fail', 'checkbox'])],
        ]);
        
        $maxOrder = $checklistTemplate->fields()->max('display_order') ?? 0;
        
        $checklistTemplate->fields()->create([
            'label' => $validated['label'],
            'field_type' => $validated['field_type'],
            'display_order' => $maxOrder + 1,
        ]);

        return redirect()->route('checklist-templates.edit', $checklistTemplate->id)
                         ->with('success', 'Field added.');
    }

    /**
     * Destroy a field from a template.
     */
    public function destroyField(ChecklistTemplateField $field)
    {
        $templateId = $field->checklist_template_id;
        $field->delete();
        return redirect()->route('checklist-templates.edit', $templateId)
                         ->with('success', 'Field deleted.');
    }
    /**
     * Move a field up in the display order.
     */
    public function moveFieldUp(ChecklistTemplate $checklistTemplate, \App\Models\ChecklistTemplateField $field)
    {
        if ($field->checklist_template_id != $checklistTemplate->id) {
            return redirect()->back()->with("error", "Invalid field.");
        }
        if ($field->display_order <= 1) {
            return redirect()->back();
        }
        $previousField = $checklistTemplate->fields()->where(display_order, $field->display_order - 1)->first();
        if ($previousField) {
            $prevOrder = $previousField->display_order;
            $previousField->display_order = $field->display_order;
            $previousField->save();

            $field->display_order = $prevOrder;
            $field->save();
        }
        return redirect()->back();
    }

    /**
     * Move a field down in the display order.
     */
    public function moveFieldDown(ChecklistTemplate $checklistTemplate, \App\Models\ChecklistTemplateField $field)
    {
        if ($field->checklist_template_id != $checklistTemplate->id) {
            return redirect()->back()->with("error", "Invalid field.");
        }
        $maxOrder = $checklistTemplate->fields()->max(display_order);
        if ($field->display_order >= $maxOrder) {
            return redirect()->back();
        }
        $nextField = $checklistTemplate->fields()->where(display_order, $field->display_order + 1)->first();
        if ($nextField) {
            $nextOrder = $nextField->display_order;
            $nextField->display_order = $field->display_order;
            $nextField->save();

            $field->display_order = $nextOrder;
            $field->save();
        }
        return redirect()->back();
    }
}
