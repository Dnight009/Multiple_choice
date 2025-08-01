<?php
?>
<footer class="footer">
    <div class="footer-container">
        <div class="footer-col">
            <div class="footer-logo">
                <span style="color:#00b894;font-weight:bold;font-size:28px;">vietJack</span>
                <span style="color:#f9a825;font-weight:bold;font-size:22px;">khoahoc</span>
                <span style="color:#f9a825;font-size:22px;">&#9997;</span>
            </div>
            <div class="footer-social">
                <a href="#"><img src="https://cdn-icons-png.flaticon.com/512/733/733547.png" alt="Facebook" width="32"></a>
                <a href="#"><img src="https://cdn-icons-png.flaticon.com/512/1384/1384060.png" alt="YouTube" width="32"></a>
            </div>
            <div class="footer-contact">
                <div><span style="color:#f9a825;">&#x1F4CD;</span> Tầng 2, Tòa G5, Five Star, số 2 Kim Giang, Phường Kim Giang, quận Thanh Xuân, Hà Nội.</div>
                <div>Phone: <span style="color:#fff;">084 283 45 85</span></div>
                <div>Email: <span style="color:#fff;">vietjackteam@gmail.com</span></div>
            </div>
        </div>
        <div class="footer-col">
            <div class="footer-title">LIÊN KẾT</div>
            <ul>
                <li><a href="#">Đội ngũ người tạo bộ đề tích cực</a></li>
                <li><a href="#">Danh sách Câu hỏi trắc nghiệm</a></li>
                <li><a href="#">Bộ đề trắc nghiệm các lớp</a></li>
            </ul>
        </div>
        <div class="footer-col">
            <div class="footer-title">THÔNG TIN TRANG WEB </div>
            <ul>
                <li><a href="#">Giới thiệu công ty</a></li>
                <li><a href="#">Chính sách hoàn học phí</a></li>
                <li><a href="#">Chính sách bảo mật</a></li>
                <li><a href="#">Điều khoản dịch vụ</a></li>
                <li><a href="#">Hướng dẫn thanh toán VNPAY</a></li>
                <li><a href="#">Tuyển dụng - Việc làm</a></li>
                <li><a href="#">Bảo mật thông tin</a></li>
            </ul>
        </div>
        <div class="footer-col">
            <div class="footer-title">TẢI ỨNG DỤNG</div>
            <div class="footer-apps">
                <img src="https://upload.wikimedia.org/wikipedia/commons/7/78/Google_Play_Store_badge_EN.svg" alt="Google Play" height="40">
                <img src="https://developer.apple.com/assets/elements/badges/download-on-the-app-store.svg" alt="App Store" height="40">
            </div>
            <div class="footer-title" style="margin-top:18px;">THANH TOÁN</div>
            <img src="https://sandbox.vnpayment.vn/apis/assets/images/logo_vnpay.png" alt="VNPAY" height="38">
            <hr style="margin:18px 0 10px 0;border:1px solid #444;">
            <div>
                <img src="https://static.vietjack.com/images/fb-vietjack.png" alt="Học cùng Web" width="260">
            </div>
        </div>
    </div>
</footer>
<style>
.footer {
    background: #232323;
    color: #ccc;
    padding: 36px 0 18px 0;
    font-size: 15px;
    border-top: 2px solid #bbb;
}
.footer-container {
    max-width: 1300px;
    margin: 0 auto;
    display: flex;
    flex-wrap: wrap;
    gap: 32px;
    justify-content: space-between;
}
.footer-col {
    flex: 1 1 220px;
    min-width: 220px;
    margin-bottom: 18px;
}
.footer-logo {
    font-size: 26px;
    margin-bottom: 16px;
}
.footer-social a {
    display: inline-block;
    margin-right: 10px;
    margin-bottom: 10px;
}
.footer-contact {
    margin-top: 16px;
    color: #ccc;
    font-size: 15px;
}
.footer-title {
    color: #fff;
    font-weight: bold;
    margin-bottom: 12px;
    font-size: 17px;
}
.footer-col ul {
    list-style: none;
    padding: 0;
    margin: 0;
}
.footer-col ul li {
    margin-bottom: 8px;
}
.footer-col ul li a {
    color: #ccc;
    text-decoration: none;
    transition: color 0.2s;
}
.footer-col ul li a:hover {
    color: #00b894;
    text-decoration: underline;
}
.footer-apps img {
    margin-right: 8px;
    margin-bottom: 8px;
    background: #fff;
    border-radius: 6px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}
@media (max-width: 900px) {
    .footer-container {
        flex-direction: column;
        gap: 0;
    }
    .footer-col {
        margin-bottom: 28px;
    }
}