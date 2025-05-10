<?php
defined('BASEPATH') or exit('No direct script access allowed');

class CAdmin extends CI_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->model('MAdmin');
    }

    // ==================== MANAJEMEN KATEGORI ====================
    public function tambah_kategori()
    {
        $data['nama'] = $this->input->post('nama');
        $this->MAdmin->tambah('kategori', $data);
        $this->session->set_flashdata('success', 'Kategori berhasil ditambahkan');
        redirect('admin/kategori');
    }

    public function update_kategori($id)
    {
        $data['nama'] = $this->input->post('nama');
        $this->MAdmin->update('kategori', $data, $id);
        $this->session->set_flashdata('success', 'Kategori berhasil diupdate');
        redirect('admin/kategori');
    }

    public function hapus_kategori($id)
    {
        $this->MAdmin->hapus('kategori', $id);
        $this->session->set_flashdata('success', 'Kategori berhasil dihapus');
        redirect('admin/kategori');
    }

    // ==================== MANAJEMEN PENGGUNA ====================
    public function tambah_pengguna()
    {
        $data = [
            'nama' => $this->input->post('nama'),
            'email' => $this->input->post('email'),
            'password' => password_hash($this->input->post('password'), PASSWORD_DEFAULT),
            'no_telepon' => $this->input->post('no_telepon'),
            'role' => $this->input->post('role'),
            'created_at' => date('Y-m-d H:i:s')
        ];

        $this->MAdmin->tambah('pengguna', $data);
        $this->session->set_flashdata('success', 'Pengguna berhasil ditambahkan');
        redirect('admin/pengguna');
    }

    public function update_pengguna($id)
    {
        $data = [
            'nama' => $this->input->post('nama'),
            'email' => $this->input->post('email'),
            'no_telepon' => $this->input->post('no_telepon'),
            'role' => $this->input->post('role')
        ];

        // Jika password diisi, update password
        if ($this->input->post('password')) {
            $data['password'] = password_hash($this->input->post('password'), PASSWORD_DEFAULT);
        }

        $this->MAdmin->update('pengguna', $data, $id);
        $this->session->set_flashdata('success', 'Pengguna berhasil diupdate');
        redirect('admin/pengguna');
    }

    public function hapus_pengguna($id)
    {
        $this->MAdmin->hapus('pengguna', $id);
        $this->session->set_flashdata('success', 'Pengguna berhasil dihapus');
        redirect('admin/pengguna');
    }

    // ==================== MANAJEMEN VOUCHER ====================
    public function tambah_voucher()
    {
        $data = [
            'nama' => $this->input->post('nama'),
            'deskripsi' => $this->input->post('deskripsi'),
            'kode_voucher' => $this->input->post('kode_voucher'),
            'tipe_diskon' => $this->input->post('tipe_diskon'),
            'nilai_diskon' => $this->input->post('nilai_diskon'),
            'maksimum_diskon' => $this->input->post('maksimum_diskon'),
            'minimum_pesanan' => $this->input->post('minimum_pesanan'),
            'tanggal_mulai' => $this->input->post('tanggal_mulai'),
            'tanggal_berakhir' => $this->input->post('tanggal_berakhir'),
            'batas_penggunaan' => $this->input->post('batas_penggunaan'),
            'batas_penggunaan_per_pengguna' => $this->input->post('batas_penggunaan_per_pengguna'),
            'is_aktif' => $this->input->post('is_aktif') ? 1 : 0
        ];

        $this->MAdmin->tambah('voucher', $data);
        $this->session->set_flashdata('success', 'Voucher berhasil ditambahkan');
        redirect('admin/voucher');
    }

    public function update_voucher($id)
    {
        $data = [
            'nama' => $this->input->post('nama'),
            'deskripsi' => $this->input->post('deskripsi'),
            'kode_voucher' => $this->input->post('kode_voucher'),
            'tipe_diskon' => $this->input->post('tipe_diskon'),
            'nilai_diskon' => $this->input->post('nilai_diskon'),
            'maksimum_diskon' => $this->input->post('maksimum_diskon'),
            'minimum_pesanan' => $this->input->post('minimum_pesanan'),
            'tanggal_mulai' => $this->input->post('tanggal_mulai'),
            'tanggal_berakhir' => $this->input->post('tanggal_berakhir'),
            'batas_penggunaan' => $this->input->post('batas_penggunaan'),
            'batas_penggunaan_per_pengguna' => $this->input->post('batas_penggunaan_per_pengguna'),
            'is_aktif' => $this->input->post('is_aktif') ? 1 : 0
        ];

        $this->MAdmin->update('voucher', $data, $id);
        $this->session->set_flashdata('success', 'Voucher berhasil diupdate');
        redirect('admin/voucher');
    }

    public function hapus_voucher($id)
    {
        $this->MAdmin->hapus('voucher', $id);
        $this->session->set_flashdata('success', 'Voucher berhasil dihapus');
        redirect('admin/voucher');
    }

    // ==================== MANAJEMEN PRODUK ====================
    public function tambah_paket()
    {
        $data = [
            'nama' => $this->input->post('nama'),
            'harga' => $this->input->post('harga'),
            'deskripsi' => $this->input->post('deskripsi')
        ];

        // Handle upload foto
        if (!empty($_FILES['foto']['name'])) {
            $config['upload_path'] = './uploads/paket/';
            $config['allowed_types'] = 'jpg|jpeg|png';
            $config['max_size'] = 2048;

            $this->load->library('upload', $config);

            if ($this->upload->do_upload('foto')) {
                $uploadData = $this->upload->data();
                $data['foto'] = $uploadData['file_name'];
            }
        }

        $this->MAdmin->tambah('paket', $data);
        $this->session->set_flashdata('success', 'Paket berhasil ditambahkan');
        redirect('admin/paket');
    }

    public function update_paket($id)
    {
        $data = [
            'nama' => $this->input->post('nama'),
            'harga' => $this->input->post('harga'),
            'deskripsi' => $this->input->post('deskripsi')
        ];

        // Handle upload foto jika ada
        if (!empty($_FILES['foto']['name'])) {
            $config['upload_path'] = './uploads/paket/';
            $config['allowed_types'] = 'jpg|jpeg|png';
            $config['max_size'] = 2048;

            $this->load->library('upload', $config);

            if ($this->upload->do_upload('foto')) {
                // Hapus foto lama jika ada
                $old_foto = $this->MAdmin->get_by_id('paket', $id)->foto;
                if ($old_foto && file_exists('./uploads/paket/' . $old_foto)) {
                    unlink('./uploads/paket/' . $old_foto);
                }

                $uploadData = $this->upload->data();
                $data['foto'] = $uploadData['file_name'];
            }
        }

        $this->MAdmin->update('paket', $data, $id);
        $this->session->set_flashdata('success', 'Paket berhasil diupdate');
        redirect('admin/paket');
    }

    public function hapus_paket($id)
    {
        // Hapus foto jika ada
        $paket = $this->MAdmin->get_by_id('paket', $id);
        if ($paket->foto && file_exists('./uploads/paket/' . $paket->foto)) {
            unlink('./uploads/paket/' . $paket->foto);
        }

        $this->MAdmin->hapus('paket', $id);
        $this->session->set_flashdata('success', 'Paket berhasil dihapus');
        redirect('admin/paket');
    }

    // Fungsi serupa untuk snack, buah_puding, dan minuman
    public function snack()
    {
        $data['snack'] = $this->MAdmin->get_all('snack');
        $this->load->view('admin/produk/snack', $data);
    }

    public function tambah_snack()
    {
        $data = [
            'nama' => $this->input->post('nama'),
            'harga' => $this->input->post('harga')
        ];

        $this->MAdmin->tambah('snack', $data);
        $this->session->set_flashdata('success', 'Snack berhasil ditambahkan');
        redirect('admin/snack');
    }

    // ... (fungsi update dan hapus snack)

    // ==================== MANAJEMEN PESANAN ====================
    public function update_status_pesanan($id)
    {
        $data['status'] = $this->input->post('status');
        $this->MAdmin->update('pesanan', $data, $id);
        $this->session->set_flashdata('success', 'Status pesanan berhasil diupdate');
        redirect('admin/pesanan');
    }

    public function detail_pesanan($id)
    {
        $data['pesanan'] = $this->MAdmin->get_pesanan_by_id($id);
        $data['items'] = $this->MAdmin->get_item_pesanan($id);
        $this->load->view('admin/pesanan/detail', $data);
    }

    // ==================== MANAJEMEN BANNER PROMO ====================

    public function tambah_banner()
    {
        if (!empty($_FILES['url_gambar']['name'])) {
            $config['upload_path'] = './uploads/banner/';
            $config['allowed_types'] = 'jpg|jpeg|png';
            $config['max_size'] = 2048;

            $this->load->library('upload', $config);

            if ($this->upload->do_upload('url_gambar')) {
                $uploadData = $this->upload->data();
                $data = [
                    'url_gambar' => $uploadData['file_name'],
                    'is_aktif' => $this->input->post('is_aktif') ? 1 : 0
                ];

                $this->MAdmin->tambah('banner_promo', $data);
                $this->session->set_flashdata('success', 'Banner berhasil ditambahkan');
            } else {
                $this->session->set_flashdata('error', $this->upload->display_errors());
            }
        }

        redirect('admin/banner_promo');
    }

    public function update_banner($id)
    {
        $data = ['is_aktif' => $this->input->post('is_aktif') ? 1 : 0];

        if (!empty($_FILES['url_gambar']['name'])) {
            $config['upload_path'] = './uploads/banner/';
            $config['allowed_types'] = 'jpg|jpeg|png';
            $config['max_size'] = 2048;

            $this->load->library('upload', $config);

            if ($this->upload->do_upload('url_gambar')) {
                // Hapus gambar lama jika ada
                $old_banner = $this->MAdmin->get_by_id('banner_promo', $id)->url_gambar;
                if ($old_banner && file_exists('./uploads/banner/' . $old_banner)) {
                    unlink('./uploads/banner/' . $old_banner);
                }

                $uploadData = $this->upload->data();
                $data['url_gambar'] = $uploadData['file_name'];
            }
        }

        $this->MAdmin->update('banner_promo', $data, $id);
        $this->session->set_flashdata('success', 'Banner berhasil diupdate');
        redirect('admin/banner_promo');
    }

    public function hapus_banner($id)
    {
        // Hapus gambar jika ada
        $banner = $this->MAdmin->get_by_id('banner_promo', $id);
        if ($banner->url_gambar && file_exists('./uploads/banner/' . $banner->url_gambar)) {
            unlink('./uploads/banner/' . $banner->url_gambar);
        }

        $this->MAdmin->hapus('banner_promo', $id);
        $this->session->set_flashdata('success', 'Banner berhasil dihapus');
        redirect('admin/banner_promo');
    }

    // ==================== LAPORAN ====================
    public function laporan_pesanan()
    {
        $start_date = $this->input->get('start_date');
        $end_date = $this->input->get('end_date');

        if ($start_date && $end_date) {
            $data['pesanan'] = $this->MAdmin->get_pesanan_by_date_range($start_date, $end_date);
        } else {
            $data['pesanan'] = $this->MAdmin->get_all('pesanan');
        }

        $this->load->view('admin/laporan/pesanan', $data);
    }
}