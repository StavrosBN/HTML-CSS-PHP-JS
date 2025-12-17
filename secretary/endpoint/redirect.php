<?php
if (isset($_GET['start_date']) && isset($_GET['end_date']) && isset($_GET['format'])) {
    $start_date = urlencode($_GET['start_date']);
    $end_date = urlencode($_GET['end_date']);
    $format = urlencode($_GET['format']);

    $url = "http://localhost/secretary/endpoint.php?start_date=$start_date&end_date=$end_date&format=$format";
    
    header("Location: $url");
    exit();
} else {
    echo "Invalid input. Please go back and fill out the form.";
}
?>
