@extends('layouts.client')

@section('title', 'Tìm kiếm')
@section('breadcrumb', 'Kết quả tìm kiếm "' . $keyword . '"')

@section('content')

    <div class="ltn__product-area ltn__product-gutter mb-120">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">

                    {{-- NGHIỆP VỤ: Kiểm tra nếu không có sản phẩm nào --}}
                    @if ($products->isEmpty())
                        <div class="text-center mt-50 mb-50">
                            <h3>Rất tiếc!</h3>
                            <p>Chúng tôi không tìm thấy sản phẩm nào phù hợp với từ khóa
                                <strong>"{{ $keyword }}"</strong>.
                            </p>
                            <a href="{{ route('home') }}" class="theme-btn-1 btn btn-effect-1">Quay lại trang chủ</a>
                        </div>
                    @else
                        {{-- Nếu có sản phẩm thì hiển thị --}}
                        <div class="tab-content">
                            <div class="tab-pane fade active show" id="liton_product_grid">
                                <div class="ltn__product-tab-content-inner ltn__product-grid-view">
                                    <div class="row">
                                        @foreach ($products as $product)
                                            @php
                                                // 1. Tính toán xem có bị hết hạn không
                                                $isExpired = false;
                                                if ($product->expiry_date) {
                                                    $isExpired = \Carbon\Carbon::now()->gt(
                                                        \Carbon\Carbon::parse($product->expiry_date),
                                                    );
                                                }
                                            @endphp

                                            <div class="col-xl-3 col-lg-4 col-sm-6 col-6">
                                                <div class="ltn__product-item ltn__product-item-3 text-center">
                                                    <div class="product-img">
                                                        <a
                                                            href="{{ route('product.detail', [
                                                                'categorySlug' => $product->category->slug,
                                                                'productSlug' => $product->slug,
                                                            ]) }}">

                                                            <img src="{{ $product->image_url }}" alt="{{ $product->name }}">
                                                        </a>

                                                        {{-- Badge hết hàng
                                                        @if ($product->stock <= 0 || $product->status == 'out_of_stock')
                                                             <div class="product-badge">
                                                                <ul>
                                                                    <li class="sale-badge">Hết hàng</li>
                                                                </ul>
                                                            </div>
                                                        @endif --}}

                                                        <div class="product-hover-action">
                                                            <ul>
                                                                <li>
                                                                    <a href="javascript:void(0)" title="Xem Nhanh"
                                                                        data-bs-toggle="modal"
                                                                        data-bs-target="#quick_view_modal-{{ $product->id }}">
                                                                        <i class="far fa-eye"></i>
                                                                    </a>
                                                                </li>

                                                                {{-- ẩn nút thêm giỏ hàng nếu hết hàng --}}
                                                                @if ($product->stock > 0 && $product->status == 'in_stock' && !$isExpired)
                                                                    <li>
                                                                        <a href="javascript:void(0)"
                                                                            title="Thêm Vào Giỏ Hàng"
                                                                            class="add-to-cart-btn"
                                                                            data-id="{{ $product->id }}">
                                                                            <i class="fas fa-shopping-cart"></i>
                                                                        </a>
                                                                    </li>
                                                                @endif
                                                            </ul>
                                                        </div>
                                                    </div>

                                                    <div class="product-info">

                                                        <div class="product-ratting">
                                                            {{-- @include('clients.components.includes.rating', [
                                                                'product' => $product,
                                                            ]) --}}
                                                        </div>
                                                        <h2 class="product-title">
                                                            <a
                                                                href="{{ route('product.detail', [
                                                                    'categorySlug' => $product->category->slug,
                                                                    'productSlug' => $product->slug,
                                                                ]) }}">{{ $product->name }}</a>
                                                        </h2>
                                                        <div class="product-price">
                                                            @if ($product->stock > 0 && $product->status == 'in_stock' && !$isExpired)
                                                                <span>{{ number_format($product->price, 0, ',', '.') }}
                                                                    VNĐ</span>
                                                            @else
                                                                <span style="color: #ff0000; font-weight: bold;">HẾT
                                                                    HÀNG</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                    {{-- Include Modals --}}
                                    @foreach ($products as $product)
                                        @include('clients.components.includes.include-modals')
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        {{-- Phân trang (Pagination) --}}
                        <div class="ltn__pagination-area text-center">

                            <div class="ltn__pagination d-flex justify-content-center">
                                {{ $products->links('pagination::bootstrap-4') }}
                            </div>
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
@endsection
