@extends('layouts.app')
@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    @if(Route::currentRouteName() === 'edit_project_form')
                        <div class="card-header">{{ 'Edit Project ' . $project->name }}</div>
                    @else
                        <div class="card-header">{{ 'Add New Project' }}</div>
                    @endif
                    <div class="card-body">
                        <div>

                            @if($project)
                                <form action="{{ route('edit_project_submit', ['id' => $project->id]) }}" method="post">
                                    @method('PUT')
                                    @else
                                        <form action="{{route('create_project_submit')}}" method="post">
                                            @endif
                                            @csrf

                                            @if(Route::currentRouteName() === 'edit_project_form' and $project)
                                                <label for="fname">Project ID: {{ $project->id }}</label><br>
                                            @endif

                                            <label for="fname">Project name:</label><br>
                                            @if($project)
                                                <input type="text" id="name" name="name" value="{{ $project->name }}"
                                                       required><br>
                                            @else
                                                <input type="text" id="name" name="name" value="{{ old('name') }}"
                                                       required><br>
                                            @endif

                                            <label for="fname">Client:</label><br>
                                            @if($project)
                                                <input type="text" id="client" name="client"
                                                       value="{{ $project->client }}"
                                                       required><br>
                                            @else
                                                <input type="text" id="client" name="client" value="{{ old('client') }}"
                                                       required>
                                                <br>
                                            @endif

                                            <label for="fname">Set project to be active:</label><br>
                                            @if($project)
                                                <input type="checkbox" id="active"
                                                       name="active" {{  ($project->active) ? 'checked' : '' }}><br>
                                            @else
                                                <input type="checkbox" id="active" name="active" checked>
                                            @endif


                                            <button type="submit" class="btn btn-primary">Save</button>
                                        </form>

                                        @if(count($errors))
                                            <div class="form-group">
                                                <div class="alert alert-danger">
                                                    <ul>
                                                        @foreach($errors->all() as $error)
                                                            <li>{{$error}}</li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            </div>
                                        @endif

                                </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
@endsection
