@extends('layouts.app')

@section('content')



    <div class="container" id="manage_users_page">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">{{ __('Manage Users') }}</div>



                    <div class="card-body">
                        @if (session('status'))
                            <div class="alert alert-success" role="alert">
                                {{ session('status') }}
                            </div>
                        @endif


                        <div>Your current role is: <span style="font-style: italic"> {{ Auth::user()->role }}</span>.
                            You can't change your own role.</div><br>

                        <div><form action="{{route('manage_users_submit')}}" method="post">
                            @csrf
                            <div class="table table-striped">
                                <div class="tr">
                                    <div class="td"><strong>Name</strong></div>
                                    <div class="td"><strong>Email</strong></div>
                                    <div class="td"><strong>Role</strong></div>
                                </div>
                                @foreach($users as $user)
                                    <div class="tr">
                                        <div class="td" id="name_field">{{$user->name}}</div>
                                        <div class="td" id="email_field">{{$user->email}}</div>
                                        <select class="custom-select" name="{{$user->id}}" id="role">
                                            @foreach($roles as $role)
                                                <option value="{{ $role }}" {{ $role == $user->role ? 'selected' : '' }}>
                                                    {{ $role }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endforeach
                            </div>



                            <button type="submit" class="btn btn-primary">Save</button>
                            </form></div>

                            @if (isset($submitted) and isset($update_msg) and $update_msg != '')
                                <br><div class="alert alert-success" role="alert">
                                    {{$update_msg}}
                                </div>
                            @endif


                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
