$(document).ready(function () {
    // --- CẤU HÌNH CHUNG ---

    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    });

    // Validate register form khi được submit
    $("#register-form").submit(function (e) {
        let name = $('input[name="name"]').val().trim();
        let email = $('input[name="email"]').val().trim();
        let password = $('input[name="password"]').val();
        let confirmPassword = $('input[name="password_confirmation"]').val();

        let errorMessages = "";

        if (name.length < 3) {
            errorMessages += "Họ tên phải có ít nhất 3 ký tự.\n";
        }

        let emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            errorMessages += "Email không hợp lệ.\n";
        }

        if (password.length < 6) {
            errorMessages += "Mật khẩu phải có ít nhất 6 ký tự.\n";
        }

        if (password !== confirmPassword) {
            errorMessages +=
                "Mật khẩu và xác nhận mật khẩu không trùng khớp.\n";
        }

        if (errorMessages !== "") {
            e.preventDefault();
            toastr.error(errorMessages, "Lỗi xác thực", { timeOut: 5000 });
        }
    });

    $("#login-form").submit(function (e) {
        toastr.clear();
        let email = $('input[name="email"]').val().trim();
        let password = $('input[name="password"]').val();

        let errorMessages = "";

        let emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            errorMessages += "Email không hợp lệ.\n";
        }

        if (password.length < 6) {
            errorMessages += "Mật khẩu phải có ít nhất 6 ký tự.\n";
        }

        if (errorMessages !== "") {
            e.preventDefault();
            toastr.error(errorMessages, "Lỗi xác thực", { timeOut: 5000 });
        }
    });

    // $("#update-account").on("submit", function (e) {
    //     e.preventDefault();

    //     let formData = new FormData(this);
    //     let urlUpdate = $(this).attr("action");

    //     $.ajaxSetup({
    //         headers: {
    //             "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
    //         },
    //     });

    //     $.ajax({
    //         url: urlUpdate,
    //         type: "POST",
    //         data: formData,
    //         processData: false,
    //         contentType: false,

    //         beforeSend: function () {
    //             toastr.info("Đang cập nhật thông tin...", "Vui lòng chờ");
    //         },

    //         success: function (response) {
    //             if (response.success) {
    //                 toastr.success(response.message, "Thành công");

    //                 $("#ltn__name").val(response.user.name);
    //                 $("#ltn__phone_number").val(response.user.phone_number);
    //                 $("#ltn__address").val(response.user.address ?? "");
    //             } else {
    //                 toastr.error(
    //                     response.message || "Cập nhật thất bại",
    //                     "Lỗi",
    //                 );
    //             }
    //         },

    //         error: function (xhr) {
    //             let errors = xhr.responseJSON?.errors || {
    //                 general: ["Lỗi hệ thống"],
    //             };
    //             let msg = Object.values(errors).flat().join("<br>");
    //             toastr.error(msg, "Lỗi xác thực");
    //         },
    //     });
    // });

    // // Validate form địa chỉ
    // // thêm địa chỉ mới
    // $("#addAddressForm").on('submit', function(e) {
    //     e.preventDefault();

    //     let form = $(this);
    //     let btn = $('#btn-add-address'); // Nút submit

    //     // khóa nút lại để tránh user bấm liên tục spam
    //     let originalText = btn.text();
    //     btn.prop('disabled', true).text('Đang lưu...');

    //     $('.error-message').remove();

    //     let formData = new FormData(this);

    //     $.ajax({
    //         url: form.attr('action'),
    //         type: 'POST',
    //         data: formData,
    //         processData: false,
    //         contentType: false,

    //         headers: {
    //             'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    //         },

    //         success: function(response) {
    //             if (response.success) {
    //                 toastr.success(response.message, 'Thành công!');
    //                 $('#addAddressModal').modal('hide');
    //                 form[0].reset();

    //                 setTimeout(function() {
    //                     location.reload();
    //                 }, 1000);
    //             }
    //         },

    //         error: function(xhr) {
    //             btn.prop('disabled', false).text(originalText);

    //             if (xhr.status === 422) {
    //                 let errors = xhr.responseJSON.errors;
    //                 $.each(errors, function(key, value) {
    //                     let input = $('#' + key); // Tìm ô input bị lỗi
    //                     if (input.length) {
    //                         input.after('<p class="error-message text-danger" style="font-size: 13px; margin-top: 5px;">' + value[0] + '</p>');
    //                     }
    //                 });
    //                 toastr.error('Vui lòng kiểm tra lại thông tin.', 'Lỗi nhập liệu');
    //             } else {
    //                 toastr.error('Có lỗi xảy ra, vui lòng thử lại sau.', 'Lỗi hệ thống');
    //                 console.log(xhr.responseText);
    //             }
    //         }
    //     });
    // });

    /*********************************
     * PAGE PRODUCTS
     *********************************/

    // Hàm lấy sản phẩm theo bộ lọc
    function fetchProducts() {
        let category_id = $(".category-filter.active").data("id") || "";
        let sort_by = $("#sort-by").val();

        $.ajax({
            url: "products/filter",
            type: "GET",
            data: {
                category_id: category_id,
                sort_by: sort_by,
            },
            beforeSend: function () {
                // $("#loading-spinner").show();
                // $("#liton_product_grid").hide();
                // $("#pagination-links").hide();
            },
            success: function (response) {
                // Cập nhật lại lưới sản phẩm
                $("#liton_product_grid").html(response.products);
                // Cập nhật lại link phân trang
                $("#pagination-links").html(response.pagination);
            },
            complete: function () {
                // $("#loading-spinner").hide();
                // $("#liton_product_grid").show();
                // $("#pagination-links").show();
            },
            error: function (xhr) {
                console.error(xhr.responseText);
                alert("Có lỗi xảy ra khi lọc sản phẩm!");
            },
        });
    }

    // Khi click vào 1 danh mục

    $(".category-filter").click(function () {
        $(".category-filter").removeClass("active"); // Xóa 'active' ở tất cả các link
        $(this).addClass("active"); // Thêm 'active' chỉ cho link vừa bấm
        fetchProducts();
    });

    //Khi thay đổi sắp xếp
    $("#sort-by").change(function () {
        fetchProducts();
    });

    // Khi click vào link phân trang (AJAX)
    // $(document).on("click", "#pagination-links a", function (e) {
    //     e.preventDefault(); // Chặn load lại trang

    //     let url = $(this).attr("href");

    //     if (!url) return;

    //     $.ajax({
    //         url: url,
    //         type: "GET",
    //         beforeSend: function () {
    //             $("#liton_product_grid").css("opacity", "0.5");
    //         },
    //         success: function (response) {
    //             $("#liton_product_grid").html(response.products_html);
    //             $("#pagination-links").html(response.pagination_html);

    //             // Cuộn lên
    //             $("html, body").animate(
    //                 {
    //                     scrollTop: $("#liton_product_grid").offset().top - 150,
    //                 },
    //                 500,
    //             );
    //         },
    //         complete: function () {
    //             $("#liton_product_grid").css("opacity", "1");
    //         },
    //         error: function (xhr) {
    //             alert("Có lỗi khi chuyển trang!");
    //         },
    //     });
    // });

    /*********************************
     * PAGE DETAIL PRODUCTS
     *********************************/

    // // Xử lý click ảnh nhỏ để hiển thị ảnh lớn
    // $(document).on('click', '.ltn__shop-details-small-img .single-small-img', function() {
    //     let $thumbnail = $(this);
    //     let $largeImg = $thumbnail.closest('.ltn__shop-details-img-gallery').find('.ltn__shop-details-large-img .single-large-img a');
    //     let imgIndex = $thumbnail.index();

    //     // Ẩn tất cả ảnh lớn
    //     $largeImg.css('opacity', '0');

    //     // Hiện ảnh tương ứng
    //     $largeImg.eq(imgIndex).css('opacity', '1');
    // });

    // Xử lý click ảnh nhỏ để thay đổi ảnh lớn
    // $(document).on(
    //     "click",
    //     ".ltn__shop-details-small-img .single-small-img",
    //     function () {
    //         let $this = $(this);

    //         let newImageSrc = $this.find("img").attr("src");

    //         let $largeImgContainer = $this
    //             .closest(".ltn__shop-details-img-gallery")
    //             .find(".ltn__shop-details-large-img .single-large-img img");

    //         $largeImgContainer.attr("src", newImageSrc);

    //         $(".ltn__shop-details-small-img .single-small-img").removeClass(
    //             "active-img",
    //         ); // Xóa active cũ
    //         $this.addClass("active-img"); // Thêm active mới
    //     },
    // );

    // Xử lý nút cộng trừ số lượng
    // if (window.location.pathname !== "/cart") {
    //     $(document).on("click", ".qtybutton", function () {
    //         var $button = $(this);
    //         var $input = $button.siblings("input");
    //         var oldValue = parseInt($input.val());
    //         var maxStock = parseInt($input.data("max"));

    //         if ($button.hasClass("inc")) {
    //             if (oldValue < maxStock) {
    //                 $input.val(oldValue + 1);
    //             }
    //         } else {
    //             if (oldValue > 1) {
    //                 $input.val(oldValue - 1);
    //             }
    //         }
    //     });
    // } else {
    //     $(document).on("click", ".qtybutton", function () {
    //         let $button = $(this);
    //         let $input = $button.siblings("input");
    //         let oldValue = parseInt($input.val());
    //         let maxStock = parseInt($input.data("max"));
    //         let productId = $input.data("id");
    //         let newValue = oldValue;

    //         if ($button.hasClass("inc") && oldValue < maxStock) {
    //             newValue = oldValue + 1;
    //         } else if ($button.hasClass("dec") && oldValue > 1) {
    //             newValue = oldValue - 1;
    //         }

    //         if (newValue != oldValue) {
    //             updateCart(productId, newValue, $input);
    //         }
    //     });
    // }

    // Xử lý nút Thêm vào giỏ hàng trong trang Chi tiết sản phẩm
    // $(document).on("click", ".add-to-cart-btn", function (e) {
    //     e.preventDefault();

    //     let productId = $(this).data("id");

    //     let quantity = $(this)
    //         .closest(".ltn__product-details-menu-2")
    //         .find('input[name="qtybutton"]')
    //         .val();
    //     //let quantity = $(this).closest('li').prev().find('.cart-plus-minus-box').val();

    //     quantity = quantity ? quantity : 1;

    //     $.ajaxSetup({
    //         headers: {
    //             "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
    //         },
    //     });

    //     $.ajax({
    //         url: "/cart/add",
    //         type: "POST",
    //         data: {
    //             product_id: productId,
    //             quantity: quantity,
    //         },
    //         success: function (response) {
    //             $("#add_to_cart_modal-" + productId).modal("show");
    //             $("#quick_view_modal-" + productId).modal("hide");

    //             $("#cart_count").text(response.cart_count);
    //         },
    //         error: function (xhr) {
    //             alert("Có lỗi xảy ra với ajax addToCart in Detail!");
    //         },
    //     });
    // });

    /*********************************
     *MINI CARTs
     *********************************/

    // Xe đẩy trên cùng:

    // $(".mini-cart-icon").on("click", function (e) {
    //     $.ajax({
    //         url: "/mini-cart",
    //         type: "GET",
    //         success: function (response) {
    //             if (response.status) {
    //                 $("#ltn__utilize-cart-menu .ltn__utilize-menu-inner").html(
    //                     response.html,
    //                 );
    //                 $("#ltn__utilize-cart-menu").addClass("ltn__utilize-open");
    //             } else {
    //                 toastr.error(
    //                     "Không thể tải giỏ hàng. Vui lòng tải lại trang !",
    //                 );
    //             }
    //         },
    //     });
    // });

    // Đóng xe đẩy
    // $(document).on("click", ".ltn__utilize-close", function () {
    //     $("#ltn__utilize-cart-menu").removeClass("ltn__utilize-open");
    //     $(".ltn__utilize-overlay").hide(); //nó là class
    // });

    // Xóa sản phẩm trong mini cart
    // $(document).on("click", ".mini-cart-item-delete", function () {
    //     let productId = $(this).data("id");
    //     $.ajax({
    //         url: "/cart/remove",
    //         type: "POST",
    //         data: { product_id: productId },
    //         success: function (response) {
    //             if (response.status) {
    //                 $("#cart_count").text(response.cart_count);
    //                 $(".mini-cart-icon").click();
    //             }
    //         },
    //     });
    // });

    /*********************************
     *PAGE CARTs
     *********************************/
    //cập nhật số lượng ở giỏ hàng lớn (Đồng bộ sang giỏ bé)
    // function updateCart(productId, quantity, $input) {
    //     $.ajaxSetup({
    //         headers: {
    //             "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
    //         },
    //     });

    //     $.ajax({
    //         url: "/cart/update",
    //         type: "POST",
    //         data: {
    //             product_id: productId,
    //             quantity: quantity,
    //         },
    //         success: function (response) {
    //             // Cập nhật ô input
    //             $input.val(response.quantity);

    //             // Cập nhật thành tiền của dòng đó
    //             $input
    //                 .closest("tr")
    //                 .find(".cart-product-subtotal")
    //                 .text(response.subtotal + "VNĐ");

    //             // Cập nhật tổng tiền giỏ hàng
    //             $(".cart-total").text(response.total + "VNĐ");
    //             $(".cart-grand-total").text(response.grandTotal + "VNĐ");

    //             // Cập nhật số lượng trên ICON (Giỏ bé)
    //             $("#cart_count").text(response.cart_count);

    //             // đồng bộ sang giỏ bé (mini cart)
    //             let $miniItem = $(
    //                 '.mini-cart-item-delete[data-id="' + productId + '"]',
    //             ).closest(".mini-cart-item");
    //             if ($miniItem.length > 0) {
    //                 let priceText = $miniItem
    //                     .find(".mini-cart-quantity")
    //                     .text()
    //                     .split("x")[1]; // Lấy lại phần giá
    //                 $miniItem
    //                     .find(".mini-cart-quantity")
    //                     .text(response.quantity + " x " + priceText);
    //             }
    //         },
    //         error: function (xhr) {
    //             alert(xhr.responseJSON.error || "Lỗi cập nhật");
    //             $input.val(parseInt($input.val()) - 1);
    //         },
    //     });
    // }

    // Xóa sản phẩm ở giỏ hàng lớn
    // $(".remove-from-cart").on("click", function (e) {
    //     e.preventDefault();
    //     let button = $(this);
    //     let productId = button.data("id");
    //     let row = button.closest("tr");

    //     $.ajax({
    //         url: "/cart/remove-cart",
    //         type: "POST",
    //         data: { product_id: productId },
    //         success: function (response) {
    //             row.remove();

    //             $(".cart-total").text(response.total + "VNĐ");
    //             $(".cart-grand-total").text(response.grandTotal + "VNĐ");

    //             // Cập nhật số lượng trên icon (Giỏ bé)
    //             $("#cart_count").text(response.cart_count);

    //             //tìm nút xóa trong mini cart có cùng id và xóa cha của nó
    //             $('.mini-cart-item-delete[data-id="' + productId + '"]')
    //                 .closest(".mini-cart-item")
    //                 .remove();

    //             // Nếu xóa hết sạch thì reload trang để hiện giỏ trống
    //             if ($(".cart-product-remove").length === 0) {
    //                 location.reload();
    //             }
    //         },
    //         error: function (xhr) {
    //             alert(xhr.responseJSON.error || "Lỗi xóa sản phẩm");
    //         },
    //     });
    // });

    /*********************************
     *Phần CHECKOUT (trang thanh toán khi đã mua xong sản phẩm)
     *********************************/
    // $("#list_address").change(function () {
    //     var addressId = $(this).val();

    //     $.ajax({
    //         url: "/checkout/get-address",
    //         type: "GET",
    //         data: { address_id: addressId },
    //         success: function (response) {
    //             if (response.success) {
    //                 $('input[name="ltn__name"]').val(response.data.full_name);
    //                 $('input[name="ltn__phone"]').val(response.data.phone);
    //                 $('input[name="ltn__address"]').val(response.data.address);
    //                 $('input[name="ltn__city"]').val(response.data.city);
    //                 $('input[name="address_id"]').val(response.data.id);
    //             }
    //         },
    //         error: function (xhr) {
    //             alert(xhr.responseJSON.error || "Lỗi khi lấy địa chỉ");
    //         },
    //     });
    // });

    /*********************************
     * ĐÁNH GIÁ SẢN PHẨM
     *********************************/
    // if (window.location.pathname.startsWith("/product")) {
    //     let selectedRating = 0;

    //     // xử lý hover start (di chuột vào sao)
    //     $(".rating-start").hover(
    //         function () {
    //             let value = $(this).data("value");
    //             highlightStarts(value);
    //         },
    //         function () {
    //             highlightStarts(selectedRating);
    //         },
    //     );

    //     $(".rating-start").click(function (e) {
    //         e.preventDefault();
    //         selectedRating = $(this).data("value");
    //         $("#rating-value").val(selectedRating);
    //         highlightStarts(selectedRating);
    //     });

    //     function highlightStarts(value) {
    //         $(".rating-start i").each(function () {
    //             let starValue = $(this).parent().data("value");
    //             if (starValue <= value) {
    //                 $(this).removeClass("far").addClass("fas");
    //             } else {
    //                 $(this).removeClass("fas").addClass("far");
    //             }
    //         });
    //     }

    //     // xử lý xếp hạng gửi bằng AJAX
    //     $("#review-form").submit(function (e) {
    //         e.preventDefault();

    //         let productId = $(this).data("product-id");
    //         let rating = $("#rating-value").val();
    //         let content = $("#review-content").val();

    //         if (rating == 0) {
    //             $("#review-content").html(
    //                 '<div class="alert alert-danger">Vui lòng chọn số sao!</div>',
    //             );
    //             return;
    //         }

    //         $.ajaxSetup({
    //             headers: {
    //                 "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
    //                     "content",
    //                 ),
    //             },
    //         });

    //         $.ajax({
    //             url: "/review",
    //             type: "POST",
    //             data: {
    //                 product_id: productId,
    //                 rating: rating,
    //                 comment: content,
    //             },
    //             success: function (response) {
    //                 $("#review-content").val("");
    //                 highlightStarts(0);
    //                 selectedRating = 0;
    //                 $(".ltn__comment-reply-area").hide();
    //                 toastr.success(response.message);

    //                 loadReviews(productId);
    //             },
    //             error: function (xhr) {
    //                 alert(xhr.responseJSON.error);
    //             },
    //         });
    //     });

    //     // hàm load lại đánh giá
    //     function loadReviews(productId) {
    //         $.ajax({
    //             url: "/review/" + productId,
    //             type: "GET",

    //             success: function (response) {
    //                 $(".ltn__comment-inner").html(response);
    //             },
    //             error: function (xhr) {
    //                 alert(xhr.responseJSON.error);
    //             },
    //         });
    //     }
    // }

    /*********************************
     *PAGE CONTACT
     *********************************/
    // $("#contact-form").on("submit", function (e) {
    //     let name = $('input[name="name"]').val();
    //     let email = $('input[name="email"]').val();
    //     let phone = $('input[name="phone"]').val();
    //     let message = $('textarea[name="message"]').val();
    //     let errorMessage = "";

    //     if (name.length < 3) {
    //         errorMessage += "Họ và tên phải có ít nhất 3 ký tự.<br>";
    //     }

    //     if (phone.length < 10 || phone.length > 11) {
    //         errorMessage += "Số điện thoại phải từ 10-11 số.<br>";
    //     }

    //     let emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    //     if (!emailRegex.test(email)) {
    //         errorMessage += "Email không hợp lệ.<br>";
    //     }

    //     if (errorMessage !== "") {
    //         toastr.error(errorMessage, "Lỗi");
    //         e.preventDefault();
    //     }
    // });

    /*********************************
     *PAGE RECIPE
     *********************************/

    // 1. Mở Modal và load dữ liệu
    // $(document).on("click", ".btn-show-recipe", function (e) {
    //     e.preventDefault();
    //     let recipeId = $(this).data("id");
    //     $("#ingredient-list").html(
    //         '<div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x text-success"></i></div>',
    //     );
    //     $("#modalRecipe").modal("show");

    //     $.ajax({
    //         url: "/get-recipe-ingredients",
    //         type: "POST",
    //         data: { id: recipeId },
    //         success: function (response) {
    //             if (response.status) {
    //                 $("#recipe-title").text(
    //                     "Nguyên liệu món: " + response.recipe_name,
    //                 );
    //                 $("#ingredient-list").html(response.html);
    //             } else {
    //                 $("#ingredient-list").html(
    //                     '<div class="text-danger text-center">' +
    //                         response.message +
    //                         "</div>",
    //                 );
    //             }
    //         },
    //         error: function () {
    //             $("#ingredient-list").html(
    //                 '<div class="text-danger text-center">Lỗi tải dữ liệu.</div>',
    //             );
    //         },
    //     });
    // });

    // 2. Xử lý nút CỘNG TRỪ trong Popup
    // $(document).on("click", ".btn-qty-minus", function () {
    //     let input = $(this).next("input");
    //     let val = parseInt(input.val());
    //     if (val > 1) input.val(val - 1);
    // });

    // nút cộng
    // $(document).on("click", ".btn-qty-plus", function () {
    //     let input = $(this).prev("input");
    //     let val = parseInt(input.val());
    //     let max = parseInt(input.data("max"));
    //     if (val < max) input.val(val + 1);
    // });

    // 3. Xử lý nút MUA của từng món (Lấy đúng số lượng cạnh nó)
    // $(document).on("click", ".btn-add-recipe-item", function (e) {
    //     e.preventDefault();
    //     let btn = $(this);
    //     let id = btn.data("id");

    //     // Tìm ô input nằm cùng dòng với nút bấm này
    //     let quantity = btn
    //         .closest(".product-action-bhx")
    //         .find(".input-qty-recipe")
    //         .val();

    //     let originalHtml = btn.html();
    //     btn.html('<i class="fas fa-spinner fa-spin"></i>').prop(
    //         "disabled",
    //         true,
    //     );

    //     $.ajax({
    //         url: "/cart/add",
    //         type: "POST",
    //         data: {
    //             product_id: id,
    //             quantity: quantity,
    //         },
    //         success: function (response) {
    //             toastr.success("Đã thêm vào giỏ hàng!");
    //             $("#cart_count").text(response.cart_count);

    //             btn.html('<i class="fas fa-check"></i>').prop(
    //                 "disabled",
    //                 false,
    //             );
    //             setTimeout(() => {
    //                 btn.html(originalHtml);
    //             }, 1500);
    //         },
    //         error: function () {
    //             toastr.error("Lỗi thêm vào giỏ hàng");
    //             btn.html(originalHtml).prop("disabled", false);
    //         },
    //     });
    // });

    // 4.xử lý nút "THÊM TẤT CẢ VÀO GIỎ"
    // $(document).on("click", ".btn-add-all-recipe", function () {
    //     let items = [];

    //     $(".btn-add-recipe-item").each(function () {
    //         let id = $(this).data("id");
    //         let qty = $(this)
    //             .closest(".product-action-bhx")
    //             .find(".input-qty-recipe")
    //             .val();
    //         items.push({ product_id: id, quantity: qty });
    //     });

    //     if (items.length === 0) return;

    //     // Gửi mảng items về server
    //     let count = 0;
    //     $(this)
    //         .html('<i class="fas fa-spinner fa-spin"></i> Đang xử lý...')
    //         .prop("disabled", true);

    //     items.forEach((item) => {
    //         $.ajax({
    //             url: "/cart/add",
    //             type: "POST",
    //             data: item,
    //             success: function (res) {
    //                 count++;
    //                 if (count === items.length) {
    //                     toastr.success("Đã thêm tất cả nguyên liệu vào giỏ!");
    //                     $("#cart_count").text(res.cart_count);
    //                     setTimeout(() => {
    //                         location.reload();
    //                     }, 1000);
    //                 }
    //             },
    //         });
    //     });
    // });

    /***********
     * đổi địa chỉ tính ship
     */

    //xử lý đổi địa chỉ tính ship
    // $("#list_address").change(function () {
    //     var addressId = $(this).val();

    //     // 1. Điền thông tin vào form (Code cũ)
    //     $.ajax({
    //         url: "/checkout/get-address",
    //         type: "GET",
    //         data: { address_id: addressId },
    //         success: function (response) {
    //             if (response.success) {
    //                 $('input[name="ltn__name"]').val(response.data.full_name);
    //                 // ... điền các ô khác ...

    //                 // 2. TÍNH SHIP MỚI
    //                 calculateShipping(addressId);
    //             }
    //         },
    //     });
    // });

    // function calculateShipping(addressId) {
    //     $("#shipping-fee").html("Wait...");
    //     $.ajax({
    //         url: "/checkout/get-shipping-fee",
    //         type: "GET",
    //         data: { address_id: addressId },
    //         success: function (res) {
    //             if (res.success) {
    //                 $("#shipping-fee").text(res.fee_formatted);
    //                 $("#distance-info").text(res.distance_text);
    //                 $("#total-price-display").text(res.grand_total);
    //             }
    //         },
    //     });
    // }

    // Gọi lần đầu khi vào trang
    // let defaultId = $("#list_address").val();
    // if (defaultId) calculateShipping(defaultId);
});
