<?php
if (empty($_GET['periode1'])) {
    die("Periode Awal Tidak Boleh Kosong!");
}
if (empty($_GET['periode2'])) {
    die("Periode Akhir Tidak Boleh Kosong!");
}
if (empty($_GET['pemasukan'])) {
    die("Akun Pemasukan Tidak Boleh Kosong!");
}
if (empty($_GET['pengeluaran'])) {
    die("Akun Pengeluaran Tidak Boleh Kosong!");
}

include '../../_Config/Connection.php';

function build_like_clause($Conn, $field, $akun_array)
{
    $likes = array();
    foreach ($akun_array as $akun) {
        $akun = mysqli_real_escape_string($Conn, $akun);
        $likes[] = "$field LIKE '%$akun%'";
    }
    return '(' . implode(' OR ', $likes) . ')';
}

$periode1 = $_GET['periode1'];
$periode2 = $_GET['periode2'];
$pemasukan_array = explode(',', $_GET['pemasukan']);
$pengeluaran_array = explode(',', $_GET['pengeluaran']);

$like_pemasukan = build_like_clause($Conn, 'kode_perkiraan', $pemasukan_array);
$like_pengeluaran = build_like_clause($Conn, 'kode_perkiraan', $pengeluaran_array);

// Set headers for Excel export
header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=Laporan_LabaRugi_{$periode1}_sampai_{$periode2}.xls");
header("Pragma: no-cache");
header("Expires: 0");

echo "<style>
    table {
        border-collapse: collapse;
        table-layout: fixed;
        width: 100%;
        font-family: Arial, sans-serif;
        font-size: 12pt;
    }
    th, td {
        border: 1px solid #000;
        padding: 6px 8px;
        word-wrap: break-word;
        vertical-align: top;
    }
    th {
        background-color: #D3D3D3;
        font-weight: bold;
        text-align: center;
    }
    td {
        text-align: left;
    }
    td.numeric {
        text-align: right;
    }
    .center {
        text-align: center;
    }
    /* Fixed widths for each column */
    col.no { width: 5%; }
    col.tanggal { width: 18%; }
    col.kode_akun { width: 30%; }
    col.transaksi { width: 37%; }
    col.jumlah { width: 10%; }
</style>";

echo "<table>";
echo "<colgroup>";
echo "<col class='no' />";
echo "<col class='tanggal' />";
echo "<col class='kode_akun' />";
echo "<col class='transaksi' />";
echo "<col class='jumlah' />";
echo "</colgroup>";

echo "<tr><th colspan='5' style='font-size:16pt;'>Laporan Laba-Rugi</th></tr>";
echo "<tr><th colspan='5' style='text-align:center;'>Periode: $periode1 - $periode2</th></tr>";
echo "<tr>";
echo "<th>No</th><th>Tanggal</th><th>Kode Akun</th><th>Transaksi</th><th>Jumlah</th>";
echo "</tr>";

echo "<tr><td class='center'><b>A.</b></td><td colspan='4'><b>Transaksi Pemasukan</b></td></tr>";

// Data Pemasukan
$NoPemasukan = 1;
$JumlahPemasukan = 0;
$QryJurnal = mysqli_query($Conn, "SELECT * FROM jurnal WHERE $like_pemasukan AND tanggal >= '$periode1' AND tanggal <= '$periode2' ORDER BY id_jurnal DESC");
while ($DataJurnal = mysqli_fetch_array($QryJurnal)) {
    $tanggal = $DataJurnal['tanggal'];
    $kode_perkiraan = $DataJurnal['kode_perkiraan'];
    $nama_perkiraan = $DataJurnal['nama_perkiraan'];
    $nilai = $DataJurnal['nilai'];

    // Menentukan label transaksi
    if (empty($DataJurnal['id_transaksi'])) {
        if (empty($DataJurnal['id_simpanan'])) {
            if (empty($DataJurnal['id_pinjaman_angsuran'])) {
                if (empty($DataJurnal['id_pinjaman'])) {
                    if (empty($DataJurnal['id_shu_session'])) {
                        $LabelTransaksi = "None";
                    } else {
                        $id_shu_session = $DataJurnal['id_shu_session'];
                        $QryBagiHasil = mysqli_query($Conn, "SELECT * FROM shu_session WHERE id_shu_session='$id_shu_session'") or die(mysqli_error($Conn));
                        $DatabagiHasil = mysqli_fetch_array($QryBagiHasil);
                        $sesi_shu = $DatabagiHasil['sesi_shu'];
                        $LabelTransaksi = "Bagi Hasil $sesi_shu ID.$id_shu_session";
                    }
                } else {
                    $id_pinjaman = $DataJurnal['id_pinjaman'];
                    $QryPinjaman = mysqli_query($Conn, "SELECT * FROM pinjaman WHERE id_pinjaman='$id_pinjaman'") or die(mysqli_error($Conn));
                    $DataPinjaman = mysqli_fetch_array($QryPinjaman);
                    $tanggal_pinjaman = $DataPinjaman['tanggal_pinjaman'];
                    $LabelTransaksi = "Pinjaman $tanggal_pinjaman ID.$id_pinjaman";
                }
            } else {
                $id_pinjaman_angsuran = $DataJurnal['id_pinjaman_angsuran'];
                $Qryangsuran = mysqli_query($Conn, "SELECT * FROM pinjaman_angsuran WHERE id_pinjaman_angsuran='$id_pinjaman_angsuran'") or die(mysqli_error($Conn));
                $DataAngsuran = mysqli_fetch_array($Qryangsuran);
                $KategoriTransaksi = $DataAngsuran['kategori_angsuran'];
                $LabelTransaksi = "Angsuran $KategoriTransaksi ID.$id_pinjaman_angsuran";
            }
        } else {
            $id_simpanan = $DataJurnal['id_simpanan'];
            $QrySimpanan = mysqli_query($Conn, "SELECT * FROM simpanan WHERE id_simpanan='$id_simpanan'") or die(mysqli_error($Conn));
            $DataSimpanan = mysqli_fetch_array($QrySimpanan);
            $KategoriTransaksi = $DataSimpanan['kategori'];
            $LabelTransaksi = "$KategoriTransaksi ID.$id_simpanan";
        }
    } else {
        $id_transaksi = $DataJurnal['id_transaksi'];
        $QryTransaksi = mysqli_query($Conn, "SELECT * FROM transaksi WHERE id_transaksi='$id_transaksi'") or die(mysqli_error($Conn));
        $DataTransaksi = mysqli_fetch_array($QryTransaksi);
        $KategoriTransaksi = $DataTransaksi['kategori'];
        $LabelTransaksi = "Transaksi $KategoriTransaksi ID.$id_transaksi";
    }

    $JumlahPemasukan += $nilai;

    echo "<tr>";
    echo "<td class='center'>A.$NoPemasukan</td>";
    echo "<td>$tanggal</td>";
    echo "<td>$kode_perkiraan $nama_perkiraan</td>";
    echo "<td>$LabelTransaksi</td>";
    echo "<td class='numeric'>$nilai</td>";
    echo "</tr>";

    $NoPemasukan++;
}

echo "<tr>";
echo "<td></td><td colspan='3' style='font-weight:bold;'>JUMLAH PEMASUKAN</td>";
echo "<td class='numeric' style='font-weight:bold;'>$JumlahPemasukan</td>";
echo "</tr>";

echo "<tr><td class='center'><b>B.</b></td><td colspan='4'><b>Transaksi Pengeluaran</b></td></tr>";

// Data Pengeluaran
$NoPengeluaran = 1;
$JumlahPengeluaran = 0;
$QryJurnal = mysqli_query($Conn, "SELECT * FROM jurnal WHERE $like_pengeluaran AND tanggal >= '$periode1' AND tanggal <= '$periode2' ORDER BY id_jurnal DESC");
while ($DataJurnal = mysqli_fetch_array($QryJurnal)) {
    $tanggal = $DataJurnal['tanggal'];
    $kode_perkiraan = $DataJurnal['kode_perkiraan'];
    $nama_perkiraan = $DataJurnal['nama_perkiraan'];
    $nilai = $DataJurnal['nilai'];

    // Menentukan label transaksi
    if (empty($DataJurnal['id_transaksi'])) {
        if (empty($DataJurnal['id_simpanan'])) {
            if (empty($DataJurnal['id_pinjaman_angsuran'])) {
                if (empty($DataJurnal['id_pinjaman'])) {
                    if (empty($DataJurnal['id_shu_session'])) {
                        $LabelTransaksi = "None";
                    } else {
                        $id_shu_session = $DataJurnal['id_shu_session'];
                        $QryBagiHasil = mysqli_query($Conn, "SELECT * FROM shu_session WHERE id_shu_session='$id_shu_session'") or die(mysqli_error($Conn));
                        $DatabagiHasil = mysqli_fetch_array($QryBagiHasil);
                        $sesi_shu = $DatabagiHasil['sesi_shu'];
                        $LabelTransaksi = "Bagi Hasil $sesi_shu ID.$id_shu_session";
                    }
                } else {
                    $id_pinjaman = $DataJurnal['id_pinjaman'];
                    $QryPinjaman = mysqli_query($Conn, "SELECT * FROM pinjaman WHERE id_pinjaman='$id_pinjaman'") or die(mysqli_error($Conn));
                    $DataPinjaman = mysqli_fetch_array($QryPinjaman);
                    $tanggal_pinjaman = $DataPinjaman['tanggal_pinjaman'];
                    $LabelTransaksi = "Pinjaman $tanggal_pinjaman ID.$id_pinjaman";
                }
            } else {
                $id_pinjaman_angsuran = $DataJurnal['id_pinjaman_angsuran'];
                $Qryangsuran = mysqli_query($Conn, "SELECT * FROM pinjaman_angsuran WHERE id_pinjaman_angsuran='$id_pinjaman_angsuran'") or die(mysqli_error($Conn));
                $DataAngsuran = mysqli_fetch_array($Qryangsuran);
                $KategoriTransaksi = $DataAngsuran['kategori_angsuran'];
                $LabelTransaksi = "Angsuran $KategoriTransaksi ID.$id_pinjaman_angsuran";
            }
        } else {
            $id_simpanan = $DataJurnal['id_simpanan'];
            $QrySimpanan = mysqli_query($Conn, "SELECT * FROM simpanan WHERE id_simpanan='$id_simpanan'") or die(mysqli_error($Conn));
            $DataSimpanan = mysqli_fetch_array($QrySimpanan);
            $KategoriTransaksi = $DataSimpanan['kategori'];
            $LabelTransaksi = "$KategoriTransaksi ID.$id_simpanan";
        }
    } else {
        $id_transaksi = $DataJurnal['id_transaksi'];
        $QryTransaksi = mysqli_query($Conn, "SELECT * FROM transaksi WHERE id_transaksi='$id_transaksi'") or die(mysqli_error($Conn));
        $DataTransaksi = mysqli_fetch_array($QryTransaksi);
        $KategoriTransaksi = $DataTransaksi['kategori'];
        $LabelTransaksi = "Transaksi $KategoriTransaksi ID.$id_transaksi";
    }

    $JumlahPengeluaran += $nilai;

    echo "<tr>";
    echo "<td class='center'>B.$NoPengeluaran</td>";
    echo "<td>$tanggal</td>";
    echo "<td>$kode_perkiraan $nama_perkiraan</td>";
    echo "<td>$LabelTransaksi</td>";
    echo "<td class='numeric'>$nilai</td>";
    echo "</tr>";

    $NoPengeluaran++;
}

echo "<tr>";
echo "<td></td><td colspan='3' style='font-weight:bold;'>JUMLAH PENGELUARAN</td>";
echo "<td class='numeric' style='font-weight:bold;'>$JumlahPengeluaran</td>";
echo "</tr>";

$LabaRugi = $JumlahPemasukan - $JumlahPengeluaran;
echo "<tr>";
echo "<td></td><td colspan='3' style='font-weight:bold;'>LABA/RUGI</td>";
echo "<td class='numeric' style='font-weight:bold;'>$LabaRugi</td>";
echo "</tr>";

echo "</table>";
