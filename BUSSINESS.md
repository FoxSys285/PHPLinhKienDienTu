# Quy trình xử lý đơn hàng và vận chuyển

## Mục đích

Tài liệu này mô tả quy trình mục tiêu giữa khách hàng, quản lý/người bán, hệ thống cửa hàng và Viettel Post. Quy trình dùng webhook/API của Viettel Post để đồng bộ hành trình sau khi tích hợp; việc đổi giao diện quản trị không tự tạo ra kết nối hãng vận chuyển.

## Vai trò

- **Khách hàng:** tạo đơn, thanh toán hoặc chọn COD, theo dõi tiến độ, cung cấp lý do khi yêu cầu hủy.
- **Quản lý/người bán:** kiểm tra đơn, xác nhận, soạn và đóng gói hàng, tạo vận đơn, xử lý yêu cầu hủy và các tình huống ngoại lệ.
- **Viettel Post:** tiếp nhận kiện, quét mã, vận chuyển, giao hàng, báo kết quả phát và thực hiện đối soát COD.
- **Hệ thống cửa hàng:** lưu dữ liệu đơn/vận đơn, nhận webhook, cập nhật trạng thái và thông báo cho khách.

## Tách riêng ba nhóm trạng thái

Không dùng một trạng thái duy nhất để biểu diễn cả việc xử lý đơn, giao hàng và thanh toán.

### Trạng thái đơn hàng

| Trạng thái hiển thị | Ý nghĩa |
| --- | --- |
| Đang xử lý đơn hàng | Đơn vừa được tạo và chờ quản lý kiểm tra/xác nhận. |
| Người bán chuẩn bị hàng | Quản lý đã xác nhận; đang soạn, kiểm tra và đóng gói. |
| Đã hủy đơn hàng | Đơn bị hủy trước khi Viettel Post nhận kiện; phải lưu lý do và người hủy. |
| Đã hoàn tất giao hàng | Viettel Post xác nhận phát thành công cho người nhận. |

### Trạng thái vận chuyển

| Trạng thái hiển thị | Sự kiện kích hoạt |
| --- | --- |
| Chờ Viettel Post nhận hàng | Đã tạo vận đơn/yêu cầu lấy hàng nhưng chưa có xác nhận nhận kiện. |
| Đã bàn giao đơn vị vận chuyển | Viettel Post đã nhận kiện vật lý và ghi nhận lên hệ thống. |
| Đang trung chuyển | Sự kiện hành trình của hãng cho biết kiện đang di chuyển trong mạng lưới. |
| Đang giao đến người nhận | Kiện đã được chuyển cho bưu tá để phát. |
| Giao không thành công | Hãng báo không phát được; cần chờ phát lại hoặc chuyển hoàn. |
| Đang hoàn hàng | Hãng đang chuyển kiện về người gửi. |
| Đã hoàn hàng | Cửa hàng xác nhận đã nhận lại kiện. |

Tên hiển thị là nhãn nghiệp vụ của cửa hàng. Khi tích hợp, phải ánh xạ từ mã/sự kiện thực tế trong tài liệu Partner của Viettel Post; không giả định mọi tuyến đều phát ra đầy đủ từng mốc trung chuyển.

### Trạng thái thanh toán

- Thanh toán online: **Chờ thanh toán → Đã thanh toán** sau khi cổng thanh toán xác nhận.
- COD: **Chờ thu hộ → Đã thu từ người nhận → Chờ đối soát → Đã đối soát**.
- Trạng thái giao thành công không đồng nghĩa Viettel Post đã chuyển tiền COD về cửa hàng.

## Luồng xử lý chính

| Bước | Người thực hiện | Việc cần làm | Trạng thái sau bước |
| --- | --- | --- | --- |
| 1. Tạo đơn | Khách hàng | Nhập thông tin liên hệ, địa chỉ nhận, sản phẩm và phương thức thanh toán; gửi đơn. | Đơn: **Đang xử lý đơn hàng**. Thanh toán: **Chờ thanh toán** hoặc **Chờ thu hộ**. |
| 2. Kiểm tra | Quản lý | Kiểm tra thông tin người nhận, khả năng cung ứng, tồn kho và tình trạng thanh toán. Đơn online chưa thanh toán thì chưa được chuyển đi chuẩn bị. | Giữ nguyên **Đang xử lý đơn hàng** cho đến khi xác nhận. |
| 3. Xác nhận | Quản lý | Chọn **Xác nhận** nếu đơn hợp lệ. Hệ thống ghi người xác nhận/thời điểm và thực hiện nghiệp vụ tồn kho. | **Người bán chuẩn bị hàng**. |
| 4. Soạn và đóng gói | Người bán/kho | Kiểm hàng, đóng gói, cân/đo kiện và chuẩn bị thông tin vận chuyển. | **Người bán chuẩn bị hàng**. |
| 5. Tạo vận đơn | Quản lý/hệ thống | Gọi API Viettel Post để tính dịch vụ/cước phù hợp và tạo vận đơn; lưu mã vận đơn, dịch vụ, phí, COD và ngày dự kiến nếu được trả về. | Vận chuyển: **Chờ Viettel Post nhận hàng**. Chưa được hiển thị là đã bàn giao. |
| 6. Bàn giao kiện | Viettel Post/hệ thống | Nhân viên Viettel Post nhận kiện và quét mã. Webhook/API xác nhận sự kiện nhận hàng. | Vận chuyển: **Đã bàn giao đơn vị vận chuyển**. Từ đây không cho hủy trực tiếp. |
| 7. Trung chuyển | Viettel Post/hệ thống | Hãng gửi sự kiện hành trình; hệ thống lưu thời điểm/địa điểm và cập nhật trang theo dõi của khách. | Vận chuyển: **Đang trung chuyển**. |
| 8. Phát hàng | Viettel Post/bưu tá | Bưu tá nhận kiện để giao cho người nhận. | Vận chuyển: **Đang giao đến người nhận**. |
| 9. Giao thành công | Viettel Post/hệ thống | Hãng báo phát thành công; hệ thống cập nhật đơn và gửi thông báo cho khách. | Đơn: **Đã hoàn tất giao hàng**. COD tiếp tục theo dõi đối soát riêng. |

Ngày giao dự kiến phải được ghi nhãn **dự kiến**, cập nhật nếu hãng cung cấp thông tin mới và không coi là cam kết giao chính xác.

## Quy tắc hủy

1. Khi đơn còn **Đang xử lý đơn hàng**, khách hàng hoặc quản lý có thể hủy trực tiếp.
2. Người hủy phải nhập lý do cụ thể. Hệ thống lưu lý do, người thao tác và thời điểm; giải phóng tồn đã giữ và thông báo cho khách/quản lý.
3. Sau khi quản lý xác nhận, không cho khách hoặc quản lý hủy trực tiếp theo luồng thông thường.
4. Khuyến nghị có nút **Yêu cầu hủy** sau khi xác nhận nhưng trước khi Viettel Post nhận kiện. Quản lý duyệt yêu cầu; nếu vận đơn đã tạo thì chỉ đánh dấu đơn hủy sau khi API hãng xác nhận hủy vận đơn.
5. Sau khi Viettel Post nhận kiện, không đổi thành **Đã hủy đơn hàng**. Nếu cần dừng giao, xử lý theo khả năng của hãng, yêu cầu chuyển hoàn hoặc quy trình đổi trả.
6. Nếu đơn online hết thời hạn thanh toán, hệ thống có thể tự hủy với lý do hệ thống như **Quá hạn thanh toán**; đây là sự kiện riêng, không giả thành thao tác hủy của khách/quản lý.

Điều khoản Viettel Post cho phép hủy trước khi bàn giao kiện vật lý cho nhân viên hãng. Vì vậy, mốc chốt vận chuyển phải dựa vào sự kiện hãng nhận kiện, không dựa vào thời điểm in nhãn.

## Giao không thành công và hoàn hàng

1. Khi Viettel Post báo giao không thành công, hệ thống lưu lý do/sự kiện và hiển thị **Giao không thành công**.
2. Nếu hãng tiếp tục giao, chuyển về trạng thái phát lại/đang giao theo sự kiện tiếp theo.
3. Nếu hãng chuyển hoàn, hiển thị **Đang hoàn hàng**.
4. Khi kho nhận lại kiện và nhân viên xác nhận, chuyển sang **Đã hoàn hàng**; quản lý xử lý tồn kho, hoàn tiền hoặc liên hệ khách tùy trường hợp.
5. Không đánh dấu đơn là đã giao hoặc đã hủy nếu hãng chưa xác nhận kết quả tương ứng.

## Tích hợp Viettel Post

1. Đăng ký/được cấp tài khoản Partner API và quyền sử dụng phù hợp với tài khoản Viettel Post.
2. Tích hợp ở môi trường Development trước; tài liệu hiện nêu base URL Development `https://partnerdev.viettelpost.vn` và Production `https://partner.viettelpost.vn`.
3. Lấy token theo tài liệu Partner và gửi token ở header cho API cần xác thực. Không lưu thông tin xác thực trong mã nguồn; cấu hình qua secrets/environment của ứng dụng.
4. Đọc danh mục địa chỉ, dịch vụ và API tạo vận đơn theo tài liệu Partner hiện hành. Các trường cần chuẩn bị gồm thông tin người gửi/nhận, địa chỉ, điện thoại, nội dung kiện, số lượng, khối lượng/kích thước, dịch vụ, cước và COD nếu dùng.
5. Cấu hình endpoint webhook công khai trong tài khoản Partner Development; kiểm tra xác thực request, lưu sự kiện thô cần thiết và chống xử lý trùng webhook.
6. Ánh xạ mã trạng thái hãng sang trạng thái vận chuyển của cửa hàng. Webhook là nguồn cập nhật chính; dùng API tra cứu để đối soát khi webhook lỗi/trễ.
7. Sau khi nghiệm thu Development, phối hợp Viettel Post mở Production và kiểm tra vận hành trước khi bật tạo vận đơn thật.

Tài liệu chính thức: [Hướng dẫn tích hợp Partner](https://partner2.viettelpost.vn/document/detail-instructions), [Môi trường và tham số API](https://partner2.viettelpost.vn/document/environment-parameter), [Token xác thực](https://partner2.viettelpost.vn/document/token-authen), [Điều khoản dịch vụ](https://viettelpost.com.vn/wp-content/uploads/2024/01/Dieu-khoan-va-dieu-kien-su-dung-dich-vu.pdf), [Câu hỏi thường gặp](https://en.viettelpost.com.vn/frequently-asked-questions).

## Nhật ký và thông báo

Hệ thống cần lưu mỗi lần chuyển trạng thái: trạng thái trước/sau, nguồn cập nhật (khách, quản lý, Viettel Post, hệ thống), mã sự kiện hãng nếu có, thời điểm hãng phát sinh và thời điểm cửa hàng nhận sự kiện. Gửi thông báo cho khách khi đơn được xác nhận, có mã vận đơn, chuyển sang giao hàng, giao thành công, bị giao lỗi/hoàn hàng hoặc bị hủy.

## Phần đã triển khai trong ứng dụng

- Tách trạng thái đơn với trạng thái vận chuyển và COD. Các trạng thái đơn đang dùng gồm **Đang xử lý đơn hàng**, **Người bán chuẩn bị hàng**, **Chờ Viettel Post nhận hàng**, **Bàn giao đơn vị vận chuyển**, **Đang trung chuyển**, **Đang giao đến người nhận**, **Giao không thành công**, **Đang hoàn hàng**, **Đã hoàn hàng**, **Đã hoàn tất giao hàng** và **Đã hủy đơn hàng**.
- Quản lý chỉ xác nhận được đơn hợp lệ; đơn online phải được cổng thanh toán báo đã thanh toán. Khi xác nhận, đơn chuyển sang chuẩn bị hàng và không còn nút hủy trực tiếp.
- Khách hàng và quản lý phải nhập lý do khi hủy đơn còn chờ xử lý. Hệ thống ghi người hủy/thời điểm, hoàn lại tồn kho đã xuất nếu có và đánh dấu đơn đã thanh toán là **chờ hoàn tiền**.
- Khi đã tạo vận đơn ở Viettel Post Partner, nhân viên nhập mã vận đơn, dịch vụ, link tra cứu và ngày giao dự kiến vào trang chi tiết đơn. Bước này chuyển sang **Chờ Viettel Post nhận hàng**; nó không giả thành hãng đã nhận kiện.
- Nhân viên được phân quyền có thể ghi sự kiện vận chuyển thủ công khi đối chiếu với cổng Viettel Post. Mỗi lần đổi trạng thái được ghi nhật ký và gửi email cho khách nếu đơn có email.
- Trang tài khoản hiển thị mã vận đơn, link tra cứu, ngày dự kiến, lý do hủy, trạng thái COD và dòng sự kiện vận chuyển.
- Khách đặt hàng không đăng nhập nhận một liên kết riêng để xem/hủy đơn; token truy cập được mã hóa khi lưu trong database.
- COD chạy riêng theo chuỗi **Chờ thu từ người nhận → Đã thu hộ → Chờ Viettel Post đối soát → Đã đối soát**. Kế toán/quản trị viên phải xác nhận từng bước; giao thành công không tự đánh dấu đã đối soát.
- Webhook có thể nhận payload đã ánh xạ và chống xử lý trùng bằng mã sự kiện. Webhook tắt mặc định; bật sau khi có tài khoản Partner và xác nhận đúng cấu trúc payload/mã trạng thái thực tế.

## Chế độ mô phỏng cho đồ án

Chạy đồ án không cần tài khoản hoặc API key thanh toán/vận chuyển thật. Trong `.env`, đặt:

```dotenv
PAYMENT_DRIVER=mock
VIETTELPOST_DRIVER=mock
VIETTELPOST_WEBHOOK_ENABLED=false
```

- Ở checkout, chọn **Thanh toán online (mô phỏng)** rồi chọn giao dịch thành công hoặc thất bại. Thành công tạo đơn đã thanh toán và giữ đúng quy tắc chỉ xác nhận đơn sau khi có kết quả giao dịch; thất bại không tạo đơn và giải phóng hàng đã giữ chỗ. Cả hai kết quả đều không chuyển tiền thật.
- Ở trang chi tiết đơn trong admin, nút **Tạo vận đơn mô phỏng** sinh mã `VTP-DEMO-...`, link tra cứu nội bộ và ngày giao ước tính sau 3 ngày. Khách có thể mở link tra cứu mà không thấy địa chỉ hay số điện thoại.
- Admin/kho có thể lần lượt chọn các mốc quét vận chuyển trong trang đơn hàng. Hệ thống lưu lịch sử với nguồn **Mô phỏng Viettel Post** và cập nhật trạng thái đơn để demo các bước nhận kiện, trung chuyển, giao hàng, giao lỗi hoặc hoàn hàng.
- Trang checkout, thông tin thanh toán, nhật ký vận chuyển và trang tra cứu đều ghi rõ đây là dữ liệu mô phỏng. Webhook PayOS và Viettel Post không xử lý sự kiện khi ứng dụng đang chạy driver mô phỏng.

Để chuyển sang tích hợp thật, cấu hình `PAYMENT_DRIVER=payos` và `VIETTELPOST_DRIVER=live`, sau đó điền thông tin PayOS, đăng ký webhook và hoàn tất các yêu cầu tài khoản/API Viettel Post nêu bên dưới. Khi bật driver thật, giao diện vận đơn dùng mã do Partner cấp; việc nhập mã thủ công vẫn không tự gọi API tạo vận đơn.

## Việc cần hoàn tất trước khi vận hành production

### Viettel Post

Tài liệu công khai của Viettel Post yêu cầu đối tác đăng ký/cấp tài khoản Partner. Họ hướng dẫn URL Development `https://partnerdev.viettelpost.vn`, Production `https://partner.viettelpost.vn`, lấy token qua API Login/ownerconnect và cấu hình webhook trong Partner. Tài liệu tạo vận đơn, mẫu webhook và mã trạng thái chi tiết nằm trong mục API document của tài khoản Partner; không thể suy ra an toàn khi chưa đăng nhập tài khoản của cửa hàng.

Tạm thời, quản lý tạo đơn trên Partner bằng tay rồi nhập mã vận đơn trong trang admin. Để chạy tự động hoàn toàn, cần lấy từ Viettel Post và triển khai/đối chiếu:

1. Tài khoản Partner, quyền API, thông tin đăng nhập/token và bật quyền production.
2. Mã địa chỉ người gửi, dịch vụ gửi, phương thức thanh toán vận chuyển và cấu hình COD.
3. Payload/mã API tạo vận đơn, báo giá cước, tra cứu hành trình và hủy vận đơn; ứng dụng hiện chưa gọi các API outbound này.
4. Mẫu webhook thật, quy tắc xác thực chữ ký/token, tên trường và bảng mã trạng thái thực tế. Ứng dụng hiện nhận payload theo cấu hình trường và ánh xạ mã trạng thái nhưng chưa thể bật với dữ liệu mặc định trước khi đối chiếu mẫu của Partner.

Ghi chú dữ liệu: checkout hiện nhận mã tỉnh và mã phường nhưng đơn hàng chỉ lưu địa chỉ dạng văn bản/tỉnh thành; chưa lưu đủ mã tỉnh/quận/phường theo cấu trúc Viettel Post. Cửa hàng cũng cần chuẩn hóa địa chỉ gửi, khối lượng/kích thước kiện và quy tắc đóng gói trước khi tích hợp API tạo đơn.

Sau khi đã lấy mẫu, cấu hình trong file `.env` của máy chủ:

```dotenv
VIETTELPOST_WEBHOOK_SECRET=<chuỗi ngẫu nhiên dài, khó đoán>
VIETTELPOST_WEBHOOK_ENABLED=true
VIETTELPOST_WEBHOOK_TRACKING_FIELD=tracking_number
VIETTELPOST_WEBHOOK_ORDER_FIELD=order_number
VIETTELPOST_WEBHOOK_STATUS_FIELD=status
VIETTELPOST_WEBHOOK_EVENT_FIELD=event_id
VIETTELPOST_WEBHOOK_DESCRIPTION_FIELD=description
VIETTELPOST_WEBHOOK_TIME_FIELD=occurred_at
VIETTELPOST_STATUS_MAP_JSON={"<ma-vtp-nhan-hang>":"handed_over","<ma-vtp-trung-chuyen>":"in_transit","<ma-vtp-dang-giao>":"out_for_delivery","<ma-vtp-giao-thanh-cong>":"completed","<ma-vtp-giao-loi>":"delivery_failed","<ma-vtp-dang-hoan>":"returning","<ma-vtp-da-hoan>":"returned"}
```

Đăng ký webhook Partner tới `https://<ten-mien-cua-hang>/webhooks/viettel-post/<VIETTELPOST_WEBHOOK_SECRET>`, thay tên miền và secret bằng giá trị production. Các tên trường phải khớp mẫu Partner; trường có thể là đường dẫn lồng nhau như `data.ORDER_NUMBER`. Chỉ bật sau khi đã xác nhận cơ chế xác thực chính thức với Viettel Post và kiểm thử event lặp, event sai thứ tự, đơn không tồn tại trên môi trường Development. Khi Viettel Post yêu cầu HMAC/header xác thực, phải bổ sung kiểm tra đó trước khi mở endpoint production; secret trong đường dẫn hiện là lớp bảo vệ tạm thời.

### Thanh toán, email và hosting

- Điền PayOS production `PAYOS_CLIENT_ID`, `PAYOS_API_KEY`, `PAYOS_CHECKSUM_KEY`; đăng ký webhook `https://<ten-mien-cua-hang>/thanh-toan/payos/webhook` trong PayOS và chạy giao dịch thử/đối soát hoàn tiền.
- Cấu hình `MAIL_MAILER`, SMTP host/port/user/password, `MAIL_FROM_ADDRESS` và tên miền gửi đã xác thực. `MAIL_MAILER=log` trong file môi trường mẫu chỉ ghi mail vào log, không gửi cho khách.
- Trên máy chủ, cấu hình `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL` HTTPS, cơ sở dữ liệu production, session/cache/queue, quyền ghi `storage` và `bootstrap/cache`, TLS, sao lưu database/tệp và giám sát log.
- Cấu hình cron của Laravel Scheduler (mỗi phút) và worker queue nếu bật tác vụ nền; kiểm tra lệnh `php artisan schedule:list` và `php artisan queue:work` theo chính sách hosting.
- Chạy migration trên database production sau khi sao lưu. Không dùng mật khẩu admin mặc định từ `.env.example`; tạo tài khoản admin riêng và đổi mật khẩu trước khi mở website.

Các mục Viettel Post outbound, xác thực webhook chính thức, khóa PayOS production, thông tin SMTP và hạ tầng hosting cần tài khoản/chứng từ của chủ cửa hàng để hoàn tất. Không bật webhook Viettel Post khi chưa xác nhận mẫu sự kiện thực tế.
