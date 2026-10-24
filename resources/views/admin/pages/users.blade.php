@extends('layouts.admin')

@section('title', 'Quản lý người dùng')

@section('content')

    <!-- page content -->
    <div class="right_col" role="main">
        <div class="">
            <div class="page-title">
                <div class="title_left">
                    <h3>Quản lý người dùng</h3>
                </div>
            </div>

            <div class="clearfix"></div>
            <div class="x_panel">
                <div class="x_content">
                    <div class="row">
                        @foreach ($users as $user)
                            <div class="col-md-4 col-sm-4  profile_details">
                                <div class="well profile_view">
                                    <div class="col-sm-12">
                                        <h4 class="brief"><i>{{ $user->role->name }}</i></h4>
                                        <div class="left col-md-7 col-sm-7">
                                            <h2>{{ $user->name }}</h2>
                                            <p><strong>Email: </strong> {{ $user->email }}</p>
                                            <ul class="list-unstyled">
                                                <li><i class="fa fa-building"></i> Address: {{ $user->address }}</li>
                                                <li><i class="fa fa-phone"></i> Phone: {{ $user->phone_number }}</li>
                                            </ul>
                                        </div>
                                        <div class="right col-md-5 col-sm-5 text-center">
                                            <img src="{{ asset('storage/' . $user->avatar ?? 'uploads/users/default-avatar.png') }}"
                                                alt=""
                                                style="width: 50px; height: 50px; object-fit: cover; border-radius: 50%;">
                                        </div>
                                    </div>
                                    <div class=" profile-bottom text-center">
                                        <td class="text-center align-middle">
                                            @if ($user->role->name == 'customer' || $user->role->name == 'staff')
                                                <button type="button" class="btn btn-primary btn-sm upgradeStaff"
                                                    data-userid="{{ $user->id }}" title="Nhân viên">
                                                    <i class="fa fa-user"> </i>
                                                </button>
                                                @if ($user->status == 'banned')
                                                    <button type="button" class="btn btn-success btn-sm changeStatus"
                                                        data-userid="{{ $user->id }}" data-status="active"
                                                        title="Bỏ chặn">
                                                        <i class="fa fa-unlock"> </i>
                                                    </button>
                                                @else
                                                    <button type="button" class="btn btn-primary btn-warning changeStatus"
                                                        data-userid="{{ $user->id }}" data-status="banned"
                                                        title="Chặn">
                                                        <i class="fa fa-ban"> </i>
                                                    </button>
                                                @endif

                                                @if ($user->status == 'deleted')
                                                    <button type="button" class="btn btn-success btn-sm changeStatus"
                                                        data-userid="{{ $user->id }}" data-status="active"
                                                        title="Khôi phục">
                                                        <i class="fa fa-recycle"> </i>
                                                    </button>
                                                @else
                                                    <button type="button" class="btn btn-primary btn-danger changeStatus"
                                                        data-userid="{{ $user->id }}" data-status="deleted"
                                                        title="Xóa">
                                                        <i class="fa fa-remove"> </i>
                                                    </button>
                                                @endif
                                            @endif
                                        </td>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /page content -->


@endsection
