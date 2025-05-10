<?php
defined('BASEPATH') or exit('No direct script access allowed');

class CPages extends CI_Controller
{

    public function customer()
    {
        $this->load->view('components/header.php');
        $this->load->view('pages/customer/beranda.php');
        $this->load->view('components/footer.php');
    }
}
