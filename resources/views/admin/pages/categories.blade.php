@extends('layouts.admin')

@section('title', 'Quản lý Danh Sách Danh Mục')


@section('content')

    <!-- page content -->
    <div class="right_col" role="main">
        <div class="">
            <div class="page-title">
                <div class="title_left">
                    <h3>Danh Sách Danh Mục</h3>
                </div>
            </div>

            <div class="clearfix"></div>

            <div class="row">
                <div class="col-md-12 col-sm-12 ">
                    <div class="x_panel">
                        <div class="x_title">
                            <h2>Tất Cả Danh Mục</h2>
                            <ul class="nav navbar-right panel_toolbox">
                                <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a>
                                </li>

                                <li><a class="close-link"><i class="fa fa-close"></i></a>
                                </li>
                            </ul>
                            <div class="clearfix"></div>
                        </div>
                        <div class="x_content">
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="card-box table-responsive">
                                        <p class="text-muted font-13 m-b-30">
                                            Trang Quản Lý Danh Mục Cho Phép Admin tạo, chỉnh sửa hoặc xóa các danh mục sản
                                            phẩm.
                                        </p>
                                        <table id="datatable-buttons" class="table table-striped table-bordered"
                                            style="width:100%">
                                            <thead>
                                                <tr>
                                                    <th>Hình Ảnh</th>
                                                    <th>Tên Danh Mục</th>
                                                    <th>Số lượng SP</th>
                                                    {{-- <th>Slug</th> --}}
                                                    <th>Mô tả</th>
                                                    <th></th>
                                                    <th></th>
                                                </tr>
                                            </thead>


                                            <tbody>

                                                @foreach ($categories as $category)
                                                    <tr id="category-row-{{ $category->id }}">
                                                        <td>
                                                            <img src="{{ asset('storage/' . $category->image) }}"
                                                                alt="{{ $category->name }}" class="image-category">
                                                        </td>
                                                        <td>{{ $category->name }}</td>
                                                        {{-- <td>{{ $category->products->count() }}</td> --}}
                                                        <td>{{ $category->slug }}</td>
                                                        <td>{{ $category->description }}</td>
                                                        <td>
                                                            <a class="btn btn-app btn-update-category" data-toggle="modal"
                                                                data-target="#modalUpdate-{{ $category->id }}">
                                                                <i class="fa fa-edit"></i>Chỉnh sửa
                                                            </a>
                                                        </td>
                                                        <td>
                                                            <a class="btn btn-app btn-delete-category"
                                                                data-id="{{ $category->id }}">
                                                                <i class="fa fa-close"></i>Xóa
                                                            </a>
                                                        </td>
                                                    </tr>

                                                    <!-- Modal -->
                                                    <div class="modal fade" id="modalUpdate-{{ $category->id }}"
                                                        tabindex="-1" aria-labelledby="categoryModalLabel"
                                                        aria-hidden="true">
                                                        <div class="modal-dialog">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title" id="categoryModalLabel">Chỉnh
                                                                        sửa</h5>
                                                                    <button type="button" class="btn-close"
                                                                        data-dismiss="modal" aria-label="Close">
                                                                        <span aria-hidden="true"> &times; </span>
                                                                    </button>
                                                                </div>
                                                                <div class="modal-body">

                                                                    <form id="update-category" method="POST"
                                                                        class="form-horizontal form-label-left"
                                                                        enctype="multipart/form-data">
                                                                        @csrf

                                                                        <div class="item form-group">
                                                                            <label
                                                                                class="col-form-label col-md-3 col-sm-3 label-align"
                                                                                for="category-name">Tên Danh
                                                                                Mục
                                                                                <span class="required">*</span>
                                                                            </label>
                                                                            <div class="col-md-6 col-sm-6 ">
                                                                                <input type="text" id="category-name"
                                                                                    name="name" required="required"
                                                                                    class="form-control"
                                                                                    value="{{ $category->name }}">
                                                                            </div>
                                                                        </div>
                                                                        <div class="item form-group">
                                                                            <label
                                                                                class="col-form-label col-md-3 col-sm-3 label-align"
                                                                                for="category-description">Mô Tả
                                                                                <span class="required">*</span>
                                                                            </label>
                                                                            <div class="col-md-6 col-sm-6 ">
                                                                                <input type="text"
                                                                                    id="category-description"
                                                                                    name="description" required="required"
                                                                                    class="form-control"
                                                                                    value="{{ $category->description }}">
                                                                            </div>
                                                                        </div>

                                                                        <div class="item form-group">

                                                                            <label
                                                                                class="col-form-label col-md-3 col-sm-3 label-align"
                                                                                for="category-image">Hình
                                                                                ảnh</label>
                                                                            <div class="col-md-6 col-sm-6 ">

                                                                                <img src="{{ asset('storage/' . $category->image) }}"
                                                                                    alt="{{ $category->name }}"
                                                                                    id="image-preview"
                                                                                    class="image-preview">

                                                                                <label class="custom-file-upload"
                                                                                    for="category-image-{{ $category->id }}">
                                                                                    Chọn ảnh </label>
                                                                                <input type="file" name="image"
                                                                                    class="category-image"
                                                                                    id="category-image-{{ $category->id }}"
                                                                                    data-id="{{ $category->id }}"
                                                                                    accept="image/*">



                                                                            </div>
                                                                        </div>




                                                                    </form>

                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary"
                                                                        data-dismiss="modal">Quay lại</button>
                                                                    <button type="button"
                                                                        class="btn btn-primary btn-update-submit-category"
                                                                        data-id="{{ $category->id }}">Chỉnh sửa</button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach

                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <!-- /page content -->
        </div>
    </div>

@endsection
