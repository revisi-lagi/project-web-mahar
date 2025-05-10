<?php
defined('BASEPATH') or exit('No direct script access allowed');

class CAdmin extends CI_Controller
{

    function __construct()
    {
        parent::__construct();
        $this->load->model('MAdmin');
    }

    public function index()
    {
        $this->load->view('welcome_message');
    }

    public function buat_kategori()
    {
        $data['nama'] = $this->input->post('nama');

        $this->MAdmin->tambah('kategori', $data);
        $this->session->set_flashdata('message', '<div class="alert alert-success">Record has been saved successfully.</div>');
        redirect(base_url());
    }
}
