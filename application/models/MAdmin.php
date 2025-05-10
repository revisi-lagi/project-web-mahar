<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MAdmin extends CI_Model
{
    // ==================== FUNGSI DASAR CRUD ====================
    public function get_all($table)
    {
        return $this->db->get($table)->result();
    }

    public function get_by_id($table, $id)
    {
        return $this->db->get_where($table, ['id' => $id])->row();
    }

    public function tambah($table, $data)
    {
        $this->db->insert($table, $data);
        return $this->db->insert_id();
    }

    public function update($table, $data, $id)
    {
        $this->db->where('id', $id);
        return $this->db->update($table, $data);
    }

    public function hapus($table, $id)
    {
        $this->db->where('id', $id);
        return $this->db->delete($table);
    }

    public function count_all($table)
    {
        return $this->db->count_all($table);
    }

    // ==================== FUNGSI KHUSUS ====================
    public function get_pengguna_by_email($email)
    {
        return $this->db->get_where('pengguna', ['email' => $email])->row();
    }

    public function get_pesanan_with_join()
    {
        $this->db->select('pesanan.*, pengguna.nama as nama_pengguna');
        $this->db->from('pesanan');
        $this->db->join('pengguna', 'pengguna.id = pesanan.pengguna_id');
        $this->db->order_by('pesanan.created_at', 'DESC');
        return $this->db->get()->result();
    }

    public function get_pesanan_by_id($id)
    {
        $this->db->select('pesanan.*, pengguna.nama as nama_pengguna, pengguna.email, pengguna.no_telepon, 
                          alamat.nama_penerima, alamat.no_telepon as no_telepon_penerima, alamat.alamat_lengkap,
                          voucher.nama as nama_voucher, voucher.kode_voucher');
        $this->db->from('pesanan');
        $this->db->join('pengguna', 'pengguna.id = pesanan.pengguna_id', 'left');
        $this->db->join('alamat', 'alamat.id = pesanan.alamat_id', 'left');
        $this->db->join('voucher', 'voucher.id = pesanan.voucher_id', 'left');
        $this->db->where('pesanan.id', $id);
        return $this->db->get()->row();
    }

    public function get_item_pesanan($pesanan_id)
    {
        $this->db->select('item_pesanan.*, 
                          COALESCE(paket.nama, snack.nama, buah_puding.nama, minuman.nama) as nama_produk,
                          COALESCE(paket.harga, snack.harga, buah_puding.harga, minuman.harga) as harga_satuan');
        $this->db->from('item_pesanan');
        $this->db->join('paket', 'paket.id = item_pesanan.produk_id AND item_pesanan.tipe_produk = "paket"', 'left');
        $this->db->join('snack', 'snack.id = item_pesanan.produk_id AND item_pesanan.tipe_produk = "snack"', 'left');
        $this->db->join('buah_puding', 'buah_puding.id = item_pesanan.produk_id AND item_pesanan.tipe_produk = "buah_puding"', 'left');
        $this->db->join('minuman', 'minuman.id = item_pesanan.produk_id AND item_pesanan.tipe_produk = "minuman"', 'left');
        $this->db->where('item_pesanan.pesanan_id', $pesanan_id);
        return $this->db->get()->result();
    }

    public function get_pesanan_by_date_range($start_date, $end_date)
    {
        $this->db->select('pesanan.*, pengguna.nama as nama_pengguna');
        $this->db->from('pesanan');
        $this->db->join('pengguna', 'pengguna.id = pesanan.pengguna_id');
        $this->db->where('pesanan.created_at >=', $start_date . ' 00:00:00');
        $this->db->where('pesanan.created_at <=', $end_date . ' 23:59:59');
        $this->db->order_by('pesanan.created_at', 'DESC');
        return $this->db->get()->result();
    }

    public function get_voucher_aktif()
    {
        $this->db->where('is_aktif', 1);
        $this->db->where('tanggal_mulai <=', date('Y-m-d'));
        $this->db->where('tanggal_berakhir >=', date('Y-m-d'));
        return $this->db->get('voucher')->result();
    }

    public function get_banner_aktif()
    {
        $this->db->where('is_aktif', 1);
        return $this->db->get('banner_promo')->result();
    }
}