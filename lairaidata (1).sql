-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th10 25, 2025 lúc 07:10 AM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `lairaidata`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `acc`
--

CREATE TABLE `acc` (
  `aid` int(11) NOT NULL,
  `afname` varchar(100) NOT NULL,
  `alname` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','seller','user') NOT NULL DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `acc`
--

INSERT INTO `acc` (`aid`, `afname`, `alname`, `email`, `phone`, `username`, `password`, `role`) VALUES
(1, 'Admin', 'Root', 'admin@ecommerce.com', '0901000000', 'admin', '123456', 'admin'),
(2, 'Nguyễn', 'Văn An', 'seller1@gmail.com', '0901111111', 'seller_an', '123456', 'seller'),
(3, 'Trần', 'Thị Bình', 'seller2@gmail.com', '0901222222', 'seller_binh', '123456', 'seller'),
(4, 'Lê', 'Minh Cường', 'seller3@gmail.com', '0901333333', 'seller_cuong', '123456', 'seller'),
(5, 'Phạm', 'Thị Dung', 'user1@gmail.com', '0902111111', 'user_dung', '123456', 'user'),
(6, 'Hoàng', 'Văn Em', 'user2@gmail.com', '0902222222', 'user_em', '123456', 'user'),
(7, 'Vũ', 'Thị Hoa', 'user3@gmail.com', '0902333333', 'user_hoa', '123456', 'user'),
(8, 'Đỗ', 'Minh Khoa', 'user4@gmail.com', '0902444444', 'user_khoa', '123456', 'user');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `cart`
--

CREATE TABLE `cart` (
  `aid` int(11) NOT NULL,
  `pid` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `cart`
--

INSERT INTO `cart` (`aid`, `pid`, `quantity`) VALUES
(5, 1, 1),
(5, 7, 2),
(5, 18, 1),
(6, 3, 1),
(6, 5, 1),
(6, 11, 1),
(7, 9, 2),
(7, 13, 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `categories`
--

CREATE TABLE `categories` (
  `cid` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `parent_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `categories`
--

INSERT INTO `categories` (`cid`, `name`, `parent_id`) VALUES
(1, 'Điện tử', NULL),
(2, 'Thời trang', NULL),
(3, 'Gia dụng', NULL),
(4, 'Sách & Văn phòng', NULL),
(5, 'Điện thoại', 1),
(6, 'Laptop', 1),
(7, 'Phụ kiện điện tử', 1),
(8, 'Áo nam', 2),
(9, 'Áo nữ', 2),
(10, 'Giày dép', 2),
(11, 'Túi xách', 2),
(12, 'Nội thất', 3),
(13, 'Đồ bếp', 3),
(14, 'Cây cảnh', 3);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `messages`
--

CREATE TABLE `messages` (
  `msg_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `sent_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `messages`
--

INSERT INTO `messages` (`msg_id`, `sender_id`, `receiver_id`, `content`, `sent_at`) VALUES
(1, 5, 2, 'Chào shop, iPhone 15 Pro Max còn màu titan trắng không ạ?', '2025-11-18 19:55:31'),
(2, 2, 5, 'Chào bạn, shop còn hàng màu titan trắng bạn nhé. Bạn đặt hàng luôn được không?', '2025-11-18 20:56:06'),
(3, 5, 2, 'Vâng, cho mình đặt 1 máy. Khi nào ship được ạ?', '2025-11-19 19:56:06'),
(4, 2, 5, 'Shop sẽ ship trong hôm nay, mai bạn nhận được hàng nhé. Cảm ơn bạn!', '2025-11-19 19:56:06'),
(5, 6, 3, 'Giày Nike AF1 size 42 còn không shop?', '2025-11-11 19:56:06'),
(6, 3, 6, 'Hi bạn, size 42 shop đang hết hàng. Dự kiến về sau 3 ngày nữa bạn nhé', '2025-11-12 19:56:06'),
(7, 6, 3, 'Ok, mình đợi shop nhập về. Báo mình nhé!', '2025-11-12 19:56:06'),
(8, 7, 4, 'Shop ơi, cây kim ngân còn cao hơn 50cm không?', '2025-11-20 19:58:58'),
(9, 4, 7, 'Dạ shop có cây cao 60-70cm giá 350k bạn nhé. Bạn có nhu cầu không?', '2025-11-20 19:58:58'),
(10, 7, 4, 'Oke, mình lấy 2 cây 60-70cm nhé. Ship COD được không?', '2025-11-20 19:58:58'),
(11, 4, 7, 'Được bạn, shop sẽ ship COD cho bạn. Cảm ơn bạn đã ủng hộ!', '2025-11-20 19:58:58');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `orders`
--

CREATE TABLE `orders` (
  `oid` int(11) NOT NULL,
  `aid` int(11) NOT NULL,
  `order_date` datetime NOT NULL DEFAULT current_timestamp(),
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','paid','shipped','completed','cancelled') NOT NULL DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `orders`
--

INSERT INTO `orders` (`oid`, `aid`, `order_date`, `total_amount`, `status`) VALUES
(1, 5, '2025-11-13 19:44:03', 3190000.00, 'completed'),
(2, 5, '2025-11-14 19:44:03', 5990000.00, 'paid'),
(3, 6, '2025-11-18 19:44:03', 30990000.00, 'shipped'),
(4, 7, '2025-11-19 19:44:03', 890000.00, 'completed'),
(5, 7, '2025-11-21 19:44:03', 1840000.00, 'pending');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `order_items`
--

CREATE TABLE `order_items` (
  `oid` int(11) NOT NULL,
  `pid` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `order_items`
--

INSERT INTO `order_items` (`oid`, `pid`, `quantity`, `price`) VALUES
(1, 8, 1, 199000.00),
(1, 11, 1, 2990000.00),
(2, 5, 1, 5990000.00),
(3, 3, 1, 32990000.00),
(3, 6, 1, 2390000.00),
(4, 10, 1, 890000.00),
(5, 19, 10, 1800000.00);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `products`
--

CREATE TABLE `products` (
  `pid` int(11) NOT NULL,
  `sid` int(11) NOT NULL,
  `cid` int(11) DEFAULT NULL,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `main_image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `products`
--

INSERT INTO `products` (`pid`, `sid`, `cid`, `name`, `description`, `price`, `stock`, `main_image`) VALUES
(1, 1, 5, 'iPhone 15 Pro Max 256GB', 'Chip A17 Pro, Camera 48MP, Titanium, Dynamic Island, Màn hình 6.7 inch Super Retina XDR', 29990000.00, 15, '/images/iphone15.jpg'),
(2, 1, 5, 'Samsung Galaxy S24 Ultra', 'Snapdragon 8 Gen 3, Camera 200MP, Bút S Pen, Màn hình 6.8 inch AMOLED 2X', 27990000.00, 20, '/images/samsung-s24.jpg'),
(3, 1, 6, 'MacBook Air M3 13 inch', 'Chip M3 8-core, RAM 16GB, SSD 512GB, Màn hình Liquid Retina 13.6 inch', 32990000.00, 10, '/images/macbook-air.jpg'),
(4, 1, 6, 'Dell XPS 15 9530', 'Intel Core i7-13700H, RTX 4060, RAM 32GB, SSD 1TB, Màn hình 15.6 inch OLED 3.5K', 45990000.00, 8, '/images/dell-xps15.jpg'),
(5, 1, 7, 'AirPods Pro 2', 'Chống ồn chủ động, Chip H2, USB-C, Âm thanh Spatial Audio', 5990000.00, 50, '/images/logitech-mx.jpg'),
(6, 1, 7, 'Chuột Logitech MX Master 3S', 'Wireless, Bluetooth + USB-C, 8000 DPI, Pin 70 ngày', 2390000.00, 30, '/images/shirt-oxford.jpg'),
(7, 2, 8, 'Áo sơ mi nam Oxford', '100% cotton, form slim fit, nhiều màu sắc, phong cách công sở', 350000.00, 100, '/images/tshirt-basic.jpg'),
(8, 2, 8, 'Áo thun nam basic Uniqlo', 'Vải voan nhẹ, họa tiết hoa nhí, thiết kế xòe nhẹ, phong cách vintage', 199000.00, 200, '/images/dress-maxi.jpg'),
(9, 2, 9, 'Váy maxi hoa nữ', 'Vải voan nhẹ, họa tiết hoa nhí, thiết kế xòe nhẹ, phong cách vintage', 450000.00, 80, '/images/blazer-women.jpg'),
(10, 2, 9, 'Áo blazer nữ công sở', 'Chất liệu cao cấp, form chuẩn, phù hợp môi trường văn phòng', 890000.00, 45, '/images/nike-af1.jpg'),
(11, 2, 10, 'Giày sneaker Nike Air Force 1', 'Da cao cấp, đế cao su bền, phong cách streetwear cổ điển', 2990000.00, 60, '/images/adidas-slides.jpg'),
(12, 2, 10, 'Dép Adidas Adilette', 'Dép quai ngang, êm ái, chống nước, phù hợp đi biển, bể bơi', 890000.00, 120, '/images/bag-ck.jpg'),
(13, 2, 11, 'Túi xách nữ Charles & Keith', 'Da PU cao cấp, nhiều ngăn, quai xách + đeo chéo, size 25x18cm', 1590000.00, 35, '/images/desk-wood.jpg'),
(14, 3, 12, 'Bàn làm việc gỗ tự nhiên', 'Gỗ sồi 1.2m x 0.6m, thiết kế tối giản Scandinavian, chống trầy xước', 3990000.00, 12, '/images/desk-wood.jpg'),
(15, 3, 12, 'Ghế gaming DXRacer', 'Da PU cao cấp, nâng hạ khí nén, tựa lưng 135 độ, tải trọng 150kg', 5490000.00, 18, '/images/gaming-chair.jpg'),
(16, 3, 13, 'Nồi cơm điện Panasonic 1.8L', 'Công nghệ nấu IH, lòng nồi chống dính, 6 chế độ nấu, hẹn giờ 24h', 2890000.00, 25, '/images/rice-cooker.jpg'),
(17, 3, 13, 'Bộ nồi inox 304 Happycook', 'Inox 3 đáy, 5 món, dùng được bếp từ, tặng kèm vá múc', 1690000.00, 40, '/images/pot-set.jpg'),
(18, 3, 14, 'Cây kim ngân', 'Chiều cao 40-50cm, dễ chăm sóc, hợp phong thủy, tặng chậu sứ', 250000.00, 100, '/images/plant-kim-ngan.jpg'),
(19, 3, 14, 'Cây lưỡi hổ', 'Lọc không khí, chịu hạn tốt, chiều cao 30-40cm, chậu nhựa composite', 180000.00, 150, '/images/plant-snake.jpg');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `product_images`
--

CREATE TABLE `product_images` (
  `img_id` int(11) NOT NULL,
  `pid` int(11) NOT NULL,
  `img_url` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `product_images`
--

INSERT INTO `product_images` (`img_id`, `pid`, `img_url`) VALUES
(1, 1, '/images/iphone15-1.jpg'),
(2, 1, '/images/iphone15-2.jpg'),
(3, 1, '/images/iphone15-3.jpg'),
(4, 3, '/images/macbook-air-1.jpg'),
(5, 3, '/images/macbook-air-2.jpg'),
(6, 7, '/images/shirt-oxford-blue.jpg'),
(7, 7, '/images/shirt-oxford-white.jpg'),
(8, 11, '/images/nike-af1-black.jpg'),
(9, 11, '/images/nike-af1-detail.jpg'),
(10, 14, '/images/desk-wood-1.jpg'),
(11, 14, '/images/desk-wood-2.jpg');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `reviews`
--

CREATE TABLE `reviews` (
  `review_id` int(11) NOT NULL,
  `pid` int(11) NOT NULL,
  `aid` int(11) NOT NULL,
  `rating` int(11) NOT NULL,
  `comment` text DEFAULT NULL,
  `review_date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `reviews`
--

INSERT INTO `reviews` (`review_id`, `pid`, `aid`, `rating`, `comment`, `review_date`) VALUES
(1, 1, 5, 5, 'Sản phẩm chính hãng, đóng gói cẩn thận. Màn hình đẹp, camera chụp ảnh xuất sắc!', '2025-11-12 19:49:49'),
(2, 1, 6, 4, 'Máy xài mượt, pin trâu. Trừ 1 sao vì giá hơi cao', '2025-11-19 19:49:49'),
(3, 3, 6, 5, 'Máy mỏng nhẹ, hiệu năng M3 quá mạnh. Lập trình, thiết kế mượt mà. Highly recommend!', '2025-11-19 19:50:42'),
(4, 7, 5, 5, 'Vải cotton mềm, form đẹp, giá cả hợp lý. Đã mua thêm 2 màu nữa', '2025-11-18 19:51:30'),
(5, 7, 8, 4, 'Chất lượng tốt nhưng size hơi nhỏ so với size chart', '2025-11-17 19:51:50'),
(6, 11, 5, 5, 'Giày đẹp, đi êm chân, real 100%. Ship nhanh, đóng gói chắc chắn', '2025-11-11 19:52:08'),
(7, 11, 7, 5, 'Chất lượng không có gì phải chê. Phối đồ đa năng', '2025-11-10 19:52:27'),
(8, 10, 7, 5, 'Áo đẹp lắm, may chuẩn form, chất vải cao cấp. Đi làm sang chảnh!', '2025-11-06 19:52:49'),
(9, 14, 6, 4, 'Bàn đẹp, gỗ thật, chắc chắn. Trừ 1 sao vì lắp ráp hơi khó', '2025-11-15 19:53:41'),
(10, 18, 5, 5, 'Cây tươi, lá xanh mướt. Chậu đẹp, giá rẻ. Sẽ ủng hộ shop tiếp!', '2025-11-14 19:53:41');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `shops`
--

CREATE TABLE `shops` (
  `sid` int(11) NOT NULL,
  `aid` int(11) NOT NULL,
  `shop_name` varchar(100) NOT NULL,
  `description` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `shops`
--

INSERT INTO `shops` (`sid`, `aid`, `shop_name`, `description`) VALUES
(1, 2, 'Tech Store An', 'Chuyên điện thoại, laptop, phụ kiện công nghệ chính hãng'),
(2, 3, 'Fashion Binh Shop', 'Thời trang nam nữ, túi xách, giày dép trendy'),
(3, 4, 'Home & Garden Cường', 'Đồ gia dụng, nội thất, cây cảnh cho ngôi nhà đẹp');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `wishlist`
--

CREATE TABLE `wishlist` (
  `aid` int(11) NOT NULL,
  `pid` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `wishlist`
--

INSERT INTO `wishlist` (`aid`, `pid`) VALUES
(5, 2),
(5, 4),
(5, 15),
(6, 1),
(6, 14),
(6, 16),
(7, 9),
(7, 11),
(7, 13),
(7, 18),
(8, 3),
(8, 5),
(8, 7);

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `acc`
--
ALTER TABLE `acc`
  ADD PRIMARY KEY (`aid`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `phone` (`phone`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Chỉ mục cho bảng `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`aid`,`pid`),
  ADD KEY `_PK_cart_products` (`pid`);

--
-- Chỉ mục cho bảng `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`cid`),
  ADD KEY `_PK_categories_categories` (`parent_id`);

--
-- Chỉ mục cho bảng `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`msg_id`),
  ADD KEY `_PK_messages_accounts` (`sender_id`),
  ADD KEY `_PK_messages_receiver` (`receiver_id`);

--
-- Chỉ mục cho bảng `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`oid`),
  ADD KEY `_PK_orders_acc` (`aid`);

--
-- Chỉ mục cho bảng `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`oid`,`pid`),
  ADD KEY `_PK_order_items_products` (`pid`);

--
-- Chỉ mục cho bảng `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`pid`),
  ADD KEY `_PK_products_shops` (`sid`),
  ADD KEY `_PK_products_categories` (`cid`);

--
-- Chỉ mục cho bảng `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`img_id`),
  ADD KEY `_PK_product_images_products` (`pid`);

--
-- Chỉ mục cho bảng `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD KEY `_PK_reviews_accounts` (`aid`),
  ADD KEY `_PK_reviews_products` (`pid`);

--
-- Chỉ mục cho bảng `shops`
--
ALTER TABLE `shops`
  ADD PRIMARY KEY (`sid`),
  ADD KEY `_PK_shops_acc` (`aid`);

--
-- Chỉ mục cho bảng `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`aid`,`pid`),
  ADD KEY `_PK_wishlist_products` (`pid`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `acc`
--
ALTER TABLE `acc`
  MODIFY `aid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT cho bảng `categories`
--
ALTER TABLE `categories`
  MODIFY `cid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT cho bảng `messages`
--
ALTER TABLE `messages`
  MODIFY `msg_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT cho bảng `orders`
--
ALTER TABLE `orders`
  MODIFY `oid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT cho bảng `products`
--
ALTER TABLE `products`
  MODIFY `pid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT cho bảng `product_images`
--
ALTER TABLE `product_images`
  MODIFY `img_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT cho bảng `reviews`
--
ALTER TABLE `reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT cho bảng `shops`
--
ALTER TABLE `shops`
  MODIFY `sid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `_PK_cart_acc` FOREIGN KEY (`aid`) REFERENCES `acc` (`aid`) ON DELETE CASCADE,
  ADD CONSTRAINT `_PK_cart_products` FOREIGN KEY (`pid`) REFERENCES `products` (`pid`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `_PK_categories_categories` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`cid`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `_PK_messages_accounts` FOREIGN KEY (`sender_id`) REFERENCES `acc` (`aid`) ON DELETE CASCADE,
  ADD CONSTRAINT `_PK_messages_receiver` FOREIGN KEY (`receiver_id`) REFERENCES `acc` (`aid`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `_PK_orders_acc` FOREIGN KEY (`aid`) REFERENCES `acc` (`aid`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `_PK_order_items_orders` FOREIGN KEY (`oid`) REFERENCES `orders` (`oid`) ON DELETE CASCADE,
  ADD CONSTRAINT `_PK_order_items_products` FOREIGN KEY (`pid`) REFERENCES `products` (`pid`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `_PK_products_categories` FOREIGN KEY (`cid`) REFERENCES `categories` (`cid`) ON DELETE SET NULL,
  ADD CONSTRAINT `_PK_products_shops` FOREIGN KEY (`sid`) REFERENCES `shops` (`sid`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `_PK_product_images_products` FOREIGN KEY (`pid`) REFERENCES `products` (`pid`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `_PK_reviews_accounts` FOREIGN KEY (`aid`) REFERENCES `acc` (`aid`) ON DELETE CASCADE,
  ADD CONSTRAINT `_PK_reviews_products` FOREIGN KEY (`pid`) REFERENCES `products` (`pid`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `shops`
--
ALTER TABLE `shops`
  ADD CONSTRAINT `_PK_shops_acc` FOREIGN KEY (`aid`) REFERENCES `acc` (`aid`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `wishlist`
--
ALTER TABLE `wishlist`
  ADD CONSTRAINT `_PK_wishlist_acc` FOREIGN KEY (`aid`) REFERENCES `acc` (`aid`) ON DELETE CASCADE,
  ADD CONSTRAINT `_PK_wishlist_products` FOREIGN KEY (`pid`) REFERENCES `products` (`pid`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
