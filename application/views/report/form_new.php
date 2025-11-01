<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>New Inspection Report</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <style>
    body{background:#f7f9fc;}
    .wrap{display:grid; grid-template-columns: 72px 1fr; gap:16px;}
    .sidebar{
      background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:12px;
      display:flex; flex-direction:column; gap:12px; align-items:center;
    }
    .sidebar .ico{
      width:44px; height:44px; border-radius:10px; display:grid; place-items:center;
      color:#2563eb; background:#eaf1ff; border:1px solid #dbe7ff;
      cursor:default;
    }
    .card{border-radius:12px; border:1px solid #e5e7eb;}
    .form-label{color:#475569; font-weight:500;}
    .label-col{display:flex; align-items:center; color:#334155;}
    .muted{color:#94a3b8;}
  </style>
</head>
<body class="p-4">
  <div class="container">
    <h4 class="mb-3">Create Inspection Report</h4>

    <div class="wrap">
      <!-- Sidebar icons -->
      <div class="sidebar">
        <div class="ico" title="Vehicle"><i class="bi bi-car-front"></i></div>
        <div class="ico" title="Photos"><i class="bi bi-image"></i></div>
        <div class="ico" title="Save"><i class="bi bi-floppy"></i></div>
        <div class="ico" title="Submit"><i class="bi bi-check2"></i></div>
      </div>

      <!-- Form card -->
      <div class="card p-4">
        <form method="post" action="<?= site_url('report/create') ?>" class="row g-3">

          <!-- Location -->
          <div class="col-md-3 label-col">
            <label class="form-label mb-0">Location</label>
          </div>
          <div class="col-md-9">
            <input name="location" class="form-control" value="Wrap Station Medan" placeholder="e.g. Wrap Station Medan">
          </div>

          <!-- Customer First Name -->
          <div class="col-md-3 label-col"><label class="form-label mb-0">Customer First Name</label></div>
          <div class="col-md-9"><input name="cust_first" class="form-control" placeholder="John"></div>

          <!-- Customer Last Name -->
          <div class="col-md-3 label-col"><label class="form-label mb-0">Customer Last name</label></div>
          <div class="col-md-9"><input name="cust_last" class="form-control" placeholder="Doe"></div>

          <!-- Customer Phone -->
          <div class="col-md-3 label-col"><label class="form-label mb-0">Customer Phone Number</label></div>
          <div class="col-md-9"><input name="cust_phone" class="form-control" placeholder="+62..."></div>

          <!-- Car Brand -->
          <div class="col-md-3 label-col"><label class="form-label mb-0">Car Brand</label></div>
          <div class="col-md-9">
            <input name="brand" class="form-control" placeholder="Toyota" required>
          </div>

          <!-- Car Model -->
          <div class="col-md-3 label-col"><label class="form-label mb-0">Car Model</label></div>
          <div class="col-md-9">
            <input name="model" class="form-control" placeholder="Yaris" required>
          </div>

          <!-- Color -->
          <div class="col-md-3 label-col"><label class="form-label mb-0">Color</label></div>
          <div class="col-md-9">
            <select name="color" class="form-control">
              <option value="">-</option>
              <option>Black</option><option>White</option><option>Silver</option>
              <option>Red</option><option>Blue</option><option>Green</option>
            </select>
          </div>

          <!-- Year -->
          <div class="col-md-3 label-col"><label class="form-label mb-0">Year</label></div>
          <div class="col-md-9">
            <input name="year" type="number" min="1990" max="<?= date('Y')+1 ?>" class="form-control" placeholder="2022">
          </div>

          <!-- License Plate (No Spacing) -->
          <div class="col-md-3 label-col"><label class="form-label mb-0">License Plate (No Spacing)</label></div>
          <div class="col-md-9">
            <input id="plateInput" name="plate" class="form-control" placeholder="B1234CD" required>
            <div class="form-text muted">Otomatis diubah ke UPPERCASE & tanpa spasi.</div>
          </div>

          <!-- Inspection Date -->
          <div class="col-md-3 label-col"><label class="form-label mb-0">Inspection Date</label></div>
          <div class="col-md-9">
            <input type="date" name="inspection_date" class="form-control" value="<?= date('Y-m-d') ?>">
            <div class="form-text muted">Format: YYYY-MM-DD (otomatis isi hari ini).</div>
          </div>

          <!-- Mileage (Kilometer) -->
          <div class="col-md-3 label-col"><label class="form-label mb-0">Mileage (Kilometer)</label></div>
          <div class="col-md-9">
            <input type="number" name="mileage_km" class="form-control" min="0" step="1" placeholder="contoh: 32500">
          </div>

          <!-- Submit -->
          <div class="col-12 text-end">
            <button class="btn btn-primary">
              <i class="bi bi-check2 me-1"></i> Create Report
            </button>
          </div>
        </form>
      </div>
    </div>

    <div class="mt-3 muted">Tip: Setelah membuat report, kamu bisa upload foto/video di halaman detail.</div>
  </div>

  <script>
    // Uppercase + no space untuk plate
    const plate = document.getElementById('plateInput');
    plate.addEventListener('input', () => {
      plate.value = plate.value.toUpperCase().replace(/\s+/g,'');
    });
  </script>
</body>
</html>
