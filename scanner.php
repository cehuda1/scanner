<?php
// PHP: Fungsi untuk mencari file mencurigakan
function scanForSuspiciousFiles($directory) {
    $suspiciousFunctions = ['eval', 'exec', 'shell_exec', 'base64_decode', 'system', 'passthru', 'popen', 'proc_open', 'curl_exec', 'file_put_contents', 'file_get_contents'];
    $suspiciousFiles = [];
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));

    foreach ($files as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $content = file_get_contents($file->getRealPath());
            foreach ($suspiciousFunctions as $function) {
                if (strpos($content, $function) !== false) {
                    $suspiciousFiles[] = [
                        'path' => $file->getRealPath(),
                        'function' => $function
                    ];
                    break;
                }
            }
        }
    }

    return $suspiciousFiles;
}

// Memproses permintaan AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['scan'])) {
    $directory = __DIR__;
    $suspiciousFiles = scanForSuspiciousFiles($directory);
    echo json_encode($suspiciousFiles);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Webshell Scanner</title>
    <style>
        body { font-family: Arial, sans-serif; }
        #scanButton { padding: 10px 20px; background-color: #4CAF50; color: white; border: none; cursor: pointer; }
        #scanButton:hover { background-color: #45a049; }
        #results { margin-top: 20px; }
        .file { padding: 10px; border-bottom: 1px solid #ddd; }
    </style>
</head>
<body>
    <h2>Webshell Scanner</h2>
    <p>Tekan tombol di bawah untuk memindai direktori ini untuk file mencurigakan.</p>
    <button id="scanButton" onclick="startScan()">Mulai Scan</button>
    <div id="results"></div>

    <script>
        function startScan() {
            document.getElementById('results').innerHTML = "<p>Scanning...</p>";

            fetch('', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'scan=true'
            })
            .then(response => response.json())
            .then(data => {
                const resultsDiv = document.getElementById('results');
                resultsDiv.innerHTML = '<h3>Hasil Scan:</h3>';

                if (data.length === 0) {
                    resultsDiv.innerHTML += '<p>Tidak ada file mencurigakan ditemukan.</p>';
                } else {
                    data.forEach(file => {
                        resultsDiv.innerHTML += `<div class="file">File: ${file.path} <br> Fungsi: ${file.function}</div>`;
                    });
                }
            })
            .catch(error => {
                document.getElementById('results').innerHTML = `<p>Error: ${error}</p>`;
            });
        }
    </script>
</body>
</html>
