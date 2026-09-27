# Tổng hợp tài liệu dự án website mua bán trang thiết bị điện tử

> Tài liệu này hợp nhất nội dung từ ba tài liệu Word trong thư mục `docs`. Những phần được suy ra để nối các mô hình hoặc luồng nghiệp vụ được đánh dấu là **đề xuất tổng hợp**, không phải thiết kế đã được chốt trong tài liệu gốc.

## 1. Nguồn tài liệu và phạm vi

Các tài liệu đã đọc:

1. [`Website_Mua_Ban_Trang_Thiet_Bi_Dien_Tu_PHP.docx`](Website_Mua_Ban_Trang_Thiet_Bi_Dien_Tu_PHP.docx) — danh sách chi tiết 47 nghiệp vụ và 20 model.
2. [`Tong_hop_nghiep_vu_model_ban_rieng.docx`](Tong_hop_nghiep_vu_model_ban_rieng.docx) — nghiệp vụ được gom thành 19 nhóm và đề xuất 15 model.
3. [`Tong_hop_nghiep_vu_va_bang_model_QLLKDT.docx`](Tong_hop_nghiep_vu_va_bang_model_QLLKDT.docx) — 23 nghiệp vụ và 12 bảng dữ liệu chính, với ví dụ phù hợp cửa hàng linh kiện điện tử.

Đề tài là website bán trang thiết bị/linh kiện điện tử. Tài liệu hiện tại mô tả yêu cầu nghiệp vụ và chức năng dữ liệu ở mức khái niệm; chưa quy định lược đồ SQL hoàn chỉnh, kiểu dữ liệu, khóa, giao diện, API, công nghệ thanh toán cụ thể hay tiêu chí nghiệm thu.

## 2. Tóm tắt hệ thống

Hệ thống có hai nhóm người dùng chính:

- **Khách hàng:** tạo và quản lý tài khoản, duyệt/tìm sản phẩm, quản lý giỏ hàng, đặt hàng, thanh toán, theo dõi đơn, dùng ưu đãi và đánh giá sản phẩm.
- **Nhân viên/quản trị viên:** quản lý người dùng, danh mục, thương hiệu, nhà cung cấp, sản phẩm, hình ảnh, tồn kho, đơn hàng, thanh toán, vận chuyển, ưu đãi và nội dung website; xem thống kê, báo cáo.

Danh mục ví dụ trong tài liệu gồm CPU, RAM, Mainboard, ổ cứng và card màn hình. Thương hiệu ví dụ gồm Intel, AMD, Asus và Corsair. Đây là ví dụ minh họa, không phải danh sách cố định.

## 3. Nghiệp vụ phía khách hàng

### 3.1. Tài khoản và thông tin cá nhân

- Đăng ký tài khoản, nhập thông tin cá nhân, email và mật khẩu.
- Đăng nhập, đăng xuất.
- Xem và cập nhật thông tin cá nhân/liên hệ như họ tên, địa chỉ, số điện thoại.
- Đổi mật khẩu.
- Khôi phục mật khẩu khi quên.

### 3.2. Duyệt và lựa chọn sản phẩm

- Xem danh mục và thương hiệu.
- Xem danh sách sản phẩm đang bán.
- Tìm kiếm theo tên, mã sản phẩm, thông số kỹ thuật hoặc khoảng giá.
- Lọc theo danh mục, giá và thương hiệu.
- Sắp xếp theo giá, sản phẩm mới nhất hoặc bán chạy.
- Xem trang chi tiết gồm hình ảnh, giá, mô tả, thông số kỹ thuật và đánh giá.

### 3.3. Giỏ hàng và đặt hàng

- Thêm sản phẩm cùng số lượng vào giỏ.
- Thay đổi số lượng hoặc xóa sản phẩm khỏi giỏ.
- Xác nhận sản phẩm trong giỏ, địa chỉ nhận hàng và thông tin đơn trước khi đặt.
- Chọn phương thức vận chuyển/giao hàng.
- Tạo đơn hàng từ giỏ hàng.

### 3.4. Ưu đãi, thanh toán và theo dõi đơn

- Nhập mã giảm giá hoặc áp dụng chương trình khuyến mãi khi đáp ứng điều kiện.
- Chọn và thực hiện phương thức thanh toán. Các ví dụ được nêu gồm COD, chuyển khoản và ví điện tử; danh sách chính thức chưa được chốt.
- Xem tình trạng thanh toán.
- Theo dõi tiến trình xử lý và giao hàng; xem lịch sử mua hàng.
- Hủy đơn khi đơn đủ điều kiện.
- Nhận thông báo về đơn hàng và khuyến mãi.

### 3.5. Sau mua

- Đánh giá/chấm điểm và viết nhận xét về sản phẩm đã mua.
- Thêm hoặc xóa sản phẩm yêu thích.

## 4. Nghiệp vụ phía nhân viên/quản trị viên

### 4.1. Quản trị tài khoản và phân quyền

- Đăng nhập khu vực quản trị.
- Xem, sửa, khóa hoặc mở khóa tài khoản khách hàng.
- Thêm, sửa hoặc xóa thông tin nhân viên.
- Quản lý vai trò/quyền của khách hàng, nhân viên và quản trị viên.

### 4.2. Quản lý dữ liệu danh mục và sản phẩm

- Thêm, sửa, xóa danh mục sản phẩm.
- Thêm, sửa, xóa thương hiệu.
- Thêm, sửa, xóa nhà cung cấp và liên kết nhà cung cấp với sản phẩm.
- Thêm, sửa, xóa/cập nhật sản phẩm; cập nhật giá, mô tả và thông số kỹ thuật.
- Thêm, thay thế hoặc xóa hình ảnh sản phẩm.
- Theo dõi/cập nhật số lượng tồn kho.
- Ghi nhận sản phẩm nhập kho.

### 4.3. Đơn hàng, thanh toán và giao hàng

- Xem và xử lý đơn hàng; xác nhận đơn của khách.
- Cập nhật trạng thái xử lý, giao hàng hoặc hủy đơn.
- Theo dõi/đối soát phương thức, giao dịch và trạng thái thanh toán.
- Theo dõi thông tin vận chuyển.

### 4.4. Ưu đãi, đánh giá và nội dung website

- Tạo, chỉnh sửa, áp dụng hoặc kết thúc mã giảm giá/voucher và chương trình khuyến mãi.
- Xem, kiểm duyệt, ẩn hoặc xóa đánh giá không phù hợp.
- Thêm, sửa, xóa banner website.
- Tạo thông báo cho khách hàng.

### 4.5. Thống kê và báo cáo

- Thống kê doanh thu theo ngày, tháng, năm hoặc khoảng thời gian.
- Thống kê số lượng/trạng thái đơn hàng.
- Theo dõi sản phẩm bán chạy và tình trạng tồn kho.

## 5. Luồng nghiệp vụ tổng quát

### 5.1. Mua hàng

1. Khách hàng đăng ký/đăng nhập hoặc duyệt sản phẩm.
2. Khách tìm, lọc hoặc sắp xếp danh mục; mở trang chi tiết sản phẩm.
3. Khách thêm sản phẩm vào giỏ và chỉnh sửa số lượng.
4. Khách xác nhận giỏ, địa chỉ giao hàng, phương thức vận chuyển và ưu đãi (nếu có).
5. Khách chọn phương thức thanh toán và tạo đơn.
6. Nhân viên xác nhận/xử lý; hệ thống lưu trạng thái thanh toán và giao hàng.
7. Khách theo dõi đơn, xem lịch sử; sau khi mua có thể đánh giá sản phẩm.

### 5.2. Quản trị

Nhân viên/quản trị đăng nhập, cập nhật dữ liệu danh mục và sản phẩm, theo dõi tồn kho, tiếp nhận/xử lý đơn và giao dịch, quản lý ưu đãi/đánh giá/nội dung website, sau đó xem báo cáo vận hành.

## 6. Danh sách model hợp nhất

Ba tài liệu đưa ra các tập model khác nhau. Bảng dưới đây là **hợp nhất các model được nhắc đến**, gồm 21 model. Đây là danh sách tham chiếu đầy đủ để không bỏ sót chức năng; không có nghĩa mọi dự án đều phải triển khai đủ 21 bảng độc lập.

| STT | Model | Vai trò dữ liệu |
|---:|---|---|
| 1 | `TaiKhoan` | Thông tin đăng nhập và liên kết người dùng với vai trò/quyền. |
| 2 | `VaiTro` | Phân loại quyền khách hàng, nhân viên và quản trị viên. |
| 3 | `KhachHang` | Thông tin cá nhân, liên hệ của khách hàng. |
| 4 | `NhanVien` | Thông tin nhân viên thực hiện nghiệp vụ quản trị. |
| 5 | `DanhMuc` | Nhóm sản phẩm/linh kiện điện tử. |
| 6 | `ThuongHieu` | Thương hiệu sản phẩm. |
| 7 | `NhaCungCap` | Đơn vị cung cấp sản phẩm cho cửa hàng. |
| 8 | `SanPham` | Tên, giá, mô tả và các liên kết phân loại/cung ứng của sản phẩm. |
| 9 | `HinhAnhSanPham` | Một hoặc nhiều hình ảnh minh họa gắn với sản phẩm. |
| 10 | `ThongSoKyThuat` | Thông số kỹ thuật chi tiết của sản phẩm. |
| 11 | `KhoHang` | Số lượng sản phẩm tồn kho. |
| 12 | `GioHang` | Giỏ hàng hiện tại/tạm thời của khách hàng. |
| 13 | `ChiTietGioHang` | Sản phẩm và số lượng trong mỗi giỏ hàng. |
| 14 | `DonHang` | Thông tin chung như khách đặt, ngày đặt, địa chỉ, trạng thái và tổng tiền. |
| 15 | `ChiTietDonHang` | Sản phẩm, số lượng và đơn giá tại thời điểm đặt hàng. |
| 16 | `ThanhToan` | Phương thức, giao dịch, thời gian và trạng thái thanh toán của đơn. |
| 17 | `VanChuyen` | Thông tin giao hàng/vận chuyển và trạng thái liên quan. |
| 18 | `DanhGia` | Số sao, nhận xét và thông tin đánh giá sản phẩm của khách hàng. |
| 19 | `SanPhamYeuThich` | Liên kết danh sách sản phẩm yêu thích của khách hàng. |
| 20 | `MaGiamGia` | Mã voucher/giảm giá và thông tin áp dụng. |
| 21 | `KhuyenMai` | Chương trình ưu đãi, mức giảm, thời gian và điều kiện áp dụng. |

### 6.1. Quan hệ dữ liệu tổng quát

Các tài liệu mô tả chức năng từng bảng nhưng chưa đưa ra ERD hay khóa ngoại chính thức. Các liên kết sau là **đề xuất tổng hợp** để mô hình hóa những luồng đã nêu:

- `TaiKhoan` gắn với vai trò và hồ sơ `KhachHang` hoặc `NhanVien`.
- `SanPham` thuộc `DanhMuc`, gắn với `ThuongHieu` và có thể gắn `NhaCungCap`.
- `HinhAnhSanPham` và `ThongSoKyThuat` liên kết với `SanPham`.
- `KhoHang` theo dõi số lượng của `SanPham`.
- `GioHang` thuộc khách hàng; `ChiTietGioHang` nối giỏ với sản phẩm và số lượng.
- `DonHang` thuộc khách hàng; `ChiTietDonHang` nối đơn hàng với sản phẩm, số lượng và đơn giá lúc đặt.
- `ThanhToan` và `VanChuyen` liên kết với `DonHang`.
- `DanhGia` liên kết khách hàng với sản phẩm; `SanPhamYeuThich` liên kết khách hàng với sản phẩm yêu thích.
- `MaGiamGia`/`KhuyenMai` được áp dụng vào đơn hàng hoặc giỏ hàng theo điều kiện chương trình.

## 7. Đối chiếu khác biệt giữa các tài liệu

| Tài liệu | Cách nhóm nghiệp vụ | Số model/bảng | Điểm nổi bật |
|---|---:|---:|---|
| `Website_Mua_Ban_Trang_Thiet_Bi_Dien_Tu_PHP.docx` | 47 mục chi tiết: 24 phía khách hàng, 23 phía nhân viên/quản trị | 20 | Chi tiết hóa giỏ hàng, vai trò, thông số, kho, vận chuyển, yêu thích, voucher, khuyến mãi. |
| `Tong_hop_nghiep_vu_model_ban_rieng.docx` | 19 nhóm: 9 phía khách hàng, 10 phía quản trị | 15 | Gom thao tác thành luồng; có `NhaCungCap`; gộp thông tin đăng nhập/vai trò vào `TaiKhoan` theo mô tả. |
| `Tong_hop_nghiep_vu_va_bang_model_QLLKDT.docx` | 23 mục: 12 phía khách hàng, 11 phía quản trị | 12 | Mô tả ví dụ linh kiện cụ thể; có `NhaCungCap`, nhưng lược bớt một số bảng tách riêng. |

Các khác biệt cần hiểu khi dùng bản tổng hợp:

- **Mức chi tiết nghiệp vụ:** tài liệu 47 mục tách thao tác nhỏ; hai tài liệu còn lại gom chúng thành nhóm luồng. Số lượng khác nhau không nhất thiết là mâu thuẫn về tính năng.
- **Tài khoản và vai trò:** bản chi tiết có `TaiKhoan` và `VaiTro` riêng; bản rút gọn mô tả vai trò/quyền bên trong `TaiKhoan`.
- **Dữ liệu người dùng:** bản 20 model tách `KhachHang`, `NhanVien`; danh sách 12 bảng mô tả tài khoản người dùng chung và không liệt kê hai bảng hồ sơ đó.
- **Nhà cung cấp:** có trong hai danh sách 12/15 model, nhưng vắng mặt trong danh sách 20 model.
- **Giỏ hàng:** `ChiTietGioHang` được liệt kê riêng trong bản 20 model; hai danh sách ngắn không tách bảng chi tiết giỏ.
- **Khoản mở rộng:** `VanChuyen`, `SanPhamYeuThich`, `MaGiamGia`, `KhuyenMai`, `ThongSoKyThuat`, `VaiTro` hoặc `NhanVien` được tách/gộp khác nhau giữa các bản.
- **Voucher và khuyến mãi:** có tài liệu xem mã giảm giá và chương trình khuyến mãi là hai model, có tài liệu gộp thành `KhuyenMai`.
- **Tên đối tượng và vai trò:** tài liệu dùng “quản trị viên”, “nhân viên/admin” với phạm vi chưa phân quyền chi tiết.

## 8. Phạm vi triển khai tham khảo

Để chọn phạm vi cho phiên bản đầu, có thể chia theo mức sau. Đây là gợi ý tổ chức từ nội dung tài liệu, không phải phạm vi đã được chủ dự án phê duyệt:

### Cốt lõi cửa hàng

- Tài khoản và hồ sơ người dùng.
- Danh mục, thương hiệu, sản phẩm, hình ảnh và tồn kho.
- Giỏ hàng và chi tiết giỏ.
- Đơn hàng và chi tiết đơn.
- Thanh toán, vận chuyển.
- Quản trị sản phẩm/đơn hàng và theo dõi trạng thái.

### Mở rộng bán hàng

- Nhà cung cấp và nhập kho.
- Mã giảm giá/chương trình khuyến mãi.
- Đánh giá và kiểm duyệt.
- Sản phẩm yêu thích.
- Thông báo, banner.
- Báo cáo doanh thu, đơn hàng, sản phẩm bán chạy và tồn kho.

## 9. Quy tắc và quyết định còn cần chốt

Các tài liệu nêu chức năng nhưng không quy định chi tiết các trường hợp dưới đây. Cần xác định trước khi hoàn thiện ERD hoặc triển khai:

1. **Vai trò và quyền:** quyền cụ thể của nhân viên so với quản trị viên; tài khoản khách có thể bị khóa/mở khóa như thế nào.
2. **Định danh người dùng:** `TaiKhoan` có hồ sơ khách hàng/nhân viên riêng hay lưu chung; quan hệ một-một hay cho phép nhiều hồ sơ.
3. **Danh mục và nhà cung cấp:** một sản phẩm thuộc một hay nhiều nhà cung cấp; có cho phép danh mục nhiều cấp không.
4. **Thông số kỹ thuật:** lưu theo bộ trường cố định hay dạng thuộc tính linh hoạt; có cần tìm kiếm theo thông số hay không.
5. **Tồn kho:** khi nào trừ/giữ tồn (thêm giỏ, đặt đơn, xác nhận hay thanh toán); xử lý khi hủy đơn/hoàn hàng; cách ghi nhận phiếu nhập.
6. **Giỏ hàng:** khách chưa đăng nhập có được dùng giỏ không; giỏ có được đồng bộ sau đăng nhập và tự hết hạn không.
7. **Đơn hàng:** các trạng thái hợp lệ, điều kiện hủy, cách lưu địa chỉ và giá tại thời điểm mua. Tài liệu có nêu đơn giá tại thời điểm đặt trong chi tiết đơn.
8. **Thanh toán:** phương thức được hỗ trợ; thời điểm xác nhận giao dịch; xử lý thanh toán thất bại, hoàn tiền và COD.
9. **Vận chuyển:** hãng/đơn vị giao hàng, phí vận chuyển, mã theo dõi, cách đồng bộ trạng thái.
10. **Ưu đãi:** điều kiện tối thiểu, thời hạn, giới hạn lượt dùng, áp dụng đồng thời nhiều ưu đãi hay không; phân biệt voucher và chương trình khuyến mãi.
11. **Đánh giá:** chỉ người đã mua mới được đánh giá hay không; một khách được đánh giá bao nhiêu lần; quy trình ẩn/duyệt.
12. **Báo cáo:** định nghĩa doanh thu, cách xử lý đơn hủy/hoàn và múi giờ/khoảng thời gian thống kê.
13. **Thông báo/banner:** kênh thông báo, đối tượng nhận, thời gian hiển thị và lịch sử thông báo.

## 10. Kết luận tổng hợp

Ba tài liệu thống nhất về mục tiêu xây dựng website thương mại điện tử cho thiết bị/linh kiện điện tử với hai phía chính là khách hàng và quản trị. Khác biệt chủ yếu nằm ở mức độ chi tiết của nghiệp vụ và việc tách hay gộp model. Danh sách 21 model ở mục 6 là tập hợp đầy đủ các thực thể được nêu; có thể rút gọn theo phạm vi sản phẩm, nhưng cần giữ đủ dữ liệu để hỗ trợ giỏ hàng, đơn hàng, thanh toán, tồn kho và các chức năng được chọn.
