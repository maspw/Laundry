-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 31 Des 2025 pada 06.39
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_laundry`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `akun`
--

CREATE TABLE `akun` (
  `id_akun` varchar(10) NOT NULL,
  `header_akun` varchar(50) DEFAULT NULL,
  `nama_akun` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `akun`
--

INSERT INTO `akun` (`id_akun`, `header_akun`, `nama_akun`) VALUES
('AKN01', '100', 'Kas'),
('AKN02', '110', 'Piutang'),
('AKN03', '200', 'Pendapatan Laundry'),
('AKN04', '210', 'HPP'),
('AKN05', '300', 'Modal'),
('AKN06', '310', 'Beban Listrik'),
('AKN07', '320', 'Beban Gaji'),
('AKN08', '330', 'Beban Operasional'),
('AKN09', '400', 'Perlengkapan'),
('AKN10', '410', 'Pembelian Perlengkapan');

-- --------------------------------------------------------

--
-- Struktur dari tabel `jurnal_pembayaran`
--

CREATE TABLE `jurnal_pembayaran` (
  `id_pembayaran` varchar(10) DEFAULT NULL,
  `id_akun` varchar(10) DEFAULT NULL,
  `posisi_dr_cr` enum('dr','cr') DEFAULT NULL,
  `nominal` decimal(12,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `jurnal_pembayaran`
--

INSERT INTO `jurnal_pembayaran` (`id_pembayaran`, `id_akun`, `posisi_dr_cr`, `nominal`) VALUES
('PB01', 'AKN01', 'dr', 24000.00),
('PB01', 'AKN03', 'cr', 24000.00),
('PB02', 'AKN01', 'dr', 16000.00),
('PB02', 'AKN03', 'cr', 16000.00),
('PB03', 'AKN01', 'dr', 12000.00),
('PB03', 'AKN03', 'cr', 12000.00),
('PB04', 'AKN01', 'dr', 40000.00),
('PB04', 'AKN03', 'cr', 40000.00),
('PB05', 'AKN01', 'dr', 60000.00),
('PB05', 'AKN03', 'cr', 60000.00),
('PB06', 'AKN01', 'dr', 18000.00),
('PB06', 'AKN03', 'cr', 18000.00),
('PB07', 'AKN01', 'dr', 25000.00),
('PB07', 'AKN03', 'cr', 25000.00),
('PB08', 'AKN01', 'dr', 30000.00),
('PB08', 'AKN03', 'cr', 30000.00),
('PB09', 'AKN01', 'dr', 20000.00),
('PB09', 'AKN03', 'cr', 20000.00),
('PB10', 'AKN01', 'dr', 24000.00),
('PB10', 'AKN03', 'cr', 24000.00);

-- --------------------------------------------------------

--
-- Struktur dari tabel `jurnal_pembelian`
--

CREATE TABLE `jurnal_pembelian` (
  `id_pembelian` varchar(10) DEFAULT NULL,
  `id_akun` varchar(10) DEFAULT NULL,
  `posisi_dr_cr` enum('dr','cr') DEFAULT NULL,
  `nominal` decimal(12,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `jurnal_pembelian`
--

INSERT INTO `jurnal_pembelian` (`id_pembelian`, `id_akun`, `posisi_dr_cr`, `nominal`) VALUES
('PBL01', 'AKN09', 'dr', 100000.00),
('PBL01', 'AKN01', 'cr', 100000.00),
('PBL02', 'AKN09', 'dr', 45000.00),
('PBL02', 'AKN01', 'cr', 45000.00),
('PBL03', 'AKN09', 'dr', 60000.00),
('PBL03', 'AKN01', 'cr', 60000.00),
('PBL04', 'AKN09', 'dr', 50000.00),
('PBL04', 'AKN01', 'cr', 50000.00),
('PBL05', 'AKN09', 'dr', 40000.00),
('PBL05', 'AKN01', 'cr', 40000.00),
('PBL06', 'AKN09', 'dr', 75000.00),
('PBL06', 'AKN01', 'cr', 75000.00),
('PBL07', 'AKN09', 'dr', 72000.00),
('PBL07', 'AKN01', 'cr', 72000.00),
('PBL08', 'AKN09', 'dr', 132000.00),
('PBL08', 'AKN01', 'cr', 132000.00),
('PBL09', 'AKN09', 'dr', 78000.00),
('PBL09', 'AKN01', 'cr', 78000.00),
('PBL10', 'AKN09', 'dr', 100000.00),
('PBL10', 'AKN01', 'cr', 100000.00);

-- --------------------------------------------------------

--
-- Struktur dari tabel `jurnal_pengeluaran`
--

CREATE TABLE `jurnal_pengeluaran` (
  `id_pengeluaran` varchar(10) DEFAULT NULL,
  `id_akun` varchar(10) DEFAULT NULL,
  `posisi_dr_cr` enum('dr','cr') DEFAULT NULL,
  `nominal` decimal(12,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `jurnal_pengeluaran`
--

INSERT INTO `jurnal_pengeluaran` (`id_pengeluaran`, `id_akun`, `posisi_dr_cr`, `nominal`) VALUES
('PGO01', 'AKN06', 'dr', 50000.00),
('PGO01', 'AKN01', 'cr', 50000.00),
('PGO02', 'AKN08', 'dr', 30000.00),
('PGO02', 'AKN01', 'cr', 30000.00),
('PGO03', 'AKN08', 'dr', 20000.00),
('PGO03', 'AKN01', 'cr', 20000.00),
('PGO04', 'AKN08', 'dr', 15000.00),
('PGO04', 'AKN01', 'cr', 15000.00),
('PGO05', 'AKN08', 'dr', 10000.00),
('PGO05', 'AKN01', 'cr', 10000.00),
('PGO06', 'AKN08', 'dr', 12000.00),
('PGO06', 'AKN01', 'cr', 12000.00),
('PGO07', 'AKN06', 'dr', 55000.00),
('PGO07', 'AKN01', 'cr', 55000.00),
('PGO08', 'AKN08', 'dr', 35000.00),
('PGO08', 'AKN01', 'cr', 35000.00),
('PGO09', 'AKN08', 'dr', 18000.00),
('PGO09', 'AKN01', 'cr', 18000.00),
('PGO10', 'AKN08', 'dr', 17000.00),
('PGO10', 'AKN01', 'cr', 17000.00);

-- --------------------------------------------------------

--
-- Struktur dari tabel `jurnal_penggunaan`
--

CREATE TABLE `jurnal_penggunaan` (
  `id_penggunaan` varchar(10) DEFAULT NULL,
  `id_akun` varchar(10) DEFAULT NULL,
  `posisi_dr_cr` enum('dr','cr') DEFAULT NULL,
  `nominal` decimal(12,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `jurnal_penggunaan`
--

INSERT INTO `jurnal_penggunaan` (`id_penggunaan`, `id_akun`, `posisi_dr_cr`, `nominal`) VALUES
('PGB01', 'AKN04', 'dr', 40000.00),
('PGB01', 'AKN09', 'cr', 40000.00),
('PGB02', 'AKN04', 'dr', 15000.00),
('PGB02', 'AKN09', 'cr', 15000.00),
('PGB03', 'AKN04', 'dr', 30000.00),
('PGB03', 'AKN09', 'cr', 30000.00),
('PGB04', 'AKN04', 'dr', 25000.00),
('PGB04', 'AKN09', 'cr', 25000.00),
('PGB05', 'AKN04', 'dr', 20000.00),
('PGB05', 'AKN09', 'cr', 20000.00),
('PGB06', 'AKN04', 'dr', 30000.00),
('PGB06', 'AKN09', 'cr', 30000.00),
('PGB07', 'AKN04', 'dr', 18000.00),
('PGB07', 'AKN09', 'cr', 18000.00),
('PGB08', 'AKN04', 'dr', 22000.00),
('PGB08', 'AKN09', 'cr', 22000.00),
('PGB09', 'AKN04', 'dr', 26000.00),
('PGB09', 'AKN09', 'cr', 26000.00),
('PGB10', 'AKN04', 'dr', 20000.00),
('PGB10', 'AKN09', 'cr', 20000.00);

-- --------------------------------------------------------

--
-- Struktur dari tabel `karyawan`
--

CREATE TABLE `karyawan` (
  `id_karyawan` varchar(10) NOT NULL,
  `nama_karyawan` varchar(100) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `no_telp` varchar(20) DEFAULT NULL,
  `jabatan` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `karyawan`
--

INSERT INTO `karyawan` (`id_karyawan`, `nama_karyawan`, `alamat`, `no_telp`, `jabatan`) VALUES
('KRY01', 'Rizal', 'Jl Mawar', '08101', 'Kasir'),
('KRY02', 'Sinta', 'Jl Melati', '08102', 'Kasir'),
('KRY03', 'Yudi', 'Jl Kenanga', '08103', 'Karyawan'),
('KRY04', 'Rara', 'Jl Anggrek', '08104', 'Karyawan'),
('KRY05', 'Bagas', 'Jl Dahlia', '08105', 'Karyawan'),
('KRY06', 'Lina', 'Jl Cemara', '08106', 'Karyawan'),
('KRY07', 'Fahri', 'Jl Teratai', '08107', 'Karyawan'),
('KRY08', 'Mila', 'Jl Flamboyan', '08108', 'Karyawan'),
('KRY09', 'Riko', 'Jl Kamboja', '08109', 'Owner'),
('KRY10', 'Sari', 'Jl Nusa', '08110', 'Karyawan');

-- --------------------------------------------------------

--
-- Struktur dari tabel `layanan`
--

CREATE TABLE `layanan` (
  `id_layanan` varchar(10) NOT NULL,
  `nama_layanan` varchar(100) DEFAULT NULL,
  `harga` decimal(12,2) DEFAULT NULL,
  `satuan` varchar(50) DEFAULT NULL,
  `estimasi_waktu` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `layanan`
--

INSERT INTO `layanan` (`id_layanan`, `nama_layanan`, `harga`, `satuan`, `estimasi_waktu`) VALUES
('LYN01', 'Cuci Kering', 8000.00, 'kg', '1 hari'),
('LYN02', 'Cuci Basah', 6000.00, 'kg', '1 hari'),
('LYN03', 'Cuci Setrika', 12000.00, 'kg', '2 hari'),
('LYN04', 'Setrika Saja', 8000.00, 'kg', '1 hari'),
('LYN05', 'Express 6 Jam', 20000.00, 'kg', '6 jam'),
('LYN06', 'Express 12 Jam', 15000.00, 'kg', '12 jam'),
('LYN07', 'Bed Cover', 25000.00, 'buah', '1 hari'),
('LYN08', 'Karpet', 30000.00, 'm2', '2 hari'),
('LYN09', 'Gorden', 20000.00, 'm2', '2 hari'),
('LYN10', 'Sepatu', 25000.00, 'pasang', '2 hari');

-- --------------------------------------------------------

--
-- Struktur dari tabel `metode_pembayaran`
--

CREATE TABLE `metode_pembayaran` (
  `id_metode` varchar(10) NOT NULL,
  `nama_metode` varchar(50) DEFAULT NULL,
  `no_rek` varchar(50) DEFAULT NULL,
  `atas_nama` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `metode_pembayaran`
--

INSERT INTO `metode_pembayaran` (`id_metode`, `nama_metode`, `no_rek`, `atas_nama`) VALUES
('MTP01', 'Cash', '-', '-'),
('MTP02', 'Transfer BCA', '1234567890', 'Zyngga Laundry'),
('MTP03', 'Transfer BRI', '098765432112345', 'Zyngga Laundry'),
('MTP04', 'Gopay', '085612345678', 'Zyngga Laundry'),
('MTP05', 'OVO', '081987654321', 'Zyngga Laundry'),
('MTP06', 'ShopeePay', '082233445566', 'Zyngga Laundry'),
('MTP07', 'Dana', '081234567890', 'Zyngga Laundry'),
('MTP08', 'QRIS', '0001122334455', 'Zyngga Laundry');

-- --------------------------------------------------------

--
-- Struktur dari tabel `orders`
--

CREATE TABLE `orders` (
  `id_order` varchar(10) NOT NULL,
  `tgl_order` date DEFAULT NULL,
  `total_order` decimal(12,2) DEFAULT NULL,
  `status_order` varchar(50) DEFAULT NULL,
  `id_pelanggan` varchar(10) DEFAULT NULL,
  `id_karyawan` varchar(10) DEFAULT NULL,
  `id_layanan` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `orders`
--

INSERT INTO `orders` (`id_order`, `tgl_order`, `total_order`, `status_order`, `id_pelanggan`, `id_karyawan`, `id_layanan`) VALUES
('ORD01', '2025-01-01', 24000.00, 'Selesai', 'PLG01', 'KRY01', 'LYN03'),
('ORD02', '2025-01-02', 16000.00, 'Selesai', 'PLG02', 'KRY02', 'LYN01'),
('ORD03', '2025-01-03', 12000.00, 'Selesai', 'PLG03', 'KRY03', 'LYN04'),
('ORD04', '2025-01-04', 40000.00, 'Selesai', 'PLG04', 'KRY01', 'LYN05'),
('ORD05', '2025-01-05', 60000.00, 'Selesai', 'PLG05', 'KRY06', 'LYN10'),
('ORD06', '2025-01-06', 18000.00, 'Selesai', 'PLG06', 'KRY07', 'LYN02'),
('ORD07', '2025-01-07', 25000.00, 'Selesai', 'PLG07', 'KRY03', 'LYN07'),
('ORD08', '2025-01-08', 30000.00, 'Selesai', 'PLG08', 'KRY08', 'LYN08'),
('ORD09', '2025-01-09', 20000.00, 'Selesai', 'PLG09', 'KRY09', 'LYN09'),
('ORD10', '2025-01-10', 24000.00, 'Selesai', 'PLG10', 'KRY10', 'LYN03'),
('ORD11', '2025-01-10', 40000.00, 'proses', 'PLG05', 'KRY05', 'LYN05'),
('ORD12', '2025-01-11', 20000.00, 'Selesai', 'PLG01', 'KRY03', 'LYN01'),
('ORD13', '2025-01-12', 30000.00, 'Selesai', 'PLG02', 'KRY04', 'LYN02'),
('ORD14', '2025-01-13', 40000.00, 'Selesai', 'PLG03', 'KRY05', 'LYN03'),
('ORD15', '2025-01-14', 25000.00, 'Selesai', 'PLG04', 'KRY06', 'LYN04'),
('ORD16', '2025-01-15', 35000.00, 'Selesai', 'PLG05', 'KRY07', 'LYN05'),
('ORD17', '2025-01-16', 15000.00, 'Selesai', 'PLG06', 'KRY08', 'LYN02'),
('ORD18', '2025-01-17', 30000.00, 'Selesai', 'PLG07', 'KRY03', 'LYN07'),
('ORD19', '2025-01-18', 28000.00, 'Selesai', 'PLG08', 'KRY04', 'LYN01'),
('ORD20', '2025-01-19', 22000.00, 'Selesai', 'PLG09', 'KRY05', 'LYN04'),
('ORD21', '2025-01-20', 26000.00, 'Selesai', 'PLG10', 'KRY06', 'LYN03'),
('ORD22', '2025-01-21', 24000.00, 'Selesai', 'PLG01', 'KRY07', 'LYN01'),
('ORD23', '2025-01-22', 18000.00, 'Selesai', 'PLG02', 'KRY08', 'LYN02'),
('ORD24', '2025-01-23', 32000.00, 'Selesai', 'PLG03', 'KRY03', 'LYN08'),
('ORD25', '2025-01-24', 40000.00, 'Selesai', 'PLG04', 'KRY04', 'LYN09'),
('ORD26', '2025-01-25', 50000.00, 'Selesai', 'PLG05', 'KRY05', 'LYN10'),
('ORD27', '2025-01-26', 20000.00, 'Selesai', 'PLG06', 'KRY06', 'LYN01'),
('ORD28', '2025-01-27', 30000.00, 'Selesai', 'PLG07', 'KRY07', 'LYN02'),
('ORD29', '2025-01-28', 35000.00, 'Selesai', 'PLG08', 'KRY08', 'LYN03'),
('ORD30', '2025-01-29', 28000.00, 'Selesai', 'PLG09', 'KRY03', 'LYN04'),
('ORD31', '2025-01-30', 42000.00, 'Selesai', 'PLG10', 'KRY04', 'LYN05');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pelanggan`
--

CREATE TABLE `pelanggan` (
  `id_pelanggan` varchar(10) NOT NULL,
  `nama` varchar(100) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `no_telp` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pelanggan`
--

INSERT INTO `pelanggan` (`id_pelanggan`, `nama`, `alamat`, `no_telp`) VALUES
('PLG01', 'Andi', 'Jl Merdeka 1', '0811111'),
('PLG02', 'Budi', 'Jl Merdeka 2', '0812222'),
('PLG03', 'Cici', 'Jl Merdeka 3', '0813333'),
('PLG04', 'Dewi', 'Jl Merdeka 4', '0814444'),
('PLG05', 'Eka', 'Jl Merdeka 5', '0815555'),
('PLG06', 'Feri', 'Jl Merdeka 6', '0816666'),
('PLG07', 'Gina', 'Jl Merdeka 7', '0817777'),
('PLG08', 'Hadi', 'Jl Merdeka 8', '0818888'),
('PLG09', 'Ika', 'Jl Merdeka 9', '0819999'),
('PLG10', 'Joni', 'Jl Merdeka 10', '0820000');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pembayaran`
--

CREATE TABLE `pembayaran` (
  `id_pembayaran` varchar(10) NOT NULL,
  `tgl_bayar` date DEFAULT NULL,
  `jml_bayar` decimal(12,2) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `id_order` varchar(10) DEFAULT NULL,
  `id_metode` varchar(10) DEFAULT NULL,
  `id_karyawan` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pembayaran`
--

INSERT INTO `pembayaran` (`id_pembayaran`, `tgl_bayar`, `jml_bayar`, `status`, `id_order`, `id_metode`, `id_karyawan`) VALUES
('PB01', '2025-01-01', 24000.00, 'Lunas', 'ORD01', 'MTP01', 'KRY01'),
('PB02', '2025-01-02', 16000.00, 'Lunas', 'ORD02', 'MTP08', 'KRY02'),
('PB03', '2025-01-03', 12000.00, 'Lunas', 'ORD03', 'MTP04', 'KRY01'),
('PB04', '2025-01-04', 40000.00, 'Lunas', 'ORD04', 'MTP02', 'KRY02'),
('PB05', '2025-01-05', 60000.00, 'Lunas', 'ORD05', 'MTP05', 'KRY01'),
('PB06', '2025-01-06', 18000.00, 'Lunas', 'ORD06', 'MTP03', 'KRY02'),
('PB07', '2025-01-07', 25000.00, 'Lunas', 'ORD07', 'MTP02', 'KRY02'),
('PB08', '2025-01-08', 30000.00, 'Lunas', 'ORD08', 'MTP01', 'KRY01'),
('PB09', '2025-01-09', 20000.00, 'Lunas', 'ORD09', 'MTP01', 'KRY01'),
('PB10', '2025-01-10', 24000.00, 'Lunas', 'ORD10', 'MTP08', 'KRY02'),
('PB11', '2025-01-11', 20000.00, 'Lunas', 'ORD12', 'MTP01', 'KRY01'),
('PB12', '2025-01-12', 30000.00, 'Lunas', 'ORD13', 'MTP02', 'KRY02'),
('PB13', '2025-01-13', 40000.00, 'Lunas', 'ORD14', 'MTP03', 'KRY01'),
('PB14', '2025-01-14', 25000.00, 'Lunas', 'ORD15', 'MTP01', 'KRY02'),
('PB15', '2025-01-15', 35000.00, 'Lunas', 'ORD16', 'MTP05', 'KRY01'),
('PB16', '2025-01-16', 15000.00, 'Lunas', 'ORD17', 'MTP06', 'KRY02'),
('PB17', '2025-01-17', 30000.00, 'Lunas', 'ORD18', 'MTP07', 'KRY01'),
('PB18', '2025-01-18', 28000.00, 'Lunas', 'ORD19', 'MTP08', 'KRY02'),
('PB19', '2025-01-19', 22000.00, 'Lunas', 'ORD20', 'MTP01', 'KRY01'),
('PB20', '2025-01-20', 26000.00, 'Lunas', 'ORD21', 'MTP02', 'KRY02'),
('PB21', '2025-01-21', 24000.00, 'Lunas', 'ORD22', 'MTP03', 'KRY01'),
('PB22', '2025-01-22', 18000.00, 'Lunas', 'ORD23', 'MTP04', 'KRY02'),
('PB23', '2025-01-23', 32000.00, 'Lunas', 'ORD24', 'MTP01', 'KRY01'),
('PB24', '2025-01-24', 40000.00, 'Lunas', 'ORD25', 'MTP06', 'KRY02'),
('PB25', '2025-01-25', 50000.00, 'Lunas', 'ORD26', 'MTP07', 'KRY01'),
('PB26', '2025-01-26', 20000.00, 'Lunas', 'ORD27', 'MTP08', 'KRY02'),
('PB27', '2025-01-27', 30000.00, 'Lunas', 'ORD28', 'MTP01', 'KRY01'),
('PB28', '2025-01-28', 35000.00, 'Lunas', 'ORD29', 'MTP02', 'KRY02'),
('PB29', '2025-01-29', 28000.00, 'Lunas', 'ORD30', 'MTP03', 'KRY01'),
('PB30', '2025-01-30', 42000.00, 'Lunas', 'ORD31', 'MTP04', 'KRY02');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pembelian_bahan_baku`
--

CREATE TABLE `pembelian_bahan_baku` (
  `id_pembelian` varchar(10) NOT NULL,
  `tgl_pembelian` date DEFAULT NULL,
  `nama_pembelian` varchar(100) DEFAULT NULL,
  `jml_pembelian` decimal(12,2) DEFAULT NULL,
  `id_karyawan` varchar(10) DEFAULT NULL,
  `id_perlengkapan` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pembelian_bahan_baku`
--

INSERT INTO `pembelian_bahan_baku` (`id_pembelian`, `tgl_pembelian`, `nama_pembelian`, `jml_pembelian`, `id_karyawan`, `id_perlengkapan`) VALUES
('PBL01', '2025-01-01', 'Deterjen', 5.00, 'KRY01', 'PRL01'),
('PBL02', '2025-01-02', 'Pelicin', 3.00, 'KRY02', 'PRL02'),
('PBL03', '2025-01-03', 'Parfum', 2.00, 'KRY03', 'PRL03'),
('PBL04', '2025-01-04', 'Plastik', 10.00, 'KRY04', 'PRL04'),
('PBL05', '2025-01-05', 'Karet Ikat', 20.00, 'KRY05', 'PRL05'),
('PBL06', '2025-01-06', 'Air Wangi', 3.00, 'KRY06', 'PRL06'),
('PBL07', '2025-01-07', 'Sabun Sepatu', 4.00, 'KRY07', 'PRL07'),
('PBL08', '2025-01-08', 'Pemutih', 6.00, 'KRY08', 'PRL08'),
('PBL09', '2025-01-09', 'Softener', 3.00, 'KRY09', 'PRL09'),
('PBL10', '2025-01-10', 'Stiker', 100.00, 'KRY10', 'PRL10');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pengambilan_laundry`
--

CREATE TABLE `pengambilan_laundry` (
  `id_pengambilan` varchar(10) NOT NULL,
  `tgl_ambil` date DEFAULT NULL,
  `nama_pengambil` varchar(100) DEFAULT NULL,
  `status_pengambilan` varchar(50) DEFAULT NULL,
  `id_pembayaran` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pengambilan_laundry`
--

INSERT INTO `pengambilan_laundry` (`id_pengambilan`, `tgl_ambil`, `nama_pengambil`, `status_pengambilan`, `id_pembayaran`) VALUES
('PGL01', '2025-01-01', 'Andi', 'Sudah Diambil', 'PB01'),
('PGL02', '2025-01-02', 'Budi', 'Sudah Diambil', 'PB02'),
('PGL03', '2025-01-03', 'Cici', 'Sudah Diambil', 'PB03'),
('PGL04', '2025-01-04', 'Dewi', 'Sudah Diambil', 'PB04'),
('PGL05', '2025-01-05', 'Eka', 'Sudah Diambil', 'PB05'),
('PGL06', '2025-01-06', 'Feri', 'Sudah Diambil', 'PB06'),
('PGL07', '2025-01-07', 'Gina', 'Sudah Diambil', 'PB07'),
('PGL08', '2025-01-08', 'Hadi', 'Sudah Diambil', 'PB08'),
('PGL09', '2025-01-09', 'Ika', 'Sudah Diambil', 'PB09'),
('PGL10', '2025-01-10', 'Joni', 'Belum Diambil', 'PB10'),
('PGL11', '2025-01-12', 'PLG01', 'Sudah Diambil', 'PB11'),
('PGL12', '2025-01-13', 'PLG02', 'Sudah Diambil', 'PB12'),
('PGL13', '2025-01-14', 'PLG03', 'Sudah Diambil', 'PB13'),
('PGL14', '2025-01-15', 'PLG04', 'Sudah Diambil', 'PB14'),
('PGL15', '2025-01-16', 'PLG05', 'Sudah Diambil', 'PB15'),
('PGL16', '2025-01-17', 'PLG06', 'Sudah Diambil', 'PB16'),
('PGL17', '2025-01-18', 'PLG07', 'Sudah Diambil', 'PB17'),
('PGL18', '2025-01-19', 'PLG08', 'Sudah Diambil', 'PB18'),
('PGL19', '2025-01-20', 'PLG09', 'Sudah Diambil', 'PB19'),
('PGL20', '2025-01-21', 'PLG10', 'Sudah Diambil', 'PB20'),
('PGL21', '2025-01-22', 'PLG01', 'Sudah Diambil', 'PB21'),
('PGL22', '2025-01-23', 'PLG02', 'Sudah Diambil', 'PB22'),
('PGL23', '2025-01-24', 'PLG03', 'Sudah Diambil', 'PB23'),
('PGL24', '2025-01-25', 'PLG04', 'Sudah Diambil', 'PB24'),
('PGL25', '2025-01-26', 'PLG05', 'Sudah Diambil', 'PB25'),
('PGL26', '2025-01-27', 'PLG06', 'Sudah Diambil', 'PB26'),
('PGL27', '2025-01-28', 'PLG07', 'Sudah Diambil', 'PB27'),
('PGL28', '2025-01-29', 'PLG08', 'Sudah Diambil', 'PB28'),
('PGL29', '2025-01-30', 'PLG09', 'Belum Diambil', 'PB29'),
('PGL30', '2025-01-31', 'PLG10', 'Belum Diambil', 'PB30');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pengeluaran_operasional`
--

CREATE TABLE `pengeluaran_operasional` (
  `id_pengeluaran` varchar(10) NOT NULL,
  `tgl_pengeluaran` date DEFAULT NULL,
  `nama_pengeluaran` varchar(100) DEFAULT NULL,
  `jml_pengeluaran` decimal(12,2) DEFAULT NULL,
  `id_karyawan` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pengeluaran_operasional`
--

INSERT INTO `pengeluaran_operasional` (`id_pengeluaran`, `tgl_pengeluaran`, `nama_pengeluaran`, `jml_pengeluaran`, `id_karyawan`) VALUES
('PGO01', '2025-01-01', 'Beban Listrik', 50000.00, 'KRY01'),
('PGO02', '2025-01-02', 'Beban Air', 30000.00, 'KRY02'),
('PGO03', '2025-01-03', 'Beban Transport', 20000.00, 'KRY03'),
('PGO04', '2025-01-04', 'Beban Internet', 15000.00, 'KRY04'),
('PGO05', '2025-01-05', 'Beban ATK', 10000.00, 'KRY05'),
('PGO06', '2025-01-06', 'Beban Kebersihan', 12000.00, 'KRY06'),
('PGO07', '2025-01-07', 'Beban Listrik', 55000.00, 'KRY07'),
('PGO08', '2025-01-08', 'Beban Air', 35000.00, 'KRY08'),
('PGO09', '2025-01-09', 'Beban Transport', 18000.00, 'KRY09'),
('PGO10', '2025-01-10', 'Beban Internet', 17000.00, 'KRY10');

-- --------------------------------------------------------

--
-- Struktur dari tabel `penggunaan_bahan_baku`
--

CREATE TABLE `penggunaan_bahan_baku` (
  `id_penggunaan` varchar(10) NOT NULL,
  `tgl_penggunaan` date DEFAULT NULL,
  `nama_penggunaan` varchar(100) DEFAULT NULL,
  `jml_penggunaan` decimal(12,2) DEFAULT NULL,
  `id_perlengkapan` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `penggunaan_bahan_baku`
--

INSERT INTO `penggunaan_bahan_baku` (`id_penggunaan`, `tgl_penggunaan`, `nama_penggunaan`, `jml_penggunaan`, `id_perlengkapan`) VALUES
('PGB01', '2025-01-01', 'Cuci Harian', 2.00, 'PRL01'),
('PGB02', '2025-01-02', 'Cuci Express', 1.00, 'PRL02'),
('PGB03', '2025-01-03', 'Cuci Setrika', 1.00, 'PRL03'),
('PGB04', '2025-01-04', 'Packing', 5.00, 'PRL04'),
('PGB05', '2025-01-05', 'Ikat Laundry', 10.00, 'PRL05'),
('PGB06', '2025-01-06', 'Parfum Harian', 1.00, 'PRL03'),
('PGB07', '2025-01-07', 'Sepatu', 1.00, 'PRL07'),
('PGB08', '2025-01-08', 'Pemutih', 1.00, 'PRL08'),
('PGB09', '2025-01-09', 'Softener', 1.00, 'PRL09'),
('PGB10', '2025-01-10', 'Stiker Label', 20.00, 'PRL10');

-- --------------------------------------------------------

--
-- Struktur dari tabel `perlengkapan`
--

CREATE TABLE `perlengkapan` (
  `id_perlengkapan` varchar(10) NOT NULL,
  `nama_perlengkapan` varchar(100) DEFAULT NULL,
  `jenis_perlengkapan` varchar(50) DEFAULT NULL,
  `harga_satuan` decimal(12,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `perlengkapan`
--

INSERT INTO `perlengkapan` (`id_perlengkapan`, `nama_perlengkapan`, `jenis_perlengkapan`, `harga_satuan`) VALUES
('PRL01', 'Deterjen Cair', 'Bahan', 12000.00),
('PRL02', 'Pelicin', 'Bahan', 10000.00),
('PRL03', 'Parfum', 'Bahan', 15000.00),
('PRL04', 'Plastik Packing', 'Bahan', 2500.00),
('PRL05', 'Karet Ikat', 'Bahan', 500.00),
('PRL06', 'Air Wangi', 'Bahan', 12000.00),
('PRL07', 'Sabun Sepatu', 'Bahan', 18000.00),
('PRL08', 'Pemutih', 'Bahan', 15000.00),
('PRL09', 'Softener', 'Bahan', 18000.00),
('PRL10', 'Stiker Label', 'Bahan', 750.00);

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `akun`
--
ALTER TABLE `akun`
  ADD PRIMARY KEY (`id_akun`);

--
-- Indeks untuk tabel `jurnal_pembayaran`
--
ALTER TABLE `jurnal_pembayaran`
  ADD KEY `id_pembayaran` (`id_pembayaran`),
  ADD KEY `id_akun` (`id_akun`);

--
-- Indeks untuk tabel `jurnal_pembelian`
--
ALTER TABLE `jurnal_pembelian`
  ADD KEY `id_pembelian` (`id_pembelian`),
  ADD KEY `id_akun` (`id_akun`);

--
-- Indeks untuk tabel `jurnal_pengeluaran`
--
ALTER TABLE `jurnal_pengeluaran`
  ADD KEY `id_pengeluaran` (`id_pengeluaran`),
  ADD KEY `id_akun` (`id_akun`);

--
-- Indeks untuk tabel `jurnal_penggunaan`
--
ALTER TABLE `jurnal_penggunaan`
  ADD KEY `id_penggunaan` (`id_penggunaan`),
  ADD KEY `id_akun` (`id_akun`);

--
-- Indeks untuk tabel `karyawan`
--
ALTER TABLE `karyawan`
  ADD PRIMARY KEY (`id_karyawan`);

--
-- Indeks untuk tabel `layanan`
--
ALTER TABLE `layanan`
  ADD PRIMARY KEY (`id_layanan`);

--
-- Indeks untuk tabel `metode_pembayaran`
--
ALTER TABLE `metode_pembayaran`
  ADD PRIMARY KEY (`id_metode`);

--
-- Indeks untuk tabel `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id_order`),
  ADD KEY `id_pelanggan` (`id_pelanggan`),
  ADD KEY `id_karyawan` (`id_karyawan`),
  ADD KEY `id_layanan` (`id_layanan`);

--
-- Indeks untuk tabel `pelanggan`
--
ALTER TABLE `pelanggan`
  ADD PRIMARY KEY (`id_pelanggan`);

--
-- Indeks untuk tabel `pembayaran`
--
ALTER TABLE `pembayaran`
  ADD PRIMARY KEY (`id_pembayaran`),
  ADD KEY `id_order` (`id_order`),
  ADD KEY `id_metode` (`id_metode`),
  ADD KEY `id_karyawan` (`id_karyawan`);

--
-- Indeks untuk tabel `pembelian_bahan_baku`
--
ALTER TABLE `pembelian_bahan_baku`
  ADD PRIMARY KEY (`id_pembelian`),
  ADD KEY `id_karyawan` (`id_karyawan`),
  ADD KEY `id_perlengkapan` (`id_perlengkapan`);

--
-- Indeks untuk tabel `pengambilan_laundry`
--
ALTER TABLE `pengambilan_laundry`
  ADD PRIMARY KEY (`id_pengambilan`),
  ADD KEY `id_pembayaran` (`id_pembayaran`);

--
-- Indeks untuk tabel `pengeluaran_operasional`
--
ALTER TABLE `pengeluaran_operasional`
  ADD PRIMARY KEY (`id_pengeluaran`),
  ADD KEY `id_karyawan` (`id_karyawan`);

--
-- Indeks untuk tabel `penggunaan_bahan_baku`
--
ALTER TABLE `penggunaan_bahan_baku`
  ADD PRIMARY KEY (`id_penggunaan`),
  ADD KEY `id_perlengkapan` (`id_perlengkapan`);

--
-- Indeks untuk tabel `perlengkapan`
--
ALTER TABLE `perlengkapan`
  ADD PRIMARY KEY (`id_perlengkapan`);

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `jurnal_pembayaran`
--
ALTER TABLE `jurnal_pembayaran`
  ADD CONSTRAINT `jurnal_pembayaran_ibfk_1` FOREIGN KEY (`id_pembayaran`) REFERENCES `pembayaran` (`id_pembayaran`),
  ADD CONSTRAINT `jurnal_pembayaran_ibfk_2` FOREIGN KEY (`id_akun`) REFERENCES `akun` (`id_akun`);

--
-- Ketidakleluasaan untuk tabel `jurnal_pembelian`
--
ALTER TABLE `jurnal_pembelian`
  ADD CONSTRAINT `jurnal_pembelian_ibfk_1` FOREIGN KEY (`id_pembelian`) REFERENCES `pembelian_bahan_baku` (`id_pembelian`),
  ADD CONSTRAINT `jurnal_pembelian_ibfk_2` FOREIGN KEY (`id_akun`) REFERENCES `akun` (`id_akun`);

--
-- Ketidakleluasaan untuk tabel `jurnal_pengeluaran`
--
ALTER TABLE `jurnal_pengeluaran`
  ADD CONSTRAINT `jurnal_pengeluaran_ibfk_1` FOREIGN KEY (`id_pengeluaran`) REFERENCES `pengeluaran_operasional` (`id_pengeluaran`),
  ADD CONSTRAINT `jurnal_pengeluaran_ibfk_2` FOREIGN KEY (`id_akun`) REFERENCES `akun` (`id_akun`);

--
-- Ketidakleluasaan untuk tabel `jurnal_penggunaan`
--
ALTER TABLE `jurnal_penggunaan`
  ADD CONSTRAINT `jurnal_penggunaan_ibfk_1` FOREIGN KEY (`id_penggunaan`) REFERENCES `penggunaan_bahan_baku` (`id_penggunaan`),
  ADD CONSTRAINT `jurnal_penggunaan_ibfk_2` FOREIGN KEY (`id_akun`) REFERENCES `akun` (`id_akun`);

--
-- Ketidakleluasaan untuk tabel `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`id_pelanggan`) REFERENCES `pelanggan` (`id_pelanggan`),
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`id_karyawan`) REFERENCES `karyawan` (`id_karyawan`),
  ADD CONSTRAINT `orders_ibfk_3` FOREIGN KEY (`id_layanan`) REFERENCES `layanan` (`id_layanan`);

--
-- Ketidakleluasaan untuk tabel `pembayaran`
--
ALTER TABLE `pembayaran`
  ADD CONSTRAINT `pembayaran_ibfk_1` FOREIGN KEY (`id_order`) REFERENCES `orders` (`id_order`),
  ADD CONSTRAINT `pembayaran_ibfk_2` FOREIGN KEY (`id_metode`) REFERENCES `metode_pembayaran` (`id_metode`),
  ADD CONSTRAINT `pembayaran_ibfk_3` FOREIGN KEY (`id_karyawan`) REFERENCES `karyawan` (`id_karyawan`);

--
-- Ketidakleluasaan untuk tabel `pembelian_bahan_baku`
--
ALTER TABLE `pembelian_bahan_baku`
  ADD CONSTRAINT `pembelian_bahan_baku_ibfk_1` FOREIGN KEY (`id_karyawan`) REFERENCES `karyawan` (`id_karyawan`),
  ADD CONSTRAINT `pembelian_bahan_baku_ibfk_2` FOREIGN KEY (`id_perlengkapan`) REFERENCES `perlengkapan` (`id_perlengkapan`);

--
-- Ketidakleluasaan untuk tabel `pengambilan_laundry`
--
ALTER TABLE `pengambilan_laundry`
  ADD CONSTRAINT `pengambilan_laundry_ibfk_1` FOREIGN KEY (`id_pembayaran`) REFERENCES `pembayaran` (`id_pembayaran`);

--
-- Ketidakleluasaan untuk tabel `pengeluaran_operasional`
--
ALTER TABLE `pengeluaran_operasional`
  ADD CONSTRAINT `pengeluaran_operasional_ibfk_1` FOREIGN KEY (`id_karyawan`) REFERENCES `karyawan` (`id_karyawan`);

--
-- Ketidakleluasaan untuk tabel `penggunaan_bahan_baku`
--
ALTER TABLE `penggunaan_bahan_baku`
  ADD CONSTRAINT `penggunaan_bahan_baku_ibfk_1` FOREIGN KEY (`id_perlengkapan`) REFERENCES `perlengkapan` (`id_perlengkapan`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
