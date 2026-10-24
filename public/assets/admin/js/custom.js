$(document).ready(function(){

    /*********************************
     * MANAGEMENT USERS
     *********************************/
    $(document).on('click', '.upgradeStaff', function(e){
        let button = $(this);
        let userId = button.data('userid');

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $.ajax({
            type: "POST",
            url: '/admin/user/upgrade',
            data: {
                user_id : userId,
            },
            success: function (response) {
                if(response.status){
                    toastr.success(response.message);
                    button.closest('.profile_view').find('.brief i').text('STAFF');
                    button.closest('.profile_view').find('.changeStatus').hide();
                    button.hide();
                }else{
                    toastr.error(response.message);
                }
            },
            error: function (xhr, status, error) {
                alert('Có lỗi xảy ra: ' + error);
            },
        });



    });



    // Xử lý nút thay đổi trạng thái (Xóa/Chặn/Khôi phục)
    $(document).on('click', '.changeStatus', function(e){
        e.preventDefault(); // Ngăn load lại trang

        let button = $(this);
        let userId = button.data('userid');
        let status = button.data('status'); // Lấy trạng thái muốn đổi thành

        // Hỏi xác nhận nếu là hành động xóa
        if(status == 'delete'){
            if(!confirm('Bạn có chắc chắn muốn xóa người này không?')) return;
        }

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $.ajax({
            type: "POST",
            url: '/admin/user/change-status', // URL trùng với Route ở Bước 2
            data: {
                user_id : userId,
                status: status
            },
            success: function (response) {
                if(response.status){
                    toastr.success(response.message);
                    // Reload trang để cập nhật lại giao diện nút bấm
                    setTimeout(function(){
                        location.reload();
                    }, 1000);
                }else{
                    toastr.error(response.message);
                }
            },
            error: function (xhr, status, error) {
                alert('Có lỗi xảy ra: ' + error);
            },
        });
    });


    $('.btn_reset').on('click', function() {
        let form = $(this).closest('form');
        form.trigger('reset');
        form.find('input[type="file"]').val('');
        form.find('#image-preview').html('');
        form.find('#image-preview').attr('src', '');


        form.find('#image-preview-container').html('');

    });


    // Xử lý thêm nhân viên mới
    $('#addStaffForm').on('submit', function(e){
        e.preventDefault();

        let form = $(this);
        let btn = $('#btnSaveStaff');
        let originalText = btn.text();

        // 1. Reset trạng thái lỗi cũ (Xóa đỏ, xóa chữ)
        $('span.error-text').text('');
        $('input.form-control').removeClass('is-invalid');

        btn.prop('disabled', true).text('Đang lưu...');

        $.ajax({
            type: "POST",
            url: form.attr('action'),
            data: form.serialize(),
            success: function (response) {
                if(response.status){
                    toastr.success(response.message); // Hiện thông báo thành công
                    $('#addStaffModal').modal('hide'); // Tắt modal
                    form[0].reset(); // Xóa trắng form

                    // Reload lại trang sau 1s để thấy nhân viên mới
                    setTimeout(function(){
                        location.reload();
                    }, 1000);
                }
            },
            error: function (xhr) {
                btn.prop('disabled', false).text(originalText);

                // 2. Xử lý lỗi 422 (Lỗi Validate từ Laravel trả về)
                if(xhr.status === 422){
                    let errors = xhr.responseJSON.errors;

                    // Duyệt qua từng lỗi và gán vào ô input tương ứng
                    $.each(errors, function(key, value){
                        // key = tên field (vd: email, phone_number)
                        // value[0] = nội dung lỗi (vd: Email đã tồn tại)

                        // Tìm thẻ input có name="key" và thêm class đỏ
                        $('input[name="'+key+'"]').addClass('is-invalid');

                        // Tìm thẻ span có class="key_error" và điền chữ lỗi vào
                        $('span.'+key+'_error').text(value[0]);
                    });

                    // toastr.error('Vui lòng kiểm tra lại thông tin nhập vào!');
                }else{
                    toastr.error('Lỗi hệ thống, vui lòng thử lại.');
                }
            }
        });
    });





    /*********************************
     * MANAGEMENT CATEGORY
     *********************************/


    $("#category-image").change(function () {
        let file = this.files[0];
        if (file) {
            let reader = new FileReader();
            reader.onload = function(e) {
                $("#image-preview").attr("src", e.target.result);
            }
            reader.readAsDataURL(file);
        } else {
            $("#image-preview").attr("src", "");


        }
    });


    $(".category-image").change(function () {
        let file = this.files[0];
        let categoryId = $(this).data("id");
        if (file) {
            let reader = new FileReader();
            reader.onload = function (e) {
                $(".image-preview").each(function () {
                    if (
                        $(this).closest(".modal").attr("id") ===
                        "modalUpdate-" + categoryId
                    ) {
                        $(this).attr("src", e.target.result);
                    }
                });
            };
            reader.readAsDataURL(file);
        } else {
            $("#image-preview").attr("src", "");
        }
    });



    //update category
    $(document).on("click", ".btn-update-submit-category", function(e){
        e.preventDefault();
        let button = $(this);
        let categoryId = button.data("id");
        let form = button.closest(".modal").find("form");
        let formData = new FormData(form[0]);

        formData.append("category_id", categoryId);
        $.ajaxSetup({
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
        });

        $.ajax({
            url: "categories/update",
            type: "POST",
            data: formData,
            contentType: false,
            processData: false,
            beforeSend: function () {
                button.prop("disabled", true);
                button.text("Đang cập nhật...");
            },
            success: function (response) {
                if (response.status) {
                    toastr.success(response.message);
                    let categoryId = response.data.id;

                    // Regenerate new HTML for updated now
                    //Regenerate new HTML for updated row
                    let newRow = `
                        <tr id="category-row-${categoryId}">
                            <td>
                                <img src="${response.data.image}" alt="${response.data.name}"  width="80px">
                            </td>

                            <td>${response.data.name}</td>
                            <td>${response.data.slug}</td>
                            <td>${response.data.description}</td>

                            <td>
                                 <a class="btn btn-app btn-update-category" data-toggle="modal"
                                    data-target="#modalUpdate-${categoryId}">
                                        <i class="fa fa-edit"></i>Chỉnh sửa
                                 </a>

                            </td>

                            <td>
                                <a class="btn btn-app btn-delete-category" data-id="${categoryId}">
                                    <i class="fa fa-trash"></i>Xóa
                                </a>

                            </td>


                        </tr>`;

                        // thay thế hàng cũ bằng hàng mới
                        $('#category-row-' + categoryId).replaceWith(newRow);
                        $('#modalUpdate-' + categoryId).modal('hide');

                } else {
                    toastr.error(response.message);
                }
            },
            error: function (xhr, status, error) {
                alert("Có lỗi xảy ra: " + error);
            },
            complete: function () {
                button.prop("disabled", false);
                button.text("Chỉnh sửa");
            },
        });


    });



    //delete category
    $(document).on("click", ".btn-delete-category", function(e){
        e.preventDefault();
        let button = $(this);
        let categoryId = button.data("id");
        let row = button.closest("tr");

        if(!confirm('Bạn có chắc chắn muốn xóa danh mục này không?')) return;

        $.ajaxSetup({
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
        });

        $.ajax({
            url: "categories/delete",
            type: "POST",
            data: {
                category_id: categoryId,
            },
            success: function (response) {
                if (response.status) {
                    toastr.success(response.message);
                    // Xóa
                    row.remove();
                } else {
                    toastr.error(response.message);
                }
            },
            error: function (xhr, status, error) {
                alert("Có lỗi xảy ra: " + error);
            },
        });
    });





    /*********************************
     * MANAGEMENT - Products
     *********************************/
    // lay hinh
    $("#product-images").change(function (e) {
        let files = e.target.files;
        console.log(files);
        let previewContainer = $("#image-preview-container");
        previewContainer.empty();

        if (files.length > 0) {
            for (let i = 0; i < files.length; i++) {
                let file = files[i];
                if (file) {
                    let reader = new FileReader();
                    reader.onload = function (e) {
                        let img = $("<img>")
                            .attr("src", e.target.result)
                            .addClass("image-preview");
                            img.css({
                                "max-width": "150px",
                                "max-height": "150px",
                                "margin": "5px",
                                "border-radius": "5px",
                            })
                        previewContainer.append(img);
                    };
                    reader.readAsDataURL(file);
                }
            }
        } else {
            previewContainer.html("");
        }
    });




    $(".product-images").change(function (e) {
        let files = e.target.files;
        let productId = $(this).data("id");

        let previewContainer = $("#image-preview-container-" + productId);
        previewContainer.empty();

        if (files.length > 0) {
            for (let i = 0; i < files.length; i++) {
                let file = files[i];
                if (file) {
                    let reader = new FileReader();
                    reader.onload = function (e) {
                        let img = $("<img>")
                            .attr("src", e.target.result)
                            .addClass("image-preview");
                            img.css({
                                "max-width": "150px",
                                "max-height": "150px",
                                "margin": "5px",
                                "border-radius": "5px",
                            })
                        previewContainer.append(img);
                    };
                    reader.readAsDataURL(file);
                }
            }
        } else {
            previewContainer.html("");
        }
    });




    //update product
    $(document).on("click", ".btn-update-submit-product", function(e){
        e.preventDefault();
        let button = $(this);
        let productId = button.data("id");
        let form = button.closest(".modal").find("form");
        let formData = new FormData(form[0]);

        formData.append("id", productId);
        $.ajaxSetup({
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
        });

        $.ajax({
            url: "product/update",
            type: "POST",
            data: formData,
            contentType: false,
            processData: false,
            beforeSend: function () {
                button.prop("disabled", true);
                button.text("Đang cập nhật...");
            },
            success: function (response) {
                if (response.status) {


                    let product = response.data;

                    let productId = product.id;

                    // let imageSrc = product.images.length > 0 ? product.images[0].url : "storage/products/default-product.png";

                    // Dùng image_url từ Controller trả về
                    let imageSrc = product.image_url;

                    // Regenerate new HTML for updated now
                    //Regenerate new HTML for updated row
                    let newRow = `
                        <tr id="product-row-${productId}">
                            <td>
                                <img src="${imageSrc}" alt="${product.name}" class="image-product" width="80px">
                            </td>
                            <td>${product.name}</td>
                            <td>${product.category_name}</td>
                            <td>${product.description}</td>
                            <td>${product.stock}</td>
                            <td>${product.price_formatted} VND</td>
                            <td>${product.unit}</td>
                            <td>${product.status}</td>

                            <td>
                                ${product.expiry_date_formatted}
                                ${product.expiry_badge}
                            </td>

                            <td>
                                <a class="btn btn-app btn-update-product" data-toggle="modal"
                                    data-target="#modalUpdate-${productId}">
                                        <i class="fa fa-edit"></i>Chỉnh sửa
                                </a>
                            </td>
                            <td>
                                <a class="btn btn-app btn-delete-product" data-id="${productId}" data-status="${product.status}">
                                    <i class="fa fa-close"></i>Xóa
                                </a>
                            </td>
                        </tr>`;

                        // Thay thế dòng cũ bằng dòng mới
                        $('#product-row-' + productId).replaceWith(newRow);

                        toastr.success(response.message);
                        $('#modalUpdate-' + productId).modal('hide');

                } else {
                    toastr.error(response.message);
                }
            },
            error: function (xhr, status, error) {
                alert("Có lỗi xảy ra: " + error);
            },
            complete: function () {
                button.prop("disabled", false);
                button.text("Chỉnh sửa");
            },
        });
    });






    //delete product
    $(document).on("click", ".btn-delete-product", function(e){
        e.preventDefault();
        let button = $(this);
        let productId = button.data("id");

        let status = button.data("status"); // Lấy trạng thái từ nút bấm

        if (status === 'in_stock') {
            toastr.warning('Sản phẩm đang bán (Còn hàng) không được phép xóa! Vui lòng chuyển trạng thái sang Hết hàng trước.');
            return; // Dừng lại ngay, không chạy tiếp code bên dưới
        }

        let row = button.closest("tr");

        if(!confirm('Bạn có chắc chắn muốn xóa sản phẩm này không?')) return;

        $.ajaxSetup({
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
        });

        $.ajax({
            url: "product/delete",
            type: "POST",
            data: {
                id: productId,
            },
            success: function (response) {
                if (response.status) {
                    toastr.success(response.message);

                    row.remove();
                } else {
                    toastr.error(response.message);
                }
            },
            error: function (xhr, status, error) {
                alert("Có lỗi xảy ra: " + error);
            },
        });
    });






    /*********************************
     * MANAGEMENT - Order- Hành động (quản lý quản trị đơn hàng, nghĩa là mình có thể quyết định xem
     * có duyệt đơn hàng hay không)
     *********************************/
    //giao hàng
    $(document).on("click", ".confirm-order", function(e){
        e.preventDefault();
        let button = $(this);
        let orderId = button.data("id");

        $.ajaxSetup({
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
        });


        $.ajax({
            type: "POST",
            url: "order/confirm",
            data: {
                id: orderId,
            },
            success: function (response) {
                if (response.status) {
                    toastr.success(response.message);
                    button
                        .closest("tr")
                        .find(".order-status")
                        .html(
                            '<span class="custom-badge badge badge-info">Đang giao hàng</span>'
                        );
                    button.hide();
                } else {
                    toastr.error(response.message);
                }
            },
            error: function (xhr, status, error) {
                alert("An error occurred: " + error);
            }
        });



    });



    //Gửi mail đến cho khách
    $(document).on("click", ".send-invoice-mail", function (e) {
        e.preventDefault();
        let button = $(this);
        let orderId = button.data("id");

        $.ajaxSetup({
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
        });

        $.ajax({
            type: "POST",
            url: "send-invoice",
            data: {
                id: orderId,
            },
            success: function (response) {
                if (response.status) {
                    toastr.success(response.message);
                    button.remove();
                } else {
                    toastr.error(response.message);
                }
            },
            error: function (xhr, status, error) {
                alert("Có lỗi xảy ra: " + error);
            },
        });
    });



    //Admin hủy đơn
    $(document).on("click", ".cancel-order", function (e) {
        e.preventDefault();
        let button = $(this);
        let orderId = button.data("id");

        $.ajaxSetup({
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
            }
        });

        $.ajax({
            type: "POST",
            url: "cancel-order",
            data: {
                id: orderId,
            },
            success: function (response) {
                if (response.status) {
                    toastr.success(response.message);
                    button.remove();
                } else {
                    toastr.error(response.message);
                }
            },
            error: function (xhr, status, error) {
                alert("Có lỗi xảy ra: " + error);
            },
        });
    });







    /*********************************
     * MANAGEMENT - Contact
     *********************************/
    if($("#editor-contact").length){
            CKEDITOR.replace("editor-contact", {
            versionCheck: false,
        });
    }


    //khi click từng mục trong khu vực "Liên hệ khách hàng"
    $(document).on("click", ".contact-item", function (e) {
        let contactName = $(this).data("name");
        let contactEmail = $(this).data("email");
        let contactMessage = $(this).data("message");
        let contactId = $(this).data("id");
        let isReplied = $(this).attr("data-is_replied");

        $(".mail_view .inbox-body .sender-info strong").text(contactName);
        $(".mail_view .inbox-body .sender-info span").text('('+ contactEmail +')');
        $(".mail_view .view-mail p").text(contactMessage);

        console.log(contactId ,isReplied);
            $(".mail_view").show();

        if (isReplied != 0){
            $('#compose').hide();
        }else{
            // Add attribute data-email to button reply
            $(".send-reply-contact").attr("data-email", contactEmail);
            $(".send-reply-contact").attr("data-id", contactId);
            $("#compose").show();
        }

    });



    //Xử lý cái nút "gửi"
    $(document).on("click", ".send-reply-contact", function (e) {
        e.preventDefault();
        let button = $(this);
        let email = button.data("email");
        let contactId = button.data("id");
        let message = CKEDITOR.instances["editor-contact"].getData();

        $.ajaxSetup({
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
            }
        });


        $.ajax({
            type: "POST",
            url: "contact/reply",
            data: {
                email: email,
                message: message,
                contact_id: contactId,
            },
            success: function (response) {
                if (response.status) {
                    toastr.success(response.message);
                    $(".mail_view").hide();
                    $("#compose").hide();
                    CKEDITOR.instances["editor-contact"].setData("");
                    $("#editor-contact").empty();


                    let contactItem = $('.contact-item[data-id="' + contactId + '"]');
                    contactItem.attr("data-is_replied" , "1");

                    contactItem.find("i.fa-circle").css("color", "green");


                    $(".compose").slideToggle();
                    button
                        .removeAttr("data-email")
                        .removeAttr("data-contactid");
                } else {
                    toastr.error(response.message);
                }
            },
            error: function (xhr, status, error) {
                alert("Có lỗi xảy ra: " + error);
            }
        });
    });




    /*********************************
     * MANAGEMENT -Profile admin
     *********************************/
    // nút đổi mật khẩu
    $(".form-change-pass").on("click", function(e){
        e.preventDefault();
        $("#change-password").toggle();

        if($("#change-password").is(":visible")){
            $(this).text("Đóng");
        }else{
            $(this).text("Đổi mật khẩu");
        }

    });

    //click vào ảnh
    $('.update-avatar').on('click', function (e) {
        e.preventDefault();
        $('#avatar').trigger('click');
    });

    //nút đổi ảnh
    $('#avatar').on('change', function (e) {
        let file = e.target.files[0];
        if (file) {
            let reader = new FileReader();
            reader.onload = function (e) {
                $('#avatar-preview').attr('src', e.target.result);
            };
            reader.readAsDataURL(file);


            //tạo formData để gửi ảnh đại diện admin
            let formData = new FormData();
            formData.append("type", "avatar");
            formData.append("avatar", file);

            updateProfile(formData);


        } else {
            $('#avatar-preview').attr('src', '');
        }
    });


    //logic validate khi nhấn nút "cập nhật"
    $("#update-profile").submit(function (e){
        let valid = true;
        let name = $("#name").val().trim();
        let phone = $("#phone").val().trim();
        let address = $("#address").val().trim();
        e.preventDefault();

        if(name.length < 3)
        {
            toastr.error(
                "Họ và tên phải có ít nhất 3 ký tự."
            );
            valid = false;
        }

        let phoneRegex = /^0\d{9}$/;

        if(!phoneRegex.test(phone)) {
            toastr.error(
                "Số điện thoại không hợp lệ. Phải có 10 số và bắt đầu bằng 0."
            );
            valid = false;
        }

        if(valid)
        {
            let formData = new FormData();
            formData.append('type', "profile");
            formData.append('name', name);
            formData.append('phone', phone);
            formData.append('address', address);

            updateProfile(formData);
        }

    });



    //bảng validate cái phần đổi mật khẩu
    $("#change-password").submit(function (e){
        let valid = true;
        let current_password = $("#current_password").val().trim();
        let new_password = $("#new_password").val().trim();
        let confirm_password = $("#confirm_password").val().trim();
        e.preventDefault();

        if(current_password === "")
        {
            toastr.error(
                "Bạn cần nhập đúng mật khẩu hiện tại."
            );
            valid = false;
        }


        if(new_password.length < 6) {
            toastr.error(
                "Mật khẩu mới phải có ít nhất 6 ký tự"
            );
            valid = false;
        }


        if(new_password !== confirm_password) {
            toastr.error(
                "Mật khẩu xác nhận không khớp."
            );
            valid = false;
        }


        if(valid)
        {
            let formData = new FormData();
            formData.append("type", "password");
            formData.append("current_password", current_password);
            formData.append("new_password", new_password);
            formData.append("confirm_password", confirm_password);

            updateProfile(formData);
        }

    });


    //logic cập nhật thông tin khi nhập chính xác
    function updateProfile(formData)
    {
        $.ajaxSetup({
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
            }
        });


        $.ajax({
            url: "profile/update",
            type: "POST",
            data: formData,
            contentType: false,
            processData: false,
            dataType: "json",

            success: function (response) {
                if (response.status) {
                    toastr.success(response.message);


                    if(formData.get("type") === "profile"){
                        console.log(formData.get("name"));

                        $("#user-name").text(formData.get("name"));
                        $("#user-address").text(formData.get("address"));

                        $("#user-phone").text(formData.get("phone"));
                    }

                    if(formData.get("type") === "password"){
                        $("#change-password")[0].reset();
                    }

                    if(formData.get("type") === "avatar"){
                        $("#avatar-preview").attr("src", response.avatar_url);
                    }



                } else {
                    toastr.error(response.message);
                }
            },
            error: function (xhr, status, error) {
                alert("Có lỗi xảy ra: " + error);
            }
        });
    }



    /*********************************
     * MANAGEMENT notification
     *********************************/
    $(document).on('click', '.notification-item', function(e) {
        e.preventDefault(); // 1. QUAN TRỌNG: Ngăn không cho trình duyệt tự chuyển trang ngay lập tức

        let noti_id = $(this).data("id");
        let redirectUrl = $(this).attr('href'); // 2. Lưu lại cái link để tí nữa chuyển sau

        $.ajaxSetup({
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
            }
        });

        $.ajax({
            url: "/admin/notification/update", // Thêm dấu / ở đầu để đảm bảo đường dẫn tuyệt đối
            type: "POST",
            dataType: "json",
            data: {
                id: noti_id
            },

            success: function(response) {
                // 3. Cập nhật thành công -> Mới bắt đầu chuyển trang
                window.location.href = redirectUrl;
            },

            error: function(xhr, status, error) {
                // Lỡ có lỗi server thì vẫn cho khách chuyển trang luôn (để họ đỡ bị kẹt lại)
                console.log("Lỗi update status: " + error);
                window.location.href = redirectUrl;
            }
        });
    })



});
