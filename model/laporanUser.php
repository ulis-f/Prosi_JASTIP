<?php 
class laporanPelanggan{
    public $nama;
    public $jumlah;

    public function __construct($nama, $jumlah){
        $this->nama = $nama;
        $this->jumlah = $jumlah;
    }
}
