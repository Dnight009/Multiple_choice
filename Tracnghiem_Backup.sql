SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

CREATE TABLE `account` (
  `IDACC` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `ho_ten` varchar(100) DEFAULT NULL,
  `ngay_sinh` date DEFAULT NULL,
  `gioi_tinh` enum('Nam','Nữ','Khác') DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `quyen` tinyint(4) NOT NULL CHECK (`quyen` in (1,2,3)),
  `ngay_tao` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `account` (`IDACC`, `username`, `email`, `ho_ten`, `ngay_sinh`, `gioi_tinh`, `password`, `quyen`, `ngay_tao`) VALUES
(1, 'Admin', NULL, 'Lê Phạm Nhật Hoàng', '2003-07-27', NULL, 'Admin', 1, '2025-10-27 19:52:04'),
(2, 'Dnight', 'rederword23@gmail.com', 'Từ Minh Gia', '2003-09-09', 'Nam', '$2y$10$.d4TbbmPkXMikgccrNYJFOSeAYk3Z9461Utyn5Tf8W0bbOHxrh.eq', 3, '2025-10-27 19:52:04'),
(3, 'Pright', NULL, 'Hà Thu Huyền', '2003-09-07', NULL, '$2y$10$M/H/uyfxZLIXMOyRD8wckewnjtbsM5SKTqrFnQ7JGHeGmYvHpPPCi', 2, '2025-10-27 19:52:04'),
(4, 'Wight', NULL, 'Văn Cao Sâm', '1999-03-03', NULL, '$2y$10$5h3p/z1AJdDGT5UcW8Kgiujjbx2Merew9jlZDt8uyEKETVWQXnUd6', 2, '2025-10-27 19:52:04'),
(5, 'Ystar003', NULL, NULL, NULL, NULL, '$2y$10$8CZe4J6iR5B1QxfLSSBpEuHDjUeFUIamwYJbGyfQEUmX90OErMUKO', 2, NULL);

CREATE TABLE `cau_hoi` (
  `ID_CH` int(11) NOT NULL,
  `cau_hoi` text DEFAULT NULL,
  `dap_an_1` text DEFAULT NULL,
  `dap_an_2` text DEFAULT NULL,
  `dap_an_3` text DEFAULT NULL,
  `dap_an_4` text DEFAULT NULL,
  `cau_tra_loi_dung` tinyint(4) DEFAULT NULL,
  `ID_TD` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `cau_hoi` (`ID_CH`, `cau_hoi`, `dap_an_1`, `dap_an_2`, `dap_an_3`, `dap_an_4`, `cau_tra_loi_dung`, `ID_TD`) VALUES
(5, '3=1', 'đều đúng', 'sai', 'không có đáp án', 'đúng', 4, 8),
(6, '2+4=', '2', '3', '4', '6', 4, 8),
(7, '3=1', 'đều đúng', 'sai', 'không có đáp án', 'đúng', 4, 9),
(8, '2+4=', '2', '3', '4', '6', 4, 9),
(9, '3=1', 'đều đúng', 'sai', 'không có đáp án', 'đúng', 4, 10),
(10, '2+4=', '2', '3', '4', '6', 4, 10),
(11, '3=1', 'đều đúng', 'sai', 'không có đáp án', 'đúng', 4, 11),
(12, '2+4=', '2', '3', '4', '6', 4, 11),
(13, '3=1', 'đều đúng', 'sai', 'không có đáp án', 'đúng', 4, 12),
(14, '2+4=', '2', '3', '4', '6', 4, 12),
(15, 'ưefgh', '1', '2', '3', '4', 1, 12),
(17, '3=1', 'đều đúng', 'sai', 'không có đáp án', 'đúng', 4, 14),
(18, '2+4=', '2', '3', '4', '6', 4, 14),
(19, '3=1', 'đều đúng', 'sai', 'không có đáp án', 'đúng', 4, 15),
(20, '2+4=', '2', '3', '4', '6', 4, 15),
(21, '3=1', 'đều đúng', 'sai', 'không có đáp án', 'đúng', 4, 16),
(22, '2+4=', '2', '3', '4', '6', 4, 16),
(23, '3=1', 'đều đúng', 'sai', 'không có đáp án', 'đúng', 2, 17),
(24, '2+4=', '2', '3', '4', '6', 4, 17),
(25, '3=1', 'đều đúng', 'sai', 'không có đáp án', 'đúng', 4, 18),
(26, '2+4=', '2', '3', '4', '6', 4, 18);

CREATE TABLE `class` (
  `ID_CLASS` int(11) NOT NULL,
  `IDACC_teach` int(11) DEFAULT NULL,
  `ten_lop_hoc` varchar(255) NOT NULL,
  `ngay_tao` datetime DEFAULT current_timestamp(),
  `trang_thai` enum('đang hoạt động','đã xóa') NOT NULL DEFAULT 'đang hoạt động'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `class` (`ID_CLASS`, `IDACC_teach`, `ten_lop_hoc`, `ngay_tao`, `trang_thai`) VALUES
(1, 2, '12A VMD', '2025-10-29 19:19:15', 'đang hoạt động'),
(2, 2, '11A4 võ minh đức', '2025-10-29 19:22:33', 'đang hoạt động'),
(3, 2, '12A5 Nguyễn Đình Chiểu', '2025-10-29 19:22:57', 'đã xóa');

CREATE TABLE `class_list` (
  `ID_LIST` int(11) NOT NULL,
  `ID_CLASS` int(11) NOT NULL,
  `IDACC_STUDENT` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `class_list` (`ID_LIST`, `ID_CLASS`, `IDACC_STUDENT`) VALUES
(3, 2, 3),
(2, 2, 4);

CREATE TABLE `contribute_ideas` (
  `ID_IDEAS` int(11) NOT NULL,
  `IDACC` int(11) NOT NULL,
  `noi_dung_y_kien` text NOT NULL,
  `ngay_dang` datetime DEFAULT current_timestamp(),
  `status` enum('chờ xử lý','đã chấp nhận','không chấp nhận') NOT NULL DEFAULT 'chờ xử lý',
  `ghi_chu` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `contribute_ideas` (`ID_IDEAS`, `IDACC`, `noi_dung_y_kien`, `ngay_dang`, `status`, `ghi_chu`) VALUES
(1, 3, 'nội dung test', '2025-10-27 18:54:30', 'đã chấp nhận', 'ok đã xử lý nha bạn'),
(2, 3, 'sdfghjkllkjhgfdsadfghjk', '2025-11-15 15:29:40', 'không chấp nhận', 'không đồng ý, không có lý do gì cả');

CREATE TABLE `conversations` (
  `ID_CONVERSATION` int(11) NOT NULL,
  `conversation_name` varchar(255) DEFAULT NULL,
  `is_group` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `conversations` (`ID_CONVERSATION`, `conversation_name`, `is_group`, `created_at`) VALUES
(1, NULL, 0, '2025-11-15 14:51:32'),
(2, NULL, 0, '2025-11-15 14:53:40'),
(3, NULL, 0, '2025-11-15 14:57:53');

CREATE TABLE `conversation_participants` (
  `ID_CP` int(11) NOT NULL,
  `ID_CONVERSATION` int(11) NOT NULL,
  `IDACC` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `conversation_participants` (`ID_CP`, `ID_CONVERSATION`, `IDACC`) VALUES
(1, 1, 2),
(2, 1, 3),
(3, 2, 3),
(4, 2, 4),
(5, 3, 2),
(6, 3, 4);

CREATE TABLE `de_thi_lop` (
  `ID_ASSIGN` int(11) NOT NULL,
  `ID_TD` int(11) NOT NULL,
  `ID_CLASS` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `de_thi_lop` (`ID_ASSIGN`, `ID_TD`, `ID_CLASS`) VALUES
(3, 16, 1),
(4, 16, 2),
(11, 17, 1),
(12, 17, 2);

CREATE TABLE `hang_muc_de_thi` (
  `ID_GAN_DE` int(11) NOT NULL,
  `ID_HANG_MUC` int(11) NOT NULL,
  `ID_TD` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `hang_muc_de_thi` (`ID_GAN_DE`, `ID_HANG_MUC`, `ID_TD`) VALUES
(2, 1, 9),
(1, 1, 14);

CREATE TABLE `hang_muc_diem` (
  `ID_HANG_MUC` int(11) NOT NULL,
  `IDACC_teach` int(11) NOT NULL,
  `ten_hang_muc` varchar(255) NOT NULL,
  `quy_tac_tinh` enum('trungbinh','caonhat','duynhat') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `hang_muc_diem` (`ID_HANG_MUC`, `IDACC_teach`, `ten_hang_muc`, `quy_tac_tinh`) VALUES
(1, 2, 'KT 15 phút', 'caonhat');

CREATE TABLE `ketqua_lambai` (
  `ID_DIEM` int(11) NOT NULL,
  `ID_TD` int(11) NOT NULL,
  `IDACC` int(11) NOT NULL,
  `diem` decimal(5,2) NOT NULL,
  `tong_thoi_gian_lam_bai` int(11) DEFAULT NULL,
  `thoi_gian_nop_bai` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `ketqua_lambai` (`ID_DIEM`, `ID_TD`, `IDACC`, `diem`, `tong_thoi_gian_lam_bai`, `thoi_gian_nop_bai`) VALUES
(3, 17, 3, 0.00, 6, '2025-10-31 13:49:17'),
(4, 17, 3, 0.00, 284, '2025-10-31 13:53:55'),
(5, 14, 3, 0.00, 5, '2025-11-06 17:02:30'),
(6, 14, 3, 0.00, 67, '2025-11-06 17:03:32'),
(7, 14, 3, 0.00, 107, '2025-11-06 17:04:12'),
(8, 14, 3, 0.00, 148, '2025-11-06 17:04:53'),
(9, 14, 3, 0.00, 172, '2025-11-06 17:05:17');

CREATE TABLE `messages` (
  `ID_MESSAGE` int(11) NOT NULL,
  `ID_CONVERSATION` int(11) NOT NULL,
  `IDACC_sender` int(11) NOT NULL,
  `message_content` text NOT NULL,
  `sent_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `messages` (`ID_MESSAGE`, `ID_CONVERSATION`, `IDACC_sender`, `message_content`, `sent_at`) VALUES
(1, 1, 2, 'chào em, em chưa làm bài tập. em có việc gì bận à ?', '2025-11-15 14:51:55'),
(2, 1, 3, 'dạ không ạ. em đang làm đầy đủ ạ', '2025-11-15 14:52:36'),
(3, 2, 3, 'hello', '2025-11-15 14:53:43'),
(4, 1, 2, 'vậy tốt', '2025-11-15 14:54:35'),
(5, 3, 2, 'hé llu làm bài chưa girl?', '2025-11-15 14:58:06');

CREATE TABLE `ten_de` (
  `ID_TD` int(11) NOT NULL,
  `ten_de` varchar(255) NOT NULL,
  `trinh_do` enum('de','binhthuong','kho','nangcao','tonghop') NOT NULL,
  `lop_hoc` tinyint(4) NOT NULL,
  `IDACC` int(11) NOT NULL,
  `ngay_tao` datetime NOT NULL DEFAULT current_timestamp(),
  `thoi_luong_phut` int(11) NOT NULL DEFAULT 45,
  `thoi_gian_bat_dau` datetime DEFAULT NULL,
  `thoi_gian_ket_thuc` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `ten_de` (`ID_TD`, `ten_de`, `trinh_do`, `lop_hoc`, `IDACC`, `ngay_tao`, `thoi_luong_phut`, `thoi_gian_bat_dau`, `thoi_gian_ket_thuc`) VALUES
(8, 'ádfgh', 'de', 1, 2, '2025-10-21 20:52:40', 45, NULL, NULL),
(9, 'ádfgh', 'de', 1, 2, '2025-10-21 20:57:02', 45, NULL, NULL),
(10, 'defhao', 'de', 1, 2, '2024-10-21 20:59:08', 45, NULL, NULL),
(11, 'toán 2', 'de', 2, 2, '2025-10-27 18:36:04', 45, NULL, NULL),
(12, 'toán 1', 'de', 2, 2, '2025-10-27 20:45:36', 45, NULL, NULL),
(14, 'GÁN TEST', 'kho', 6, 2, '2025-10-31 19:02:25', 45, NULL, NULL),
(15, 'GÁN TEST 2', 'kho', 6, 2, '2025-10-31 19:08:02', 30, NULL, NULL),
(16, 'GÁN TEST 3', 'nangcao', 6, 2, '2025-10-31 19:12:10', 10, NULL, NULL),
(17, 'GÁN TEST 4', 'nangcao', 6, 2, '2025-10-31 19:16:49', 16, '2000-10-10 00:00:00', '2025-10-10 00:00:00'),
(18, 'GÁN TEST Ngày Giờ', 'de', 1, 2, '2025-11-10 18:49:13', 10, '2000-10-10 00:00:00', '2025-02-12 12:00:00');

ALTER TABLE `account`
  ADD PRIMARY KEY (`IDACC`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

ALTER TABLE `cau_hoi`
  ADD PRIMARY KEY (`ID_CH`),
  ADD KEY `ID_TD` (`ID_TD`);

ALTER TABLE `class`
  ADD PRIMARY KEY (`ID_CLASS`),
  ADD KEY `IDACC_teach` (`IDACC_teach`);

ALTER TABLE `class_list`
  ADD PRIMARY KEY (`ID_LIST`),
  ADD UNIQUE KEY `uk_student_class` (`ID_CLASS`,`IDACC_STUDENT`),
  ADD KEY `IDACC_STUDENT` (`IDACC_STUDENT`);

ALTER TABLE `contribute_ideas`
  ADD PRIMARY KEY (`ID_IDEAS`),
  ADD KEY `IDACC` (`IDACC`);

ALTER TABLE `conversations`
  ADD PRIMARY KEY (`ID_CONVERSATION`);

ALTER TABLE `conversation_participants`
  ADD PRIMARY KEY (`ID_CP`),
  ADD UNIQUE KEY `uk_user_conversation` (`ID_CONVERSATION`,`IDACC`),
  ADD KEY `IDACC` (`IDACC`);

ALTER TABLE `de_thi_lop`
  ADD PRIMARY KEY (`ID_ASSIGN`),
  ADD UNIQUE KEY `uk_de_thi_lop` (`ID_TD`,`ID_CLASS`),
  ADD KEY `ID_CLASS` (`ID_CLASS`);

ALTER TABLE `hang_muc_de_thi`
  ADD PRIMARY KEY (`ID_GAN_DE`),
  ADD UNIQUE KEY `uk_hangmuc_dethi` (`ID_HANG_MUC`,`ID_TD`),
  ADD KEY `ID_TD` (`ID_TD`);

ALTER TABLE `hang_muc_diem`
  ADD PRIMARY KEY (`ID_HANG_MUC`),
  ADD KEY `IDACC_teach` (`IDACC_teach`);

ALTER TABLE `ketqua_lambai`
  ADD PRIMARY KEY (`ID_DIEM`),
  ADD KEY `IDACC` (`IDACC`),
  ADD KEY `ID_TD` (`ID_TD`);

ALTER TABLE `messages`
  ADD PRIMARY KEY (`ID_MESSAGE`),
  ADD KEY `ID_CONVERSATION` (`ID_CONVERSATION`),
  ADD KEY `IDACC_sender` (`IDACC_sender`);

ALTER TABLE `ten_de`
  ADD PRIMARY KEY (`ID_TD`),
  ADD KEY `IDACC` (`IDACC`);

ALTER TABLE `account`
  MODIFY `IDACC` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

ALTER TABLE `cau_hoi`
  MODIFY `ID_CH` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

ALTER TABLE `class`
  MODIFY `ID_CLASS` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

ALTER TABLE `class_list`
  MODIFY `ID_LIST` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

ALTER TABLE `contribute_ideas`
  MODIFY `ID_IDEAS` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

ALTER TABLE `conversations`
  MODIFY `ID_CONVERSATION` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

ALTER TABLE `conversation_participants`
  MODIFY `ID_CP` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

ALTER TABLE `de_thi_lop`
  MODIFY `ID_ASSIGN` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

ALTER TABLE `hang_muc_de_thi`
  MODIFY `ID_GAN_DE` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

ALTER TABLE `hang_muc_diem`
  MODIFY `ID_HANG_MUC` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `ketqua_lambai`
  MODIFY `ID_DIEM` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

ALTER TABLE `messages`
  MODIFY `ID_MESSAGE` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

ALTER TABLE `ten_de`
  MODIFY `ID_TD` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

ALTER TABLE `cau_hoi`
  ADD CONSTRAINT `cau_hoi_ibfk_1` FOREIGN KEY (`ID_TD`) REFERENCES `ten_de` (`ID_TD`) ON DELETE CASCADE;

ALTER TABLE `class`
  ADD CONSTRAINT `class_ibfk_1` FOREIGN KEY (`IDACC_teach`) REFERENCES `account` (`IDACC`) ON DELETE SET NULL;

ALTER TABLE `class_list`
  ADD CONSTRAINT `class_list_ibfk_1` FOREIGN KEY (`ID_CLASS`) REFERENCES `class` (`ID_CLASS`) ON DELETE CASCADE,
  ADD CONSTRAINT `class_list_ibfk_2` FOREIGN KEY (`IDACC_STUDENT`) REFERENCES `account` (`IDACC`) ON DELETE CASCADE;

ALTER TABLE `contribute_ideas`
  ADD CONSTRAINT `contribute_ideas_ibfk_1` FOREIGN KEY (`IDACC`) REFERENCES `account` (`IDACC`) ON DELETE CASCADE;

ALTER TABLE `conversation_participants`
  ADD CONSTRAINT `conversation_participants_ibfk_1` FOREIGN KEY (`ID_CONVERSATION`) REFERENCES `conversations` (`ID_CONVERSATION`) ON DELETE CASCADE,
  ADD CONSTRAINT `conversation_participants_ibfk_2` FOREIGN KEY (`IDACC`) REFERENCES `account` (`IDACC`) ON DELETE CASCADE;

ALTER TABLE `de_thi_lop`
  ADD CONSTRAINT `de_thi_lop_ibfk_1` FOREIGN KEY (`ID_TD`) REFERENCES `ten_de` (`ID_TD`) ON DELETE CASCADE,
  ADD CONSTRAINT `de_thi_lop_ibfk_2` FOREIGN KEY (`ID_CLASS`) REFERENCES `class` (`ID_CLASS`) ON DELETE CASCADE;

ALTER TABLE `hang_muc_de_thi`
  ADD CONSTRAINT `hang_muc_de_thi_ibfk_1` FOREIGN KEY (`ID_HANG_MUC`) REFERENCES `hang_muc_diem` (`ID_HANG_MUC`) ON DELETE CASCADE,
  ADD CONSTRAINT `hang_muc_de_thi_ibfk_2` FOREIGN KEY (`ID_TD`) REFERENCES `ten_de` (`ID_TD`) ON DELETE CASCADE;

ALTER TABLE `hang_muc_diem`
  ADD CONSTRAINT `hang_muc_diem_ibfk_1` FOREIGN KEY (`IDACC_teach`) REFERENCES `account` (`IDACC`) ON DELETE CASCADE;

ALTER TABLE `ketqua_lambai`
  ADD CONSTRAINT `ketqua_lambai_ibfk_1` FOREIGN KEY (`IDACC`) REFERENCES `account` (`IDACC`) ON DELETE CASCADE,
  ADD CONSTRAINT `ketqua_lambai_ibfk_2` FOREIGN KEY (`ID_TD`) REFERENCES `ten_de` (`ID_TD`) ON DELETE CASCADE;

ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`ID_CONVERSATION`) REFERENCES `conversations` (`ID_CONVERSATION`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`IDACC_sender`) REFERENCES `account` (`IDACC`) ON DELETE CASCADE;

ALTER TABLE `ten_de`
  ADD CONSTRAINT `ten_de_ibfk_1` FOREIGN KEY (`IDACC`) REFERENCES `account` (`IDACC`);
COMMIT;
