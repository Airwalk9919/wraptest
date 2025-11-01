<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Report extends CI_Controller
{
    private $reportDir;
    private $uploadBase;

    public function __construct()
        {
            parent::__construct();
            date_default_timezone_set('Asia/Jakarta');
            $this->load->helper(['url','file','form']);
            $this->load->library('form_validation');

            $this->reportDir  = FCPATH.'storage/reports/';
            $this->uploadBase = FCPATH.'uploads/';

            if (!is_dir($this->reportDir))  @mkdir($this->reportDir, 0775, true);
            if (!is_dir($this->uploadBase)) @mkdir($this->uploadBase, 0775, true);
        }


    public function index() { return $this->new(); }

    public function new()
    {
        $this->load->view('report/form_new');
    }

   public function create()
{
    $code = 'RPT-'.date('ymd-His');

    // Ambil nilai form
    $location      = $this->input->post('location', true);
    $custFirst     = $this->input->post('cust_first', true);
    $custLast      = $this->input->post('cust_last', true);
    $custPhone     = $this->input->post('cust_phone', true);
    $brand         = $this->input->post('brand', true);
    $model         = $this->input->post('model', true);
    $color         = $this->input->post('color', true);
    $year          = $this->input->post('year', true);
    $plate         = strtoupper(str_replace(' ','', $this->input->post('plate', true)));
    $inspectorName = $this->input->post('inspector', true);

    // Tambahan baru
    $inspectionDate = $this->input->post('inspection_date', true) ?: date('Y-m-d');
    $mileageKm      = (int)($this->input->post('mileage_km', true) ?? 0);

    $payload = [
        'code'            => $code,
        'inspection_date' => $inspectionDate,
        'location'        => $location,
        'customer'        => [
            'first_name' => $custFirst,
            'last_name'  => $custLast,
            'phone'      => $custPhone,
            'full_name'  => trim(($custFirst ?? '').' '.($custLast ?? '')),
        ],
        'vehicle' => [
            'brand'      => $brand,
            'model'      => $model,
            'color'      => $color,
            'year'       => $year,
            'plate'      => $plate,
            'mileage_km' => $mileageKm
        ],
        'inspector' => ['name'=>$inspectorName],
        'datetime'  => date('c'),

        // biarkan apa adanya
        'panels'    => [],
        'checklist' => [
            ['item'=>'Edges sealed','ok'=>false,'notes'=>''],
            ['item'=>'No dust under film','ok'=>false,'notes'=>''],
            ['item'=>'No edge-lift','ok'=>false,'notes'=>'']
        ],
        'summary' => ['overall'=>'','recommendation'=>'']
    ];

    $this->_json_write($this->_report_path($code), $payload);
    @mkdir($this->_media_dir($code), 0775, true);
    redirect('report/view/'.$code);
}


    public function view($code='')
    {
        $rep = $this->_load_report($code);
        if (!$rep) show_404();

        $files = $this->_list_media_files($code);
        $this->load->view('report/view', ['r'=>$rep,'files'=>$files]);
    }

    public function upload($code='')
    {
        $rep = $this->_load_report($code);
        if (!$rep) show_404();

        $dir = $this->_media_dir($code);
        if (!is_dir($dir)) @mkdir($dir, 0775, true);

        $panel  = $this->input->post('panel', true) ?: '';
        $status = $this->input->post('status', true) ?: '';
        $notes  = $this->input->post('notes', true) ?: '';

        $filename = null;
        $hasFile  = isset($_FILES['media']) && !empty($_FILES['media']['name']);

        if ($hasFile) {
            $config = [
                'upload_path'   => $dir,
                'allowed_types' => 'jpg|jpeg|png|gif|mp4',
                'max_size'      => 20480, // 20MB
                'encrypt_name'  => TRUE
            ];
            $this->load->library('upload', $config);
            if (!$this->upload->do_upload('media')) {
                show_error($this->upload->display_errors('', ''), 400);
            }
            $data = $this->upload->data();
            $filename = $data['file_name'];
        }

        if ($panel) {
            $rep['panels'] = $rep['panels'] ?? [];
            $found = false;
            foreach ($rep['panels'] as &$p) {
                if (($p['code'] ?? '') === $panel) {
                    if ($filename) { $p['photos'][] = $filename; }
                    if ($status)   { $p['status']   = $status;   }
                    if ($notes !== '') { $p['notes'] = $notes; }
                    $found = true; break;
                }
            }
            if (!$found) {
                $rep['panels'][] = [
                    'code'   => $panel,
                    'status' => $status,
                    'notes'  => $notes,
                    'photos' => $filename ? [$filename] : []
                ];
            }
            $this->_save_report($rep);
        }

        redirect('report/view/'.$code);
    }


   public function pdf($code='')
{
    $rep = $this->_load_report($code);
    if (!$rep) show_404();

    // (guard kelengkapan yang sudah kamu pasang)

    // Render view sekali
    $html = $this->load->view('report/pdf', ['r'=>$rep], TRUE);

    require_once FCPATH.'vendor/autoload.php';
    $temp = APPPATH.'cache/mpdf_tmp';
    if (!is_dir($temp)) @mkdir($temp, 0777, true);

    // Naikkan batas PCRE (besar karena ada banyak tag)
    @ini_set('pcre.backtrack_limit', '20000000');   // 20 juta
    @ini_set('pcre.recursion_limit', '1000000');
    @set_time_limit(0);

    $mpdf = new \Mpdf\Mpdf([
        'format'        => 'A4',
        'tempDir'       => $temp,
        'margin_left'   => 14,
        'margin_right'  => 14,
        'margin_top'    => 18,
        'margin_bottom' => 18,
        'useSubstitutions' => false,   // sedikit mengurangi parse cost
    ]);

    // Tulis bertahap per <pagebreak />
    $parts = preg_split('/<pagebreak\s*\/?>/i', $html, -1, PREG_SPLIT_NO_EMPTY);
    if ($parts) {
        // tulis CSS jika ada <style> di bagian pertama (opsional, aman dibiarkan langsung)
        $mpdf->WriteHTML($parts[0]);
        for ($i = 1; $i < count($parts); $i++) {
            $mpdf->AddPage();
            $mpdf->WriteHTML($parts[$i]);
        }
    } else {
        $mpdf->WriteHTML($html);
    }

    $mpdf->Output('inspection_'.$rep['code'].'.pdf', 'I');
}



    private function _report_path($code) { return rtrim($this->reportDir,'/').'/'.$code.'.json'; }
    private function _media_dir($code)   { return rtrim($this->uploadBase,'/').'/'.$code; }

    private function _json_read($file) {
        if (!file_exists($file)) return NULL;
        return json_decode(file_get_contents($file), true);
    }
    private function _json_write($file, $arr) {
        file_put_contents($file, json_encode($arr, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE));
    }
    private function _load_report($code) {
        if (!$code) return NULL;
        return $this->_json_read($this->_report_path($code));
    }
    private function _save_report($rep) {
        $this->_json_write($this->_report_path($rep['code']), $rep);
    }
    private function _list_media_files($code) {
        $dir = $this->_media_dir($code);
        if (!is_dir($dir)) return [];
        $files = array_values(array_filter(scandir($dir), function($f){ return $f !== '.' && $f !== '..'; }));
        return $files;
    }
}
