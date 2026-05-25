-- ShareSpace: Restore demo users, listings & rentals
-- Safe to run on a partial DB (uses INSERT IGNORE to skip duplicates)
SET FOREIGN_KEY_CHECKS = 0;

-- -------------------------------------------------------
-- Users (demo password for users 2-6 is: password)
-- -------------------------------------------------------
INSERT IGNORE INTO `users` (`user_id`,`full_name`,`email`,`phone`,`password_hash`,`location`,`role`,`is_verified`,`created_at`) VALUES
(1,'heinrich','hpotgieter0310@gmail.com','0829339132','$2y$10$h3.j0DUkJGwqiyovC0aG9.k5PJSqiu3.JKOcmq1mfrriFknRseWXS','Other','admin',0,'2026-04-12 14:34:45'),
(2,'Sipho Mokoena','sipho@demo.com','0821234567','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Soweto','user',0,'2026-04-12 14:41:25'),
(3,'Nomsa Khumalo','nomsa@demo.com','0837654321','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Alexandra','user',0,'2026-04-12 14:41:25'),
(4,'Thabo Dlamini','thabo@demo.com','0761112233','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Khayelitsha','user',0,'2026-04-12 14:41:25'),
(5,'Zanele Petersen','zanele@demo.com','0849998877','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Diepsloot','user',0,'2026-04-12 14:41:25'),
(6,'Bongani Nkosi','bongani@demo.com','0715556644','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Mitchells Plain','user',0,'2026-04-12 14:41:25'),
(7,'Test User','testuser@sharespace.co.za','0821234567','$2y$10$ZEmJUv9fLiLMP.bqJ8OLXOi3pYw3m.SdCQnfOuPZCBWHm8CcF.UNe','Soweto','user',0,'2026-05-19 17:49:19');

-- -------------------------------------------------------
-- Listings (demo data — 20 items across 4 categories)
-- -------------------------------------------------------
INSERT IGNORE INTO `listings` (`listing_id`,`user_id`,`category_id`,`title`,`description`,`image_path`,`price_per_day`,`availability_status`,`delivery_option`,`created_at`) VALUES
(1,2,1,'Heavy-duty Power Drill','Bosch 18V cordless drill with 2 batteries and charger included. Perfect for home renovations. Good condition, well maintained.','',80.00,'available','collection','2026-04-12 14:41:25'),
(2,3,1,'Angle Grinder','Makita 125mm angle grinder. Includes 5 cutting discs. Great for metal cutting and grinding work.','',65.00,'available','collection','2026-04-12 14:41:25'),
(3,4,1,'Cement Mixer (Mini)','Small electric cement mixer, 60L drum. Ideal for small building projects and repairs. Collect from Khayelitsha.','',200.00,'available','collection','2026-04-12 14:41:25'),
(4,5,1,'Pressure Washer','Karcher K4 pressure washer. Great for cleaning driveways, cars, and walls. Includes 10m hose.','',150.00,'available','collection','2026-04-12 14:41:25'),
(5,6,1,'Scaffolding Set','Aluminium scaffolding set, 3m height. Safe and sturdy. Perfect for painting or roof repairs.','',300.00,'available','collection','2026-04-12 14:41:25'),
(6,2,2,'Plastic Chairs (30 pack)','30 white plastic chairs in excellent condition. Perfect for parties, funerals, or any community event. Delivery available.','',120.00,'available','collection','2026-04-12 14:41:25'),
(7,3,2,'Party Tent (6x6m)','Large white party tent, 6x6 meters. Waterproof. Includes poles and pegs. Fits up to 40 guests comfortably.','',250.00,'available','collection','2026-04-12 14:41:25'),
(8,4,2,'Folding Tables (10 pack)','10 strong folding tables, 1.8m long each. Great for events, catering, or market stalls.','',150.00,'available','collection','2026-04-12 14:41:25'),
(9,5,2,'Chafing Dishes Set (8)','Set of 8 stainless steel chafing dishes with lids and stands. Includes fuel canisters. Perfect for catering.','',180.00,'available','collection','2026-04-12 14:41:25'),
(10,6,2,'Inflatable Jumping Castle','Kids jumping castle with blower. Fits children up to 12 years. Great for birthday parties. Bright colours.','',350.00,'available','collection','2026-04-12 14:41:25'),
(11,2,3,'Sound System + 2 Speakers','Professional 2000W sound system with 2 large speakers, subwoofer, and microphone. Perfect for parties and events.','',350.00,'available','collection','2026-04-12 14:41:25'),
(12,3,3,'DJ Controller & Mixer','Pioneer DJ controller with mixer. Includes all cables. Bluetooth and USB compatible. Great for any event.','',280.00,'available','collection','2026-04-12 14:41:25'),
(13,4,3,'Projector & Screen','Full HD projector with 2m pull-down screen. Perfect for outdoor movie nights, church, or business presentations.','',220.00,'available','collection','2026-04-12 14:41:25'),
(14,5,3,'Generator (3.5kVA)','Petrol generator, 3.5kVA. Handles 4-6 appliances. Perfect for load shedding or outdoor events. Fuel not included.','',400.00,'available','collection','2026-04-12 14:41:25'),
(15,6,3,'LED Party Lights Set','Set of 10 LED colour changing lights with remote. Includes strobe, disco ball, and UV light. Great atmosphere!','',120.00,'available','collection','2026-04-12 14:41:25'),
(16,2,4,'Petrol Lawn Mower','Honda petrol lawn mower. Self-propelled. Cuts up to 1 acre on one tank. Sharp blade, well serviced.','',90.00,'available','collection','2026-04-12 14:41:25'),
(17,3,4,'Electric Hedge Trimmer','Ryobi electric hedge trimmer, 550mm blade. Perfect for shaping hedges and shrubs. 10m cable included.','',55.00,'available','collection','2026-04-12 14:41:25'),
(18,4,4,'Garden Chipper/Shredder','Electric garden shredder for branches up to 40mm. Great for clearing garden waste after pruning.','',130.00,'available','collection','2026-04-12 14:41:25'),
(19,5,4,'Wheelbarrow + Garden Tools','Heavy duty wheelbarrow plus full set of garden tools: spade, fork, rake, hoe. Perfect for a big garden day.','',70.00,'available','collection','2026-04-12 14:41:25'),
(20,6,4,'Water Pump (Submersible)','Submersible water pump for pools, flooded areas or irrigation. 1200W, 20m head. Includes 10m hose.','',110.00,'available','collection','2026-04-12 14:41:25');

-- -------------------------------------------------------
-- Rentals (demo bookings)
-- -------------------------------------------------------
INSERT IGNORE INTO `rentals` (`rental_id`,`listing_id`,`renter_id`,`start_date`,`num_days`,`total_price`,`status`,`booked_at`) VALUES
(1,1,3,'2026-04-10',3,240.00,'completed','2026-04-12 14:41:25'),
(2,7,4,'2026-04-12',2,500.00,'active','2026-04-12 14:41:25'),
(3,11,5,'2026-04-13',1,350.00,'pending','2026-04-12 14:41:25'),
(4,4,6,'2026-04-14',2,300.00,'pending','2026-04-12 14:41:25');

-- Reset AUTO_INCREMENT so new rows don't clash
ALTER TABLE `users`    AUTO_INCREMENT = 20;
ALTER TABLE `listings` AUTO_INCREMENT = 30;
ALTER TABLE `rentals`  AUTO_INCREMENT = 10;

SET FOREIGN_KEY_CHECKS = 1;
