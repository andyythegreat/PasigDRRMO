<?php
include 'connection.php';

require 'dompdf/autoload.inc.php';

use Dompdf\Dompdf;
use Dompdf\Options;

if(isset($_GET['pdf']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Fetch the specific record based on the provided ID
    $sql = mysqli_query($conn, "SELECT * FROM brgy_incidentreport WHERE ID = '$id'");
    $c3_incidentreport = mysqli_fetch_assoc($sql);

    if ($c3_incidentreport) {
        $options = new Options();
        $options->set('chroot', realpath(__DIR__)); // Adjust this if needed
        $dompdf = new Dompdf($options);

        ob_start();
        require('details_pdf.php');
        $html = ob_get_clean();

        $dompdf->loadHtml($html);

        $dompdf->setPaper(array(0, 0, 8.5 * 72, 13 * 72));

        $dompdf->render();

        $dompdf->stream('print-details.pdf', ['Attachment' => false]);
    } else {
        echo "Record with ID $id not found.";
    }
} else {
    echo "ID parameter is missing.";
}
?>
