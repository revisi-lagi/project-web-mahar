<?php
defined('BASEPATH') or exit('No direct script access allowed');

class CCustomer extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('MCustomer');
        $this->load->model('MAdmin'); // Untuk akses data produk

        // Validasi login (role customer/pengguna)
        if (!$this->session->userdata('logged_in') || $this->session->userdata('role') != 'pengguna') {
            redirect('auth/login');
        }
    }

    // ==================== MANAJEMEN KERANJANG ====================
    public function keranjang()
    {
        $user_id = $this->session->userdata('user_id');

        // Dapatkan atau buat keranjang
        $keranjang = $this->MCustomer->get_keranjang_by_user($user_id);
        if (!$keranjang) {
            $keranjang_id = $this->MCustomer->buat_keranjang($user_id);
            $keranjang = $this->MCustomer->get_keranjang_by_user($user_id);
        }

        $data['items'] = $this->MCustomer->get_item_keranjang($keranjang->id);
        $this->load->view('customer/keranjang', $data);
    }

    public function tambah_ke_keranjang()
    {
        $user_id = $this->session->userdata('user_id');
        $produk_id = $this->input->post('produk_id');
        $jumlah = $this->input->post('jumlah') ?: 1;

        // Dapatkan info produk
        $produk = $this->MAdmin->get_by_id('produk', $produk_id);
        if (!$produk) {
            $this->session->set_flashdata('error', 'Produk tidak ditemukan');
            redirect($_SERVER['HTTP_REFERER']);
        }

        // Dapatkan atau buat keranjang
        $keranjang = $this->MCustomer->get_keranjang_by_user($user_id);
        if (!$keranjang) {
            $keranjang_id = $this->MCustomer->buat_keranjang($user_id);
            $keranjang = $this->MCustomer->get_keranjang_by_user($user_id);
        }

        // Tambahkan item ke keranjang
        $this->MCustomer->tambah_item_keranjang([
            'keranjang_id' => $keranjang->id,
            'produk_id' => $produk_id,
            'jumlah' => $jumlah,
            'harga' => $produk->harga,
            'total' => $jumlah * $produk->harga
        ]);

        $this->session->set_flashdata('success', 'Produk berhasil ditambahkan ke keranjang');
        redirect('customer/keranjang');
    }

    public function update_keranjang()
    {
        $item_id = $this->input->post('item_id');
        $jumlah = $this->input->post('jumlah');

        $this->MCustomer->update_item_keranjang($item_id, $jumlah);
        $this->session->set_flashdata('success', 'Keranjang berhasil diperbarui');
        redirect('customer/keranjang');
    }

    public function hapus_item_keranjang($item_id)
    {
        $this->MCustomer->hapus_item_keranjang($item_id);
        $this->session->set_flashdata('success', 'Item berhasil dihapus dari keranjang');
        redirect('customer/keranjang');
    }

    // ==================== MANAJEMEN PESANAN ====================
    public function checkout()
    {
        $user_id = $this->session->userdata('user_id');

        // Validasi keranjang tidak kosong
        $keranjang = $this->MCustomer->get_keranjang_by_user($user_id);
        $items = $this->MCustomer->get_item_keranjang($keranjang->id);

        if (empty($items)) {
            $this->session->set_flashdata('error', 'Keranjang belanja kosong');
            redirect('customer/keranjang');
        }

        $data['items'] = $items;
        $data['alamat'] = $this->MCustomer->get_alamat_user($user_id);
        $data['voucher'] = $this->MAdmin->get_voucher_aktif();

        $this->load->view('customer/checkout', $data);
    }

    public function proses_pesanan()
    {
        $user_id = $this->session->userdata('user_id');

        // Validasi input
        $this->form_validation->set_rules('alamat_id', 'Alamat', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->checkout();
            return;
        }

        // Dapatkan data keranjang
        $keranjang = $this->MCustomer->get_keranjang_by_user($user_id);
        $items = $this->MCustomer->get_item_keranjang($keranjang->id);

        // Hitung total
        $subtotal = array_reduce($items, function ($carry, $item) {
            return $carry + ($item->harga * $item->jumlah);
        }, 0);

        // Data pesanan
        $data_pesanan = [
            'nomor_pesanan' => 'ORD-' . date('Ymd') . '-' . substr(time(), -4),
            'pengguna_id' => $user_id,
            'alamat_id' => $this->input->post('alamat_id'),
            'voucher_id' => $this->input->post('voucher_id') ?: null,
            'subtotal' => $subtotal,
            'total' => $subtotal, // Akan diupdate jika ada voucher
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s')
        ];

        // Proses voucher jika ada
        if ($this->input->post('voucher_id')) {
            $voucher = $this->MAdmin->get_by_id('voucher', $this->input->post('voucher_id'));
            if ($voucher) {
                $diskon = $voucher->tipe_diskon == 'persen'
                    ? ($subtotal * $voucher->nilai_diskon / 100)
                    : $voucher->nilai_diskon;

                // Batasi diskon maksimum jika ada
                if ($voucher->maksimum_diskon && $diskon > $voucher->maksimum_diskon) {
                    $diskon = $voucher->maksimum_diskon;
                }

                $data_pesanan['total'] = max($subtotal - $diskon, 0);
            }
        }

        // Format item untuk disimpan
        $item_pesanan = array_map(function ($item) {
            return [
                'produk_id' => $item->produk_id,
                'jumlah' => $item->jumlah,
                'harga' => $item->harga,
                'total' => $item->total
            ];
        }, $items);

        // Buat pesanan
        $pesanan_id = $this->MCustomer->buat_pesanan($data_pesanan, $item_pesanan);

        $this->session->set_flashdata('success', 'Pesanan berhasil dibuat dengan nomor: ' . $data_pesanan['nomor_pesanan']);
        redirect('customer/pesanan/detail/' . $pesanan_id);
    }

    public function daftar_pesanan()
    {
        $user_id = $this->session->userdata('user_id');
        $data['pesanan'] = $this->MCustomer->get_pesanan_by_user($user_id);
        $this->load->view('customer/daftar_pesanan', $data);
    }

    public function detail_pesanan($pesanan_id)
    {
        $user_id = $this->session->userdata('user_id');
        $data['pesanan'] = $this->MCustomer->get_detail_pesanan($pesanan_id, $user_id);
        $data['items'] = $this->MCustomer->get_item_pesanan($pesanan_id);

        if (!$data['pesanan']) {
            show_404();
        }

        $this->load->view('customer/detail_pesanan', $data);
    }

    // ==================== MANAJEMEN ALAMAT ====================
    public function tambah_alamat()
    {
        $user_id = $this->session->userdata('user_id');

        $this->form_validation->set_rules('nama_penerima', 'Nama Penerima', 'required');
        $this->form_validation->set_rules('no_telepon', 'No Telepon', 'required');
        $this->form_validation->set_rules('alamat_lengkap', 'Alamat Lengkap', 'required');

        if ($this->form_validation->run() == TRUE) {
            $data = [
                'pengguna_id' => $user_id,
                'nama_penerima' => $this->input->post('nama_penerima'),
                'no_telepon' => $this->input->post('no_telepon'),
                'provinsi' => $this->input->post('provinsi'),
                'kota' => $this->input->post('kota'),
                'kecamatan' => $this->input->post('kecamatan'),
                'kelurahan' => $this->input->post('kelurahan'),
                'kode_pos' => $this->input->post('kode_pos'),
                'alamat_lengkap' => $this->input->post('alamat_lengkap'),
                'detail_alamat' => $this->input->post('detail_alamat'),
                'is_default' => $this->input->post('is_default') ? 1 : 0
            ];

            $this->MCustomer->tambah_alamat($data);
            $this->session->set_flashdata('success', 'Alamat berhasil ditambahkan');
            redirect('customer/checkout');
        }

        $this->load->view('customer/tambah_alamat');
    }
}