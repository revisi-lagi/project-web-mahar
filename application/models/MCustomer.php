<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MCustomer extends CI_Model
{
    // ==================== MANAJEMEN KERANJANG ====================
    public function get_keranjang_by_user($user_id)
    {
        return $this->db->get_where('keranjang', ['pengguna_id' => $user_id])->row();
    }

    public function buat_keranjang($user_id)
    {
        $this->db->insert('keranjang', ['pengguna_id' => $user_id]);
        return $this->db->insert_id();
    }

    public function get_item_keranjang($keranjang_id)
    {
        $this->db->select('item_keranjang.*, produk.nama as nama_produk, produk.harga');
        $this->db->from('item_keranjang');
        $this->db->join('produk', 'produk.id = item_keranjang.produk_id');
        $this->db->where('keranjang_id', $keranjang_id);
        return $this->db->get()->result();
    }

    public function tambah_item_keranjang($data)
    {
        // Cek apakah item sudah ada di keranjang
        $existing = $this->db->get_where('item_keranjang', [
            'keranjang_id' => $data['keranjang_id'],
            'produk_id' => $data['produk_id']
        ])->row();

        if ($existing) {
            // Update jumlah jika sudah ada
            $this->db->where('id', $existing->id);
            $this->db->update('item_keranjang', [
                'jumlah' => $existing->jumlah + $data['jumlah'],
                'total' => ($existing->jumlah + $data['jumlah']) * $existing->harga
            ]);
        } else {
            // Tambah baru jika belum ada
            $this->db->insert('item_keranjang', $data);
        }
    }

    public function update_item_keranjang($item_id, $jumlah)
    {
        $item = $this->db->get_where('item_keranjang', ['id' => $item_id])->row();
        if ($item) {
            $this->db->where('id', $item_id);
            $this->db->update('item_keranjang', [
                'jumlah' => $jumlah,
                'total' => $jumlah * $item->harga
            ]);
        }
    }

    public function hapus_item_keranjang($item_id)
    {
        $this->db->where('id', $item_id);
        $this->db->delete('item_keranjang');
    }

    // ==================== MANAJEMEN PESANAN ====================
    public function buat_pesanan($data_pesanan, $items)
    {
        // Mulai transaksi database
        $this->db->trans_start();

        // 1. Simpan data pesanan
        $this->db->insert('pesanan', $data_pesanan);
        $pesanan_id = $this->db->insert_id();

        // 2. Simpan item pesanan
        foreach ($items as $item) {
            $item['pesanan_id'] = $pesanan_id;
            $this->db->insert('item_pesanan', $item);
        }

        // 3. Kosongkan keranjang
        $this->db->where('keranjang_id', $data_pesanan['keranjang_id']);
        $this->db->delete('item_keranjang');

        // Selesaikan transaksi
        $this->db->trans_complete();

        return $pesanan_id;
    }

    public function get_pesanan_by_user($user_id)
    {
        $this->db->where('pengguna_id', $user_id);
        $this->db->order_by('created_at', 'DESC');
        return $this->db->get('pesanan')->result();
    }

    public function get_detail_pesanan($pesanan_id, $user_id)
    {
        $this->db->select('pesanan.*, alamat.nama_penerima, alamat.alamat_lengkap');
        $this->db->from('pesanan');
        $this->db->join('alamat', 'alamat.id = pesanan.alamat_id', 'left');
        $this->db->where('pesanan.id', $pesanan_id);
        $this->db->where('pesanan.pengguna_id', $user_id);
        return $this->db->get()->row();
    }

    public function get_item_pesanan($pesanan_id)
    {
        $this->db->select('item_pesanan.*, produk.nama as nama_produk');
        $this->db->from('item_pesanan');
        $this->db->join('produk', 'produk.id = item_pesanan.produk_id');
        $this->db->where('pesanan_id', $pesanan_id);
        return $this->db->get()->result();
    }

    // ==================== MANAJEMEN ALAMAT ====================
    public function get_alamat_user($user_id)
    {
        $this->db->where('pengguna_id', $user_id);
        return $this->db->get('alamat')->result();
    }

    public function tambah_alamat($data)
    {
        $this->db->insert('alamat', $data);
        return $this->db->insert_id();
    }
}