<?php
$ch = curl_init('http://localhost:8000/login.php');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['username'=>'admin', 'password'=>'Admin@12345']));
$resp = curl_exec($ch);
curl_close($ch);

preg_match('/Set-Cookie:\s*PHPSESSID=([^;]+)/i', $resp, $matches);
$sessId = $matches[1] ?? '';

$ch2 = curl_init('http://localhost:8000/index.php');
curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch2, CURLOPT_HTTPHEADER, ['Cookie: PHPSESSID=' . $sessId]);
$html = curl_exec($ch2);
curl_close($ch2);

echo "=== DASHBOARD HTML VERIFICATION ===\n";
if (preg_match('/<div class="metric-value" id="metricDetectionEvents">([^<]+)<\/div>/', $html, $m)) {
    echo "Metric Detection Events: " . $m[1] . "\n";
} else {
    echo "Metric Detection Events NOT matched\n";
}

if (preg_match('/<div class="metric-value" id="metricActiveAlerts">([^<]+)<\/div>/', $html, $m)) {
    echo "Metric Active Alerts: " . $m[1] . "\n";
} else {
    echo "Metric Active Alerts NOT matched\n";
}

if (preg_match('/<div class="metric-value" id="metricCriticalThreats">([^<]+)<\/div>/', $html, $m)) {
    echo "Metric Critical Threats: " . $m[1] . "\n";
} else {
    echo "Metric Critical Threats NOT matched\n";
}

echo "Search Input: " . (strpos($html, 'id="detectionSearchInput"') !== false ? 'FOUND' : 'MISSING') . "\n";
echo "Type Filter: " . (strpos($html, 'id="detectionTypeFilter"') !== false ? 'FOUND' : 'MISSING') . "\n";
echo "Severity Filter: " . (strpos($html, 'id="detectionSevFilter"') !== false ? 'FOUND' : 'MISSING') . "\n";
echo "Status Filter: " . (strpos($html, 'id="detectionStatusFilter"') !== false ? 'FOUND' : 'MISSING') . "\n";

echo "Detection ID column: " . (strpos($html, 'Detection ID') !== false ? 'FOUND' : 'MISSING') . "\n";
echo "Detection Type column: " . (strpos($html, 'Detection Type') !== false ? 'FOUND' : 'MISSING') . "\n";
echo "Target column: " . (strpos($html, '<th>Target</th>') !== false ? 'FOUND' : 'MISSING') . "\n";
echo "Affected Files column: " . (strpos($html, 'Affected Files') !== false ? 'FOUND' : 'MISSING') . "\n";
echo "Severity column: " . (strpos($html, '<th>Severity</th>') !== false ? 'FOUND' : 'MISSING') . "\n";
echo "Risk Score column: " . (strpos($html, 'Risk Score') !== false ? 'FOUND' : 'MISSING') . "\n";
echo "Timestamp column: " . (strpos($html, '<th>Timestamp</th>') !== false ? 'FOUND' : 'MISSING') . "\n";
echo "Status column: " . (strpos($html, '<th>Status</th>') !== false ? 'FOUND' : 'MISSING') . "\n";
