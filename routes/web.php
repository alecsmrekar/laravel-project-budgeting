<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;


Route::get('/', function() {
    return redirect()->route('project_list');
})->middleware('auth')->name('landing');

Route::view('/', 'home')->middleware('auth')->name('home');
Route::get('/settings/manage_users', 'App\Http\Controllers\ManageUsersController')->middleware('auth')->name('manage_users');
Route::post('/settings/manage_users', 'App\Http\Controllers\ManageUsersController@submitUserForm')->middleware('auth')->name('manage_users_submit');

Route::view('/transactions', 'transactions_list')->middleware('auth')->name('transactions_list');
Route::get('/api/transactions/all2', 'App\Http\Controllers\ProviderController@read_transactions')->middleware('auth')->name('api_get_all2');

Route::get('/api/projects/all', 'App\Http\Controllers\ApiController@getAllProjects')->middleware('auth')->name('api_get_all_projects');
Route::get('/api/projects/id/{id}', 'App\Http\Controllers\ApiController@getProject')->middleware('auth')->name('api_get_project');
Route::post('/api/projects/create', 'App\Http\Controllers\ApiController@createProject')->middleware('auth')->name('api_project_create');
Route::post('/api/projects/update/{id}', 'App\Http\Controllers\ApiController@updateProject')->middleware('auth')->name('api_project_update');
Route::post('/api/projects/delete/{id}', 'App\Http\Controllers\ApiController@deleteProject')->middleware('auth')->name('api_project_delete');

Route::get('/api/links/all/{provider}/{id}', 'App\Http\Controllers\ApiController@getLinks')->middleware('auth')->name('api_get_links');
Route::post('/api/links/create', 'App\Http\Controllers\ApiController@createLink')->middleware('auth')->name('api_links_create');
Route::post('/api/links/update/{provider}/{id}', 'App\Http\Controllers\ApiController@updateLink')->middleware('auth')->name('api_link_update');
Route::post('/api/links/delete/{id}', 'App\Http\Controllers\ApiController@deleteLink')->middleware('auth')->name('api_link_delete');

Route::get('/api/actuals/project/{id}', 'App\Http\Controllers\ApiController@getProjectActuals')->middleware('auth')->name('api_get_project_actuals');
Route::get('/api/actuals/cost/{id}', 'App\Http\Controllers\ApiController@getCostActuals')->middleware('auth')->name('api_get_cost_actuals');
Route::get('/api/actuals/all', 'App\Http\Controllers\ApiController@getAllActuals')->middleware('auth')->name('api_get_all_actuals');

Route::get('/api/costs/all2/{id}', 'App\Http\Controllers\ApiController@getAllCosts2')->middleware('auth')->name('api_get_all_costs2');
Route::get('/api/projects/tree/{id}', 'App\Http\Controllers\ApiController@getTree')->middleware('auth')->name('api_get_tree');
Route::get('/api/costs/id/{id}', 'App\Http\Controllers\ApiController@getCost')->middleware('auth')->name('api_get_cost');
Route::post('/api/costs/create', 'App\Http\Controllers\ApiController@createCost')->middleware('auth')->name('api_cost_create');
Route::post('/api/costs/update/{id}', 'App\Http\Controllers\ApiController@updateCost')->middleware('auth')->name('api_cost_update');
Route::post('/api/costs/delete/{id}', 'App\Http\Controllers\ApiController@deleteCost')->middleware('auth')->name('api_cost_delete');

Route::get('/project_list', 'App\Http\Controllers\ProjectController@listView')->middleware('auth')->name('project_list');
Route::view('/project_editor/{id}', 'project_editor')->middleware('auth')->name('project_editor');

Route::view('/cashflow_check', 'cashflow_check')->middleware('auth')->name('cashflow_check');
Route::get('/create_project', 'App\Http\Controllers\ProjectController@viewProjectForm')->middleware('auth')->name('add_project_form');
Route::get('/edit_project/{id}', 'App\Http\Controllers\ProjectController@viewProjectForm')->middleware('auth')->name('edit_project_form');
Route::post('/create_project', 'App\Http\Controllers\ProjectController@formSubmit')->middleware('auth')->name('create_project_submit');
Route::put('/edit_project/{id}', 'App\Http\Controllers\ProjectController@formSubmit')->middleware('auth')->name('edit_project_submit');

Auth::routes();


Route::get('/vue/{vue_capture?}', function () {
    return view('vue.index');
})->where('vue_capture', '[\/\w\.-]*');
