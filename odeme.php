<?php
/**
 * iyzico Ödeme Endpoint'i
 * 
 * NOT: iyzico PHP SDK'sını kurmak için:
 * composer require iyzico/iyzipay-php
 */

require_once 'config.php';
setCORSHeaders();

header('Content-Type: application/json');

require_once 'session.php';

// Sadece POST isteklerini kabul et
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Sadece POST isteği kabul edilir.']);
    exit;
}

// Kullanıcı giriş kontrolü
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Ödeme yapmak için giriş yapmanız gerekiyor.'
    ]);
    exit;
}

require_once 'database.php';

// JSON verisini al
$input = json_decode(file_get_contents('php://input'), true);

if (empty($input)) {
    $input = $_POST;
}

// Veri validasyonu
$cart = $input['cart'] ?? [];
$cardName = $input['cardName'] ?? '';
$cardNumber = $input['cardNumber'] ?? '';
$expiryMonth = $input['expiryMonth'] ?? '';
$expiryYear = $input['expiryYear'] ?? '';
$cvv = $input['cvv'] ?? '';

// Validasyon kontrolleri
$errors = [];

if (empty($cart) || !is_array($cart) || count($cart) === 0) {
    $errors[] = 'Sepetiniz boş. Lütfen önce ürün ekleyin.';
}

if (empty($cardName)) {
    $errors[] = 'Kart üzerindeki isim gereklidir.';
}

if (empty($cardNumber) || strlen(str_replace(' ', '', $cardNumber)) < 13) {
    $errors[] = 'Geçerli bir kart numarası giriniz.';
}

if (empty($expiryMonth) || empty($expiryYear)) {
    $errors[] = 'Son kullanma tarihi gereklidir.';
}

if (empty($cvv) || strlen($cvv) < 3) {
    $errors[] = 'CVV kodu gereklidir.';
}

if (!empty($errors)) {
    echo json_encode([
        'success' => false,
        'message' => implode(' ', $errors)
    ]);
    exit;
}

// Toplam fiyatı hesapla
$totalAmount = 0;
foreach ($cart as $item) {
    $totalAmount += floatval($item['price'] ?? 0);
}

if ($totalAmount <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Geçersiz toplam tutar.'
    ]);
    exit;
}

try {
    $db = new Database();
    $userId = getUserId();
    
    // Önce sipariş oluştur (pending durumunda)
    $orderResult = $db->createOrder($userId, $cart, $totalAmount, 'iyzico');
    
    if (!$orderResult['success']) {
        echo json_encode([
            'success' => false,
            'message' => 'Sipariş oluşturulamadı. Lütfen tekrar deneyin.'
        ]);
        exit;
    }
    
    $orderId = $orderResult['order_id'];
    $orderNumber = $orderResult['order_number'];
    
    // iyzico SDK kontrolü
    if (!class_exists('Iyzipay\Options')) {
        // SDK yüklü değilse, test modunda siparişi tamamla
        // Gerçek uygulamada burada iyzico entegrasyonu olacak
        $db->updateOrderStatus($orderId, 'completed', 'completed');
        
        echo json_encode([
            'success' => true,
            'message' => 'Ödeme başarılı! (Test modu - iyzico SDK yüklü değil)',
            'order_number' => $orderNumber
        ]);
        exit;
    }

    // iyzico ayarları (BURAYA KENDI API BİLGİLERİNİZİ GİRİN)
    $options = new \Iyzipay\Options();
    $options->setApiKey('YOUR_API_KEY'); // iyzico API Key
    $options->setSecretKey('YOUR_SECRET_KEY'); // iyzico Secret Key
    $options->setBaseUrl('https://sandbox-api.iyzipay.com'); // Test için sandbox, canlı için: https://api.iyzipay.com

    // Kullanıcı bilgilerini al
    $userId = getUserId();
    $userEmail = $_SESSION['user_email'] ?? '';
    $userName = $_SESSION['user_name'] ?? '';

    // Ödeme isteği oluştur
    $request = new \Iyzipay\Request\CreatePaymentRequest();
    $request->setLocale(\Iyzipay\Model\Locale::TR);
    $request->setConversationId($userId . '_' . time());
    
    $paymentCard = new \Iyzipay\Model\PaymentCard();
    $paymentCard->setCardHolderName($cardName);
    $paymentCard->setCardNumber($cardNumber);
    $paymentCard->setExpireMonth($expiryMonth);
    $paymentCard->setExpireYear('20' . $expiryYear);
    $paymentCard->setCvc($cvv);
    $paymentCard->setRegisterCard(0);
    $request->setPaymentCard($paymentCard);

    $buyer = new \Iyzipay\Model\Buyer();
    $buyer->setId($userId);
    $buyer->setName($userName ?: $cardName);
    $buyer->setSurname('');
    $buyer->setGsmNumber('');
    $buyer->setEmail($userEmail);
    $buyer->setIdentityNumber('11111111111'); // TC Kimlik No (test için)
    $buyer->setLastLoginDate(date('Y-m-d H:i:s'));
    $buyer->setRegistrationDate(date('Y-m-d H:i:s'));
    $buyer->setRegistrationAddress('');
    $buyer->setIp($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');
    $buyer->setCity('Istanbul');
    $buyer->setCountry('Turkey');
    $buyer->setZipCode('34000');
    $request->setBuyer($buyer);

    $shippingAddress = new \Iyzipay\Model\Address();
    $shippingAddress->setContactName($userName ?: $cardName);
    $shippingAddress->setCity('Istanbul');
    $shippingAddress->setCountry('Turkey');
    $shippingAddress->setAddress('');
    $shippingAddress->setZipCode('34000');
    $request->setShippingAddress($shippingAddress);

    $billingAddress = new \Iyzipay\Model\Address();
    $billingAddress->setContactName($userName ?: $cardName);
    $billingAddress->setCity('Istanbul');
    $billingAddress->setCountry('Turkey');
    $billingAddress->setAddress('');
    $billingAddress->setZipCode('34000');
    $request->setBillingAddress($billingAddress);

    // Sepetteki ürünleri basket items olarak ekle
    $basketItems = array();
    foreach ($cart as $index => $item) {
        $basketItem = new \Iyzipay\Model\BasketItem();
        $basketItem->setId($item['id'] ?? $item['planId'] ?? 'item_' . $index);
        $basketItem->setName($item['name'] ?? 'Ürün');
        $basketItem->setCategory1('Danışmanlık');
        $basketItem->setCategory2('Hizmet');
        $basketItem->setItemType(\Iyzipay\Model\BasketItemType::VIRTUAL);
        $basketItem->setPrice(floatval($item['price'] ?? 0));
        $basketItems[] = $basketItem;
    }
    $request->setBasketItems($basketItems);

    $request->setPaymentChannel(\Iyzipay\Model\PaymentChannel::WEB);
    $request->setCurrency(\Iyzipay\Model\Currency::TL);
    $request->setPrice($totalAmount);
    $request->setPaidPrice($totalAmount);

    // Ödeme işlemini başlat
    $payment = \Iyzipay\Model\Payment::create($request, $options);

    if ($payment->getStatus() === 'success') {
        // Ödeme başarılı - sipariş durumunu güncelle
        $db->updateOrderStatus($orderId, 'completed', 'completed');
        
        echo json_encode([
            'success' => true,
            'message' => 'Ödeme başarılı!',
            'paymentId' => $payment->getPaymentId(),
            'paymentStatus' => $payment->getStatus(),
            'order_number' => $orderNumber
        ]);
    } else {
        // Ödeme başarısız - sipariş durumunu güncelle
        $db->updateOrderStatus($orderId, 'failed', 'failed');
        
        echo json_encode([
            'success' => false,
            'message' => $payment->getErrorMessage() ?: 'Ödeme işlemi başarısız oldu.'
        ]);
    }
} catch (Exception $e) {
    error_log("Ödeme hatası: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Ödeme işlemi sırasında bir hata oluştu. Lütfen tekrar deneyin.'
    ]);
}
