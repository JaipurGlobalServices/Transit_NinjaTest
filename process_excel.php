<?php
require 'vendor/autoload.php'; // Ensure PHPExcel is included

use PhpOffice\PhpSpreadsheet\IOFactory;

$inputFileName = $target_file;

$spreadsheet = IOFactory::load($inputFileName);
$worksheet = $spreadsheet->getActiveSheet();

foreach ($worksheet->getRowIterator() as $row) {
    $cellIterator = $row->getCellIterator();
    $cellIterator->setIterateOnlyExistingCells(FALSE);

    $data = [];
    foreach ($cellIterator as $cell) {
        $data[] = $cell->getValue();
    }

    // Ensure column count matches your table schema
    if (count($data) == 39) { // Adjusted to 39 columns

        // Insert data
        $sql = "INSERT INTO shipment_tracking (
            order_id, order_item_id, purchase_date, payments_date, buyer_email, 
            buyer_name, buyer_phone_number, sku, product_name, quantity_purchased, 
            currency, item_price, item_tax, shipping_price, shipping_tax, 
            ship_service_level, recipient_name, ship_address_1, ship_address_2, ship_address_3, 
            ship_city, ship_state, ship_postal_code, ship_country, ship_phone_number, 
            delivery_start_date, delivery_end_date, delivery_time_zone, delivery_instructions, payment_method, 
            cod_collectible_amount, already_paid, payment_method_fee, is_business_order, 
            purchase_order_number, price_designation, is_prime, fulfilled_by, is_iba) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssssssssssssssssssssssssssssssssssss", 
            $data[0], $data[1], $data[2], $data[3], $data[4], 
            $data[5], $data[6], $data[7], $data[8], $data[9], 
            $data[10], $data[11], $data[12], $data[13], $data[14], 
            $data[15], $data[16], $data[17], $data[18], $data[19], 
            $data[20], $data[21], $data[22], $data[23], $data[24], 
            $data[25], $data[26], $data[27], $data[28], $data[29], 
            $data[30], $data[31], $data[32], $data[33], $data[34], 
            $data[35], $data[36], $data[37], $data[38]);
        $stmt->execute();
    } else {
        echo "Column count mismatch: Expected 39 columns but got " . count($data) . ".<br>";
    }
}

echo "<br>Data imported successfully.";
?>
