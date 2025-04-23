<?php
include 'connection.php';

function fetchBarangayData($conn, $year, $month) {
    $allBarangays = [
        'BRGY_BAGONGILOG', 'BRGY_BAGONGKATIPUNAN', 'BRGY_BAMBANG', 'BRGY_BUTING', 'BRGY_CANIOGAN', 
        'BRGY_DELAPAZ', 'BRGY_KALAWAAN', 'BRGY_KAPASIGAN', 'BRGY_KAPITOLYO', 'BRGY_MALINAO', 
        'BRGY_MANGGAHAN', 'BRGY_MAYBUNGA', 'BRGY_ORANBO', 'BRGY_PALATIW', 'BRGY_PINAGBUHATAN', 
        'BRGY_PINEDA', 'BRGY_ROSARIO', 'BRGY_SAGAD', 'BRGY_SANANTONIO', 'BRGY_SANJOAQIN', 
        'BRGY_SANJOSE', 'BRGY_SANMIGUEL', 'BRGY_SANNICOLAS', 'BRGY_STACRUZ', 'BRGY_SANTALUCIA', 
        'BRGY_SANTAROSA', 'BRGY_SANTOTOMAS', 'BRGY_SANTOLAN', 'BRGY_SUMILANG', 'BRGY_UGONG'
    ];

    $sql = "SELECT Barangay, COUNT(*) AS Total FROM c3_locate WHERE YEAR(Date) = ? AND MONTH(Date) = ? GROUP BY Barangay";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $year, $month);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = array_fill_keys($allBarangays, 0); // Initialize all barangays with 0

    while ($row = $result->fetch_assoc()) {
        if (in_array($row['Barangay'], $allBarangays)) {
            $data[$row['Barangay']] = $row['Total'];
        }
    }

    $stmt->close();
    return $data;
}

if (isset($_GET['year']) && isset($_GET['month'])) {
    $year = $_GET['year'];
    $month = $_GET['month'];
    $barangayData = fetchBarangayData($conn, $year, $month);
    header('Content-Type: application/json');
    echo json_encode($barangayData);
} else {
    echo "Error: Month and year parameters are required.";
}

$conn->close();
?>
