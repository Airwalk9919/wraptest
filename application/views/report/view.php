<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Report <?= html_escape($r['code']) ?></title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <style>
    /* Batasi lebar kartu agar kanvas tidak melebar */
    .sig-card { max-width: 340px; }

    /* Ukuran tampilan kanvas (CSS size) */
    #sigCanvas {
      width: 260px;          /* ubah sesuka kamu: 220px/300px, dll */
      height: 120px;
      border: 1px solid #e5e7eb;
      border-radius: 6px;
      background: #fff;
      touch-action: none;    /* penting untuk menggambar di mobile */
      display: block;
    }
    body { background:#f7f9fc; }
    .card{ background:#fff; border:1px solid #e5e7eb; border-radius:12px; }
    .muted{ color:#6b7280; }
    .btn-status { border:1px solid #dbe7ff; background:#eaf1ff; color:#1f4fd6; }
    .btn-status.active { background:#1f4fd6; color:#fff; }
    .panel-row{padding:14px 10px; border-bottom:1px dashed #e5e7eb;}
    .panel-row:last-child{border-bottom:0;}
    .media-card{border:1px solid #e5e7eb;border-radius:10px;padding:8px;background:#fff;}
    .media-card img, .media-card video{width:100%;border-radius:8px;object-fit:cover;aspect-ratio:16/9;}
    .note-btn{ background:#6b7280; color:#fff; border:0; }
    .note-box{ display:none; }
    .badge-chip{font-size:11px;}
    /* Vehicle angles board */
    .board { position:relative; width:100%; max-width:940px; margin:0 auto; background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:18px; }
    .board .canvas { position:relative; width:100%; padding-top:50%; background:#fafafa url('<?= base_url('img/car-top.png') ?>') center/contain no-repeat; border:1px dashed #d1d5db; border-radius:8px; }
    .drop { position:absolute; width:120px; height:90px; border:2px dashed #9ca3af; border-radius:10px; background:#ffffffa6; display:flex; align-items:center; justify-content:center; }
    .drop .ph { font-size:12px; color:#6b7280; text-align:center; }
    .drop .thumb { width:100%; height:100%; object-fit:cover; border-radius:8px; }
    /* approximate positions */
    .pos-front  { top:8%;  left:50%; transform:translate(-50%,0); }
    .pos-rear   { bottom:6%; left:50%; transform:translate(-50%,0); }
    .pos-left   { top:50%;  left:6%;  transform:translate(0,-50%);  height:160px; }
    .pos-right  { top:50%;  right:6%; transform:translate(0,-50%);  height:160px; }
  </style>
</head>
<body class="p-4">
<div class="container">

  <?php
    $panels = $r['panels'] ?? [];
    function get_panel($panels,$code){ foreach($panels as $p){ if(($p['code']??'')===$code) return $p; } return null; }

    // --- Completeness checks ---
    $reqChecklist = ['PAINT','WINDSHIELD','WINDOWS','MIRRORS','REAR_WINDOW','TIRES','WHEELS'];
    $tab1Complete = true;
    foreach ($reqChecklist as $c) { $p = get_panel($panels,$c); if(!$p || empty($p['status'])) { $tab1Complete=false; break; } }

    $reqAngles = ['ANGLE_FRONT','ANGLE_REAR','ANGLE_LEFT','ANGLE_RIGHT'];
    $tab2Complete = true;
    foreach ($reqAngles as $c) { $p = get_panel($panels,$c); if(!$p || empty($p['photos'])) { $tab2Complete=false; break; } }

    $tab3Complete = true; // placeholder; nanti kita ganti logika aslinya
    $allComplete  = $tab1Complete && $tab2Complete && $tab3Complete;
  ?>

  <!-- Header -->
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <h4 class="mb-1">Inspection Report</h4>
      <div class="muted">Code: <?= html_escape($r['code']) ?> • Plate: <?= html_escape($r['vehicle']['plate']) ?> • <?= html_escape($r['inspection_date'] ?? '') ?></div>
    </div>
    <div class="d-flex gap-2">
      <?php if ($allComplete): ?>
        <a class="btn btn-success" href="<?= site_url('report/'.$r['code'].'/pdf')?>" target="_blank"><i class="bi bi-filetype-pdf me-1"></i>Export PDF</a>
      <?php else: ?>
        <button class="btn btn-success" disabled><i class="bi bi-filetype-pdf me-1"></i>Export PDF</button>
        <span class="small text-danger">
          Lengkapi: <?= $tab1Complete?'':'Checklist ' ?><?= (!$tab1Complete && !$tab2Complete)?'& ':''
          ?><?= $tab2Complete?'':'Vehicle Angles' ?><?= (!$tab1Complete || !$tab2Complete) && !$tab3Complete ? ' & Final':'' ?>
        </span>
      <?php endif; ?>
    </div>
  </div>

  <!-- NAV TABS -->
  <ul class="nav nav-tabs" role="tablist">
    <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tab1" role="tab"><i class="bi bi-journal-text me-1"></i>Checklist</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab2" role="tab"><i class="bi bi-camera me-1"></i>Vehicle Angles</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab3" role="tab"><i class="bi bi-check2-circle me-1"></i>Final & Export</a></li>
  </ul>

  <div class="tab-content pt-3">

    <!-- ============= TAB 1: CHECKLIST (G/F/P) ============= -->
    <div class="tab-pane fade show active" id="tab1" role="tabpanel">
      <div class="card p-3 mb-3">
        <div class="row text-center">
          <div class="col-6 col-md-3"><div class="muted">Brand</div><div class="fs-6"><?= html_escape($r['vehicle']['brand']) ?></div></div>
          <div class="col-6 col-md-3"><div class="muted">Model</div><div class="fs-6"><?= html_escape($r['vehicle']['model']) ?></div></div>
          <div class="col-6 col-md-3"><div class="muted">Mileage</div><div class="fs-6"><?= isset($r['vehicle']['mileage_km'])? number_format($r['vehicle']['mileage_km']).' km':'-' ?></div></div>
          <div class="col-6 col-md-3"><div class="muted">Location</div><div class="fs-6"><?= html_escape($r['location'] ?? '') ?></div></div>
        </div>
      </div>

      <?php
        $groups = [
          'Paint' => [['code'=>'PAINT','label'=>'Paint']],
          'Glass' => [
            ['code'=>'WINDSHIELD','label'=>'Windshield'],
            ['code'=>'WINDOWS','label'=>'Windows'],
            ['code'=>'MIRRORS','label'=>'Mirrors'],
            ['code'=>'REAR_WINDOW','label'=>'Rear Window'],
          ],
          'Tires and Wheels' => [
            ['code'=>'TIRES','label'=>'Tires'],
            ['code'=>'WHEELS','label'=>'Wheels'],
          ],
        ];
      ?>

      <div class="muted mb-2">G = Good &nbsp;&nbsp; F = Fair &nbsp;&nbsp; P = Poor</div>

      <?php foreach ($groups as $title => $items): ?>
        <div class="text-center fw-bold mb-1"><?= html_escape($title) ?></div>
        <div class="card mb-3">
          <?php foreach ($items as $it):
            $code = $it['code']; $label = $it['label'];
            $saved = get_panel($panels,$code);
            $savedStatus = strtoupper($saved['status'] ?? '');
            $savedNotes  = $saved['notes'] ?? '';
            $savedPhotos = $saved['photos'] ?? [];
          ?>
            <div class="panel-row">
              <div class="row align-items-start g-2">
                <div class="col-12 col-md-3">
                  <div class="btn-group" role="group">
                    <button type="button" class="btn btn-status <?= $savedStatus==='G'?'active':'' ?> js-status" data-status="G" data-target="#box-<?= $code ?>">G</button>
                    <button type="button" class="btn btn-status <?= $savedStatus==='F'?'active':'' ?> js-status" data-status="F" data-target="#box-<?= $code ?>">F</button>
                    <button type="button" class="btn btn-status <?= $savedStatus==='P'?'active':'' ?> js-status" data-status="P" data-target="#box-<?= $code ?>">P</button>
                  </div>
                  <?php if ($savedStatus): ?><span class="badge bg-secondary ms-2 badge-chip">Saved: <?= $savedStatus ?></span><?php endif; ?>
                </div>
                <div class="col-8 col-md-6 d-flex align-items-center"><strong><?= html_escape($label) ?></strong></div>
                <div class="col-4 col-md-3 text-end">
                  <button type="button" class="btn note-btn btn-sm" onclick="document.querySelector('#box-<?= $code ?>').style.display='block'; document.querySelector('#box-<?= $code ?> textarea')?.focus();">Note</button>
                </div>

                <div class="col-12">
                  <form class="note-box card p-3 mt-2" id="box-<?= $code ?>" method="post"
                        action="<?= site_url('report/'.$r['code'].'/upload')?>" enctype="multipart/form-data"
                        style="<?= $savedStatus ? '' : 'display:none;' ?>">
                    <input type="hidden" name="panel"  value="<?= html_escape($code) ?>">
                    <input type="hidden" name="status" value="<?= html_escape($savedStatus ?: '') ?>" class="js-hidden-status">

                    <label class="form-label mb-1">Notes</label>
                    <textarea name="notes" class="form-control mb-2" rows="3" placeholder="Ketik catatan (opsional)"><?= html_escape($savedNotes) ?></textarea>

                    <label class="form-label mb-1">Upload Photo/Video</label>
                    <input type="file" name="media" class="form-control mb-3" accept=".jpg,.jpeg,.png,.gif,.mp4" <?= empty($savedPhotos) ? 'required' : '' ?>>

                    <div class="d-flex gap-2">
                      <button class="btn btn-primary"><i class="bi bi-upload me-1"></i>Save</button>
                      <span class="muted">jpg/png/gif/mp4. Maks 20MB.</span>
                    </div>

                    <?php if (!empty($savedPhotos)): ?>
                      <div class="row g-2 mt-3">
                        <?php foreach ($savedPhotos as $pf): $url = base_url('uploads/'.$r['code'].'/'.$pf); ?>
                          <div class="col-6 col-md-3">
                            <div class="media-card">
                              <?php if (preg_match('/\.(mp4)$/i',$pf)): ?><video src="<?= $url ?>" controls></video>
                              <?php else: ?><img src="<?= $url ?>" alt=""><?php endif; ?>
                              <div class="small muted mt-1 text-truncate"><?= html_escape($pf) ?></div>
                            </div>
                          </div>
                        <?php endforeach; ?>
                      </div>
                    <?php endif; ?>
                  </form>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- ============= TAB 2: VEHICLE ANGLES (Front/Rear/Left/Right) ============= -->
    <div class="tab-pane fade" id="tab2" role="tabpanel">
      <div class="board mb-3">
        <div class="canvas">
          <?php
            $angles = [
              ['code'=>'ANGLE_FRONT','label'=>'Front','pos'=>'pos-front'],
              ['code'=>'ANGLE_REAR','label'=>'Rear','pos'=>'pos-rear'],
              ['code'=>'ANGLE_LEFT','label'=>'Left','pos'=>'pos-left'],
              ['code'=>'ANGLE_RIGHT','label'=>'Right','pos'=>'pos-right'],
            ];
            foreach ($angles as $a):
              $ap = get_panel($panels,$a['code']); $photo = $ap['photos'][0] ?? null;
              $thumb = $photo ? base_url('uploads/'.$r['code'].'/'.$photo) : null;
          ?>
            <div class="drop <?= $a['pos'] ?>">
              <?php if ($thumb): ?>
                <img class="thumb" src="<?= $thumb ?>" alt="">
              <?php else: ?>
                <div class="ph"><?= $a['label'] ?><br><small>Upload</small></div>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="row g-3">
        <?php foreach ($angles as $a):
          $ap = get_panel($panels,$a['code']); $photo = $ap['photos'][0] ?? null;
        ?>
          <div class="col-12 col-md-6 col-lg-3">
            <div class="card p-3 h-100">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <strong><?= $a['label'] ?></strong>
                <?php if ($photo): ?><span class="badge bg-success">Done</span><?php else: ?><span class="badge bg-secondary">Pending</span><?php endif; ?>
              </div>
              <form action="<?= site_url('report/'.$r['code'].'/upload')?>" method="post" enctype="multipart/form-data">
                <input type="hidden" name="panel" value="<?= $a['code'] ?>">
                <input type="file" name="media" class="form-control mb-2" accept=".jpg,.jpeg,.png,.gif,.mp4" <?= $photo?'':'required' ?>>
                <input type="text" name="notes" class="form-control mb-2" placeholder="Notes (opsional)" value="<?= html_escape($ap['notes'] ?? '') ?>">
                <button class="btn btn-primary w-100">Save</button>
              </form>
              <?php if ($photo): $url = base_url('uploads/'.$r['code'].'/'.$photo); ?>
                <div class="media-card mt-2">
                  <?php if (preg_match('/\.(mp4)$/i',$photo)): ?><video src="<?= $url ?>" controls></video>
                  <?php else: ?><img src="<?= $url ?>" alt=""><?php endif; ?>
                  <div class="small muted mt-1 text-truncate"><?= html_escape($photo) ?></div>
                </div>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

                    <?php
  // siapkan “status lengkap” untuk dipakai di JS
  $tabFlags = [
    'tab1' => $tab1Complete ? 1 : 0,
    'tab2' => $tab2Complete ? 1 : 0,
  ];
?>

<!-- ============= TAB 3: FINAL & EXPORT ============= -->
<div class="tab-pane fade" id="tab3" role="tabpanel">
  <div class="card p-3 mb-3">
    <h5 class="mb-2">Syarat dan Ketentuan – Serah Terima Kendaraan di Wrap Station</h5>
    <textarea class="form-control" rows="8" readonly style="background:#fff">
1. Kondisi kendaraan dapat berubah setelah pembersihan. Tim akan menginformasikan jika ada perubahan.
2. Status cat kendaraan (repaint/original) tidak dapat dipastikan, risiko ditanggung pemilik.
3. Penambahan jarak tempuh (mileage) bisa terjadi, bukan tanggung jawab Wrap Station.
4. Kerusakan/malfungsi mesin atau sistem setelah pengerjaan bukan tanggung jawab kami.
5. Kerusakan akibat pembongkaran aksesoris pihak lain bukan tanggung jawab kami.
6. Kehilangan barang pribadi bukan tanggung jawab Wrap Station. Harap kosongkan kendaraan.
7. Tindakan teknis dilakukan bila disetujui sebelumnya.
8. Modifikasi khusus yang tidak dikonfirmasi menjadi tanggung jawab pemilik.
9. Penurunan baterai EV adalah kondisi alami.
10. Estimasi pengerjaan dapat berubah dan akan diinformasikan ke pelanggan.
    </textarea>

    <div class="form-check mt-2">
      <input class="form-check-input" type="checkbox" id="agreeChk">
      <label for="agreeChk" class="form-check-label">
        Saya telah membaca dan menyetujui Syarat dan Ketentuan di atas.
      </label>
    </div>
  </div>

  <div class="card p-3 sig-card">
    <div class="mb-2"><strong>Signature</strong></div>

    <!-- width/height HTML diabaikan untuk tampilan; dipakai untuk resolusi bitmap -->
    <canvas id="sigCanvas" width="260" height="120"></canvas>

    <div class="mt-2 d-flex gap-2">
      <button id="btnClear" class="btn btn-warning btn-sm">Clear</button>
      <button id="btnSubmitFinal" class="btn btn-success btn-sm">Submit</button>
    </div>
  </div>
</div>



  </div> <!-- /tab-content -->

  <!-- (Opsional) All Media gallery di bawah… hilangkan jika tak perlu -->
  <div class="mt-4">
    <div class="fw-bold mb-2">All Media</div>
    <div class="row g-3">
      <?php foreach ($files as $f): $url = base_url('uploads/'.$r['code'].'/'.$f); ?>
        <div class="col-6 col-md-3">
          <div class="media-card">
            <?php if (preg_match('/\.(mp4)$/i',$f)): ?><video src="<?= $url ?>" controls></video>
            <?php else: ?><img src="<?= $url ?>" alt=""><?php endif; ?>
            <div class="small muted mt-1 text-truncate"><?= html_escape($f) ?></div>
          </div>
        </div>
      <?php endforeach; if (empty($files)): ?>
        <div class="muted">Belum ada media.</div>
      <?php endif; ?>
    </div>
  </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
   // ----- status kelengkapan dari PHP -----
  const TAB_COMPLETE = <?= json_encode($tabFlags) ?>;

  // Canvas signature (kode yang lama boleh tetap dipakai)
  const cvs = document.getElementById('sigCanvas');
  const ctx = cvs.getContext('2d');
  let drawing=false, dirty=false, last=null;
  const start=(x,y)=>{drawing=true; last={x,y};};
  const line=(x,y)=>{ if(!drawing) return; ctx.lineWidth=2; ctx.lineCap='round'; ctx.strokeStyle='#111';
    ctx.beginPath(); ctx.moveTo(last.x,last.y); ctx.lineTo(x,y); ctx.stroke(); last={x,y}; dirty=true; };
  const stop=()=>{drawing=false;};
  cvs.addEventListener('mousedown',e=>start(e.offsetX,e.offsetY));
  cvs.addEventListener('mousemove',e=>line(e.offsetX,e.offsetY));
  cvs.addEventListener('mouseup',stop); cvs.addEventListener('mouseleave',stop);
  cvs.addEventListener('touchstart',e=>{const r=cvs.getBoundingClientRect();const t=e.touches[0];start(t.clientX-r.left,t.clientY-r.top);e.preventDefault();},{passive:false});
  cvs.addEventListener('touchmove',e=>{const r=cvs.getBoundingClientRect();const t=e.touches[0];line(t.clientX-r.left,t.clientY-r.top);e.preventDefault();},{passive:false});
  cvs.addEventListener('touchend',stop);
  document.getElementById('btnClear').onclick = ()=>{ ctx.clearRect(0,0,cvs.width,cvs.height); dirty=false; };

  // Helper: canvas -> Blob
  const toBlob = () => new Promise(res => cvs.toBlob(b=>res(b), 'image/png'));

  document.getElementById('btnSubmitFinal').onclick = async () => {
    const agree = document.getElementById('agreeChk').checked;

    // Validasi awal (pakai alert biasa biar cepat)
    if (!TAB_COMPLETE.tab1 || !TAB_COMPLETE.tab2) {
      Swal.fire('Incomplete', 'Lengkapi Tab 1 (Checklist) dan Tab 2 (Vehicle Angles) dulu.', 'warning');
      return;
    }
    if (!agree) { Swal.fire('Checklist', 'Centang persetujuan Syarat & Ketentuan.', 'info'); return; }
    if (!dirty) { Swal.fire('Signature', 'Mohon tanda tangani pada kotak Signature.', 'info'); return; }

    // Konfirmasi
    const conf = await Swal.fire({
      icon: 'warning',
      title: 'Are you sure?',
      text: 'Do you want to proceed?',
      showCancelButton: true,
      confirmButtonText: 'Yes, proceed',
      cancelButtonText: 'Cancel',
      reverseButtons: true
    });
    if (!conf.isConfirmed) return;

    // Modal loading
    Swal.fire({
      title: 'Please wait...',
      html: 'Processing inspection data',
      allowOutsideClick: false,
      didOpen: () => { Swal.showLoading(); }
    });

    try {
      // Upload signature
      const blob = await toBlob();
      const fd = new FormData();
      fd.append('panel','SIGNATURE');
      fd.append('status','AGREED');
      fd.append('notes','Signed at '+ new Date().toISOString());
      fd.append('media', blob, 'signature.png');

      const resp = await fetch('<?= site_url('report/'.$r['code'].'/upload') ?>', {
        method: 'POST', body: fd
      });
      if (!resp.ok) throw new Error(await resp.text());

      // Sukses
      Swal.fire({
        icon: 'success',
        title: 'Success',
        text: 'Signature saved. Generating PDF...',
        timer: 1200,
        showConfirmButton: false
      });
      // Buka PDF
      window.open('<?= site_url('report/'.$r['code'].'/pdf') ?>', '_blank');
    } catch (e) {
      Swal.fire('Error', e.message || 'Failed to submit.', 'error');
    }
  };

// ----- Sinkron tombol G/F/P existing (dari Tab 1) -----
document.querySelectorAll('.js-status').forEach(btn => {
  btn.addEventListener('click', () => {
    const targetSel = btn.getAttribute('data-target');
    const box = document.querySelector(targetSel);
    box.style.display = 'block';
    const group = btn.parentElement;
    group.querySelectorAll('.btn-status').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    const form = box.closest('form');
    form.querySelector('.js-hidden-status').value = btn.getAttribute('data-status');
  });
});

// === Signature canvas ==

function resizeCanvasToDisplaySize() {
  // jika tab masih hidden, tunda
  if (cvs.clientWidth === 0 || cvs.clientHeight === 0) return;

  const ratio = Math.max(window.devicePixelRatio || 1, 1);
  const displayWidth  = Math.round(cvs.clientWidth  * ratio);
  const displayHeight = Math.round(cvs.clientHeight * ratio);

  if (cvs.width !== displayWidth || cvs.height !== displayHeight) {
    cvs.width = displayWidth;
    cvs.height = displayHeight;
  }
  // set transform agar koordinat pakai CSS pixels
  ctx.setTransform(ratio, 0, 0, ratio, 0, 0);
}

// panggil saat load (kalau kebetulan tab3 sudah terlihat)
resizeCanvasToDisplaySize();
// panggil saat window di-resize
window.addEventListener('resize', resizeCanvasToDisplaySize);

// panggil saat tab3 benar-benar ditampilkan (Bootstrap event)
const tab3Link = document.querySelector('[data-bs-toggle="tab"][href="#tab3"]');
if (tab3Link) {
  tab3Link.addEventListener('shown.bs.tab', () => {
    // tunggu satu frame agar layout sudah settled
    requestAnimationFrame(resizeCanvasToDisplaySize);
  });
}

// tombol clear tetap mereset ukuran & transform
document.getElementById('btnClear').addEventListener('click', () => {
  ctx.clearRect(0, 0, cvs.width, cvs.height);
  resizeCanvasToDisplaySize();
});
</script>
</body>
</html>
