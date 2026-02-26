<?php

use App\Http\Controllers\Api\FormStructureController as ApiFormStructureController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\FormBuilderController;
use App\Http\Controllers\FormController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\SubmissionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('forms.index');
});

Route::get('/departments', [DepartmentController::class, 'index'])->name('departments.index');
Route::get('/departments/create', [DepartmentController::class, 'create'])->name('departments.create');
Route::post('/departments', [DepartmentController::class, 'store'])->name('departments.store');
Route::get('/patients', [PatientController::class, 'index'])->name('patients.index');
Route::get('/patients/create', [PatientController::class, 'create'])->name('patients.create');
Route::post('/patients', [PatientController::class, 'store'])->name('patients.store');
Route::get('/patients/{patient}', [PatientController::class, 'show'])->name('patients.show');
Route::get('/patients/{patient}/submissions/{entity}/edit', [SubmissionController::class, 'edit'])->name('patients.submissions.edit');
Route::put('/patients/{patient}/submissions/{entity}', [SubmissionController::class, 'update'])->name('patients.submissions.update');
Route::get('/forms', [FormController::class, 'index'])->name('forms.index');
Route::get('/forms/build', [FormBuilderController::class, 'index'])->name('forms.build.index');
Route::get('/forms/build/create', [FormBuilderController::class, 'create'])->name('forms.build.create');
Route::post('/forms/build', [FormBuilderController::class, 'store'])->name('forms.build.store');
Route::get('/forms/build/{form}/edit', [FormBuilderController::class, 'edit'])->name('forms.build.edit');
Route::put('/forms/build/{form}', [FormBuilderController::class, 'update'])->name('forms.build.update');
Route::get('/forms/build/{form}/fields/create', [FormBuilderController::class, 'createField'])->name('forms.build.fields.create');
Route::post('/forms/build/{form}/fields', [FormBuilderController::class, 'storeField'])->name('forms.build.fields.store');
Route::get('/forms/build/{form}/fields/{field}/edit', [FormBuilderController::class, 'editField'])->name('forms.build.fields.edit');
Route::put('/forms/build/{form}/fields/{field}', [FormBuilderController::class, 'updateField'])->name('forms.build.fields.update');
Route::post('/forms/build/{form}/fields/{field}/options', [FormBuilderController::class, 'updateFieldOptions'])->name('forms.build.fields.options');
Route::post('/forms/build/{form}/fields/{field}/move-up', [FormBuilderController::class, 'moveFieldUp'])->name('forms.build.fields.moveUp');
Route::post('/forms/build/{form}/fields/{field}/move-down', [FormBuilderController::class, 'moveFieldDown'])->name('forms.build.fields.moveDown');
Route::delete('/forms/build/{form}/fields/{field}', [FormBuilderController::class, 'destroyField'])->name('forms.build.fields.destroy');
Route::get('/forms/{slug}', [FormController::class, 'show'])->name('forms.show');
Route::post('/forms/{slug}', [FormController::class, 'store'])->name('forms.store');

// API: department-wise form structure (Patient Details + dynamic form)
Route::get('/api/departments/{department}/form-structure', [ApiFormStructureController::class, 'getByDepartment'])->name('api.departments.form-structure');
