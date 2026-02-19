-- phpMyAdmin SQL Dump
-- Database: `absensi_guru`

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

SET NAMES utf8mb4;

-- ==============================
-- USERS
-- ==============================

CREATE TABLE `users` (
  `id_user` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('siswa','guru','walikelas','admin') NOT NULL,
  `nama` varchar(100) NOT NULL,
  `no_telp` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_user`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ==============================
-- KELAS
-- ==============================

CREATE TABLE `kelas` (
  `id_kelas` int(11) NOT NULL AUTO_INCREMENT,
  `nama_kelas` varchar(50) NOT NULL,
  `id_walikelas` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_kelas`),
  KEY `id_walikelas` (`id_walikelas`),
  CONSTRAINT `kelas_ibfk_1`
    FOREIGN KEY (`id_walikelas`)
    REFERENCES `users` (`id_user`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ==============================
-- SISWA
-- ==============================

CREATE TABLE `siswa` (
  `id_siswa` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) NOT NULL,
  `id_kelas` int(11) NOT NULL,
  PRIMARY KEY (`id_siswa`),
  KEY `id_user` (`id_user`),
  KEY `id_kelas` (`id_kelas`),
  CONSTRAINT `siswa_ibfk_1`
    FOREIGN KEY (`id_kelas`)
    REFERENCES `kelas` (`id_kelas`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `siswa_ibfk_2`
    FOREIGN KEY (`id_user`)
    REFERENCES `users` (`id_user`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ==============================
-- JADWAL MENGAJAR
-- ==============================

CREATE TABLE `jadwal_mengajar` (
  `id_jadwal` int(11) NOT NULL AUTO_INCREMENT,
  `id_guru` int(11) NOT NULL,
  `id_kelas` int(11) NOT NULL,
  `mapel` varchar(100) NOT NULL,
  `hari` enum('Senin','Selasa','Rabu','Kamis','Jumat','Sabtu') NOT NULL,
  `jam_mulai` time NOT NULL,
  `jam_selesai` time NOT NULL,
  PRIMARY KEY (`id_jadwal`),
  KEY `id_guru` (`id_guru`),
  KEY `id_kelas` (`id_kelas`),
  CONSTRAINT `jadwal_mengajar_ibfk_1`
    FOREIGN KEY (`id_guru`)
    REFERENCES `users` (`id_user`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `jadwal_mengajar_ibfk_2`
    FOREIGN KEY (`id_kelas`)
    REFERENCES `kelas` (`id_kelas`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ==============================
-- ABSENSI GURU
-- ==============================

CREATE TABLE `absensi_guru` (
  `id_absensi_guru` int(11) NOT NULL AUTO_INCREMENT,
  `id_jadwal` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `status` enum('hadir','tidak_hadir','izin') NOT NULL,
  `diinput_oleh` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_absensi_guru`),
  UNIQUE KEY `unik_jadwal_tanggal` (`id_jadwal`,`tanggal`),
  KEY `diinput_oleh` (`diinput_oleh`),
  CONSTRAINT `absensi_guru_ibfk_1`
    FOREIGN KEY (`id_jadwal`)
    REFERENCES `jadwal_mengajar` (`id_jadwal`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `absensi_guru_ibfk_2`
    FOREIGN KEY (`diinput_oleh`)
    REFERENCES `users` (`id_user`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ==============================
-- JURNAL MENGAJAR
-- ==============================

CREATE TABLE `jurnal_mengajar` (
  `id_jurnal` int(11) NOT NULL AUTO_INCREMENT,
  `id_absensi_guru` int(11) NOT NULL,
  `materi` text NOT NULL,
  `catatan` text DEFAULT NULL,
  `diisi_oleh` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_jurnal`),
  KEY `id_absensi_guru` (`id_absensi_guru`),
  KEY `diisi_oleh` (`diisi_oleh`),
  CONSTRAINT `jurnal_mengajar_ibfk_1`
    FOREIGN KEY (`id_absensi_guru`)
    REFERENCES `absensi_guru` (`id_absensi_guru`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `jurnal_mengajar_ibfk_2`
    FOREIGN KEY (`diisi_oleh`)
    REFERENCES `users` (`id_user`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ==============================
-- ABSENSI SISWA
-- ==============================

CREATE TABLE `absensi_siswa` (
  `id_absensi_siswa` int(11) NOT NULL AUTO_INCREMENT,
  `id_jurnal` int(11) NOT NULL,
  `id_siswa` int(11) NOT NULL,
  `status` enum('hadir','izin','sakit','alpa') NOT NULL,
  PRIMARY KEY (`id_absensi_siswa`),
  UNIQUE KEY `unik_jurnal_siswa` (`id_jurnal`,`id_siswa`),
  KEY `id_siswa` (`id_siswa`),
  CONSTRAINT `absensi_siswa_ibfk_1`
    FOREIGN KEY (`id_jurnal`)
    REFERENCES `jurnal_mengajar` (`id_jurnal`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `absensi_siswa_ibfk_2`
    FOREIGN KEY (`id_siswa`)
    REFERENCES `siswa` (`id_siswa`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ==============================
-- KOMPLAIN
-- ==============================

CREATE TABLE `komplain` (
  `id_komplain` int(11) NOT NULL AUTO_INCREMENT,
  `id_siswa` int(11) NOT NULL,
  `id_jadwal` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `pesan` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_komplain`),
  KEY `id_siswa` (`id_siswa`),
  KEY `id_jadwal` (`id_jadwal`),
  CONSTRAINT `komplain_ibfk_1`
    FOREIGN KEY (`id_siswa`)
    REFERENCES `siswa` (`id_siswa`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `komplain_ibfk_2`
    FOREIGN KEY (`id_jadwal`)
    REFERENCES `jadwal_mengajar` (`id_jadwal`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

COMMIT;
