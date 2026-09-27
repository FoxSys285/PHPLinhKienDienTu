# Phân công công việc nhóm 4 thành viên

## 1. Nguyên tắc phân công

- Mỗi thành viên phụ trách trọn một **phân hệ**: giao diện, xử lý backend/API và dữ liệu thuộc phân hệ đó.
- Mỗi page và component nghiệp vụ có đúng một người sở hữu. Thành viên khác tích hợp qua API/component contract đã thống nhất, không tự sửa trực tiếp phần của nhau.
- Tên thành viên hiện dùng là **Thành viên 1–4**; có thể thay bằng tên thật của nhóm.
- Phân công dựa trên 3 tài liệu yêu cầu của dự án. Các endpoint và route dưới đây là tên quy ước để nhóm thống nhất, không phụ thuộc framework cụ thể.

## 2. Bảng phân công tổng quan

| Thành viên | Phân hệ sở hữu | Frontend | Backend và dữ liệu |
|---|---|---|---|
| **Thành viên 1** | Nền tảng, tài khoản và phân quyền | Khung ứng dụng dùng chung; đăng ký, đăng nhập, khôi phục mật khẩu, hồ sơ; trang quản trị tài khoản/nhân viên | Xác thực, phiên đăng nhập, phân quyền; `TaiKhoan`, `VaiTro`, `KhachHang`, `NhanVien` |
| **Thành viên 2** | Danh mục, sản phẩm và kho | Trang danh mục, tìm kiếm/lọc/sắp xếp, chi tiết sản phẩm; trang quản trị danh mục, thương hiệu, nhà cung cấp, sản phẩm, hình ảnh, thông số, tồn kho/nhập hàng | Danh mục, thương hiệu, nhà cung cấp, sản phẩm, hình ảnh, thông số, tồn kho; `DanhMuc`, `ThuongHieu`, `NhaCungCap`, `SanPham`, `HinhAnhSanPham`, `ThongSoKyThuat`, `KhoHang` |
| **Thành viên 3** | Giỏ hàng và vòng đời đơn hàng | Giỏ hàng, đặt hàng/checkout, lịch sử và chi tiết đơn; trang quản trị xử lý đơn, thanh toán, vận chuyển, khuyến mãi | Giỏ hàng, chi tiết giỏ, đơn hàng, chi tiết đơn, thanh toán, vận chuyển, mã giảm giá/chương trình; `GioHang`, `ChiTietGioHang`, `DonHang`, `ChiTietDonHang`, `ThanhToan`, `VanChuyen`, `MaGiamGia`, `KhuyenMai` |
| **Thành viên 4** | Trang chủ, tương tác khách hàng và báo cáo | Trang chủ/banner; yêu thích; xem và gửi đánh giá; thông báo; trang kiểm duyệt đánh giá, quản lý banner/thông báo, báo cáo | Banner, thông báo, đánh giá, yêu thích, truy vấn thống kê; `DanhGia`, `SanPhamYeuThich` và dữ liệu nội dung/thông báo cần thiết |

## 3. Chi tiết theo thành viên

### Thành viên 1 — Nền tảng, tài khoản và phân quyền

**Frontend sở hữu**

- Khung giao diện dùng chung: layout, header/navigation, footer, menu quản trị, thành phần kiểm tra quyền truy cập.
- Trang đăng ký, đăng nhập, đăng xuất, quên/đặt lại mật khẩu.
- Trang xem/cập nhật thông tin cá nhân và đổi mật khẩu.
- Trang quản trị tài khoản khách hàng: xem, sửa, khóa/mở khóa.
- Trang quản lý nhân viên và vai trò/quyền.

**Backend và dữ liệu sở hữu**

- Đăng ký, xác thực, đăng xuất và quản lý phiên/token theo cách nhóm thống nhất.
- Cập nhật hồ sơ, đổi/khôi phục mật khẩu.
- Quản trị tài khoản, hồ sơ nhân viên và phân quyền.
- Sở hữu schema/migration cho `TaiKhoan`, `VaiTro`, `KhachHang`, `NhanVien`.

**Phạm vi không thuộc thành viên 1:** xử lý nội dung nghiệp vụ của các trang sản phẩm, đơn hàng, khuyến mãi hay báo cáo. Các thành viên khác dùng layout và cơ chế quyền do thành viên 1 cung cấp.

### Thành viên 2 — Danh mục, sản phẩm và kho

**Frontend sở hữu**

- Trang duyệt danh mục/thương hiệu và danh sách sản phẩm.
- Tìm kiếm theo tên/mã/thông số/giá; lọc theo danh mục, thương hiệu, giá; sắp xếp theo giá, mới nhất, bán chạy.
- Trang chi tiết sản phẩm: hình ảnh, giá, mô tả, thông số, tình trạng còn hàng và liên kết tới đánh giá.
- Trang quản trị danh mục, thương hiệu, nhà cung cấp, sản phẩm, hình ảnh và thông số kỹ thuật.
- Trang quản lý tồn kho và ghi nhận nhập hàng.

**Backend và dữ liệu sở hữu**

- CRUD danh mục, thương hiệu, nhà cung cấp, sản phẩm, hình ảnh và thông số.
- Tìm kiếm/lọc/sắp xếp và truy vấn danh sách/chi tiết sản phẩm.
- Tồn kho, nhập hàng và khả năng cung cấp thông tin còn hàng cho phân hệ đơn hàng.
- Sở hữu schema/migration cho `DanhMuc`, `ThuongHieu`, `NhaCungCap`, `SanPham`, `HinhAnhSanPham`, `ThongSoKyThuat`, `KhoHang`.

**Ranh giới page:** thành viên 2 sở hữu trang chi tiết sản phẩm. Thành viên 4 sở hữu route đánh giá riêng; trang chi tiết chỉ hiển thị liên kết/điểm tổng hợp theo API contract, không nhúng form đánh giá do thành viên 4 quản lý.

### Thành viên 3 — Giỏ hàng và vòng đời đơn hàng

**Frontend sở hữu**

- Trang giỏ hàng: thêm, đổi số lượng, xóa sản phẩm.
- Trang checkout: xác nhận sản phẩm, địa chỉ, vận chuyển, mã giảm giá và phương thức thanh toán.
- Trang lịch sử đơn hàng, chi tiết đơn và theo dõi trạng thái.
- Trang quản trị xử lý/xác nhận đơn, theo dõi thanh toán và vận chuyển.
- Trang quản trị tạo/sửa/kết thúc voucher và chương trình khuyến mãi.

**Backend và dữ liệu sở hữu**

- Giỏ hàng và chi tiết giỏ; tạo đơn từ giỏ.
- Lưu chi tiết sản phẩm, số lượng và đơn giá tại thời điểm đặt hàng.
- Áp dụng và kiểm tra điều kiện voucher/khuyến mãi trong luồng checkout.
- Cập nhật trạng thái xử lý đơn, thanh toán và giao hàng; hỗ trợ hủy đơn theo điều kiện được nhóm thống nhất.
- Sở hữu schema/migration cho `GioHang`, `ChiTietGioHang`, `DonHang`, `ChiTietDonHang`, `ThanhToan`, `VanChuyen`, `MaGiamGia`, `KhuyenMai`.

**Phụ thuộc tích hợp:** lấy sản phẩm và tình trạng tồn kho từ thành viên 2; lấy tài khoản/khách hàng và quyền từ thành viên 1. Quy tắc giữ/trừ/hoàn tồn phải được thống nhất với thành viên 2 trước khi hoàn thiện đặt hàng và hủy đơn.

### Thành viên 4 — Trang chủ, tương tác khách hàng và báo cáo

**Frontend sở hữu**

- Toàn bộ nội dung route trang chủ, gồm banner và các khu vực giới thiệu/nhóm sản phẩm. Dữ liệu sản phẩm lấy qua API của thành viên 2.
- Trang danh sách sản phẩm yêu thích.
- Route xem/viết đánh giá sản phẩm và khu vực đánh giá của khách hàng.
- Trung tâm/thanh xem thông báo phía khách hàng.
- Trang quản trị duyệt/ẩn/xóa đánh giá, quản lý banner và thông báo.
- Trang dashboard/báo cáo doanh thu, đơn hàng, sản phẩm bán chạy và tồn kho.

**Backend và dữ liệu sở hữu**

- Tạo, sửa, xóa và hiển thị banner/thông báo.
- Thêm/xóa yêu thích.
- Gửi, xem và kiểm duyệt đánh giá.
- Truy vấn dữ liệu tổng hợp cho dashboard/báo cáo; đọc dữ liệu đơn hàng và kho qua contract của chủ sở hữu các phân hệ, không tự sửa bảng nghiệp vụ của họ.
- Sở hữu `DanhGia`, `SanPhamYeuThich` và schema nội dung/thông báo nếu nhóm cần lưu riêng.

**Ranh giới page:** thành viên 4 sở hữu route trang chủ và các trang đánh giá; không sửa trang danh sách/chi tiết sản phẩm của thành viên 2. Thành viên 4 sở hữu trang báo cáo, còn dữ liệu nguồn đơn hàng/kho vẫn do thành viên 3/2 quản lý.

## 4. Quy ước giao diện và API để tránh làm trùng

1. **Thành viên 1** tạo layout, menu, quy ước route, style/token dùng chung và cơ chế đăng nhập/phân quyền. Các thành viên còn lại tạo nội dung page trong layout đó, không tạo bản sao header/sidebar.
2. Mỗi phân hệ ghi trước contract gồm: route/page sở hữu, API, request/response, trạng thái lỗi và model liên quan. Nếu cần dữ liệu của phân hệ khác, gọi API đã thống nhất thay vì truy cập trực tiếp bảng của nhau.
3. Một file/page/component chỉ có một owner. Ví dụ: `TrangChiTietSanPham` thuộc thành viên 2; `TrangDanhGiaSanPham` thuộc thành viên 4; `TrangCheckout` thuộc thành viên 3.
4. Mỗi thành viên tạo migration riêng cho các bảng mình sở hữu. Nếu cần đổi bảng của người khác, thống nhất với owner trước và để owner đó cập nhật migration.
5. Thành viên 1 sở hữu các thành phần nền tảng dùng chung; yêu cầu thêm thành phần dùng chung được gửi thành viên 1 thay vì mỗi người tự tạo component tương tự.
6. Dùng chung các quy ước đã chốt về tên field, định dạng tiền/ngày, phản hồi lỗi, phân trang và trạng thái đơn. Chốt các quy tắc này trước khi các module tích hợp.

## 5. Thứ tự tích hợp đề xuất

Các phân hệ có thể phát triển song song sau khi chốt contract. Tích hợp theo các mốc phụ thuộc sau:

1. **Nền tảng:** layout, đăng nhập, tài khoản và phân quyền (thành viên 1).
2. **Dữ liệu hàng hóa:** danh mục, sản phẩm, giá và tình trạng tồn (thành viên 2).
3. **Mua hàng:** giỏ, áp dụng ưu đãi, tạo đơn, thanh toán và giao hàng (thành viên 3).
4. **Tương tác và vận hành:** trang chủ, banner, đánh giá, yêu thích, thông báo và báo cáo (thành viên 4).
5. **Tích hợp xuyên luồng:** đăng nhập → duyệt sản phẩm → giỏ hàng → checkout/đặt đơn → theo dõi đơn → đánh giá sau mua.

## 6. Checklist bàn giao của từng thành viên

Trước khi bàn giao một chức năng, owner cần cung cấp:

- Page/route và component thuộc phạm vi của mình.
- API contract và cách gọi chức năng từ phân hệ khác.
- Model/bảng, quan hệ và migration do mình sở hữu.
- Phân quyền cần thiết, các trạng thái rỗng/lỗi/đang tải và validation đầu vào.
- Hướng dẫn cấu hình hoặc dữ liệu mẫu cần có để thành viên khác tích hợp.

## 7. Những quyết định cả nhóm cần thống nhất sớm

- Tên thật của 4 thành viên và framework/ngôn ngữ sẽ dùng.
- Cấu trúc route, quy ước API, cách xác thực và phân quyền.
- Trạng thái đơn hàng, điều kiện hủy, thời điểm trừ/hoàn tồn.
- Phương thức thanh toán/vận chuyển sẽ mô phỏng hay tích hợp thật.
- Quy tắc áp dụng voucher/khuyến mãi và điều kiện khách hàng được đánh giá.
- Phạm vi phiên bản đầu nếu thời gian không đủ cho toàn bộ tính năng như banner, thông báo, yêu thích và báo cáo.
