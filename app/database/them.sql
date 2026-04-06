

INSERT INTO products 
(product_id, name, description, category_id, unit, image, current_stock, profit_percent, selling_price, status) 
VALUES

-- macaron
('1', 'Macaron Totoro Xoài', 'Hương xoài chín kết hợp với chút chanh dây chua nhẹ, gợi cảm giác tươi vui và tràn đầy năng lượng. Vị ngọt mát của xoài hòa với chút chua thanh của chanh dây khiến ai thưởng thức cũng thấy như đang đắm mình trong nắng vàng của một sáng mai ấm áp và đầy sức sống.', 1, 'cái', 'public/assets/Img/Macaron/Chu_Totoro.jpg', 50, 20.00, 25000.00, 'AVAILABLE'),
('2', 'Macaron Hoa Dừa', 'Hương dừa thơm nồng kết hợp với chút vị vani nhẹ nhàng, mang đến cảm giác thanh mát và dịu dàng. Vị ngọt mềm mịn của dừa hòa quyện với chút hương vani khiến ai thưởng thức cũng cảm thấy như đang được bao bọc bởi một bầu không khí ấm áp và dễ chịu.', 1, 'cái', 'public/assets/Img/Macaron/Flower_coconut.jpg', 50, 20.00, 25000.00, 'AVAILABLE'),
('3', 'Macaron Sữa Chua Kiwi', 'Chiếc Macaron với hương vị Kiwi Yogurt mang đến vị tươi mát, chua chua ngọt ngọt từ kiwi kết hợp với kem bơ yogurt béo dịu, tạo nên tổng thể hoà quyện và cuốn hút. Một chiếc bánh nhỏ xinh nhưng mang lại cảm giác tươi tắn và vui miệng', 1, 'cái', 'public/assets/Img/Macaron/Kiwi_Yogurt.jpg', 50, 20.00, 25000.00, 'AVAILABLE'),
('4', 'Macaron Xoài Chanh Dây', 'Hương xoài chín kết hợp với chút chanh dây chua nhẹ, gợi cảm giác tươi vui và tràn đầy năng lượng. Vị ngọt mát của xoài hòa với chút chua thanh của chanh dây khiến ai thưởng thức cũng thấy như đang đắm mình trong nắng vàng của một sáng mai ấm áp và đầy sức sống.', 1, 'cái', 'public/assets/Img/Macaron/Mango_Passion_Fruit.jpg', 50, 20.00, 25000.00, 'AVAILABLE'),
('5', 'Macaron Vô Diện', 'Chiếc bánh là sự hòa quyện hoàn hảo giữa khoai lang mật béo mịn và cheesecake ngọt dịu. Vị khoai lang ấm áp, bùi ngọt tự nhiên quyện lấy lớp kem phô mai mềm tan, tạo nên cảm giác vừa thân thuộc vừa mới mẻ. Khi thưởng thức như đưa bạn vào buổi chiều lặng gió, nơi mùi thơm dịu dàng lan trong không khí, và tâm hồn cũng được xoa dịu theo từng lớp vị ngọt ngào ấy.', 1, 'cái', 'public/assets/Img/Macaron/No_Face.jpg', 50, 20.00, 25000.00, 'AVAILABLE'),
('6', 'Macaron Kem Hạt Dẻ', 'Chiếc bánh Macaron mang hương vị ăn một lần rồi dễ tương tư nhen. Phần vỏ mềm xốp ôm trọn phần nhân kem bơ hạt dẻ cười béo bùi, thơm lừng vị hạt, ăn tới đâu là vị hạt dẻ cười lan ra tới đó. Kết hợp thêm với chocolate đen đắng dịu vừa phải, cân lại độ béo nên tổng thể không bị ngọt gắt, ăn vào rất là cuốn', 1, 'cái', 'public/assets/Img/Macaron/Mont_Blanc.jpg', 50, 20.00, 25000.00, 'AVAILABLE'),
('7', 'Macaron Mâm Xôi Hạt Dẻ Cười', 'Một chiếc Macaron với tạo hình chim cánh cụt xinh xắn dành cho những ai thích vị ngọt vừa, có chút chua chịu và bùi béo, siêu thích hợp để tự thưởng cho bản thân hoặc là chia sẻ đến bạn bè và gia đình', 1, 'cái', 'public/assets/Img/Macaron/Raspberry_Pistachio.jpg', 50, 20.00, 25000.00, 'AVAILABLE'),
('8', 'Macaron Kem Caramel', 'là một trong những vị bánh Macaron best seller của Bittersweet với lớp kem bơ vị custard beo béo, nhân caramel đắng nhẹ cùng lớp đường cháy giòn tan.', 1, 'cái', 'public/assets/Img/Macaron/Cream_Brulee.jpg', 50, 20.00, 25000.00, 'AVAILABLE'),


-- bánh sừng bò
('9', 'Bánh Sừng vị Bơ', 'Bánh sừng bò bơ giòn xốp kẹp bơ tươi, phô mai kem và chút mật ong.', 2, 'cái', 'public/assets/Img/Croissant/Avocado_Croissant.jpg', 50, 20.00, 30000.00, 'AVAILABLE'),
('10', 'Bánh Sừng Mallo Socola', 'Bánh sừng bò giòn nhân socola đen tan chảy và marshmallow mềm, phủ bột cacao.', 2, 'cái', 'public/assets/Img/Croissant/Choco_Mallow_Croissant.png', 50, 20.00, 30000.00, 'AVAILABLE'),
('11', 'Bánh Sừng Khủng Long', 'Bánh sừng bò bơ phủ kem hạnh nhân, hạnh nhân lát và đường bột tạo độ giòn.', 2, 'cái', 'public/assets/Img/Croissant/Dinosaur_Almond_Croissant.png', 50, 20.00, 30000.00, 'AVAILABLE'),
('12', 'Bánh Sừng Mật Ong', 'Bánh sừng bò vàng giòn phủ mật ong nguyên chất và hạnh nhân rang.', 2, 'cái', 'public/assets/Img/Croissant/Honey_Almond_Croissant.png', 50, 20.00, 30000.00, 'AVAILABLE'),
('13', 'Bánh Sừng Matcha', 'Bánh sừng bò truyền thống kết hợp bột matcha cao cấp, nhân kem matcha và phủ nhẹ đường bột.', 2, 'cái', 'public/assets/Img/Croissant/Matcha_Croissant.jpg', 50, 20.00, 30000.00, 'AVAILABLE'),
('14', 'Bánh Sừng Truyền Thống', 'Bánh sừng bò kiểu Pháp truyền thống với lớp vỏ giòn và ruột mềm thơm bơ.', 2, 'cái', 'public/assets/Img/Croissant/Plain_Croissant.png', 50, 20.00, 30000.00, 'AVAILABLE'),

-- đồ uống
('15', 'Choco Mallow', 'Socola nóng đậm vị từ cacao Bỉ, sữa tươi và lớp marshmallow mềm bên trên.', 3, 'ly', 'public/assets/Img/Drink/Choco_Mallow.png', 50, 20.00, 35000.00, 'AVAILABLE'),
('16', 'Trà Chanh Mật Ong', 'Trà đen pha cùng lát chanh tươi, mật ong nguyên chất và chút bạc hà mát lạnh.', 3, 'ly', 'public/assets/Img/Drink/Lemon_Tea.png', 50, 20.00, 35000.00, 'AVAILABLE'),
('17', 'Trà Vải', 'Trà xanh hương vải thơm nhẹ, kèm thịt vải tươi.', 3, 'ly', 'public/assets/Img/Drink/Lychee_Tea.png', 50, 20.00, 35000.00, 'AVAILABLE'),
('18', 'Matcha Latte', 'Matcha Nhật Bản cao cấp pha cùng sữa nóng và siro vani nhẹ.', 3, 'ly', 'public/assets/Img/Drink/Matcha_Latte.png', 50, 20.00, 35000.00, 'AVAILABLE'),
('19', 'Matcha Mallow', 'Thức uống matcha kết hợp sữa yến mạch, phủ kem tươi và marshmallow nướng.', 3, 'ly', 'public/assets/Img/Drink/Matcha_Mallow.png', 50, 20.00, 35000.00, 'AVAILABLE'),
('20', 'Matcha Misu', 'Thức uống matcha lấy cảm hứng từ tiramisu với lớp kem mascarpone và bột cacao.', 3, 'ly', 'public/assets/Img/Drink/Matcha_Misu.png', 50, 20.00, 35000.00, 'AVAILABLE');