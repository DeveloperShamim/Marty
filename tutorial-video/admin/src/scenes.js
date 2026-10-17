// What happens on screen, line by line of the voiceover. C(n) = start of SRT cue n, E(n) = its end.
// Boxes are "screen.name" from screens.json (positions of real buttons in the captured admin pages).
({ skipped, tl, $, world, box, union, pad, cam, camBrowser, show, scrollTo, scrollToBox, cursor, click, tap, hideCursor, ring, type, toast, C, E, VX, VY, VW, VH }) => {
  const GREEN = '#28a745', BRAND = '#8b1e33', GOLD = '#c9902f', BLUE = '#2f6fec';

  // ---- the long admin menu: its own scrolling layer for items below the fold ----
  const NAV = { navOrders: 213, navCourier: 257, navPos: 301, navProducts: 385, navInventory: 429, navCatalog: 473, navReviews: 517,
    navHomepage: 601, navCoupons: 773, navPromotions: 817, navCarts: 861, navAnalytics: 945, navExpenses: 989, navCustomers: 1073,
    navStaff: 1117, navSettings: 1231, navIntegrations: 1275 };
  const overlay = $('sidebarOverlay');
  // Opens a page from the menu: scrolls the menu if needed, moves the pointer there and clicks.
  function nav(t, name, screen, opts = {}) {
    camBrowser(t - 0.3, 0.9);
    const y = NAV[name];
    const s = y > 760 ? y - 560 : 0;
    if (s) {
      tl.set(overlay, { y: 0 }, t);
      tl.to(overlay, { opacity: 1, duration: 0.25 }, t);
      tl.to(overlay, { y: -s, duration: 0.9, ease: 'power2.inOut' }, t + 0.2);
    }
    const b = { x: VX + 33, y: VY + y - s, w: 222, h: 40 };
    tap(t + (s ? 0.6 : 0), b, 0.8);
    show(t + (s ? 1.6 : 1.0), screen, opts.y || 0);
    if (s) { tl.to(overlay, { opacity: 0, duration: 0.25 }, t + 1.6); hideCursor(t + 2.2); }
  }

  // ================= 1. Intro: laptop and phone (0 – 14.2 s) =================
  const OVER = { x: -190, y: -40, w: 2330, h: 1110 };
  tl.set(world, { x: 960 - (OVER.x + OVER.w / 2) * 0.8, y: 470 - (OVER.y + OVER.h / 2) * 0.8, scale: 0.8 }, 0);
  tl.set('#browser', { opacity: 0, scale: 0.2, y: 160 }, 0);
  cam(0.2, OVER, 0.86, 3.2);                                  // slow push-in
  cam(C(2), { x: -60, y: 0, w: 2200, h: 1060 }, 0.9, 4);
  cam(C(3), { x: -40, y: -30, w: 1680, h: 1090 }, 0.95, 1.6); // the computer
  cam(C(4), { x: 1680, y: 140, w: 440, h: 860 }, 0.95, 1.4);  // the phone: same panel
  tl.to('#phoneImg', { opacity: 1, duration: 0.5 }, C(4) + 0.6);
  cam(E(4) + 0.3, { x: -40, y: -30, w: 1680, h: 1090 }, 0.95, 1.4);

  // ================= 2. Open the browser, type /admin (14.8 – 23.5 s) =================
  cursor(C(5), { x: 762, y: 928, w: 52, h: 52 }, 1.1);        // to the browser icon in the dock
  click(C(5) + 1.3);
  tl.to('#browser', { opacity: 1, scale: 1, y: 0, duration: 0.7, ease: 'power3.out' }, C(5) + 1.5);
  tl.to(['#dock', '#menubar'], { opacity: 0, duration: 0.5 }, C(5) + 2.0);   // the browser goes full screen
  cam(C(5) + 2.3, { x: 80, y: 40, w: 900, h: 300 }, 0.95, 1.3); // the address bar
  cursor(C(5) + 2.6, { x: 190, y: 98, w: 400, h: 26 }, 0.8);
  click(C(5) + 3.5);
  tl.to('#url-newtab', { opacity: 0, duration: 0.15 }, C(6) + 0.2);
  type(C(6) + 0.3, { x: 176, y: 97, w: 600, h: 26 }, 'vantbd.com/admin', 2.2, { size: 14, dx: 16, bg: '#f1f1f1', inset: 2, until: C(7) });
  ring(C(6) + 2.6, { x: 176, y: 97, w: 260, h: 26 }, 1.4, 6);
  hideCursor(E(6) - 0.4);
  show(C(7), 'login');
  camBrowser(C(7) - 0.2, 1.2);

  // ================= 3. Log in (24.2 – 31.5 s) =================
  cam(C(7) + 0.9, pad(union('login.email', 'login.signin'), 120), 0.9, 1.2);
  tap(C(7) + 1.0, 'login.email', 0.7);
  type(C(7) + 1.9, 'login.email', 'admin@vantbd.com', 1.3, { size: 16, until: C(9) });
  tap(C(7) + 3.2, 'login.password', 0.5);
  type(C(7) + 3.8, 'login.password', '••••••••••', 0.7, { size: 16, until: C(9) });
  tap(C(8) + 0.2, 'login.signin', 0.8);
  ring(C(8) + 1.0, 'login.signin', 1.4, 6);

  // ================= 4. Dashboard (32.2 – 76.2 s) =================
  show(C(9), 'dashboard');
  hideCursor(C(9));
  camBrowser(C(9), 1.2);
  cam(C(10), pad(union('dashboard.needs', 'dashboard.lowstock'), 40), 0.92, 1.3);
  ring(C(10) + 0.4, pad(union('dashboard.needs', 'dashboard.verify'), 14), E(11) - C(10) - 0.4, 0);
  ring(C(12), [VX + 323, VY + 228, 262, 96], 2.4, 2);          // payments to verify
  ring(C(13), [VX + 596, VY + 228, 262, 96], 2.8, 2);          // risky orders
  ring(C(14), [VX + 871, VY + 228, 262, 96], 3.0, 2);          // carts to call back
  ring(C(15), [VX + 1147, VY + 228, 262, 96], 2.6, 2);         // low stock
  tap(C(16) + 0.2, [VX + 330, VY + 248, 240, 56], 1.0);
  hideCursor(E(16));
  scrollToBox(C(17) - 0.3, 'toship', 1.2);
  cam(C(17) + 0.4, { x: VX + 289, y: box('dashboard.toship').y - 20, w: 1140, h: 330 }, 0.95, 1.2);
  ring(C(18), { x: VX + 289, y: box('dashboard.toship').y + 50, w: 1130, h: 230 }, 3.2, 4);
  scrollToBox(C(19) - 0.3, 'today', 1.1);
  cam(C(19), { x: VX + 289, y: box('dashboard.today').y - 30, w: 1130, h: 180 }, 0.95, 1.1);
  ring(C(19) + 0.3, { x: VX + 289, y: box('dashboard.today').y - 22, w: 360, h: 140 }, 1.5, 4);
  ring(C(19) + 1.6, { x: VX + 671, y: box('dashboard.today').y - 22, w: 360, h: 140 }, 1.5, 4);
  ring(C(20) + 0.3, { x: VX + 1053, y: box('dashboard.today').y - 22, w: 360, h: 140 }, 3.5, 4);
  scrollTo(C(21) - 0.4, 0, 1.0);
  cam(C(21) + 0.2, { x: VX, y: VY + 60, w: 520, h: 800 }, 0.92, 1.3);   // the menu
  ring(C(21) + 0.8, 'dashboard.sidebar', 3.2, 4);
  cam(C(22), { x: VX + 700, y: VY - 10, w: 720, h: 260 }, 0.95, 1.2);  // the page search
  tap(C(22) + 0.4, 'dashboard.search', 0.8);
  type(C(22) + 1.4, 'dashboard.search', 'Orders', 1.0, { size: 15, dx: 40, bg: '#f4f4f5', coverDx: 30, inset: 4, until: C(22) + 2.6 });
  show(C(22) + 2.6, 'dashboard-search');
  cam(C(22) + 2.7, { x: VX, y: VY - 10, w: 1300, h: 600 }, 0.95, 1.3);

  // ================= 5. Handling a new order (86.5 – 152 s) =================
  show(C(23), 'dashboard');
  camBrowser(C(23), 1.2);
  hideCursor(C(23));
  nav(C(24) + 0.3, 'navOrders', 'orders');
  toast(C(25) + 0.2, '!', BRAND, 'নতুন অর্ডার এসেছে', '#VB-888252 · Kazi Mahmud · ৳1,230', 3.4);
  ring(C(25) + 0.4, [VX + 33, VY + 213, 222, 40], 3, 4);
  cam(C(26) - 0.2, { x: VX + 280, y: VY + 220, w: 1150, h: 320 }, 0.95, 1.2);
  tap(C(26) + 0.2, 'orders.pendingTab', 0.8);
  show(C(26) + 1.2, 'orders-pending');
  ring(C(27) + 0.2, 'orders-pending.row', 3.8, 4);
  cam(C(28) - 0.2, { x: VX + 289, y: VY + 380, w: 1140, h: 160 }, 0.95, 1.2);
  tap(C(28) + 1.2, 'orders-pending.open', 1.0);
  show(C(28) + 2.6, 'order');
  camBrowser(C(28) + 2.6, 1.0);
  hideCursor(C(28) + 2.6);
  // customer, phone, address, items
  cam(C(29), { x: VX + 289, y: VY + 430, w: 760, h: 420 }, 0.95, 1.2);
  ring(C(29) + 0.4, 'order.name', 1.6, 8);
  ring(C(29) + 1.8, 'order.phoneBtn', 1.6, 6);
  ring(C(30) + 0.2, [VX + 600, VY + 505, 430, 60], 1.8, 6);       // address
  scrollTo(C(30) + 1.8, 330, 1.0);
  ring(C(30) + 2.6, [VX + 289, VY + 760 - 330, 760, 180], 1.6, 4); // items
  // delivery history
  scrollTo(C(31), 160, 1.0);
  cam(C(31) + 0.3, { x: VX + 289, y: VY + 620 - 160, w: 760, h: 160 }, 0.95, 1.2);
  ring(C(31) + 1.0, [VX + 299, VY + 640 - 160, 740, 100], E(33) - C(31) - 1, 6);
  // call / WhatsApp
  scrollTo(C(34), 0, 0.8);
  cam(C(34) + 0.2, { x: VX + 289, y: VY + 500, w: 760, h: 200 }, 0.95, 1.0);
  tap(C(35) + 0.2, 'order.phoneBtn', 0.8);
  tap(C(35) + 2.2, 'order.whatsapp', 0.7);
  hideCursor(E(35));
  // calls & staff notes
  scrollTo(C(36), 494, 1.1);
  cam(C(36) + 0.4, { x: VX + 1060, y: VY + 960 - 494, w: 360, h: 300 }, 0.85, 1.2);
  ring(C(36) + 1.0, [VX + 1064, VY + 970 - 494, 345, 290], 2.4, 4);
  tap(C(37), 'order.noteBox', 0.7);
  type(C(37) + 0.9, 'order.noteBox', 'কাস্টমার নিশ্চিত করেছেন, কাল পার্সেল নেবেন', 2.0, { size: 14, dx: 12, dy: -14, inset: 4, until: C(39) - 0.5 });
  tap(C(37) + 3.2, 'order.saveNote', 0.6);
  toast(C(38), '✓', GREEN, 'নোট সেভ হয়েছে', 'টিমের সবাই দেখতে পাবেন', 2.4);
  // confirm / reject
  scrollTo(C(39) - 0.4, 0, 1.0);
  cam(C(39), { x: VX + 289, y: VY + 255, w: 1130, h: 120 }, 0.95, 1.1);
  tap(C(39) + 0.8, 'order.confirm', 0.8);
  ring(C(39) + 1.7, 'order.confirm', 1.6, 6);
  cursor(C(40) + 0.3, 'order.reject', 0.8);
  ring(C(41), 'order.reject', 2.4, 6);
  hideCursor(E(41));
  toast(C(42) + 0.2, '✓', GREEN, 'নিরাপদ COD অর্ডার: অটো-কনফার্মড', 'সিস্টেম নিজেই যাচাই করে কনফার্ম করেছে', 7.4);
  camBrowser(C(42), 1.2);

  // ================= 6. bKash / Nagad payment (171.5 – 197.2 s) =================
  show(C(44), 'order-bkash', 860);
  cam(C(45), { x: VX + 289, y: VY + 1060 - 860, w: 760, h: 120 }, 0.92, 1.2);
  ring(C(46), 'order-bkash.sender', 2.0, 8);
  ring(C(46) + 2.1, 'order-bkash.trx', 2.0, 8);
  scrollTo(C(47), 0, 1.0);
  cam(C(47) + 0.4, { x: VX + 289, y: VY + 255, w: 1130, h: 120 }, 0.95, 1.1);
  tap(C(48), 'order-bkash.verify', 0.8);
  toast(C(48) + 1.0, '✓', GREEN, 'পেমেন্ট যাচাই হয়েছে', '#VB-579944 · bKash · ৳3,530', 2.6);
  show(C(49), 'dashboard');
  cam(C(49) + 0.2, { x: VX + 300, y: VY + 300, w: 1120, h: 120 }, 0.92, 1.2);
  ring(C(49) + 0.8, pad(union('dashboard.approvals', 'dashboard.verify'), 10), 3.2, 4);
  hideCursor(C(49));

  // ================= 7. Edit customer details (197.8 – 208.5 s) =================
  show(C(50), 'order');
  cam(C(50) + 0.2, { x: VX + 289, y: VY + 430, w: 760, h: 300 }, 0.95, 1.1);
  tap(C(51), 'order.edit', 0.8);
  show(C(51) + 1.2, 'order-edit');
  camBrowser(C(51) + 1.2, 1.0);
  cam(C(52), { x: VX + 460, y: VY + 210, w: 520, h: 490 }, 0.95, 1.1);
  ring(C(52) + 0.3, [VX + 484, VY + 290, 472, 300], 1.8, 6);
  tap(C(52) + 2.0, 'order-edit.save', 0.7);
  show(E(52), 'order');

  // ================= 8. Send to the courier, label and invoice (209.2 – 241.8 s) =================
  camBrowser(C(53), 1.0);
  scrollTo(C(53) + 0.4, 420, 1.0);
  cam(C(54), { x: VX + 1060, y: VY + 740 - 420, w: 360, h: 140 }, 0.85, 1.2);
  ring(C(55) + 0.4, 'order.send', 2.6, 6);
  tap(C(55) + 3.0, [VX + 1074, VY + 793 - 420, 120, 40], 0.7);
  show(C(56), 'order-shipped', 420);
  ring(C(57), 'order-shipped.tracking', 2.4, 8);
  toast(C(56) + 0.3, '→', BLUE, 'Steadfast-এ বুক হয়েছে', 'ট্র্যাকিং কোড: ST-882100', 3.6);
  show(C(58) - 0.3, 'order');
  scrollTo(C(58) - 0.3, 0, 0.8);
  cam(C(58) + 0.3, { x: VX + 289, y: VY + 350, w: 500, h: 90 }, 0.85, 1.0);
  tap(C(58) + 1.0, 'order.label', 0.7);
  show(C(59), 'label');
  camBrowser(C(59), 1.0);
  show(C(60) + 0.3, 'order');
  cam(C(60) + 0.3, { x: VX + 289, y: VY + 350, w: 500, h: 90 }, 0.85, 1.0);
  tap(C(60) + 1.4, 'order.invoice', 0.7);
  show(C(61) + 0.4, 'invoice');
  camBrowser(C(61) + 0.4, 1.0);
  hideCursor(C(61) + 0.4);

  // ================= 9. Many orders at once (242.4 – 270.2 s) =================
  show(C(62), 'orders');
  camBrowser(C(62), 1.0);
  cam(C(63), { x: VX + 289, y: VY + 380, w: 600, h: 260 }, 0.9, 1.1);
  tap(C(63) + 0.6, 'orders-selected.check1', 0.6);
  tap(C(63) + 1.4, 'orders-selected.check2', 0.5);
  tap(C(63) + 2.1, 'orders-selected.check3', 0.5);
  show(C(63) + 2.7, 'orders-selected');
  cam(C(64), { x: VX + 760, y: VY + 800, w: 680, h: 90 }, 0.85, 1.2);
  tap(C(64) + 1.5, 'orders-selected.sendCourier', 0.8);
  ring(C(64) + 2.4, 'orders-selected.sendCourier', 2.0, 6);
  tap(C(65), [VX + 1282, VY + 830, 76, 36], 0.7);
  show(C(65) + 0.9, 'orders-print');
  cam(C(65) + 0.9, { x: VX + 1000, y: VY + 700, w: 440, h: 190 }, 0.85, 1.0);
  ring(C(65) + 1.3, 'orders-print.printLabels', 2.2, 4);
  ring(C(66) + 0.4, 'orders-print.printInvoices', 2.6, 4);
  tap(C(67), 'orders-selected.more', 0.7);
  show(C(67) + 0.9, 'orders-more');
  ring(C(67) + 1.3, 'orders-more.cancelOrders', 2.6, 4);

  // ================= 10. Courier Scan (270.8 – 359.4 s) =================
  camBrowser(C(68), 1.1);
  hideCursor(C(68));
  nav(C(69), 'navCourier', 'courier-scan');
  cam(C(70), { x: VX + 289, y: VY + 220, w: 1130, h: 120 }, 0.95, 1.1);
  tap(C(70) + 0.6, 'courier-scan.pickup', 0.7);
  ring(C(71), 'courier-scan.pickup', 2.2, 6);
  ring(C(72) + 0.3, 'courier-scan.scan', 3.0, 6);
  tap(C(73), 'courier-scan.scan', 0.7);
  type(C(74), 'courier-scan.scan', 'VB-579944', 1.2, { size: 15, dx: 40, coverDx: 32, inset: 4, until: C(76) + 0.4 });
  tap(C(74) + 1.4, 'courier-scan.enter', 0.6);
  tap(C(75) + 0.5, 'courier-scan.camera', 0.7);
  ring(C(75) + 1.3, 'courier-scan.camera', 2.6, 6);
  toast(C(76) + 0.2, '✓', GREEN, '#VB-579944 → Shipped', 'Steadfast-কে হস্তান্তর করা হয়েছে', 3.6);
  cam(C(77), { x: VX + 1100, y: VY + 60, w: 340, h: 100 }, 0.8, 1.2);
  tap(C(78), 'courier-scan.manifest', 0.7);
  show(C(78) + 1.0, 'manifest');
  camBrowser(C(78) + 1.0, 1.0);
  show(C(80), 'courier-scan');
  cam(C(80) + 0.2, { x: VX + 1100, y: VY + 60, w: 340, h: 100 }, 0.8, 1.1);
  tap(C(81), 'courier-scan.sync', 0.7);
  ring(C(81) + 0.9, 'courier-scan.sync', 2.0, 6);
  cam(C(82), { x: VX + 289, y: VY + 120, w: 700, h: 110 }, 0.85, 1.1);
  tap(C(82) + 0.8, 'courier-scan.inTab', 0.7);
  ring(C(82) + 1.6, 'courier-scan.inTab', 1.6, 6);
  show(C(84), 'courier-in');
  camBrowser(C(84), 0.9);
  cam(C(85), { x: VX + 470, y: VY + 320, w: 500, h: 180 }, 0.9, 1.1);
  ring(C(86) + 0.2, 'courier-in.paid', 2.2, 10);
  ring(C(86) + 2.5, 'courier-in.refused', 2.2, 10);
  cam(C(87), { x: VX + 470, y: VY + 580, w: 500, h: 150 }, 0.9, 1.1);
  tap(C(88), 'courier-in.restock', 0.6);
  ring(C(88) + 0.8, 'courier-in.restock', 2.2, 8);
  tap(C(89) + 0.3, 'courier-in.confirm', 0.7);
  toast(C(90), '↩', GOLD, 'রিটার্ন সম্পন্ন: VB-905811', 'পণ্যগুলো আবার স্টকে যোগ হয়েছে', 6.2);
  camBrowser(C(90) + 0.4, 1.0);
  show(C(90) + 0.6, 'courier-scan');
  hideCursor(C(90) + 0.6);

  // ================= 11. POS (360 – 408.2 s) =================
  nav(C(93) + 0.2, 'navPos', 'pos');
  cam(C(94), { x: VX + 289, y: VY + 150, w: 680, h: 100 }, 0.9, 1.1);
  ring(C(94) + 0.4, 'pos.scan', 2.2, 6);
  ring(C(95) + 0.2, 'pos.search', 1.6, 6);
  cam(C(95) + 1.6, { x: VX + 289, y: VY + 150, w: 1140, h: 520 }, 0.95, 1.0);
  tap(C(95) + 2.0, 'pos.firstProduct', 0.8);
  show(C(96) - 0.4, 'pos-cart');
  ring(C(96) + 0.2, [VX + 948, VY + 520, 472, 90], 2.4, 4);
  cam(C(97), { x: VX + 940, y: VY + 170, w: 490, h: 120 }, 0.9, 1.1);
  tap(C(98), 'pos-cart.customer', 0.6);
  ring(C(98) + 0.8, 'pos-cart.customer', 1.8, 6);
  cam(C(99), { x: VX + 940, y: VY + 680, w: 490, h: 170 }, 0.9, 1.1);
  ring(C(99) + 0.5, 'pos-cart.cash', 1.0, 4);
  ring(C(99) + 1.6, 'pos-cart.bkash', 1.0, 4);
  ring(C(100) + 0.2, 'pos-cart.nagad', 1.0, 4);
  ring(C(100) + 1.4, [VX + 1295, VY + 708, 105, 32], 1.2, 4);
  ring(C(101) + 0.3, [VX + 968, VY + 755, 215, 38], 2.4, 4);
  ring(C(102) + 0.3, [VX + 1192, VY + 755, 215, 38], 3.2, 4);
  tap(C(103) + 0.6, 'pos-cart.complete', 0.8);
  toast(C(103) + 1.6, '৳', BRAND, 'বিক্রি সম্পন্ন · রিসিট প্রিন্ট হচ্ছে', 'POS · ৳1,190', 2.8);
  toast(C(104) + 0.3, '✓', GREEN, 'অনলাইন স্টকও আপডেট হয়েছে', 'Vant Classic Bifold Leather Wallet', 3.8);
  camBrowser(C(104), 1.1);
  hideCursor(E(104));

  // ================= 12. New product (408.8 – 514.2 s) =================
  nav(C(106), 'navProducts', 'products');
  cam(C(107), { x: VX + 1100, y: VY + 60, w: 340, h: 100 }, 0.8, 1.1);
  tap(C(107) + 0.8, 'products.newProduct', 0.7);
  show(C(107) + 1.8, 'product-new');
  camBrowser(C(107) + 1.8, 1.0);
  cam(C(108), { x: VX + 300, y: VY + 230, w: 740, h: 190 }, 0.92, 1.1);
  tap(C(108) + 0.5, 'product-new.name', 0.6);
  type(C(108) + 1.2, 'product-new.name', 'Vant Premium Leather Wallet', 2.0, { size: 15, inset: 4, until: C(109) - 0.4 });
  scrollTo(C(109) - 0.3, 200, 1.0);
  cam(C(109), { x: VX + 300, y: VY + 560 - 200, w: 740, h: 120 }, 0.92, 1.1);
  ring(C(109) + 0.4, [VX + 663, VY + 572 - 200, 362, 82], 2.6, 4);
  ring(C(110) + 0.4, [VX + 301, VY + 572 - 200, 362, 82], 3.2, 4);
  scrollTo(C(111) - 0.3, 520, 1.1);
  cam(C(111), { x: VX + 300, y: VY + 760 - 520, w: 740, h: 380 }, 0.92, 1.1);
  ring(C(111) + 0.5, [VX + 305, VY + 765 - 520, 730, 360], 3.6, 4);
  ring(C(112) + 0.3, [VX + 640, VY + 765 - 520, 400, 40], 4.2, 4);   // toolbar: image, video, table
  scrollToBox(C(113) - 0.3, 'upload', 1.1);
  cam(C(113), { x: VX + 300, y: box('product-new.upload').y - 160, w: 740, h: 330 }, 0.92, 1.1);
  tap(C(114), 'product-new.upload', 0.8);
  ring(C(114) + 0.9, pad(box('product-new.upload'), 50), 2.4, 0);
  ring(C(116) + 0.3, { x: VX + 300, y: box('product-new.upload').y - 130, w: 740, h: 110 }, 4, 4);
  scrollToBox(C(117) - 0.3, 'pricing', 1.1);
  cam(C(117), { x: VX + 300, y: box('product-new.pricing').y - 20, w: 740, h: 260 }, 0.92, 1.1);
  ring(C(117) + 0.5, { x: VX + 305, y: box('product-new.pricing').y - 10, w: 730, h: 240 }, 4.6, 4);
  scrollToBox(C(118) - 0.3, 'variants', 1.1);
  cam(C(118) + 0.2, { x: VX + 300, y: box('product-new.variants').y - 20, w: 740, h: 380 }, 0.92, 1.2);
  ring(C(119) + 0.3, { x: VX + 305, y: box('product-new.variants').y - 10, w: 730, h: 360 }, 5.4, 4);
  scrollToBox(C(120) - 0.3, 'seo', 1.1);
  cam(C(120), { x: VX + 300, y: box('product-new.seo').y - 20, w: 740, h: 120 }, 0.92, 1.1);
  ring(C(121) + 0.3, { x: VX + 305, y: box('product-new.seo').y - 10, w: 730, h: 100 }, 3.8, 4);
  scrollTo(C(122) - 0.3, 0, 1.2);
  cam(C(122) + 0.2, { x: VX + 1060, y: VY + 160, w: 370, h: 300 }, 0.85, 1.1);
  ring(C(122) + 0.8, [VX + 1066, VY + 280, 345, 40], 2.4, 4);
  tap(C(123), 'product-new.save', 0.8);
  toast(C(124) - 0.2, '✓', GREEN, 'পণ্য প্রকাশিত হয়েছে', 'Vant Premium Leather Wallet এখন ওয়েবসাইটে', 3.6);
  show(C(125), 'products');
  camBrowser(C(125), 1.0);
  cam(C(126), { x: VX + 1100, y: VY + 60, w: 340, h: 100 }, 0.8, 1.1);
  tap(C(126) + 1.2, 'products.import', 0.7);
  show(C(127), 'product-import');
  camBrowser(C(127), 0.9);
  cam(C(127) + 0.9, { x: VX + 470, y: VY + 240, w: 500, h: 420 }, 0.9, 1.1);
  ring(C(127) + 1.6, 'product-import.file', 2.6, 14);
  tap(C(128) + 0.4, 'product-import.start', 0.7);
  hideCursor(E(128));

  // ================= 13. Inventory & barcodes (514.8 – 558.2 s) =================
  nav(C(129) + 0.2, 'navInventory', 'inventory');
  cam(C(130), { x: VX + 289, y: VY + 500, w: 1140, h: 100 }, 0.95, 1.1);
  tap(C(130) + 0.8, 'inventory.restock', 0.7);
  show(C(130) + 1.8, 'inventory-restock');
  cam(C(130) + 1.8, { x: VX + 470, y: VY + 300, w: 500, h: 400 }, 0.9, 1.1);
  ring(C(131) + 0.3, [VX + 508, VY + 418, 210, 76], 3.0, 4);
  ring(C(132) + 0.3, [VX + 718, VY + 418, 210, 76], 3.8, 4);
  ring(C(133) + 0.3, [VX + 516, VY + 508, 410, 80], 4.4, 4);
  tap(C(134), 'inventory-restock.save', 0.7);
  toast(C(135), '✓', GREEN, 'স্টক যোগ হয়েছে', 'নতুন গড় কেনা দাম হিসাব হয়েছে', 4.2);
  show(C(135) + 0.4, 'inventory');
  camBrowser(C(135) + 0.4, 1.0);
  cam(C(136), { x: VX + 289, y: VY + 70, w: 500, h: 80 }, 0.8, 1.0);
  tap(C(136) + 0.6, 'inventory.barcodesTab', 0.6);
  show(C(136) + 1.4, 'barcodes');
  camBrowser(C(136) + 1.6, 1.1);
  ring(C(137) + 0.3, [VX + 289, VY + 360, 1130, 480], 4.4, 4);
  hideCursor(E(138));

  // ================= 14. Catalog setup & reviews (558.8 – 596.2 s) =================
  nav(C(139), 'navCatalog', 'categories');
  cam(C(139) + 1.4, { x: VX + 280, y: VY + 70, w: 420, h: 70 }, 0.75, 1.1);
  ring(C(139) + 2.2, union('categories.categories', 'categories.variations'), 2.0, 6);
  ring(C(140), 'categories.categories', 1.6, 4);
  camBrowser(C(140) + 1.0, 1.1);
  tap(C(141), 'categories.brands', 0.7);
  show(C(141) + 0.9, 'brands');
  tap(C(142), 'categories.variations', 0.7);
  show(C(142) + 0.9, 'variations');
  hideCursor(E(144));
  nav(C(145), 'navReviews', 'reviews');
  cam(C(146), { x: VX + 289, y: VY + 290, w: 1140, h: 160 }, 0.95, 1.1);
  tap(C(146) + 1.2, 'reviews.approve', 0.7);
  ring(C(146) + 2.0, 'reviews.approve', 2.4, 6);
  toast(C(147), '✓', GREEN, 'রিভিউ অ্যাপ্রুভ হয়েছে', 'এখন ওয়েবসাইটে দেখা যাবে', 2.6);
  camBrowser(C(147) + 0.6, 1.0);
  hideCursor(E(147));

  // ================= 15. Homepage, coupons, promotions, abandoned carts (596.8 – 673.2 s) =================
  nav(C(148), 'navHomepage', 'banners');
  ring(C(149), [VX + 289, VY + 210, 1130, 380], 6.0, 4);
  tap(C(151), 'banners.ticker', 0.7);
  show(C(151) + 0.9, 'news-ticker');
  ring(C(151) + 1.6, [VX + 289, VY + 240, 1130, 90], 3.0, 4);
  tap(C(152), 'banners.menubar', 0.7);
  show(C(152) + 0.9, 'header-menu');
  ring(C(153), [VX + 289, VY + 230, 1130, 110], 3.4, 4);
  hideCursor(E(153));
  nav(C(154), 'navCoupons', 'coupons');
  cam(C(155) - 0.3, { x: VX + 1100, y: VY + 60, w: 340, h: 100 }, 0.8, 1.0);
  tap(C(155) + 0.6, 'coupons.create', 0.7);
  camBrowser(C(156), 1.1);
  ring(C(156) + 0.4, [VX + 289, VY + 262, 1130, 130], 9, 4);
  hideCursor(C(156));
  nav(C(159), 'navPromotions', 'flash-sale');
  ring(C(160) + 0.2, [VX + 289, VY + 370, 1130, 140], 3.0, 4);
  tap(C(161) + 0.2, 'flash-sale.freeTab', 0.7);
  show(C(161) + 1.1, 'free-delivery');
  ring(C(162) + 0.3, [VX + 289, VY + 720, 1130, 140], 4.6, 4);
  hideCursor(E(162));
  nav(C(163) + 0.3, 'navCarts', 'abandoned-carts');
  cam(C(164) + 0.4, { x: VX + 289, y: VY + 420, w: 1140, h: 220 }, 0.95, 1.1);
  ring(C(164) + 0.8, [VX + 289, VY + 430, 1130, 200], 3.0, 4);
  tap(C(165) + 0.4, [VX + 1040, VY + 470, 100, 34], 0.8);
  toast(C(165) + 1.4, '✉', GREEN, 'রিমাইন্ডার পাঠানো হয়েছে', 'Rahim Uddin · WhatsApp', 3.2);
  camBrowser(C(166) + 1.6, 1.0);
  hideCursor(E(166));

  // ================= 16. Profit, expenses, customers, staff (673.8 – 746.5 s) =================
  if (!skipped(167)) {
    nav(C(167), 'navAnalytics', 'analytics');
    cam(C(168), { x: VX + 289, y: VY + 230, w: 1140, h: 200 }, 0.95, 1.1);
    ring(C(168) + 0.3, [VX + 289, VY + 240, 1130, 110], 3.2, 4);
    ring(C(169) + 0.2, [VX + 289, VY + 350, 1130, 160], 4.0, 4);
    cam(C(170), { x: VX + 1100, y: VY + 60, w: 340, h: 100 }, 0.8, 1.1);
    tap(C(170) + 0.8, 'analytics.export', 0.7);
    ring(C(171), 'analytics.export', 2.6, 6);
    hideCursor(E(171));
    nav(C(172) + 0.3, 'navExpenses', 'expenses');
    ring(C(173), [VX + 289, VY + 410, 1130, 300], 3.0, 4);
  } else {
    // The recording goes straight from abandoned carts to "these costs come off the profit".
    nav(C(174) - 0.6, 'navExpenses', 'expenses');
  }
  ring(C(174) + 0.3, [VX + 289, VY + 150, 1130, 115], 4.6, 4);
  nav(C(175), 'navCustomers', 'customers');
  ring(C(175) + 1.6, [VX + 289, VY + 470, 1130, 380], 3.0, 4);
  tap(C(177), 'customers.blacklistTab', 0.7);
  show(C(177) + 0.9, 'blacklist');
  ring(C(178), [VX + 289, VY + 325, 1130, 125], 3.6, 4);
  hideCursor(E(178));
  nav(C(179), 'navStaff', 'staff');
  ring(C(180), [VX + 289, VY + 420, 1130, 420], 3.0, 4);
  ring(C(181) + 0.2, [VX + 700, VY + 480, 170, 360], 9.6, 4);    // role column
  tap(C(184), 'staff.auditTab', 0.7);
  show(C(184) + 0.9, 'audit');
  ring(C(184) + 1.6, [VX + 289, VY + 270, 1130, 420], 2.8, 4);
  hideCursor(E(184));

  // ================= 17. Settings & integrations (747.2 – 793.8 s) =================
  nav(C(185), 'navSettings', 'settings');
  ring(C(186), [VX + 289, VY + 200, 1130, 380], 3.6, 4);
  ring(C(187) + 0.2, [VX + 556, VY + 150, 140, 36], 5.0, 6);   // Payments & delivery
  scrollTo(C(188) - 0.3, 400, 1.1);
  cam(C(188) + 0.3, { x: VX + 289, y: VY + 700 - 400, w: 1140, h: 160 }, 0.92, 1.1);
  ring(C(188) + 0.8, [VX + 305, VY + 720 - 400, 1100, 120], 6.0, 4);
  scrollTo(E(189) - 0.2, 0, 0.6);
  nav(C(190), 'navIntegrations', 'integrations');
  ring(C(190) + 1.4, [VX + 289, VY + 210, 1130, 600], 4.2, 4);
  tap(C(192) + 0.2, 'integrations.trackingTab', 0.7);
  show(C(192) + 1.1, 'integrations-tracking');
  ring(C(192) + 1.6, [VX + 289, VY + 210, 1130, 220], 2.0, 4);
  scrollTo(C(192) + 3.4, 420, 1.0);
  ring(C(192) + 4.4, [VX + 289, VY + 640 - 420, 1130, 220], 1.8, 4);
  scrollTo(C(193) - 0.2, 0, 0.8);
  cam(C(193) + 0.5, { x: VX + 289, y: VY + 200, w: 560, h: 80 }, 0.8, 1.2);
  ring(C(193) + 1.2, [VX + 364, VY + 226, 58, 22], 5.0, 8);        // the Bangla setup guide button
  hideCursor(C(193));

  // ================= 18. Close (794.4 – 806.8 s) =================
  show(C(195), 'dashboard');
  cam(C(195), { x: -190, y: -40, w: 2330, h: 1110 }, 0.86, 2.6);
  tl.to('#end', { opacity: 1, duration: 1.2, ease: 'power1.inOut' }, C(196) - 0.4);
  tl.fromTo('#endLogo', { scale: 0.92, opacity: 0 }, { scale: 1, opacity: 1, duration: 1.2, ease: 'power2.out' }, C(196) - 0.1);
  tl.fromTo(['#endTag', '#endLine', '#endHelp'], { opacity: 0, y: 16 }, { opacity: 1, y: 0, duration: 0.8, stagger: 0.25 }, C(196) + 0.6);
  tl.fromTo('#endThanks', { opacity: 0, y: 20 }, { opacity: 1, y: 0, duration: 0.8 }, C(197));
}
