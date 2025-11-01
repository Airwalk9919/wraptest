<?php
if (!function_exists('fmt_date')) {
  function fmt_date($s){ if(!$s) return ''; $t=strtotime($s); return $t?date('d/m/Y',$t):$s; }
}
$V = $r['vehicle']  ?? [];
$C = $r['customer'] ?? [];
$P = $r['panels']   ?? [];

// get panel by code
$getPanel = function($code) use ($P) {
  foreach ($P as $p) if (($p['code']??'') === $code) return $p;
  return null;
};

// base64 image helper
$img64 = function($path){
  if (!is_file($path)) return null;
  $ext  = strtolower(pathinfo($path, PATHINFO_EXTENSION));
  if ($ext === 'mp4') return null;             // abaikan video di PDF (atau buat thumbnail sendiri)
  $mime = ($ext==='png') ? 'image/png' : 'image/jpeg';
  return 'data:'.$mime.';base64,'.base64_encode(file_get_contents($path));
};

// fetch photos list for a panel code
$pphotos = function($code) use ($getPanel, $r, $img64) {   // <— tambahkan $img64 di sini
  $p = $getPanel($code); $out = [];
  if ($p && !empty($p['photos'])) {
    foreach ($p['photos'] as $f) {
      $path = FCPATH.'uploads/'.$r['code'].'/'.$f;
      $b64  = $img64($path);                               // aman, tidak undefined
      if ($b64) $out[] = $b64;
    }
  }
  return $out;
};
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<style>
  @page {
    margin: 16mm 14mm 16mm 14mm;
    header: html_hdr;
    footer: html_ftr;
  }
  body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 10.5pt; color:#111; }
  /* header/footer */
  .hdr-wrap { width:100%; }
  .hdr-left { width:70%; }
  .hdr-right{ width:30%; text-align:center; font-weight:bold; font-size:14pt; border:1px solid #000; padding:6px; }
  /* tables */
  table { border-collapse: collapse; width:100%; }
  th, td { border: 1px solid #000; padding: 6px; vertical-align: top; }
  .noborder td { border:0; }
  .soft th { background:#f3f4f6; }
  .muted { color:#555; }
  .bar { height:1px; background:#000; margin:8px 0 10px; }
  /* checklist */
  .box { display:inline-block; width:12px; height:12px; border:1px solid #000; margin:0 10px; }
  .fill { background:#000; }
  .cond-head { text-align:center; }
  .section { font-weight:700; margin:8px 0 4px; }
  /* photo grid */
  .grid td { border:none; padding:6px; }
  .ph { width:100%; height:140px; object-fit:cover; border:1px solid #000; }
  .ph-lg { height:160px; }
  .caption { font-size:9pt; color:#333; margin-top:2px; }
  .center { text-align:center; }
</style>
</head>
<body>

<!-- ===== Global Header & Footer ===== -->
<htmlpageheader name="hdr">
  <table class="noborder hdr-wrap">
    <tr>
      <td class="hdr-left">
        <?php
          $logo = FCPATH.'img/logo.png';
          if (is_file($logo)) {
            echo '<img src="'.$img64($logo).'" style="height:24px">';
          }
        ?>
      </td>
      <td class="hdr-right">INSPECTION<br>REPORT</td>
    </tr>
  </table>
  <div class="bar"></div>
</htmlpageheader>

<htmlpagefooter name="ftr">
  <div style="text-align:center; font-size:9pt; color:#666;">
    Page {PAGENO} of {nbpg} • <?= html_escape($r['code']) ?>
  </div>
</htmlpagefooter>

<!-- ===================== PAGE 1 ===================== -->
<!-- Metadata block -->
<table class="soft">
  <tr>
    <td style="width:18%;">CUSTOMER</td>
    <td style="width:32%;"><?= html_escape($C['full_name'] ?? '') ?></td>
    <td style="width:18%;">INSPECTION #</td>
    <td style="width:32%;"><?= html_escape($r['code']) ?></td>
  </tr>
  <tr>
    <td>INVOICE #</td><td><?= '' /* isi jika ada */ ?></td>
    <td>DATE</td><td><?= html_escape(fmt_date($r['inspection_date'] ?? $r['datetime'] ?? '')) ?></td>
  </tr>
  <tr>
    <td>INSPECTOR</td><td><?= html_escape($r['inspector']['name'] ?? '') ?></td>
    <td>LOCATION</td><td><?= html_escape($r['location'] ?? '') ?></td>
  </tr>
</table>

<table style="margin-top:6px;">
  <tr>
    <td style="width:18%;"><b>ITEM</b></td>
    <td style="width:32%;"><?= html_escape(($V['brand'] ?? '').' '.($V['model'] ?? '')) ?></td>
    <td style="width:18%;"><b>LICENSE PLATE</b></td>
    <td style="width:12%;"><?= html_escape($V['plate'] ?? '') ?></td>
    <td style="width:8%;"><b>MILEAGE (KM)</b></td>
    <td style="width:12%;"><?= isset($V['mileage_km'])? number_format($V['mileage_km']) : '' ?></td>
  </tr>
</table>

<div class="section" style="margin-top:10px;">ITEM INSPECTIONS</div>
<table>
  <tr>
    <th style="width:58%;">ARTICLE</th>
    <th class="cond-head" style="width:22%;">CONDITIONS<br>G &nbsp;&nbsp; F &nbsp;&nbsp; P</th>
    <th style="width:20%;">NOTES</th>
  </tr>
<?php
$items = [
  ['no'=>1,'code'=>'PAINT','label'=>'PAINT'],
  ['no'=>2,'code'=>'WINDSHIELD','label'=>'WINDSHIELD'],
  ['no'=>3,'code'=>'WINDOWS','label'=>'WINDOWS'],
  ['no'=>4,'code'=>'MIRRORS','label'=>'MIRRORS'],
  ['no'=>5,'code'=>'REAR_WINDOW','label'=>'REAR WINDOW'],
  ['no'=>6,'code'=>'TIRES','label'=>'TIRES'],
  ['no'=>7,'code'=>'WHEELS','label'=>'WHEELS'],
];
foreach ($items as $it):
  $p = $getPanel($it['code']); $st = strtoupper($p['status'] ?? '');
  $notes = trim($p['notes'] ?? '');
?>
  <tr>
    <td><?= $it['no'].'. '.$it['label'] ?></td>
    <td class="center">
      <span class="box <?= $st==='G'?'fill':'' ?>"></span>
      <span class="box <?= $st==='F'?'fill':'' ?>"></span>
      <span class="box <?= $st==='P'?'fill':'' ?>"></span>
    </td>
    <td><?= $notes !== '' ? html_escape($notes) : '' ?></td>
  </tr>
<?php endforeach; ?>
</table>

<div style="margin-top:12px;">
  <div class="muted"><b>Remarks :</b></div>
  <div class="muted" style="margin-top:6px;">
    G = GOOD – This item is in good condition and/or in performing to standard<br>
    F = FAIR – This item is in fair condition and/or in performing adequately.<br>
    P = POOR – This item is in poor condition and/or in performing below standard.
  </div>
</div>

<div style="border:1px solid #000; padding:6px; text-align:center; margin-top:14px; font-weight:bold;">
  DETAILED PHOTOGRAPH PRESENTED ON THE LATER PAGES
</div>

<!-- ===================== PAGE 2+ ===================== -->
<pagebreak />

<!-- CAR ANGLES -->
<div class="section">CAR</div>
<table class="grid">
  <tr>
    <?php
      $carAngles = [
        ['code'=>'ANGLE_FRONT','label'=>'Front'],
        ['code'=>'ANGLE_REAR','label'=>'Rear'],
        ['code'=>'ANGLE_LEFT','label'=>'Left'],
        ['code'=>'ANGLE_RIGHT','label'=>'Right'],
      ];
      $cells=0;
      foreach ($carAngles as $a){
        $img = ($pp = $pphotos($a['code'])) ? $pp[0] : null;
        echo '<td style="width:50%">'.
               ($img ? '<img class="ph ph-lg" src="'.$img.'">' : '<div class="ph ph-lg"></div>').
               '<div class="caption">'.$a['label'].'</div>'.
             '</td>';
        $cells++;
        if ($cells%2===0) echo '</tr><tr>';
      }
      if ($cells%2!==0) echo '<td></td></tr>';
    ?>
  </tr>
</table>

<?php
// PANEL PHOTO SECTIONS (1–7)
$panelSections = $items; // reuse same order
foreach ($panelSections as $idx => $it):
  $photos = $pphotos($it['code']);
?>
  <div class="bar" style="margin-top:18px;"></div>
  <div class="section"><?= $it['no'].'. '.$it['label'] ?></div>
  <?php if (empty($photos)): ?>
    <div class="muted">No photo.</div>
  <?php else: ?>
    <table class="grid">
      <?php
        $cols = 2; $i=0;
        foreach ($photos as $b64){
          if ($i%$cols===0) echo '<tr>';
          echo '<td style="width:50%"><img class="ph" src="'.$b64.'"></td>';
          if ($i%$cols===($cols-1)) echo '</tr>';
          $i++;
        }
        if ($i%$cols!==0) echo '<td></td></tr>';
      ?>
    </table>
  <?php endif; ?>
<?php endforeach; ?>

<!-- ===================== LAST PAGE: TERMS & SIGNATURE ===================== -->
<pagebreak />
<div class="section">Syarat dan Ketentuan – Serah Terima Kendaraan di Wrap Station</div>
<ol style="margin:6px 0 10px 18px;">
  <li>Kondisi kendaraan dapat berubah setelah pembersihan. Tim akan menginformasikan jika ada perubahan.</li>
  <li>Status cat kendaraan (repaint/original) tidak dapat dipastikan, risiko ditanggung pemilik.</li>
  <li>Penambahan jarak tempuh (mileage) bisa terjadi, dan bukan tanggung jawab Wrap Station.</li>
  <li>Kerusakan/malfungsi mesin selama atau setelah pengerjaan bukan tanggung jawab kami.</li>
  <li>Kerusakan akibat pembongkaran aksesoris oleh pihak lain bukan tanggung jawab kami.</li>
  <li>Kehilangan barang pribadi: bukan tanggung jawab Wrap Station. Harap kosongkan kendaraan.</li>
  <li>Wrap Station berhak melakukan tindakan teknis bila diperlukan dan disetujui sebelumnya.</li>
  <li>Kondisi/modifikasi khusus yang tidak dikonfirmasikan menjadi tanggung jawab pemilik.</li>
  <li>Penurunan baterai EV adalah kondisi alami, bukan tanggung jawab kami.</li>
  <li>Estimasi pengerjaan dapat berubah. Keterlambatan akan diinformasikan ke pelanggan.</li>
  <li>Dengan menandatangani, Anda menyetujui semua syarat dan ketentuan ini.</li>
</ol>

<div style="margin-top:12px;">Customer Signature :</div>
<?php
  $sig = $pphotos('SIGNATURE');
  if (!empty($sig)) {
    echo '<img src="'.$sig[0].'" style="height:120px; border:1px solid #000; padding:4px; border-radius:4px;">';
  } else {
    echo '<div style="width:360px;height:120px;border:1px solid #000;background:#fff;"></div>';
  }
?>
<div class="center" style="margin-top:12px;"><?= html_escape($C['full_name'] ?? '') ?></div>

</body>
</html>
