@extends('layouts.client')

@section('title', 'Sản phẩm')
@section('breadcrumb', 'Sản phẩm')

@section('content')
    <!-- PRODUCT DETAILS AREA START -->
    <div class="ltn__product-area ltn__product-gutter">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 order-lg-2 mb-120">
                    <div class="ltn__shop-options">
                        <ul>
                            <li>
                                <div class="ltn__grid-list-tab-menu ">
                                    <div class="nav">
                                        <a class="active show" data-bs-toggle="tab" href="#liton_product_grid"><i
                                                class="fas fa-th-large"></i></a>
                                    </div>
                                </div>
                            </li>
                            <li>
                                <div class="short-by text-center">
                                    <select id="sort-by" class="nice-select">

                                        <option value="default">Mặc định</option>
                                        <option value="latest">Sắp xếp theo hàng mới</option>
                                        <option value="price_asc">Sắp xếp theo giá: thấp đến cao</option>
                                        <option value="price_desc">Sắp xếp theo giá: cao đến thấp</option>

                                    </select>
                                </div>
                            </li>

                        </ul>
                    </div>
                    <div class="tab-content">
                        <div id="">
                            <div id="loader">

                            </div>
                        </div>
                        <div class="tab-pane fade active show" id="liton_product_grid">
                            @include('clients.components.products_grid', ['products' => $products]) {{-- $products từ controller trả về --}}
                        </div>
                    </div>
                    <div class="ltn__pagination-area text-center">
                        <div class="ltn__pagination">
                            <ul>
                                <li><a href="#"><i class="fas fa-angle-double-left"></i></a></li>
                                <li><a href="#">1</a></li>
                                <li class="active"><a href="#">2</a></li>
                                <li><a href="#">3</a></li>
                                <li><a href="#">...</a></li>
                                <li><a href="#">10</a></li>
                                <li><a href="#"><i class="fas fa-angle-double-right"></i></a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4  mb-120">
                    <aside class="sidebar ltn__shop-sidebar">
                        <!-- Category Widget -->
                        <div class="widget ltn__menu-widget">
                            <h4 class="ltn__widget-title ltn__widget-title-border">Danh Mục Sản Phẩm</h4>
                            <ul>
                                <!--Bộ lọc-->
                                <li>
                                    <a href="javascript:void(0)" class="category-filter active" data-id="">Tất cả
                                        <span><i class="fas fa-long-arrow-alt-right"></i></span>
                                    </a>
                                </li>

                                @foreach ($categories as $category)
                                    <li>
                                        <a href="javascript:void(0)" class="category-filter"
                                            data-id="{{ $category->id }}">{{ $category->name }} <span><i
                                                    class="fas fa-long-arrow-alt-right"></i></span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                        <!-- Price Filter Widget -->
                        <!-- <div class="widget ltn__price-filter-widget">
                                                                                                                                                                                                                                                    <h4 class="ltn__widget-title ltn__widget-title-border">Filter by price</h4>
                                                                                                                                                                                                                                                    <div class="price_filter">
                                                                                                                                                                                                                                                        <div class="price_slider_amount">
                                                                                                                                                                                                                                                            <input type="submit" value="Your range:" />
                                                                                                                                                                                                                                                            <input type="text" class="amount" name="price" placeholder="Add Your Price" />
                                                                                                                                                                                                                                                        </div>
                                                                                                                                                                                                                                                        <div class="slider-range"></div>
                                                                                                                                                                                                                                                    </div>
                                                                                                                                                                                                                                                </div> -->
                        <!-- Top Rated Product Widget -->
                        <!-- <div class="widget ltn__top-rated-product-widget">
                                                                                                                                                                                                                                                    <h4 class="ltn__widget-title ltn__widget-title-border">Top Rated Product</h4>
                                                                                                                                                                                                                                                    <ul>
                                                                                                                                                                                                                                                        <li>
                                                                                                                                                                                                                                                            <div class="top-rated-product-item clearfix">
                                                                                                                                                                                                                                                                <div class="top-rated-product-img">
                                                                                                                                                                                                                                                                    <a href="product-details.html"><img src="img/product/1.png" alt="#"></a>
                                                                                                                                                                                                                                                                </div>
                                                                                                                                                                                                                                                                <div class="top-rated-product-info">
                                                                                                                                                                                                                                                                    <div class="product-ratting">
                                                                                                                                                                                                                                                                        <ul>
                                                                                                                                                                                                                                                                            <li><a href="#"><i class="fas fa-star"></i></a></li>
                                                                                                                                                                                                                                                                            <li><a href="#"><i class="fas fa-star"></i></a></li>
                                                                                                                                                                                                                                                                            <li><a href="#"><i class="fas fa-star"></i></a></li>
                                                                                                                                                                                                                                                                            <li><a href="#"><i class="fas fa-star"></i></a></li>
                                                                                                                                                                                                                                                                            <li><a href="#"><i class="fas fa-star"></i></a></li>
                                                                                                                                                                                                                                                                        </ul>
                                                                                                                                                                                                                                                                    </div>
                                                                                                                                                                                                                                                                    <h6><a href="product-details.html">Mixel Solid Seat Cover</a></h6>
                                                                                                                                                                                                                                                                    <div class="product-price">
                                                                                                                                                                                                                                                                        <span>$49.00</span>
                                                                                                                                                                                                                                                                        <del>$65.00</del>
                                                                                                                                                                                                                                                                    </div>
                                                                                                                                                                                                                                                                </div>
                                                                                                                                                                                                                                                            </div>
                                                                                                                                                                                                                                                        </li>
                                                                                                                                                                                                                                                        <li>
                                                                                                                                                                                                                                                            <div class="top-rated-product-item clearfix">
                                                                                                                                                                                                                                                                <div class="top-rated-product-img">
                                                                                                                                                                                                                                                                    <a href="product-details.html"><img src="img/product/2.png" alt="#"></a>
                                                                                                                                                                                                                                                                </div>
                                                                                                                                                                                                                                                                <div class="top-rated-product-info">
                                                                                                                                                                                                                                                                    <div class="product-ratting">
                                                                                                                                                                                                                                                                        <ul>
                                                                                                                                                                                                                                                                            <li><a href="#"><i class="fas fa-star"></i></a></li>
                                                                                                                                                                                                                                                                            <li><a href="#"><i class="fas fa-star"></i></a></li>
                                                                                                                                                                                                                                                                            <li><a href="#"><i class="fas fa-star"></i></a></li>
                                                                                                                                                                                                                                                                            <li><a href="#"><i class="fas fa-star"></i></a></li>
                                                                                                                                                                                                                                                                            <li><a href="#"><i class="fas fa-star"></i></a></li>
                                                                                                                                                                                                                                                                        </ul>
                                                                                                                                                                                                                                                                    </div>
                                                                                                                                                                                                                                                                    <h6><a href="product-details.html">Nấm hương</a></h6>
                                                                                                                                                                                                                                                                    <div class="product-price">
                                                                                                                                                                                                                                                                        <span>$49.00</span>
                                                                                                                                                                                                                                                                        <del>$65.00</del>
                                                                                                                                                                                                                                                                    </div>
                                                                                                                                                                                                                                                                </div>
                                                                                                                                                                                                                                                            </div>
                                                                                                                                                                                                                                                        </li>
                                                                                                                                                                                                                                                        <li>
                                                                                                                                                                                                                                                            <div class="top-rated-product-item clearfix">
                                                                                                                                                                                                                                                                <div class="top-rated-product-img">
                                                                                                                                                                                                                                                                    <a href="product-details.html"><img src="img/product/3.png" alt="#"></a>
                                                                                                                                                                                                                                                                </div>
                                                                                                                                                                                                                                                                <div class="top-rated-product-info">
                                                                                                                                                                                                                                                                    <div class="product-ratting">
                                                                                                                                                                                                                                                                        <ul>
                                                                                                                                                                                                                                                                            <li><a href="#"><i class="fas fa-star"></i></a></li>
                                                                                                                                                                                                                                                                            <li><a href="#"><i class="fas fa-star"></i></a></li>
                                                                                                                                                                                                                                                                            <li><a href="#"><i class="fas fa-star"></i></a></li>
                                                                                                                                                                                                                                                                            <li><a href="#"><i class="fas fa-star-half-alt"></i></a></li>
                                                                                                                                                                                                                                                                            <li><a href="#"><i class="far fa-star"></i></a></li>
                                                                                                                                                                                                                                                                        </ul>
                                                                                                                                                                                                                                                                    </div>
                                                                                                                                                                                                                                                                    <h6><a href="product-details.html">Coil Spring Conversion</a></h6>
                                                                                                                                                                                                                                                                    <div class="product-price">
                                                                                                                                                                                                                                                                        <span>$49.00</span>
                                                                                                                                                                                                                                                                        <del>$65.00</del>
                                                                                                                                                                                                                                                                    </div>
                                                                                                                                                                                                                                                                </div>
                                                                                                                                                                                                                                                            </div>
                                                                                                                                                                                                                                                        </li>
                                                                                                                                                                                                                                                    </ul>
                                                                                                                                                                                                                                                </div> -->
                        <!-- Search Widget -->
                        {{-- <div class="widget ltn__search-widget">
                            <h4 class="ltn__widget-title ltn__widget-title-border">Tìm Kiếm</h4>
                            <form action="#">
                                <input type="text" name="search" placeholder="Nhập tên sản phẩm...">
                                <button type="submit"><i class="fas fa-search"></i></button>
                            </form>
                        </div> --}}
                        <!-- Tagcloud Widget -->
                        <!-- <div class="widget ltn__tagcloud-widget">
                                                                                                                                                                                                                                                    <h4 class="ltn__widget-title ltn__widget-title-border">Popular Tags</h4>
                                                                                                                                                                                                                                                    <ul>
                                                                                                                                                                                                                                                        <li><a href="#">Popular</a></li>
                                                                                                                                                                                                                                                        <li><a href="#">desgin</a></li>
                                                                                                                                                                                                                                                        <li><a href="#">ux</a></li>
                                                                                                                                                                                                                                                        <li><a href="#">usability</a></li>
                                                                                                                                                                                                                                                        <li><a href="#">develop</a></li>
                                                                                                                                                                                                                                                        <li><a href="#">icon</a></li>
                                                                                                                                                                                                                                                        <li><a href="#">Car</a></li>
                                                                                                                                                                                                                                                        <li><a href="#">Service</a></li>
                                                                                                                                                                                                                                                        <li><a href="#">Repairs</a></li>
                                                                                                                                                                                                                                                        <li><a href="#">Auto Parts</a></li>
                                                                                                                                                                                                                                                        <li><a href="#">Oil</a></li>
                                                                                                                                                                                                                                                        <li><a href="#">Dealer</a></li>
                                                                                                                                                                                                                                                        <li><a href="#">Oil Change</a></li>
                                                                                                                                                                                                                                                        <li><a href="#">Body Color</a></li>
                                                                                                                                                                                                                                                    </ul>
                                                                                                                                                                                                                                                </div> -->
                        <!-- Size Widget -->
                        <!-- <div class="widget ltn__tagcloud-widget ltn__size-widget">
                                                                                                                                                                                                                                                    <h4 class="ltn__widget-title ltn__widget-title-border">Product Size</h4>
                                                                                                                                                                                                                                                    <ul>
                                                                                                                                                                                                                                                        <li><a href="#">S</a></li>
                                                                                                                                                                                                                                                        <li><a href="#">M</a></li>
                                                                                                                                                                                                                                                        <li><a href="#">L</a></li>
                                                                                                                                                                                                                                                        <li><a href="#">XL</a></li>
                                                                                                                                                                                                                                                        <li><a href="#">XXL</a></li>
                                                                                                                                                                                                                                                    </ul>
                                                                                                                                                                                                                                                </div> -->
                        <!-- Color Widget -->
                        <!-- <div class="widget ltn__color-widget">
                                                                                                                                                                                                                                                    <h4 class="ltn__widget-title ltn__widget-title-border">Product Color</h4>
                                                                                                                                                                                                                                                    <ul>
                                                                                                                                                                                                                                                        <li class="black"><a href="#"></a></li>
                                                                                                                                                                                                                                                        <li class="white"><a href="#"></a></li>
                                                                                                                                                                                                                                                        <li class="red"><a href="#"></a></li>
                                                                                                                                                                                                                                                        <li class="silver"><a href="#"></a></li>
                                                                                                                                                                                                                                                        <li class="gray"><a href="#"></a></li>
                                                                                                                                                                                                                                                        <li class="maroon"><a href="#"></a></li>
                                                                                                                                                                                                                                                        <li class="yellow"><a href="#"></a></li>
                                                                                                                                                                                                                                                        <li class="olive"><a href="#"></a></li>
                                                                                                                                                                                                                                                        <li class="lime"><a href="#"></a></li>
                                                                                                                                                                                                                                                        <li class="green"><a href="#"></a></li>
                                                                                                                                                                                                                                                        <li class="aqua"><a href="#"></a></li>
                                                                                                                                                                                                                                                        <li class="teal"><a href="#"></a></li>
                                                                                                                                                                                                                                                        <li class="blue"><a href="#"></a></li>
                                                                                                                                                                                                                                                        <li class="navy"><a href="#"></a></li>
                                                                                                                                                                                                                                                        <li class="fuchsia"><a href="#"></a></li>
                                                                                                                                                                                                                                                        <li class="purple"><a href="#"></a></li>
                                                                                                                                                                                                                                                        <li class="pink"><a href="#"></a></li>
                                                                                                                                                                                                                                                        <li class="nude"><a href="#"></a></li>
                                                                                                                                                                                                                                                        <li class="orange"><a href="#"></a></li>

                                                                                                                                                                                                                                                        <li><a href="#" class="orange"></a></li>
                                                                                                                                                                                                                                                        <li><a href="#" class="orange"></a></li>
                                                                                                                                                                                                                                                    </ul>
                                                                                                                                                                                                                                                </div> -->
                        <!-- Banner Widget -->
                        <div class="widget ltn__banner-widget">
                            <a href="{{ route('products.index') }}"><img
                                    src="{{ asset('assets/clients/img/banner/banner-1.jpg') }}" alt="#"></a>
                        </div>

                    </aside>
                </div>
            </div>
        </div>
    </div>
    <!-- PRODUCT DETAILS AREA END -->
@endsection
