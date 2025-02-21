ALTER TABLE
  `pinjaman`
ADD
  `keterangan` VARCHAR
(255) NOT NULL
AFTER
  `status`;

ALTER TABLE `pinjaman_angsuran`
ADD `keterangan` VARCHAR
(255) NOT NULL AFTER `datetime`;