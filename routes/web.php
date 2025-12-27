<?php



use App\Livewire\Dashboard;
use App\Livewire\Reports\Index as ReportsIndex;
use App\Livewire\Reports\Create as ReportsCreate;
use App\Livewire\Reports\Edit as ReportsEdit;
use App\Livewire\Categories\Index as CategoriesIndex;
use App\Livewire\Categories\Create as CategoriesCreate;
use App\Livewire\Categories\Edit as CategoriesEdit;
use App\Livewire\Residents\ResidentReports;
use App\Livewire\Residents\Units;
use Illuminate\Support\Facades\Route;

Route::get('/', Dashboard::class)->name('dashboard');
Route::get('/reports', ReportsIndex::class)->name('reports.index');
Route::get('/reports/create', ReportsCreate::class)->name('reports.create');
Route::get('/reports/edit/{id}', ReportsEdit::class)->name('reports.edit');
Route::get('/categories', CategoriesIndex::class)->name('categories.index');
Route::get('/categories/create', CategoriesCreate::class)->name('categories.create');
Route::get('/categories/edit/{id}', CategoriesEdit::class)->name('categories.edit');

Route::get('/residents', Units::class)->name('residents.units');
Route::get('/resident-reports', ResidentReports::class)->name('residents.reports');
Route::get('/sms', \App\Livewire\Sms\Index::class)->name('sms.index');
Route::get('/sms/manual', \App\Livewire\Sms\Manual::class)->name('sms.manual');
Route::get('/sms/group', \App\Livewire\Sms\Group::class)->name('sms.group');


// Route های API برای حذف
Route::post('/api/reports/bulk-delete', function () {
    // این Route برای حذف گروهی استفاده می‌شود
    return response()->json(['success' => true]);
})->name('reports.bulk-delete');

Route::post('/api/categories/bulk-delete', function () {
    // این Route برای حذف گروهی استفاده می‌شود
    return response()->json(['success' => true]);
})->name('categories.bulk-delete');
