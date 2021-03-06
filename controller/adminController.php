<?php
require_once "controller/services/mysqlDB.php";
require_once "controller/services/view.php";
require_once "model/trip.php";
require_once "model/user.php";
require_once "model/transaksi.php";
require_once "model/barang.php";
require_once "model/notifikasi.php";
require_once "model/ListPembayaran.php";
require_once "model/PendapatanAdmin.php";
require_once "model/laporanTrip.php";
require_once "model/laporanUser.php";
require_once "model/total.php";
class adminController
{
    protected $db;

    public function __construct()
    {
        $this->db = new MySQLDB("localhost", "root", "", "titipaja");
    }

    public function view_index()
    {
        $result = $this->getPostTrip();
        return view::createViewAdmin('homeAdmin.php', ["result" => $result]);
    }

    public function view_persetujuan()
    {
        $id = $_GET['id'];
        $result = $this->getTrip($id);
        return view::createViewAdmin('persetujuanTrip.php', ["result" => $result]);
    }

    public function view_persetujuanBarang()
    {
        $result = $this->getBarang();
        return view::createViewAdmin("persetujuanBarang.php", ["result" => $result]);
    }

    public function view_detailBarangMarketWanted()
    {
        $namaBarang = $_GET['namaBarang'];
        $wanted = $_GET['market'];
        $idUser = $_GET['id'];
        $result = $this->getDetailBarangMarket($namaBarang, $wanted, $idUser);
        return view::createViewAdmin("detailBarangWanted.php", ["result" => $result]);
    }

    public function view_detailBarang()
    {
        $namaBarang = $_GET['namaBarang'];
        $wanted = $_GET['market'];
        if ($wanted == 'wanted') {
            $result = $this->getDetailBarangMarket($namaBarang, $wanted);
        } else {
            $result = $this->getDetailBarang($namaBarang);
        }
        $trip = $this->getTripSendiri($namaBarang);
        return view::createViewAdmin("detailPersetujuanBarang.php", ["result" => $result, "trip" => $trip]);
    }

    public function view_getProfile()
    {
        $result = $this->getProfile();
        return view::createViewAdmin('persetujuanPendaftaran.php', ["result" => $result]);
    }

    public function view_getProfileLengkap()
    {
        $id = $_GET['id'];
        $result = $this->getProfileLengkap($id);
        return view::createViewAdmin('detailPendaftaran.php', ["result" => $result]);
    }

    public function view_pembayaran()
    {
        $result  = $this->getListPembayaran('pending');
        $result2 = $this->getListPembayaran('verified');
        $result3 = $this->getListPembayaran('unverified');
        return view::createViewAdmin('pembayaranAdmin.php', ["result" => $result, "result2" => $result2, "result3" => $result3]);
    }

    public function view_detailPembayaran()
    {
        $idPenerima = $_GET['namaPenerima'];
        $idPembeli = $_GET['namaPembeli'];
        $namaBarang = $_GET['namaBarang'];
        $idTrip = $_GET['idTrip'];
        $trip = $this->getTrip($idTrip);
        $user1 = $this->getProfilePembayaran($idPenerima);
        $user2 = $this->getProfilePembayaran($idPembeli);
        $hasil = $this->getDetailPembayaran($idPenerima, $idPembeli, $namaBarang);
        return view::createViewAdmin('detailPembayaranAdmin.php', ["trip" => $trip, "user1" => $user1, "user2" => $user2, "hasil" => $hasil]);
    }

    public function view_pengirimanUang()
    {
        $result = $this->getListPengirimanUang();
        return view::createViewAdmin('pengirimanUang.php', ["result" => $result]);
    }

    public function view_detailPengirimanUang()
    {
        $idPenerima = $_GET['namaPenerima'];
        $idPembeli = $_GET['namaPembeli'];
        $namaBarang = $_GET['namaBarang'];
        $idTrip = $_GET['idTrip'];
        $trip = $this->getTrip($idTrip);
        $user1 = $this->getProfilePembayaran($idPenerima);
        $user2 = $this->getProfilePembayaran($idPembeli);
        $hasil = $this->getDetailPembayaran($idPenerima, $idPembeli, $namaBarang);
        return view::createViewAdmin('detailPengirimanUang.php', ["trip" => $trip, "user1" => $user1, "user2" => $user2, "hasil" => $hasil]);
    }

    public function view_laporan()
    {
        $bulan = $_GET['bulan'];
        $pendapatan = $this->getPendapatanAdmin($bulan);
        $tripTraveller = $this->getJumlahTrip();
        $pendapatanCustomer = $this->getPendapatanCustomer();
        $total = $this->getTotal();
        return view::createViewAdmin('laporan.php', ["pendapatan" => $pendapatan, "tripTraveller" => $tripTraveller, "pendapatanCustomer" => $pendapatanCustomer, "total" => $total]);
    }

    public function getTotal()
    {
        $query = "SELECT sum(hargaBarang) as 'hargaBarang', sum(hargaOngkir) as  'hargaOngkir', sum(hargaJasa) as 'hargaJasa', waktuTransaksi, namaBank 
        FROM `transaksi` inner join bank on transaksi.idBankPembayaran = bank.idBank
        where year(waktuTransaksi) = 2021
        group by idBankPembayaran ";
        $query_result = $this->db->executeSelectQuery($query);
        $result = [];
        foreach ($query_result as $key => $value) {
            $total = (($value['hargaBarang'] + $value['hargaOngkir'] + $value['hargaJasa']) * 25 / 1000);
            $result[]  =  new Total($value['namaBank'], $total);
        }
        return $result;
    }

    public function getPendapatanCustomer()
    {
        $query = "SELECT count(idUser1) as 'jumlah' , transaksi.idUser1 , user.namaUser FROM `transaksi` inner join user on transaksi.idUser1 = user.idUser where MONTH(waktuTransaksi) = 10 or  MONTH(waktuTransaksi) = 11 or  MONTH(waktuTransaksi) = 12 group by idUser1";
        $query_result = $this->db->executeSelectQuery($query);
        $result = [];
        foreach ($query_result as $key => $value) {
            $result[] = new laporanPelanggan($value['namaUser'], $value['jumlah']);
        }
        return $result;
    }

    public function getJumlahTrip()
    {
        $query = "select namaUser, count(idTrip) as 'jumlah'
        from (SELECT post.idUser, trip.IdTrip , waktuAwal FROM trip inner join post on trip.idTrip = post.idTrip where MONTH(waktuAwal) = 10 or MONTH(waktuAwal) = 11 or MONTH(waktuAwal) = 12) as himpA inner join user on himpA.idUser = user.idUser
        group by namaUser";
        $query_result = $this->db->executeSelectQuery($query);
        $result = [];
        foreach ($query_result as $key => $value) {
            $result[] = new laporanTrip($value['namaUser'], $value['jumlah']);
        }
        return $result;
    }

    public function getPendapatanAdmin($bulan)
    {
        $result = [];
        if ($bulan == NULL || $bulan == 'Semua') {
            $query = "SELECT sum(hargaBarang) as 'hargaBarang', sum(hargaOngkir) as  'hargaOngkir', sum(hargaJasa) as 'hargaJasa', waktuTransaksi, namaBank 
            FROM `transaksi` inner join bank on transaksi.idBankPembayaran = bank.idBank 
            where MONTH(waktuTransaksi) >0 and YEAR(waktuTransaksi) = 2021
            group by MONTH(waktuTransaksi)";
            $query_result = $this->db->executeSelectQuery($query);
            foreach ($query_result as $key => $value) {
                $total = (($value['hargaBarang'] + $value['hargaOngkir'] + $value['hargaJasa']) * 25 / 1000);
                $month = date("m", strtotime($value['waktuTransaksi']));
                $year = date("Y", strtotime($value['waktuTransaksi']));
                if ($month == '01') {
                    $result[] = new pendapatanAdmin('Januari ', $value['namaBank'], $total);
                } else if ($month == '02') {
                    $result[] = new pendapatanAdmin('Februari ', $value['namaBank'], $total);
                } else if ($month == '03') {
                    $result[] = new pendapatanAdmin('Maret ', $value['namaBank'], $total);
                } else if ($month == '04') {
                    $result[] = new pendapatanAdmin('April ', $value['namaBank'], $total);
                } else if ($month == '05') {
                    $result[] = new pendapatanAdmin('Mei ', $value['namaBank'], $total);
                } else if ($month == '06') {
                    $result[] = new pendapatanAdmin('Juni ', $value['namaBank'], $total);
                } else if ($month == '07') {
                    $result[] = new pendapatanAdmin('Juli ', $value['namaBank'], $total);
                } else if ($month == '08') {
                    $result[] = new pendapatanAdmin('Agustus ', $value['namaBank'], $total);
                } else if ($month == '09') {
                    $result[] = new pendapatanAdmin('September ', $value['namaBank'], $total);
                } else if ($month == '10') {
                    $result[] = new pendapatanAdmin('Oktober ', $value['namaBank'], $total);
                } else if ($month == '11') {
                    $result[] = new pendapatanAdmin('November ', $value['namaBank'], $total);
                } else if ($month == '12') {
                    $result[] = new pendapatanAdmin('Desember ', $value['namaBank'], $total);
                }
            }
        } else if ($bulan == 1) {
            $query = "SELECT sum(hargaBarang) as 'hargaBarang', sum(hargaOngkir) as  'hargaOngkir', sum(hargaJasa) as 'hargaJasa', waktuTransaksi, namaBank 
            FROM `transaksi` inner join bank on transaksi.idBankPembayaran = bank.idBank  where MONTH(waktuTransaksi) = 1 and YEAR(waktuTransaksi) = 2021 group by MONTH(waktuTransaksi)";
            $query_result = $this->db->executeSelectQuery($query);
            $result = [];
            foreach ($query_result as $key => $value) {
                $total = (($value['hargaBarang'] + $value['hargaOngkir'] + $value['hargaJasa']) * 25 / 1000);
                $month = date("m", strtotime($value['waktuTransaksi']));
            }
            $result[] = new pendapatanAdmin('Januari', $value['namaBank'], $total);
        } else if ($bulan == 2) {
            $query = "SELECT sum(hargaBarang) as 'hargaBarang', sum(hargaOngkir) as  'hargaOngkir', sum(hargaJasa) as 'hargaJasa', waktuTransaksi, namaBank 
            FROM `transaksi` inner join bank on transaksi.idBankPembayaran = bank.idBank  where MONTH(waktuTransaksi) = 2 and YEAR(waktuTransaksi) = 2021 group by MONTH(waktuTransaksi)";
            $query_result = $this->db->executeSelectQuery($query);
            $result = [];
            foreach ($query_result as $key => $value) {
                $total = (($value['hargaBarang'] + $value['hargaOngkir'] + $value['hargaJasa']) * 25 / 1000);
                $month = date("m", strtotime($value['waktuTransaksi']));
            }
            $result[] = new pendapatanAdmin('Februari', $value['namaBank'], $total);
        } else if ($bulan == 3) {
            $query = "SELECT sum(hargaBarang) as 'hargaBarang', sum(hargaOngkir) as  'hargaOngkir', sum(hargaJasa) as 'hargaJasa', waktuTransaksi, namaBank 
            FROM `transaksi` inner join bank on transaksi.idBankPembayaran = bank.idBank  where MONTH(waktuTransaksi) = 3 and YEAR(waktuTransaksi) = 2021 group by MONTH(waktuTransaksi)";
            $query_result = $this->db->executeSelectQuery($query);
            $result = [];
            foreach ($query_result as $key => $value) {
                $total = (($value['hargaBarang'] + $value['hargaOngkir'] + $value['hargaJasa']) * 25 / 1000);
                $month = date("m", strtotime($value['waktuTransaksi']));
            }
            $result[] = new pendapatanAdmin('Maret', $value['namaBank'], $total);
        } else if ($bulan == 4) {
            $query = "SELECT sum(hargaBarang) as 'hargaBarang', sum(hargaOngkir) as  'hargaOngkir', sum(hargaJasa) as 'hargaJasa', waktuTransaksi, namaBank 
            FROM `transaksi` inner join bank on transaksi.idBankPembayaran = bank.idBank  where MONTH(waktuTransaksi) = 4 and YEAR(waktuTransaksi) = 2021 group by MONTH(waktuTransaksi)";
            $query_result = $this->db->executeSelectQuery($query);
            $result = [];
            foreach ($query_result as $key => $value) {
                $total = (($value['hargaBarang'] + $value['hargaOngkir'] + $value['hargaJasa']) * 25 / 1000);
                $month = date("m", strtotime($value['waktuTransaksi']));
            }
            $result[] = new pendapatanAdmin('April', $value['namaBank'], $total);
        } else if ($bulan == 5) {
            $query = "SELECT sum(hargaBarang) as 'hargaBarang', sum(hargaOngkir) as  'hargaOngkir', sum(hargaJasa) as 'hargaJasa', waktuTransaksi, namaBank 
            FROM `transaksi` inner join bank on transaksi.idBankPembayaran = bank.idBank  where MONTH(waktuTransaksi) = 5 and YEAR(waktuTransaksi) = 2021 group by MONTH(waktuTransaksi)";
            $query_result = $this->db->executeSelectQuery($query);
            $result = [];
            foreach ($query_result as $key => $value) {
                $total = (($value['hargaBarang'] + $value['hargaOngkir'] + $value['hargaJasa']) * 25 / 1000);
                $month = date("m", strtotime($value['waktuTransaksi']));
            }
            $result[] = new pendapatanAdmin('Mei', $value['namaBank'], $total);
        } else if ($bulan == 6) {
            $query = "SELECT sum(hargaBarang) as 'hargaBarang', sum(hargaOngkir) as  'hargaOngkir', sum(hargaJasa) as 'hargaJasa', waktuTransaksi, namaBank 
            FROM `transaksi` inner join bank on transaksi.idBankPembayaran = bank.idBank  where MONTH(waktuTransaksi) = 6  and YEAR(waktuTransaksi) = 2021 group by MONTH(waktuTransaksi)";
            $query_result = $this->db->executeSelectQuery($query);
            $result = [];
            foreach ($query_result as $key => $value) {
                $total = (($value['hargaBarang'] + $value['hargaOngkir'] + $value['hargaJasa']) * 25 / 1000);
                $month = date("m", strtotime($value['waktuTransaksi']));
            }
            $result[] = new pendapatanAdmin('Juni', $value['namaBank'], $total);
        } else if ($bulan == 7) {
            $query = "SELECT sum(hargaBarang) as 'hargaBarang', sum(hargaOngkir) as  'hargaOngkir', sum(hargaJasa) as 'hargaJasa', waktuTransaksi, namaBank 
            FROM `transaksi` inner join bank on transaksi.idBankPembayaran = bank.idBank  where MONTH(waktuTransaksi) = 7 and YEAR(waktuTransaksi) = 2021 group by MONTH(waktuTransaksi)";
            $query_result = $this->db->executeSelectQuery($query);
            $result = [];
            foreach ($query_result as $key => $value) {
                $total = (($value['hargaBarang'] + $value['hargaOngkir'] + $value['hargaJasa']) * 25 / 1000);
                $month = date("m", strtotime($value['waktuTransaksi']));
            }
            $result[] = new pendapatanAdmin('Juli', $value['namaBank'], $total);
        } else if ($bulan == 8) {
            $query = "SELECT sum(hargaBarang) as 'hargaBarang', sum(hargaOngkir) as  'hargaOngkir', sum(hargaJasa) as 'hargaJasa', waktuTransaksi, namaBank 
            FROM `transaksi` inner join bank on transaksi.idBankPembayaran = bank.idBank  where MONTH(waktuTransaksi) = 8 and YEAR(waktuTransaksi) = 2021 group by MONTH(waktuTransaksi)";
            $query_result = $this->db->executeSelectQuery($query);
            $result = [];
            foreach ($query_result as $key => $value) {
                $total = (($value['hargaBarang'] + $value['hargaOngkir'] + $value['hargaJasa']) * 25 / 1000);
                $month = date("m", strtotime($value['waktuTransaksi']));
            }
            $result[] = new pendapatanAdmin('Agustus', $value['namaBank'], $total);
        } else if ($bulan == 9) {
            $query = "SELECT sum(hargaBarang) as 'hargaBarang', sum(hargaOngkir) as  'hargaOngkir', sum(hargaJasa) as 'hargaJasa', waktuTransaksi, namaBank 
            FROM `transaksi` inner join bank on transaksi.idBankPembayaran = bank.idBank  where MONTH(waktuTransaksi) = 9 and YEAR(waktuTransaksi) = 2021 group by MONTH(waktuTransaksi)";
            $query_result = $this->db->executeSelectQuery($query);
            $result = [];
            foreach ($query_result as $key => $value) {
                $total = (($value['hargaBarang'] + $value['hargaOngkir'] + $value['hargaJasa']) * 25 / 1000);
                $month = date("m", strtotime($value['waktuTransaksi']));
            }
            $result[] = new pendapatanAdmin('September', $value['namaBank'], $total);
        } else if ($bulan == 10) {
            $query = "SELECT sum(hargaBarang) as 'hargaBarang', sum(hargaOngkir) as  'hargaOngkir', sum(hargaJasa) as 'hargaJasa', waktuTransaksi, namaBank 
            FROM `transaksi` inner join bank on transaksi.idBankPembayaran = bank.idBank  where MONTH(waktuTransaksi) = 10 and YEAR(waktuTransaksi) = 2021 group by MONTH(waktuTransaksi)";
            $query_result = $this->db->executeSelectQuery($query);
            $result = [];
            foreach ($query_result as $key => $value) {
                $total = (($value['hargaBarang'] + $value['hargaOngkir'] + $value['hargaJasa']) * 25 / 1000);
                $month = date("m", strtotime($value['waktuTransaksi']));
            }
            $result[] = new pendapatanAdmin('Oktober', $value['namaBank'], $total);
        } else if ($bulan == 11) {
            $query = "SELECT sum(hargaBarang) as 'hargaBarang', sum(hargaOngkir) as  'hargaOngkir', sum(hargaJasa) as 'hargaJasa', waktuTransaksi, namaBank 
            FROM `transaksi` inner join bank on transaksi.idBankPembayaran = bank.idBank  where MONTH(waktuTransaksi) = 11 and YEAR(waktuTransaksi) = 2021 group by MONTH(waktuTransaksi)";
            $query_result = $this->db->executeSelectQuery($query);
            $result = [];
            foreach ($query_result as $key => $value) {
                $total = (($value['hargaBarang'] + $value['hargaOngkir'] + $value['hargaJasa']) * 25 / 1000);
                $month = date("m", strtotime($value['waktuTransaksi']));
            }
            $result[] = new pendapatanAdmin('November', $value['namaBank'], $total);
        } else if ($bulan == 12) {
            $query = "SELECT sum(hargaBarang) as 'hargaBarang', sum(hargaOngkir) as  'hargaOngkir', sum(hargaJasa) as 'hargaJasa', waktuTransaksi, namaBank 
            FROM `transaksi` inner join bank on transaksi.idBankPembayaran = bank.idBank  where MONTH(waktuTransaksi) = 12 and YEAR(waktuTransaksi) = 2021 group by MONTH(waktuTransaksi)";
            $query_result = $this->db->executeSelectQuery($query);
            $result = [];
            foreach ($query_result as $key => $value) {
                $total = (($value['hargaBarang'] + $value['hargaOngkir'] + $value['hargaJasa']) * 25 / 1000);
                $month = date("m", strtotime($value['waktuTransaksi']));
            }
            $result[] = new pendapatanAdmin('Desember', $value['namaBank'], $total);
        }
        return $result;
    }

    public function getListPengirimanUang()
    {
        $query = "select himpA.idUser1, himpA.idUser2, himpA.namaSatu, himpA.email, himpA.idTrip, himpA.namaBarang
        from(select idUser1,idTrip, idUser2, user.namaUser as 'namaSatu', user.email, namaBarang
        from transaksi inner join user 
        on user.idUser = transaksi.idUser1
            where statusBarang = 'transactionComplete') as himpA inner join user on himpA.idUser2 = user.idUser";
        $query_result = $this->db->executeSelectQuery($query);
        $result = [];
        foreach ($query_result as $key => $value) {
            $result[] = new ListPembayaran($value['idUser1'], $value['idUser2'], $value['idTrip'], $value['namaSatu'], $value['email'], $value['namaBarang']);
        }
        return $result;
    }

    public function getPostTrip()
    {
        // $nama = $_POST['nama'];
        $result = [];
        $query = "SELECT himpA.namaUser, trip.gambarTrip, trip.idTrip
                    FROM trip inner join 
                        (SELECT user.namaUser, transaksi.idTrip 
                        FROM user inner join transaksi on user.idUser = transaksi.idUser1 
                        WHERE user.isTraveller = 'verified') as himpA on trip.idTrip = himpA.idTrip 
                    WHERE trip.statusTrip = 'pending'";
        $query_result = $this->db->executeSelectQuery($query);
        foreach ($query_result as $key => $value) {
            $result[] = new Trip($value['namaUser'], $value['gambarTrip'], $value['idTrip'], null, null, null, null);
        }
        return $result;
    }

    public function getTrip($id)
    {
        $query = "SELECT himpA.idTrip,himpA.gambarTrip, himpA.waktuAwal,himpA.waktuAkhir,himpA.namaKota as 'kota_Awal', kota.namaKota as 'kota_tujuan' 
                    FROM kota inner join (SELECT * FROM trip inner join kota on trip.idKota1 = kota.idKota WHERE idTrip = '$id') 
                    as himpA on kota.idKota = himpA.idKota2";
        $query_result = $this->db->executeSelectQuery($query);
        $result = [];
        foreach ($query_result as $key => $value) {
            $result[] = new Trip($value['idTrip'], $value['gambarTrip'], $value['idTrip'], $value['waktuAwal'], $value['waktuAkhir'], $value['kota_Awal'], $value['kota_tujuan']);
        }
        return $result;
    }

    public function verifikasi()
    {
        $idTrip = $_POST['id'];
        $verifikasi = $_POST['verified'];
        $nama = $_SESSION['nama'];
        $deskripsi = $_POST['deskripsi'];

        $query = "UPDATE trip SET statusTrip = '$verifikasi' WHERE idTrip = '$idTrip'";

        $query_result = $this->db->executeNonSelectQuery($query);

        $query_idUser = "SELECT idUser FROM `user` WHERE `namaUser` LIKE '$nama'";
        $query_idUser_result = $this->db->executeSelectQuery($query_idUser);
        $idUser = $query_idUser_result[0]['idUser'];
        $timezone = new DateTimeZone('Asia/Jakarta');
        $date = new DateTime();
        $date->setTimeZone($timezone);
        $now = $date->format('Y-m-d H:i:s');

        if ($verifikasi == 'verified') {
            if ($deskripsi != '') {
                $queryNotifikasi = "INSERT INTO notifikasi VALUES('$idUser',null, 'Verifikasi Berhasil','$deskripsi',0,'$now')";
                $queryNotifikasi_result = $this->db->executeNonSelectQuery($queryNotifikasi);
            } else {
                $queryNotifikasi = "INSERT INTO notifikasi VALUES('$idUser',null, 'Verifikasi Berhasil','Trip anda sudah diverifikasi',0,'$now')";
                $queryNotifikasi_result = $this->db->executeNonSelectQuery($queryNotifikasi);
            }
        } else {
            if ($deskripsi != '') {
                $queryNotifikasi = "INSERT INTO notifikasi VALUES('$idUser',null, 'Verifikasi Gagal','$deskripsi',0,'$now')";
                $queryNotifikasi_result = $this->db->executeNonSelectQuery($queryNotifikasi);
            } else {
                $queryNotifikasi = "INSERT INTO notifikasi VALUES('$idUser',null, 'Verifikasi Gagal','Trip anda gagal untuk diverifikasi',0,'$now')";
                $queryNotifikasi_result = $this->db->executeNonSelectQuery($queryNotifikasi);
            }
        }
    }

    public function getProfile()
    {
        $result = [];
        $query = "SELECT idUser, namaUser, email FROM user WHERE isTraveller = 'pending' ";
        $query_result = $this->db->executeSelectQuery($query);
        foreach ($query_result as $key => $value) {
            $result[] = new User($value['idUser'], $value['namaUser'], null, null, null, $value['email'], null, null, null, null, null, null);
        }
        return $result;
    }

    public function getProfileLengkap($id)
    {
        $query = "SELECT * FROM user WHERE idUser = '$id'";
        $query_result = $this->db->executeSelectQuery($query);
        $result = [];
        foreach ($query_result as $key => $value) {
            $result[] = new User($value['idUser'], $value['namaUser'], null, null, null, null, $value['swafoto'], $value['gambarKTP'], $value['namaBank'], $value['norek'], $value['noKTP'], null);
        }

        return $result;
    }

    public function verifikasiDaftar()
    {
        $iduser = $_POST['id'];
        $verifikasi = $_POST['verified'];
        $nama = $_SESSION['nama'];
        $deskripsi = $_POST['deskripsi'];

        $query = "UPDATE user SET isTraveller = '$verifikasi' WHERE idUser = '$iduser'";

        $query_result = $this->db->executeNonSelectQuery($query);

        $query_idUser = "SELECT idUser FROM `user` WHERE `namaUser` LIKE '$nama'";
        $query_idUser_result = $this->db->executeSelectQuery($query_idUser);
        $idUser = $query_idUser_result[0]['idUser'];
        $timezone = new DateTimeZone('Asia/Jakarta');
        $date = new DateTime();
        $date->setTimeZone($timezone);
        $now = $date->format('Y-m-d H:i:s');

        if ($verifikasi == 'verified') {
            if ($deskripsi != '') {
                $queryNotifikasi = "INSERT INTO notifikasi VALUES('$idUser',null, 'Verifikasi Berhasil','$deskripsi',0,'$now')";
                $queryNotifikasi_result = $this->db->executeNonSelectQuery($queryNotifikasi);
            } else {
                $queryNotifikasi = "INSERT INTO notifikasi VALUES('$idUser',null, 'Verifikasi Berhasil','Kelengkapan profil anda sudah berhasil',0,'$now')";
                $queryNotifikasi_result = $this->db->executeNonSelectQuery($queryNotifikasi);
            }
        } else {
            if ($deskripsi != '') {
                $queryNotifikasi = "INSERT INTO notifikasi VALUES('$idUser',null, 'Verifikasi Gagal','$deskripsi',0,'$now')";
                $queryNotifikasi_result = $this->db->executeNonSelectQuery($queryNotifikasi);
            } else {
                $queryNotifikasi = "INSERT INTO notifikasi VALUES('$idUser',null, 'Verifikasi Gagal','Kelengkapan profil anda gagal',0,'$now')";
                $queryNotifikasi_result = $this->db->executeNonSelectQuery($queryNotifikasi);
            }
        }
    }

    public function getBarang()
    {
        $query = "SELECT idUser1, namaUser, email, namaBarang, statusBarang  FROM transaksi inner join user ON transaksi.idUser1 = user.idUser WHERE statusBarang = 'onPendingWanted' OR statusBarang = 'onPending' ";
        $query_result = $this->db->executeSelectQuery($query);
        $result = [];
        foreach ($query_result as $key => $value) {
            $result[] = new Barang($value['idUser1'], $value['namaUser'], $value['email'], $value['namaBarang'], $value['statusBarang']);
        }
        return $result;
    }

    public function getDetailBarang($namaBarang)
    {
        $query = "SELECT * FROM transaksi inner join kategori on transaksi.idKategori = kategori.idKategori 
                    WHERE namaBarang LIKE '$namaBarang'";
        $query_result = $this->db->executeSelectQuery($query);
        $result = [];
        foreach ($query_result as $key => $value) {
            $result[] = new Transaksi(null, null, null, null, $value['hargaBarang'], null, null, $value['namaBarang'], $value['statusBarang'], $value['deskripsiBarang'], $value['gambarBarang'], null, $value['namaKategori']);
        }
        return $result;
    }
    public function getTripSendiri($namaBarang)
    {
        $nama = $_SESSION['nama'];
        $query = "SELECT * From transaksi inner JOIN (SELECT himpA.idTrip,himpA.gambarTrip, himpA.waktuAwal,himpA.waktuAkhir,himpA.namaKota
         as 'kota_Awal', kota.namaKota as 'kota_tujuan' FROM kota inner join (SELECT * FROM trip inner join kota on trip.idKota1 = kota.idKota ) 
         as himpA on kota.idKota = himpA.idKota2 inner join post on post.idTrip = himpA.idTrip inner join user 
        on user.idUser= post.idUser) as himpBanyak on transaksi.IdTrip = himpBanyak.idTrip WHERE transaksi.namaBarang like '$namaBarang' LIMIT 1";
        $query_result = $this->db->executeSelectQuery($query);
        $result = [];
        foreach ($query_result as $key => $value) {
            $result[] = new Trip(null, null, $value['idTrip'], $value['waktuAwal'], $value['waktuAkhir'], $value['kota_Awal'], $value['kota_tujuan']);
        }
        return $result;
    }

    public function verifikasiBarang()
    {
        $nama = $_SESSION['nama'];
        $namaBarang = $_POST['namaBarang'];
        $verifikasi = $_POST['verified'];
        $statusBarang = $_POST['market'];
        $unverified = $_POST['unverified'];

        $query_idUser = "SELECT * FROM `user` WHERE `namaUser` LIKE '$nama'";
        $query_idUser_result = $this->db->executeSelectQuery($query_idUser);

        $fk_idUser = $query_idUser_result[0]['idUser'];

        $timezone = new DateTimeZone('Asia/Jakarta');
        $date = new DateTime();
        $date->setTimeZone($timezone);
        $now = $date->format('Y-m-d H:i:s');

        if ($verifikasi == 'verified') {
            if ($statusBarang == 'onPending') {
                $query = "UPDATE transaksi SET statusBarang = 'onMarketOffer' WHERE namaBarang = '$namaBarang'AND statusBarang='$statusBarang'";
                $query_result = $this->db->executeNonSelectQuery($query);

                $query_notifikasi = "INSERT INTO Notifikasi VALUES ('$fk_idUser',null,'Verifikasi Berhasil', 'Offer an Item Anda dengan nama $namaBarang 
                telah berhasil disetujui. Sekarang barang anda sudah ada di fitur Market (Offer an Item).', 0, '$now')";
                $query_result1 = $this->db->executeNonSelectQuery($query_notifikasi);
            } else {
                $query = "UPDATE transaksi SET statusBarang = 'onMarketWanted' WHERE namaBarang = '$namaBarang'AND statusBarang='$statusBarang'";
                $query_result = $this->db->executeNonSelectQuery($query);

                $query_notifikasi = "INSERT INTO Notifikasi VALUES ('$fk_idUser',null,'Verifikasi Berhasil', 'Wanted Item Anda dengan nama $namaBarang 
                telah berhasil disetujui. Sekarang barang anda sudah ada di fitur Market (Wanted Item).', 0, '$now')";
                $query_result1 = $this->db->executeNonSelectQuery($query_notifikasi);
            }
        } else {
            $query = "UPDATE transaksi SET statusBarang = 'onPending' WHERE namaBarang = '$namaBarang'AND statusBarang='$statusBarang'";
            $query_result = $this->db->executeNonSelectQuery($query);

            $query_notifikasi = "INSERT INTO Notifikasi VALUES ('$fk_idUser',null,'Verifikasi Gagal', 'Barang Anda dengan nama $namaBarang 
            tidak berhasil disetujui. Anda diharapkan untuk mengisi ulang form.', 0, '$now')";
            $query_result1 = $this->db->executeNonSelectQuery($query_notifikasi);
        }
    }

    public function getListPembayaran($param)
    {
        $query = "select himpA.idUser1, himpA.idUser2, himpA.namaSatu, himpA.email, himpA.idTrip, himpA.namaBarang
        from(select idUser1,idTrip, idUser2, user.namaUser as 'namaSatu', user.email, namaBarang
        from transaksi inner join user 
        on user.idUser = transaksi.idUser1
            where statusPembayaran = '$param') as himpA inner join user on himpA.idUser2 = user.idUser";
        $query_result = $this->db->executeSelectQuery($query);
        $result = [];
        foreach ($query_result as $key => $value) {
            $result[] = new ListPembayaran($value['idUser1'], $value['idUser2'], $value['idTrip'], $value['namaSatu'], $value['email'], $value['namaBarang']);
        }
        return $result;
    }

    public function getDetailPembayaran($idPenerima, $idPembeli, $namaBarang)
    {

        $query = "SELECT * FROM transaksi inner join bank on transaksi.idBankPembayaran = bank.idBank where idUser1 = '$idPenerima' AND idUser2 = '$idPembeli' AND namaBarang = '$namaBarang'";
        $query_result = $this->db->executeSelectQuery($query);
        $result = [];
        foreach ($query_result as $key => $value) {
            $totalHarga = ($value['hargaBarang'] + $value['hargaOngkir'] + $value['hargaJasa']) - $value['kodeUnik'];
            $result[] = new transaksi($value['idUser1'], $value['kodeUnik'], $value['idUser2'], $value['namaBank'], $value['hargaBarang'], $value['hargaOngkir'], $value['hargaJasa'], $value['namaBarang'], null, $value['deskripsiBarang'], $value['gambarBarang'], $totalHarga, $value['buktiPembayaran']);
        }
        return $result;
    }

    public function getProfilePembayaran($id)
    {
        $query = "SELECT * FROM user WHERE idUser = '$id'";
        $query_result = $this->db->executeSelectQuery($query);
        $result = [];
        foreach ($query_result as $key => $value) {
            $result[] = new user(null, $value['namaUser'], null, $value['alamat'], $value['nohp'], null, null, null, null, $value['norek'], null, $value['gambarProfile']);
        }
        return $result;
    }

    public function updatePembayaran()
    {
        $verified = $_POST['verified'];
        if ($verified == 'verified') {
            $idPenerima = $_POST['idPenerima'];
            $idPembeli = $_POST['idPembeli'];
            $timezone = new DateTimeZone('Asia/Jakarta');
            $date = new DateTime();
            $date->setTimeZone($timezone);
            $now = $date->format('Y-m-d H:i:s');
            $query = "UPDATE transaksi SET statusBarang = 'onDelivery', statusPembayaran = 'verified' WHERE idUser1 = '$idPenerima' AND idUser2 = '$idPembeli'";
            $query_result = $this->db->executeNonSelectQuery($query);
            $query_notifikasi1 = "INSERT INTO Notifikasi VALUES ('$idPembeli',null,'Pembayaran Berhasil', 'Cek barang anda di tracking kami', 0, '$now')";
            $query_result1 = $this->db->executeNonSelectQuery($query_notifikasi1);
            $query_notifikasi2 = "INSERT INTO Notifikasi VALUES ('$idPenerima',null,'Pembayaran Berhasil', 'Silahkan diproses barang yang akan dibeli', 0, '$now')";
            $query_result2 = $this->db->executeNonSelectQuery($query_notifikasi2);
        } else {
            $idPenerima = $_POST['idPenerima'];
            $idPembeli = $_POST['idPembeli'];
            $timezone = new DateTimeZone('Asia/Jakarta');
            $date = new DateTime();
            $date->setTimeZone($timezone);
            $now = $date->format('Y-m-d H:i:s');
            $query = "UPDATE transaksi SET statusBarang = 'onDelivery', statusPembayaran = 'unverified' WHERE idUser1 = '$idPenerima' AND idUser2 = '$idPembeli'";
            $query_result = $this->db->executeNonSelectQuery($query);
            $query_notifikasi1 = "INSERT INTO Notifikasi VALUES ('$idPembeli',null,'Pembayaran Gagal', 'Silahkan pilih barang ulang di market', 0, '$now')";
            $query_result1 = $this->db->executeNonSelectQuery($query_notifikasi1);
            $query_notifikasi2 = "INSERT INTO Notifikasi VALUES ('$idPenerima',null,'Pembayaran Pembeli Gagal', 'Barang anda akan tertampil lagi di market ', 0, '$now')";
            $query_result2 = $this->db->executeNonSelectQuery($query_notifikasi2);
        }
    }

    public function updatePembayaranAdmin()
    {
        $idPenerima = $_POST['idPenerima'];
        $idPembeli = $_POST['idPembeli'];
        $namaBarang = $_POST['namaBarang'];
        $timezone = new DateTimeZone('Asia/Jakarta');
        $date = new DateTime();
        $date->setTimeZone($timezone);
        $now = $date->format('Y-m-d H:i:s');
        $query = "UPDATE transaksi SET statusBarang = 'transactionCompleteAndPaid' WHERE idUser1 = '$idPenerima' AND idUser2 = '$idPembeli' AND namaBarang = '$namaBarang'";
        $query_result = $this->db->executeNonSelectQuery($query);
        $query_notifikasi1 = "INSERT INTO Notifikasi VALUES ('$idPenerima',null,'Pembayaran Titipaja Berhasil', 'Titipaja telah membayar ke rekening anda', 0, '$now')";
        $query_result1 = $this->db->executeNonSelectQuery($query_notifikasi1);
    }
}
