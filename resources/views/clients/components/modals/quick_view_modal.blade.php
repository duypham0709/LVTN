<!-- MODAL AREA START (Quick View Modal) -->
@php
    $isExpired = $product->expiry_date?->isBefore(today()->addDay()) ?? false;
    $canPurchase = $product->stock > 0 && $product->status === 'in_stock' && !$isExpired;
@endphp
<div class="ltn__modal-area ltn__quick-view-modal-area">
    <div class="modal fade" id="quick_view_modal-{{ $product->id }}" tabindex="-1">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="ltn__quick-view-modal-inner">
                        <div class="modal-product-item">
                            <div class="row">
                                <div class="col-lg-6 col-12">
                                    <div class="modal-product-img">
                                        <img src="{{ $product->image_url }}" alt="{{ $product->name }}">
                                    </div>
                                </div>
                                <div class="col-lg-6 col-12">
                                    <div class="modal-product-info">
                                        <div class="product-ratting">
                                            <ul>
                                                <li><a href="#"><i class="fas fa-star"></i></a></li>
                                            </ul>
                                        </div>
                                        <h3>{{ $product->name }}</h3>
                                        <div class="product-price">
                                            <span>${{ number_format($product->price, 0, '.', ',') }}đ</span>
                                            <span class="product-unit">/ {{ $product->unit_label }}</span>
                                        </div>
                                        <div class="modal-product-meta ltn__product-details-menu-1">
                                            <ul>
                                                <li>
                                                    <strong>Danh mục:</strong>
                                                    <span>
                                                        <a
                                                            href="{{ route('products.byCategory', $product->category->slug) }}">{{ $product->category->name }}</a>
                                                    </span>
                                                </li>
                                            </ul>
                                        </div>
                                        <div class="ltn__product-details-menu-2">
                                            <ul>
                                                {{-- LOGIC NÚT MUA --}}
                                                @if ($canPurchase)
                                                    <li>
                                                        <div class="cart-plus-minus">
                                                            <div class="dec qtybutton">-</div>
                                                            <input type="text" value="1" name="qtybutton"
                                                                class="cart-plus-minus-box" readonly
                                                                data-max="{{ $product->stock }}">
                                                            <div class="inc qtybutton">+</div>
                                                        </div>
                                                    </li>
                                                    <li>
                                                        <a href="javascript:void(0)"
                                                            class="theme-btn-1 btn btn-effect-1 add-to-cart-btn"
                                                            title="Thêm vào giỏ hàng" data-id="{{ $product->id }}">
                                                            <i class="fas fa-shopping-cart"></i>
                                                            <span>Thêm vào giỏ hàng</span>
                                                        </a>
                                                    </li>
                                                @else
                                                    <li>
                                                        <button class="btn theme-btn-1 btn-effect-1"
                                                            style="background-color: #ccc; cursor: not-allowed; width: 100%;"
                                                            disabled>
                                                            <span>{{ $isExpired ? 'Sản phẩm đã hết hạn' : 'Tạm Hết Hàng' }}</span>
                                                        </button>
                                                    </li>
                                                @endif
                                            </ul>
                                        </div>

                                        <hr>
                                        <div class="ltn__social-media">
                                            <ul>
                                                <li>Chia sẻ:</li>
                                                <li><a href="javascript:void(0)" title="Facebook"><i
                                                            class="fab fa-facebook-f"></i></a>
                                                </li>
                                                <li><a href="javascript:void(0)" title="Twitter"><i
                                                            class="fab fa-twitter"></i></a></li>
                                                <li><a href="javascript:void(0)" title="Linkedin"><i
                                                            class="fab fa-linkedin"></i></a>
                                                </li>
                                                <li><a href="javascript:void(0)" title="Instagram"><i
                                                            class="fab fa-instagram"></i></a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- MODAL AREA END -->
