<?php
/* GuestFlow
 * Guest Check-in via QR Code
 * 
 * The check-in is based on QR code scanning
 * 
 * This QR code, also used by guests for registration, contains various information.
 * Here we only use the qr_unique parameter.
 * 
 * Guests also receive an email confirmation containing a QR code.
 * This simplified QR code also contains the qr_unique.
 * 
 * The reception.csv file contains the guest list
 * qr_unique | last_name | first_name
 * 
 * Created by cybermonde.org
*/

// Guest list file and language configuration
include 'includes/config.php';

// Function to update presence status
function updatePresence($file, $id) {
    $rows = [];
    $found = false;
    $alreadyPresent = false;
    $person = ["nom" => "", "prenom" => ""];
	// Normalize qr_unique: only A-Z and 0-9
	$id = strtoupper($id);                 // force to uppercase
	$id = preg_replace('/[^A-Z0-9]/', '', $id);

    if (($handle = fopen($file, "r")) !== false) {
        while (($data = fgetcsv($handle, 1000, ",")) !== false) {
            if ($data[0] === $id) {
                $found = true;
                $person["nom"] = $data[1] ?? "";
                $person["prenom"] = $data[2] ?? "";

                if (isset($data[3]) && trim($data[3]) === "1") {
                    $alreadyPresent = true;
                } else {
                    $data[3] = "1";
                }
            }
            $rows[] = $data;
        }
        fclose($handle);
    }

    if ($found && !$alreadyPresent) {
        $handle = fopen($file, "w");
        foreach ($rows as $row) {
            fputcsv($handle, $row);
        }
        fclose($handle);
    }

    if (!$found) return ["status" => "not_found"];
    if ($alreadyPresent) return ["status" => "already_present", "nom" => $person["nom"], "prenom" => $person["prenom"]];
    return ["status" => "success", "nom" => $person["nom"], "prenom" => $person["prenom"]];
}

if (isset($_POST['identifier'])) {
    $url = $_POST['identifier'];
    $parsedUrl = parse_url($url);
    parse_str($parsedUrl['query'] ?? '', $params);
    $id = $params['qr_unique'] ?? '';

    if ($id === '') {
        echo json_encode([
            "status" => "invalid",
            "scanned_url" => $url
        ]);
        exit;
    }

    $result = updatePresence($csvFile, $id);

// Enrich response with useful info
$result['scanned_url'] = $url;
$result['qr_unique'] = $id;

echo json_encode($result);
exit;

}
?>

<!DOCTYPE html>
<html lang="<?php echo $lang_code; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($lang_strings['title']); ?></title>
    <script src="js/html5-qrcode.js" type="text/javascript"></script>
    <link rel="stylesheet" href="includes/guestflow.css">
</head>
<body>
    <header><?php echo htmlspecialchars($lang_strings['header']); ?></header>

    <div id="reader"></div>
    <div id="message"></div>
    <button id="startCameraBtn" style="display: none; margin: 20px auto; padding: 15px 30px; font-size: 1.2em; cursor: pointer;"><?php echo htmlspecialchars($lang_strings['enable_camera']); ?></button>

    <footer>cybermonde.org - version 0.1 <a href="admin.php?lang=<?php echo $lang; ?>" title="<?php echo htmlspecialchars($lang_strings['admin_link']); ?>" class="admin-link">🔒</a></footer>

   <script>
        const messageBox = document.getElementById("message");
        let scanEnabled = true;
        let html5QrCode = null;

        function showMessage(text, cssClass, duration = 3000) {
            messageBox.textContent = text;
            messageBox.className = cssClass;
            setTimeout(() => {
                messageBox.textContent = "";
            }, duration);
        }

        function onScanSuccess(decodedText) {
            if (!scanEnabled) return;
            scanEnabled = false;

            fetch("", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: "identifier=" + encodeURIComponent(decodedText)
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === "success") {
                    showMessage("<?php echo addslashes($lang_strings['checkin_success']); ?>".replace('%s', data.prenom).replace('%s', data.nom), "success", 3000);
                } else if (data.status === "already_present") {
                    showMessage("<?php echo addslashes($lang_strings['checkin_duplicate']); ?>".replace('%s', data.prenom).replace('%s', data.nom), "already", 3000);
                } else if (data.status === "not_found") {
                    showMessage("<?php echo addslashes($lang_strings['checkin_not_found']); ?>".replace('%s', data.qr_unique), "error", 3000);
                } else if (data.status === "invalid") {
                    showMessage("<?php echo addslashes($lang_strings['checkin_invalid']); ?>".replace('%s', data.scanned_url), "error", 3000);
                }
            })
            .catch(() => showMessage("<?php echo addslashes($lang_strings['checkin_error']); ?>", "error"))
            .finally(() => {
                setTimeout(() => { scanEnabled = true; }, 3000);
            });
        }

        function startCamera() {
            const isMobile = window.innerWidth < 600;
            const config = { fps: 10, qrbox: isMobile ? 200 : 300 };

            html5QrCode = new Html5Qrcode("reader");

            html5QrCode.start(
                { facingMode: "environment" },
                config,
                onScanSuccess
            ).catch(err => {
                console.error("Camera error:", err);
                showMessage("<?php echo addslashes($lang_strings['camera_error']); ?>".replace('%s', err.message), "error");
            });
        }

        startCamera();

        document.getElementById("startCameraBtn").addEventListener("click", startCamera);
    </script>
</body>
</html>
